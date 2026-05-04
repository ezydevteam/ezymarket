<?php

namespace App\Classes;

class GoogleFonts
{
    /**
     * Get a list of popular Google Fonts.
     *
     * @return array
     */
    public static function getList(): array
    {
        return [
            "Roboto" => "'Roboto', sans-serif",
            "Open Sans" => "'Open Sans', sans-serif",
            "Lato" => "'Lato', sans-serif",
            "Montserrat" => "'Montserrat', sans-serif",
            "Oswald" => "'Oswald', sans-serif",
            "Source Sans Pro" => "'Source Sans Pro', sans-serif",
            "Slabo 27px" => "'Slabo 27px', serif",
            "Raleway" => "'Raleway', sans-serif",
            "PT Sans" => "'PT Sans', sans-serif",
            "Merriweather" => "'Merriweather', serif",
            "Noto Sans" => "'Noto Sans', sans-serif",
            "Nunito" => "'Nunito', sans-serif",
            "Concert One" => "'Concert One', cursive",
            "Prompt" => "'Prompt', sans-serif",
            "Work Sans" => "'Work Sans', sans-serif",
            "Poppins" => "'Poppins', sans-serif",
            "Inter" => "'Inter', sans-serif",
            "Maven Pro" => "'Maven Pro', sans-serif",
            "Roboto Condensed" => "'Roboto Condensed', sans-serif",
            "Roboto Mono" => "'Roboto Mono', monospace",
            "Ubuntu" => "'Ubuntu', sans-serif",
            "Playfair Display" => "'Playfair Display', serif",
            "Lora" => "'Lora', serif",
            "Mulish" => "'Mulish', sans-serif",
            "Titillium Web" => "'Titillium Web', sans-serif",
            "Quicksand" => "'Quicksand', sans-serif",
            "Inconsolata" => "'Inconsolata', monospace",
            "Crimson Text" => "'Crimson Text', serif",
            "Dosis" => "'Dosis', sans-serif",
            "Bitter" => "'Bitter', serif",
            "Cabin" => "'Cabin', sans-serif",
            "Fjalla One" => "'Fjalla One', sans-serif",
            "Indie Flower" => "'Indie Flower', cursive",
            "Anton" => "'Anton', sans-serif",
            "Lobster" => "'Lobster', cursive",
            "Arvo" => "'Arvo', serif",
            "Noto Serif" => "'Noto Serif', serif",
            "Nunito Sans" => "'Nunito Sans', sans-serif",
            "Josefin Sans" => "'Josefin Sans', sans-serif",
            "Pacifico" => "'Pacifico', cursive",
            "Dancing Script" => "'Dancing Script', cursive",
            "Exo 2" => "'Exo 2', sans-serif",
            "Karla" => "'Karla', sans-serif",
            "Barlow" => "'Barlow', sans-serif",

            // Specialized / International Scripts
            "Cairo" => "'Cairo', sans-serif", // Arabic
            "Noto Sans Arabic" => "'Noto Sans Arabic', sans-serif", // Arabic
            "Noto Sans Bengali" => "'Noto Sans Bengali', sans-serif", // Bangla
            "Hind" => "'Hind', sans-serif", // Hindi
            "Noto Sans Devanagari" => "'Noto Sans Devanagari', sans-serif", // Hindi
            "Noto Sans SC" => "'Noto Sans SC', sans-serif", // Chinese (Simplified)
            "Noto Sans TC" => "'Noto Sans TC', sans-serif", // Chinese (Traditional)
            "Noto Nastaliq Urdu" => "'Noto Nastaliq Urdu', serif", // Urdu
            "Tajawal" => "'Tajawal', sans-serif", // Arabic
            "Hind Siliguri" => "'Hind Siliguri', sans-serif", // Bangla

            // Hebrew
            "Heebo" => "'Heebo', sans-serif",
            "Rubik" => "'Rubik', sans-serif",
            "Assistant" => "'Assistant', sans-serif",
            "Frank Ruhl Libre" => "'Frank Ruhl Libre', serif",

            // Vietnamese / Extended Latin (France, Portugal, Turkey)
            "Be Vietnam Pro" => "'Be Vietnam Pro', sans-serif",
            "EB Garamond" => "'EB Garamond', serif",
            "Fira Sans" => "'Fira Sans', sans-serif",
            "Kanit" => "'Kanit', sans-serif", // Turkish support + Thai
            "Manrope" => "'Manrope', sans-serif",
            "Mukta" => "'Mukta', sans-serif",

            // Japanese
            "Noto Sans JP" => "'Noto Sans JP', sans-serif",
            "Sawarabi Mincho" => "'Sawarabi Mincho', serif",

            // Korean
            "Noto Sans KR" => "'Noto Sans KR', sans-serif",
            "Nanum Gothic" => "'Nanum Gothic', sans-serif",

            // Greek
            "Comfortaa" => "'Comfortaa', cursive",

            // Cyrillic
            "Russo One" => "'Russo One', sans-serif",

            // Tamil
            "Noto Sans Tamil" => "'Noto Sans Tamil', sans-serif",
            "Hind Madurai" => "'Hind Madurai', sans-serif",

            // Gujarati
            "Noto Sans Gujarati" => "'Noto Sans Gujarati', sans-serif",
            "Hind Vadodara" => "'Hind Vadodara', sans-serif",

            // Malayalam
            "Noto Sans Malayalam" => "'Noto Sans Malayalam', sans-serif",
            "Manjari" => "'Manjari', sans-serif",

            // Telugu
            "Noto Sans Telugu" => "'Noto Sans Telugu', sans-serif",
            "Ramabhadra" => "'Ramabhadra', sans-serif",

            // Galada
            "Galada" => "'Galada', cursive",
        ];
    }

