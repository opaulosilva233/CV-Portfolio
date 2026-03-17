<?php

namespace App\Services;

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

    /**
     * Translate model's specific fields.
     */
    public function translateModel($model): void
    {
        if (!method_exists($model, 'getTranslatableFields')) {
            return;
        }

        $fields = $model->getTranslatableFields();

        // Check if there's any condition, e.g. for SiteSetting to only translate certain keys
        if (method_exists($model, 'isTranslatable') && !$model->isTranslatable()) {
            return;
        }

        foreach ($fields as $field) {
            $originalText = $model->$field;

            if (empty($originalText)) {
                continue;
            }

            foreach ($this->targetLocales as $locale) {
                try {
                    $translatedText = $this->translateText($originalText, 'pt', $locale);
                    
                    if ($translatedText) {
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
                    }
                } catch (\Exception $e) {
                    Log::error("Translation failed for {$locale} on " . get_class($model) . " (ID: {$model->id}): " . $e->getMessage());
                }
            }
        }

        // Project specific logic for Gallery Metadata
        if ($model instanceof \App\Models\Project) {
            $this->translateProjectGallery($model);
        }
    }

    /**
     * Translates project gallery descriptions directly in metadata.json
     */
    protected function translateProjectGallery(\App\Models\Project $project): void
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

                // Translate only if it doesn't exist yet or if we want to force
                // (To force it we would need to check if the PT source changed, but for now we just translate if missing or different)
                // Actually, let's just translate if missing. If the user wants to force update, they can delete the key and save.
                if (!isset($fullMetadata[$locale][$imageKey]) || empty($fullMetadata[$locale][$imageKey])) {
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
