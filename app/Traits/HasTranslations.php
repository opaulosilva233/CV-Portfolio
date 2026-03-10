<?php

namespace App\Traits;

use App\Models\Translation;
use Illuminate\Support\Facades\App;

trait HasTranslations
{
    /**
     * Boot the trait to add saved model event.
     */
    protected static function bootHasTranslations()
    {
        static::saved(function ($model) {
            \App\Jobs\TranslateModelJob::dispatch($model);
        });
    }

    /**
     * Get all translations for the model.
     */
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Get translated value for a field.
     */
    public function translated($field, $locale = null)
    {
        $locale = $locale ?: App::getLocale();

        if ($locale === 'pt' || current($this->getTranslatableFields() ?? []) === null) {
            return $this->{$field};
        }

        // We load translations if not loaded
        $translation = $this->translations->where('field', $field)->where('locale', $locale)->first();

        return $translation ? $translation->value : $this->{$field};
    }

    /**
     * Override toArray to automatically send translated fields to the frontend.
     */
    public function toArray()
    {
        $array = parent::toArray();
        $fields = $this->getTranslatableFields() ?? [];
        
        foreach ($fields as $field) {
            if (array_key_exists($field, $array)) {
                $array[$field] = $this->translated($field);
            }
        }
        
        return $array;
    }
}