    /**
     * Get a list of common system safe fonts.
     *
     * @return array
     */
    public static function getSystemFonts(): array
    {
        return [
            "Arial" => "Arial, sans-serif",
            "Times New Roman" => "'Times New Roman', serif",
            "Courier New" => "'Courier New', monospace",
            "Georgia" => "Georgia, serif",
            "Verdana" => "Verdana, sans-serif",
            "Impact" => "Impact, sans-serif",
            "Trebuchet MS" => "'Trebuchet MS', sans-serif",
        ];
    }

    /**
     * Get all available fonts (System + Google).
     *
     * @return array
     */
    public static function getAll(): array
    {
        return array_merge(self::getSystemFonts(), self::getList());
    }

    /**
     * Get valid weights for specific fonts string.
     * Returns standard heavy weights (300-700) if not specified.
     */
    private static function getWeightsForFont(string $fontName): string
    {
        $singleWeights = [
            'Slabo 27px' => '400',
            'Concert One' => '400',
            'Fjalla One' => '400',
            'Anton' => '400',
            'Lobster' => '400',
            'Pacifico' => '400',
            'Abril Fatface' => '400',
            'Bebas Neue' => '400',
            'Shadows Into Light' => '400',
            'Sawarabi Mincho' => '400',
            'Russo One' => '400',
            'Ramabhadra' => '400',
            'Galada' => '400',
        ];

        // Fonts with specific limited weights
        $limitedWeights = [
            'Noto Nastaliq Urdu' => '400;500;600;700',
            'Indie Flower' => '400',
            'Dancing Script' => '400;500;600;700',
            'Crimson Text' => '400;600;700',
            'Arvo' => '400;700',
            'EB Garamond' => '400;500;600;700;800',
            'Source Sans Pro' => '300;400;600;700',
            'Titillium Web' => '300;400;600;700',
            'Tajawal' => '300;400;500;700',
            'PT Sans' => '400;700',
            'Cabin' => '400;500;600;700',
            'Noto Sans SC' => '100;300;400;500;600;700;900',
            'Noto Sans TC' => '100;300;400;500;600;700;900',
            'Nanum Gothic' => '400;700;800',
            'Manjari' => '100;400;700',
            'Noto Sans JP' => '100;300;400;500;700;900',
            'Noto Sans KR' => '100;300;400;500;700;900',
        ];

        if (array_key_exists($fontName, $singleWeights)) {
            return $singleWeights[$fontName];
        }

        if (array_key_exists($fontName, $limitedWeights)) {
            return $limitedWeights[$fontName];
        }

        // Default range for most modern variable/multi-weight fonts
        // We include 300(Light), 400(Regular), 500(Medium), 600(SemiBold), 700(Bold)
        return '300;400;500;600;700';
    }

    /**
     * Generate the Google Fonts URL for the given fonts.
     * This can be used to load the fonts in the frontend/preview.
     *
     * @param array|string $fonts Array of font names or a single font name.
     * @return string|null
     */
    public static function getLink(array|string $fonts): ?string
    {
        if (is_string($fonts)) {
            $fonts = [$fonts];
        }

        $googleFonts = array_keys(self::getList());
        $fontsToLoad = [];

        foreach ($fonts as $font) {
            // Check if the font is in our Google Fonts list
            // We use the key (Name) to check, not the CSS value
            $fontName = self::identifyFontName($font);

            if ($fontName && in_array($fontName, $googleFonts)) {
                $fontsToLoad[] = $fontName;
            }
        }

        if (empty($fontsToLoad)) {
            return null;
        }

        $fontsToLoad = array_unique($fontsToLoad);
        $queryParts = [];

        foreach ($fontsToLoad as $f) {
            $weights = self::getWeightsForFont($f);
            $queryParts[] = "family=" . urlencode($f) . ":wght@" . $weights;
        }

        $queryString = implode('&', $queryParts);

        return "https://fonts.googleapis.com/css2?{$queryString}&display=swap";
    }

    /**
     * Helper to extract the simple font name from a CSS definition like "'Roboto', sans-serif"
     * or just return the name if it's already simple.
     */
    private static function identifyFontName($cssValue)
    {
        // If it's just "Roboto", return it.
        // If it's "'Roboto', sans-serif", extract "Roboto".

        // Remove generic families
        $cleaned = str_replace([", sans-serif", ", serif", ", monospace", ", cursive", "'", '"'], "", $cssValue);
        return trim($cleaned);
    }
}
