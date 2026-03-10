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
