<?php

namespace App\Classes;

/**
 * Localization Helper for EasyMarket
 *
 * Provides comprehensive language code mapping for ISO 639-1 standard.
 * Used for translation management and multilingual support.
 *
 * Features:
 * - 184 language codes mapped to human-readable names
 * - ISO 639-1 compliant language codes
 * - Translatable language names via translate() helper
 *
 * Usage:
 * - Localization::all() - Get all available languages
 * - Localization::get('en') - Get specific language name
 * - Used in admin translation settings
 * - Used in Faker for generating localized content
 *
 * @see https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
 */
class Localization
{
    /**
     * Get all available locale codes with their names
     *
     * Returns an associative array where:
     * - Key: ISO 639-1 language code (2 letters)
     * - Value: Translated language name
     *
     * @return array<string, string> Language codes and names
     */
    public static function all(): array
    {
        return [
            'aa' => translate('Afar'),
            'ab' => translate('Abkhazian'),
            'ae' => translate('Avestan'),
            'af' => translate('Afrikaans'),
            'ak' => translate('Akan'),
            'am' => translate('Amharic'),
            'an' => translate('Aragonese'),
            'ar' => translate('Arabic'),
            'as' => translate('Assamese'),
            'av' => translate('Avaric'),
            'ay' => translate('Aymara'),
            'az' => translate('Azerbaijani'),
            'ba' => translate('Bashkir'),
            'be' => translate('Belarusian'),
            'bg' => translate('Bulgarian'),
            'bh' => translate('Bihari languages'),
            'bi' => translate('Bislama'),
            'bm' => translate('Bambara'),
            'bn' => translate('Bangla'),
            'bo' => translate('Tibetan'),
            'br' => translate('Breton'),
            'bs' => translate('Bosnian'),
            'ca' => translate('Catalan, Valencian'),
            'ce' => translate('Chechen'),
            'ch' => translate('Chamorro'),
            'co' => translate('Corsican'),
            'cr' => translate('Cree'),
            'cs' => translate('Czech'),
            'cu' => translate('Church Slavonic, Old Bulgarian, Old Church Slavonic'),
            'cv' => translate('Chuvash'),
            'cy' => translate('Welsh'),
            'da' => translate('Danish'),
            'de' => translate('German'),
            'dv' => translate('Divehi, Dhivehi, Maldivian'),
            'dz' => translate('Dzongkha'),
            'ee' => translate('Ewe'),
            'el' => translate('Greek (Modern)'),
            'en' => translate('English (US)'),
            'en_gb' => translate('English (UK)'),
            'eo' => translate('Esperanto'),
            'es' => translate('Spanish, Castilian'),
            'et' => translate('Estonian'),
            'eu' => translate('Basque'),
            'fa' => translate('Persian'),
            'ff' => translate('Fulah'),
            'fi' => translate('Finnish'),
            'fj' => translate('Fijian'),
            'fo' => translate('Faroese'),
            'fr' => translate('French'),
            'fy' => translate('Western Frisian'),
            'ga' => translate('Irish'),
            'gd' => translate('Gaelic, Scottish Gaelic'),
            'gl' => translate('Galician'),
            'gn' => translate('Guarani'),
            'gu' => translate('Gujarati'),
            'gv' => translate('Manx'),
            'ha' => translate('Hausa'),
            'he' => translate('Hebrew'),
            'hi' => translate('Hindi'),
            'ho' => translate('Hiri Motu'),
            'hr' => translate('Croatian'),
            'ht' => translate('Haitian, Haitian Creole'),
            'hu' => translate('Hungarian'),
            'hy' => translate('Armenian'),
            'hz' => translate('Herero'),
            'ia' => translate('Interlingua (International Auxiliary Language Association)'),
            'id' => translate('Indonesian'),
            'ie' => translate('Interlingue'),
            'ig' => translate('Igbo'),
            'ii' => translate('Nuosu, Sichuan Yi'),
            'ik' => translate('Inupiaq'),
            'io' => translate('Ido'),
            'is' => translate('Icelandic'),
            'it' => translate('Italian'),
            'iu' => translate('Inuktitut'),
            'ja' => translate('Japanese'),
            'jv' => translate('Javanese'),
            'ka' => translate('Georgian'),
            'kg' => translate('Kongo'),
            'ki' => translate('Gikuyu, Kikuyu'),
            'kj' => translate('Kwanyama, Kuanyama'),
            'kk' => translate('Kazakh'),
            'kl' => translate('Greenlandic, Kalaallisut'),
            'km' => translate('Central Khmer'),
            'kn' => translate('Kannada'),
            'ko' => translate('Korean'),
            'kr' => translate('Kanuri'),
            'ks' => translate('Kashmiri'),
            'ku' => translate('Kurdish'),
            'kv' => translate('Komi'),
            'kw' => translate('Cornish'),
            'ky' => translate('Kyrgyz'),
            'la' => translate('Latin'),
            'lb' => translate('Letzeburgesch, Luxembourgish'),
            'lg' => translate('Ganda'),
            'li' => translate('Limburgish, Limburgan, Limburger'),
            'ln' => translate('Lingala'),
            'lo' => translate('Lao'),
            'lt' => translate('Lithuanian'),
            'lu' => translate('Luba-Katanga'),
            'lv' => translate('Latvian'),
            'mg' => translate('Malagasy'),
            'mh' => translate('Marshallese'),
            'mi' => translate('Maori'),
            'mk' => translate('Macedonian'),
            'ml' => translate('Malayalam'),
            'mn' => translate('Mongolian'),
            'mr' => translate('Marathi'),
            'ms' => translate('Malay'),
            'mt' => translate('Maltese'),
            'my' => translate('Burmese'),
            'na' => translate('Nauru'),
            'nb' => translate('Norwegian Bokmål'),
            'nd' => translate('Northern Ndebele'),
            'ne' => translate('Nepali'),
            'ng' => translate('Ndonga'),
            'nl' => translate('Dutch, Flemish'),
            'nn' => translate('Norwegian Nynorsk'),
            'no' => translate('Norwegian'),
            'nr' => translate('South Ndebele'),
            'nv' => translate('Navajo, Navaho'),
            'ny' => translate('Chichewa, Chewa, Nyanja'),
            'oc' => translate('Occitan (post 1500)'),
            'oj' => translate('Ojibwa'),
            'om' => translate('Oromo'),
            'or' => translate('Oriya'),
            'os' => translate('Ossetian, Ossetic'),
            'pa' => translate('Panjabi, Punjabi'),
            'pi' => translate('Pali'),
            'pl' => translate('Polish'),
            'ps' => translate('Pashto, Pushto'),
            'pt' => translate('Portuguese'),
            'qu' => translate('Quechua'),
            'rm' => translate('Romansh'),
            'rn' => translate('Rundi'),
            'ro' => translate('Moldovan, Moldavian, Romanian'),
            'ru' => translate('Russian'),
            'rw' => translate('Kinyarwanda'),
            'sa' => translate('Sanskrit'),
            'sc' => translate('Sardinian'),
            'sd' => translate('Sindhi'),
            'se' => translate('Northern Sami'),
            'sg' => translate('Sango'),
            'si' => translate('Sinhala, Sinhalese'),
            'sk' => translate('Slovak'),
            'sl' => translate('Slovenian'),
            'sm' => translate('Samoan'),
            'sn' => translate('Shona'),
            'so' => translate('Somali'),
            'sq' => translate('Albanian'),
            'sr' => translate('Serbian'),
            'ss' => translate('Swati'),
            'st' => translate('Sotho, Southern'),
            'su' => translate('Sundanese'),
            'sv' => translate('Swedish'),
            'sw' => translate('Swahili'),
            'ta' => translate('Tamil'),
            'te' => translate('Telugu'),
            'tg' => translate('Tajik'),
            'th' => translate('Thai'),
            'ti' => translate('Tigrinya'),
            'tk' => translate('Turkmen'),
            'tl' => translate('Tagalog'),
            'tn' => translate('Tswana'),
            'to' => translate('Tonga (Tonga Islands)'),
            'tr' => translate('Turkish'),
            'ts' => translate('Tsonga'),
            'tt' => translate('Tatar'),
            'tw' => translate('Twi'),
            'ty' => translate('Tahitian'),
            'ug' => translate('Uighur, Uyghur'),
            'uk' => translate('Ukrainian'),
            'ur' => translate('Urdu'),
            'uz' => translate('Uzbek'),
            've' => translate('Venda'),
            'vi' => translate('Vietnamese'),
            'vo' => translate('Volap_k'),
            'wa' => translate('Walloon'),
            'wo' => translate('Wolof'),
            'xh' => translate('Xhosa'),
            'yi' => translate('Yiddish'),
            'yo' => translate('Yoruba'),
            'za' => translate('Zhuang, Chuang'),
            'zh' => translate('Chinese'),
            'zu' => translate('Zulu'),
        ];
    }

    /**
     * Get a specific language name by its code
     *
     * @param string $langCode ISO 639-1 language code (e.g., 'en', 'fr', 'de')
     * @return string|null Language name or null if not found
     */
    public static function get(string $langCode): ?string
    {
        return self::all()[$langCode] ?? null;
    }

    /**
     * Check if a language code exists
     *
     * @param string $langCode Language code to check
     * @return bool True if language code exists
     */
    public static function has(string $langCode): bool
    {
        return array_key_exists($langCode, self::all());
    }

    /**
     * Get all language codes (without names)
     *
     * @return array<string> Array of language codes
     */
    public static function codes(): array
    {
        return array_keys(self::all());
    }

    /**
     * Get language count
     *
     * @return int Number of supported languages
     */
    public static function count(): int
    {
        return count(self::all());
    }

    /**
     * Backward compatibility alias for all()
     *
     * @deprecated Use all() instead
     * @return array<string, string>
     */
    public static function availableLocale(): array
    {
        return self::all();
    }
}
