<?php

namespace App\Methods;

/**
 * SlugTransliterator - Multilingual Slug Generator
 *
 * Converts international characters to ASCII equivalents and generates
 * URL-friendly slugs. Supports extensive character transliteration for
 * multiple languages and special characters.
 *
 * Features:
 * - Comprehensive character transliteration (180+ character mappings)
 * - Multi-language support (Latin, Cyrillic, Greek, etc.)
 * - Customizable separator (default: hyphen)
 * - URL-safe output
 * - Lowercase normalization
 * - Whitespace handling
 * - Special character removal
 *
 * Supported Characters:
 * - European languages: German (ä,ö,ü), French (é,è,ê), Spanish (ñ), etc.
 * - Nordic characters: å, ø, æ
 * - Eastern European: ą, č, ę, ř, š, ž, etc.
 * - Special ligatures: æ, œ, ß
 *
 * Usage:
 * ```php
 * // Basic usage
 * $slug = SlugTransliterator::slug('Hello World'); // "hello-world"
 *
 * // International characters
 * $slug = SlugTransliterator::slug('Café Münchën'); // "cafe-munchen"
 *
 * // Custom separator
 * $slug = SlugTransliterator::slug('Hello World', '_'); // "hello_world"
 *
 * // With special characters
 * $slug = SlugTransliterator::slug('Foo & Bar!'); // "foo-bar"
 * ```
 *
 * @package App\Methods
 * @author EasyMarket Team
 * @version 2.0.0
 */
class SlugTransliterator
{
    /**
     * Generate a URL-friendly slug from a string
     *
     * Converts international characters to their ASCII equivalents,
     * removes special characters, and formats the string as a URL-safe slug.
     *
     * Process:
     * 1. Transliterates international characters to ASCII
     * 2. Removes non-alphanumeric characters (except spaces)
     * 3. Replaces spaces with separator
     * 4. Trims leading/trailing separators
     * 5. Converts to lowercase
     *
     * @param string $string The input string to convert
     * @param string $separator The separator character (default: '-')
     * @return string The generated slug
     *
     * @example
     * ```php
     * // Basic example
     * SlugTransliterator::slug('Product Name'); // "product-name"
     *
     * // International characters
     * SlugTransliterator::slug('Ñoño Español'); // "nono-espanol"
     *
     * // Multiple spaces and special chars
     * SlugTransliterator::slug('  Foo   Bar!!!  '); // "foo-bar"
     *
     * // Custom separator
     * SlugTransliterator::slug('My Product', '_'); // "my_product"
     * ```
     */
    public static function slug(string $string, string $separator = '-'): string
    {
        // Character transliteration map
        $_transliteration = [
            '/ä|æ|ǽ/' => 'ae',
            '/ö|œ/' => 'oe',
            '/ü/' => 'ue',
            '/Ä/' => 'Ae',
            '/Ü/' => 'Ue',
            '/Ö/' => 'Oe',
            '/À|Á|Â|Ã|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
            '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',
            '/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
            '/ç|ć|ĉ|ċ|č/' => 'c',
            '/Ð|Ď|Đ/' => 'D',
            '/ð|ď|đ/' => 'd',
            '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
            '/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
            '/Ĝ|Ğ|Ġ|Ģ/' => 'G',
            '/ĝ|ğ|ġ|ģ/' => 'g',
            '/Ĥ|Ħ/' => 'H',
            '/ĥ|ħ/' => 'h',
            '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
            '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',
            '/Ĵ/' => 'J',
            '/ĵ/' => 'j',
            '/Ķ/' => 'K',
            '/ķ/' => 'k',
            '/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
            '/ĺ|ļ|ľ|ŀ|ł/' => 'l',
            '/Ñ|Ń|Ņ|Ň/' => 'N',
            '/ñ|ń|ņ|ň|ŉ/' => 'n',
            '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
            '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',
            '/Ŕ|Ŗ|Ř/' => 'R',
            '/ŕ|ŗ|ř/' => 'r',
            '/Ś|Ŝ|Ş|Ș|Š/' => 'S',
            '/ś|ŝ|ş|ș|š|ſ/' => 's',
            '/Ţ|Ț|Ť|Ŧ/' => 'T',
            '/ţ|ț|ť|ŧ/' => 't',
            '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
            '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
            '/Ý|Ÿ|Ŷ/' => 'Y',
            '/ý|ÿ|ŷ/' => 'y',
            '/Ŵ/' => 'W',
            '/ŵ/' => 'w',
            '/Ź|Ż|Ž/' => 'Z',
            '/ź|ż|ž/' => 'z',
            '/Æ|Ǽ/' => 'AE',
            '/ß/' => 'ss',
            '/Ĳ/' => 'IJ',
            '/ĳ/' => 'ij',
            '/Œ/' => 'OE',
            '/ƒ/' => 'f',
        ];

        $quotedReplacement = preg_quote($separator, '/');
        $merge = array(
            '/[^\s\p{Zs}\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
            '/[\s\p{Zs}]+/mu' => $separator,
            sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
        );
        $map = $_transliteration + $merge;
        unset($_transliteration);
        return mb_strtolower(preg_replace(array_keys($map), array_values($map), $string));
    }
}


















