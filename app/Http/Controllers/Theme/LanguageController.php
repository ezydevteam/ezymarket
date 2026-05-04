<?php

namespace App\Http\Controllers\Theme;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        $availableLocales = $this->getAvailableLocales();

        if (!in_array($locale, $availableLocales)) {
            abort(404);
        }

        Session::put('locale', $locale);
        return redirect()->back();
    }

    private function getAvailableLocales()
    {
        $langPath = base_path('lang');
        $locales = [];

        if (is_dir($langPath)) {
            $directories = array_filter(glob($langPath . '/*'), 'is_dir');
            foreach ($directories as $dir) {
                $locales[] = basename($dir);
            }

            // Check JSON files
            $jsonFiles = glob($langPath . '/*.json');
            foreach ($jsonFiles as $file) {
                $locale = basename($file, '.json');
                if (!in_array($locale, $locales)) {
                    $locales[] = $locale;
                }
            }
        }

        return $locales ?: ['en'];
    }
}



















