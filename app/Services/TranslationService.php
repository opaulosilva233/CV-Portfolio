<?php

namespace App\Services;

use App\Models\Education;
use App\Models\ExperienceRole;
use App\Models\Interest;
use App\Models\PageSection;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\Translation;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Target locales to translate into.
     * Original is 'pt'.
     */
    protected array $targetLocales = ['en', 'nl'];
    protected string $sourceLocale = 'pt';

    public function getTargetLocales(): array
    {
        return $this->targetLocales;
    }

    public function getSourceLocale(): string
    {
        return $this->sourceLocale;
    }

    /**
     * Translate model's specific fields or all translatable fields.
     */
    public function translateModel($model, bool $force = false): void
    {
        if (!method_exists($model, 'getTranslatableFields')) {
            return;
        }

        $fields = $model->getTranslatableFields();

        // Check if there's any condition, e.g. for SiteSetting to only translate certain keys
        if (method_exists($model, 'isTranslatable') && !$model->isTranslatable()) {
            return;
        }

        $this->translateSpecificFields($model, $fields, $this->targetLocales, $force);

        // Project specific logic for Gallery Metadata
        if ($model instanceof \App\Models\Project) {
            $this->translateProjectGallery($model, $force);
        }

        $this->refreshTranslatedContentCaches($model);
    }

    /**
     * Translate only specific fields of a model to specific target locales.
     */
    public function translateSpecificFields($model, array $fields, array $locales = [], bool $force = false): array
    {
        $locales = empty($locales) ? $this->targetLocales : $locales;
        $results = ['success' => 0, 'errors' => 0];

        foreach ($fields as $field) {
            // Get original value directly from raw model attribute to avoid translated accessor
            $originalText = $model->getRawOriginal($field) ?? $model->$field;

            if (empty($originalText)) {
                continue;
            }

            foreach ($locales as $locale) {
                // Do not translate if target locale is same as source locale
                if ($locale === $this->sourceLocale) {
                    continue;
                }

                // If not forcing, skip if translation already exists and is not empty
                if (!$force) {
                    $existing = Translation::where('translatable_type', get_class($model))
                        ->where('translatable_id', $model->id)
                        ->where('field', $field)
                        ->where('locale', $locale)
                        ->first();

                    if ($existing && !empty(trim($existing->value))) {
                        continue;
                    }
                }

                try {
                    $translatedText = $this->translateText($originalText, $this->sourceLocale, $locale);
                    
                    if ($translatedText !== null && trim($translatedText) !== '') {
                        Translation::updateOrCreate(
                            [
                                'translatable_type' => get_class($model),
                                'translatable_id' => $model->id,
                                'field' => $field,
                                'locale' => $locale,
                            ],
                            [
                                'value' => $translatedText,
                            ]
                        );
                        $results['success']++;
                    } else {
                        $results['errors']++;
                    }
                } catch (\Exception $e) {
                    Log::error("Translation failed for {$locale} on " . get_class($model) . " (ID: {$model->id}): " . $e->getMessage());
                    $results['errors']++;
                }
            }
        }

        $this->refreshTranslatedContentCaches($model);

        return $results;
    }

    /**
     * Translate all translatable models in the entire application.
     */
    public function translateAll(bool $force = false): array
    {
        $summary = [
            'total_models' => 0,
            'success' => 0,
            'errors' => 0,
        ];

        $modelsToTranslate = [
            SiteSetting::class => SiteSetting::all(),
            PageSection::class => PageSection::all(),
            ExperienceRole::class => ExperienceRole::all(),
            Education::class => Education::all(),
            Skill::class => Skill::all(),
            Project::class => Project::all(),
            Interest::class => Interest::all(),
        ];

        foreach ($modelsToTranslate as $modelClass => $records) {
            foreach ($records as $record) {
                $summary['total_models']++;
                try {
                    $this->translateModel($record, $force);
                    $summary['success']++;
                } catch (\Exception $e) {
                    Log::error("Bulk translation error on {$modelClass} ID {$record->id}: " . $e->getMessage());
                    $summary['errors']++;
                }
            }
        }

        $this->clearAllPortfolioCaches();

        return $summary;
    }

    /**
     * Clear all portfolio-related caches.
     */
    public function clearAllPortfolioCaches(): void
    {
        \App\Models\SiteSetting::clearCache();
        \App\Models\Project::clearPortfolioCache();
        \App\Models\Skill::clearPortfolioCache();
        \App\Models\Experience::clearPortfolioCache();
        \App\Models\Education::clearPortfolioCache();
        \App\Models\Interest::clearPortfolioCache();
        \App\Models\PageSection::clearPortfolioCache();
    }

    /**
     * Refresh caches that can mask freshly created translations.
     */
    public function refreshTranslatedContentCaches(object $model): void
    {
        if ($model instanceof \App\Models\SiteSetting) {
            \App\Models\SiteSetting::clearCache();
        }

        // Portfolio aggregates are cached for 1 hour and should be invalidated after translating.
        if (method_exists($model, 'clearPortfolioCache')) {
            $model::clearPortfolioCache();
        }
    }

    /**
     * Translates project gallery descriptions directly in metadata.json
     */
    public function translateProjectGallery(\App\Models\Project $project, bool $force = false): void
    {
        $dir = storage_path('projects/' . $project->id);
        $metadataPath = $dir . '/metadata.json';
        
        if (!file_exists($metadataPath)) {
            return;
        }

        $fullMetadata = json_decode(file_get_contents($metadataPath), true);
        
        // Assume 'pt' is the source of truth for translations if it exists
        $sourceLocale = 'pt';
        if (!isset($fullMetadata[$sourceLocale]) || empty($fullMetadata[$sourceLocale])) {
            return;
        }

        $hasChanges = false;

        foreach ($fullMetadata[$sourceLocale] as $imageKey => $description) {
            if (empty($description)) continue;

            foreach ($this->targetLocales as $locale) {
                if ($locale === $sourceLocale) continue;

                if (!isset($fullMetadata[$locale])) {
                    $fullMetadata[$locale] = [];
                }

                if ($force || !isset($fullMetadata[$locale][$imageKey]) || empty($fullMetadata[$locale][$imageKey])) {
                    try {
                        $translatedText = $this->translateText($description, $sourceLocale, $locale);
                        if ($translatedText) {
                            $fullMetadata[$locale][$imageKey] = $translatedText;
                            $hasChanges = true;
                        }
                    } catch (\Exception $e) {
                         Log::error("Gallery Translation failed for {$locale} on Project (ID: {$project->id}) image {$imageKey}: " . $e->getMessage());
                    }
                }
            }
        }

        if ($hasChanges) {
            \Illuminate\Support\Facades\File::put($metadataPath, json_encode($fullMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Translate a string using multiple high-reliability translation engines.
     */
    public function translateText(string $text, ?string $source = 'pt', string $target = 'en'): ?string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        // If target is same as source, no translation needed
        if ($source && $source === $target) {
            return $text;
        }

        // Engine 1: Google Translate GTX Endpoint (Ultra-fast, direct)
        try {
            $sl = ($source && $source !== 'auto') ? $source : 'auto';
            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                    'Accept' => 'application/json',
                ])
                ->get('https://translate.googleapis.com/translate_a/single', [
                    'client' => 'gtx',
                    'sl' => $sl,
                    'tl' => $target,
                    'dt' => 't',
                    'q' => $text,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data[0]) && is_array($data[0])) {
                    $translated = '';
                    foreach ($data[0] as $segment) {
                        if (isset($segment[0]) && is_string($segment[0])) {
                            $translated .= $segment[0];
                        }
                    }
                    if (!empty(trim($translated))) {
                        return trim($translated);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("Google GTX Translation failed for ({$source}->{$target}): " . $e->getMessage());
        }

        // Engine 2: Stichoza GoogleTranslate library
        try {
            $tr = new GoogleTranslate();
            $tr->setSource($source ?: 'pt');
            $tr->setTarget($target);
            $tr->setOptions([
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ],
                'timeout' => 8,
            ]);
            $translated = $tr->translate($text);
            if (!empty(trim($translated))) {
                return trim($translated);
            }
        } catch (\Exception $e) {
            Log::warning("Stichoza GoogleTranslate failed for ({$source}->{$target}): " . $e->getMessage());
        }

        // Engine 3: MyMemory Free Translation API
        try {
            $langpair = ($source ?: 'pt') . '|' . $target;
            $response = Http::timeout(8)
                ->get('https://api.mymemory.translated.net/get', [
                    'q' => $text,
                    'langpair' => $langpair,
                ]);

            if ($response->successful()) {
                $json = $response->json();
                $translated = $json['responseData']['translatedText'] ?? null;
                if (!empty(trim($translated)) && !str_starts_with(strtoupper($translated), 'MYMEMORY WARNING')) {
                    return trim($translated);
                }
            }
        } catch (\Exception $e) {
            Log::warning("MyMemory Translation failed for ({$source}->{$target}): " . $e->getMessage());
        }

        Log::error("All translation engines failed for text: '{$text}' ({$source}->{$target})");
        return null;
    }
}
