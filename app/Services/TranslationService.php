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
            $originalText = $model->$field;

            if (empty($originalText)) {
                continue;
            }

            foreach ($locales as $locale) {
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
                    
                    if ($translatedText !== null) {
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
     * Translate a string using Google Translate.
     */
    public function translateText(string $text, string $source, string $target): ?string
    {
        try {
            if (trim($text) === '') {
                return '';
            }

            $tr = new GoogleTranslate();
            $tr->setSource($source);
            $tr->setTarget($target);
            
            return $tr->translate($text);
        } catch (\Exception $e) {
            Log::error("Google Translate Error ({$source}->{$target}): " . $e->getMessage());
            return null;
        }
    }
}
