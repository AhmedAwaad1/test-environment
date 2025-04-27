<?php
namespace App\Traits;

use Illuminate\Http\Request;

trait TranslatableResource
{
    public function getTranslatedName($locale = null)
    {
        $locale = $locale ?: request()->header('Accept-Language') ?? null;

        if ($locale === 'ar') {
            return $this->name_ar; // return the name in Arabic
        } elseif ($locale === 'en') {
            return $this->name_en; // return the name in English
        }

        // if no language is specified, return both languages
        return [
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
        ];
    }
}
