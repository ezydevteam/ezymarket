<?php

namespace App\Classes;

/**
 * Nationality Helper for EasyMarket
 *
 * Provides comprehensive nationality adjectives mapped to ISO 3166-1 alpha-2 codes.
 * Used for user profile settings and metadata.
 *
 * Features:
 * - ISO 3166-1 compliant country codes
 * - Translated nationality adjectives via translate() helper
 *
 * Usage:
 * - Nationality::all() - Get all available nationalities
 * - Nationality::get('BD') - Get specific nationality name (e.g., 'Bangladeshi')
 * - Nationality::has('BD') - Check if a code exists
 *
 * @package App\Classes
 */
class Nationality
{
    /**
     * Get all available nationality adjectives with their codes
     *
     * Returns an associative array where:
     * - Key: ISO 3166-1 alpha-2 code (2 letters)
     * - Value: Translated nationality adjective
     *
     * @return array<string, string> Nationality codes and adjectives
     */
    public static function all(): array
    {
        return [
            'AF' => translate('Afghan'),
            'AL' => translate('Albanian'),
            'DZ' => translate('Algerian'),
            'AS' => translate('American'),
            'AD' => translate('Andorran'),
            'AO' => translate('Angolan'),
            'AI' => translate('Anguillan'),
            'AQ' => translate('Antarctic'),
            'AG' => translate('Antiguan or Barbudan'),
            'AR' => translate('Argentine'),
            'AM' => translate('Armenian'),
            'AW' => translate('Aruban'),
            'AU' => translate('Australian'),
            'AT' => translate('Austrian'),
            'AZ' => translate('Azerbaijani'),
            'BS' => translate('Bahamian'),
            'BH' => translate('Bahraini'),
            'BD' => translate('Bangladeshi'),
            'BB' => translate('Barbadian'),
            'BY' => translate('Belarusian'),
            'BE' => translate('Belgian'),
            'BZ' => translate('Belizean'),
            'BJ' => translate('Beninese'),
            'BM' => translate('Bermudian'),
            'BT' => translate('Bhutanese'),
            'BO' => translate('Bolivian'),
            'BA' => translate('Bosnian or Herzegovinian'),
            'BW' => translate('Botswanan'),
            'BR' => translate('Brazilian'),
            'IO' => translate('Chagos Islander'),
            'VG' => translate('British Virgin Islander'),
            'BN' => translate('Bruneian'),
            'BG' => translate('Bulgarian'),
            'BF' => translate('Burkinabe'),
            'BI' => translate('Burundian'),
            'KH' => translate('Cambodian'),
            'CM' => translate('Cameroonian'),
            'CA' => translate('Canadian'),
            'CV' => translate('Cape Verdean'),
            'KY' => translate('Caymanian'),
            'CF' => translate('Central African'),
            'TD' => translate('Chadian'),
            'CL' => translate('Chilean'),
            'CN' => translate('Chinese'),
            'CO' => translate('Colombian'),
            'KM' => translate('Comoran'),
            'CG' => translate('Congolese'),
            'CD' => translate('Congolese'),
            'CK' => translate('Cook Islander'),
            'CR' => translate('Costa Rican'),
            'HR' => translate('Croatian'),
            'CU' => translate('Cuban'),
            'CY' => translate('Cypriot'),
            'CZ' => translate('Czech'),
            'DK' => translate('Danish'),
            'DJ' => translate('Djiboutian'),
            'DM' => translate('Dominican'),
            'DO' => translate('Dominican'),
            'EC' => translate('Ecuadorian'),
            'EG' => translate('Egyptian'),
            'SV' => translate('Salvadoran'),
            'GQ' => translate('Equatoguinean'),
            'ER' => translate('Eritrean'),
            'EE' => translate('Estonian'),
            'ET' => translate('Ethiopian'),
            'FK' => translate('Falkland Islander'),
            'FO' => translate('Faroese'),
            'FJ' => translate('Fijian'),
            'FI' => translate('Finnish'),
            'FR' => translate('French'),
            'GF' => translate('French Guianese'),
            'PF' => translate('French Polynesian'),
            'GA' => translate('Gabonese'),
            'GM' => translate('Gambian'),
            'GE' => translate('Georgian'),
            'DE' => translate('German'),
            'GH' => translate('Ghanaian'),
            'GI' => translate('Gibraltarian'),
            'GR' => translate('Greek'),
            'GL' => translate('Greenlandic'),
            'GD' => translate('Grenadian'),
            'GP' => translate('Guadeloupean'),
            'GU' => translate('Guamanian'),
            'GT' => translate('Guatemalan'),
            'GG' => translate('Guernseyman'),
            'GN' => translate('Guinean'),
            'GW' => translate('Bissau-Guinean'),
            'GY' => translate('Guyanese'),
            'HT' => translate('Haitian'),
            'HN' => translate('Honduran'),
            'HK' => translate('Hong Konger'),
            'HU' => translate('Hungarian'),
            'IS' => translate('Icelandic'),
            'IN' => translate('Indian'),
            'ID' => translate('Indonesian'),
            'IR' => translate('Iranian'),
            'IQ' => translate('Iraqi'),
            'IE' => translate('Irish'),
            'IM' => translate('Manxman'),
            'IL' => translate('Israeli'),
            'IT' => translate('Italian'),
            'JM' => translate('Jamaican'),
            'JP' => translate('Japanese'),
            'JE' => translate('Jerseyman'),
            'JO' => translate('Jordanian'),
            'KZ' => translate('Kazakhstani'),
            'KE' => translate('Kenyan'),
            'KI' => translate('I-Kiribati'),
            'KW' => translate('Kuwaiti'),
            'KG' => translate('Kyrgyz'),
            'LA' => translate('Lao'),
            'LV' => translate('Latvian'),
            'LB' => translate('Lebanese'),
            'LS' => translate('Basotho'),
            'LR' => translate('Liberian'),
            'LY' => translate('Libyan'),
            'LI' => translate('Liechtensteiner'),
            'LT' => translate('Lithuanian'),
            'LU' => translate('Luxembourgish'),
            'MO' => translate('Macanese'),
            'MK' => translate('Macedonian'),
            'MG' => translate('Malagasy'),
            'MW' => translate('Malawian'),
            'MY' => translate('Malaysian'),
            'MV' => translate('Maldivian'),
            'ML' => translate('Malian'),
            'MT' => translate('Maltese'),
            'MH' => translate('Marshallese'),
            'MQ' => translate('Martinican'),
            'MR' => translate('Mauritanian'),
            'MU' => translate('Mauritian'),
            'YT' => translate('Mahoran'),
            'MX' => translate('Mexican'),
            'FM' => translate('Micronesian'),
            'MD' => translate('Moldovan'),
            'MC' => translate('Monegasque'),
            'MN' => translate('Mongolian'),
            'ME' => translate('Montenegrin'),
            'MS' => translate('Montserratian'),
            'MA' => translate('Moroccan'),
            'MZ' => translate('Mozambican'),
            'MM' => translate('Burmese'),
            'NA' => translate('Namibian'),
            'NR' => translate('Nauruan'),
            'NP' => translate('Nepali'),
            'NL' => translate('Dutch'),
            'NC' => translate('New Caledonian'),
            'NZ' => translate('New Zealander'),
            'NI' => translate('Nicaraguan'),
            'NE' => translate('Nigerien'),
            'NG' => translate('Nigerian'),
            'NU' => translate('Niuean'),
            'NF' => translate('Norfolk Islander'),
            'KP' => translate('North Korean'),
            'NO' => translate('Norwegian'),
            'OM' => translate('Omani'),
            'PK' => translate('Pakistani'),
            'PW' => translate('Palauan'),
            'PS' => translate('Palestinian'),
            'PA' => translate('Panamanian'),
            'PG' => translate('Papua New Guinean'),
            'PY' => translate('Paraguayan'),
            'PE' => translate('Peruvian'),
            'PH' => translate('Filipino'),
            'PN' => translate('Pitcairn Islander'),
            'PL' => translate('Polish'),
            'PT' => translate('Portuguese'),
            'PR' => translate('Puerto Rican'),
            'QA' => translate('Qatari'),
            'RO' => translate('Romanian'),
            'RU' => translate('Russian'),
            'RW' => translate('Rwandan'),
            'RE' => translate('Reunionese'),
            'BL' => translate('Saint Barthelemois'),
            'SH' => translate('Saint Helenian'),
            'KN' => translate('Kittitian or Nevisian'),
            'LC' => translate('Saint Lucian'),
            'MF' => translate('Saint Martin Frenchman'),
            'PM' => translate('Saint-Pierrais or Miquelonnais'),
            'VC' => translate('Saint Vincentian'),
            'WS' => translate('Samoan'),
            'SM' => translate('Sammarinese'),
            'ST' => translate('Sao Tomean'),
            'SA' => translate('Saudi'),
            'SN' => translate('Senegalese'),
            'RS' => translate('Serbian'),
            'SC' => translate('Seychellois'),
            'SL' => translate('Sierra Leonean'),
            'SG' => translate('Singaporean'),
            'SK' => translate('Slovak'),
            'SI' => translate('Slovenian'),
            'SB' => translate('Solomon Islander'),
            'SO' => translate('Somali'),
            'ZA' => translate('South African'),
            'KR' => translate('South Korean'),
            'ES' => translate('Spanish'),
            'LK' => translate('Sri Lankan'),
            'SD' => translate('Sudanese'),
            'SR' => translate('Surinamese'),
            'SZ' => translate('Swazi'),
            'SE' => translate('Swedish'),
            'CH' => translate('Swiss'),
            'SY' => translate('Syrian'),
            'TW' => translate('Taiwanese'),
            'TJ' => translate('Tajikistani'),
            'TZ' => translate('Tanzanian'),
            'TH' => translate('Thai'),
            'TL' => translate('Timorese'),
            'TG' => translate('Togolese'),
            'TK' => translate('Tokelauan'),
            'TO' => translate('Tongan'),
            'TT' => translate('Trinidadian or Tobagonian'),
            'TN' => translate('Tunisian'),
            'TR' => translate('Turkish'),
            'TM' => translate('Turkmen'),
            'TC' => translate('Turks and Caicos Islander'),
            'TV' => translate('Tuvaluan'),
            'UG' => translate('Ugandan'),
            'UA' => translate('Ukrainian'),
            'AE' => translate('Emirati'),
            'GB' => translate('British'),
            'US' => translate('American'),
            'UY' => translate('Uruguayan'),
            'UZ' => translate('Uzbekistani'),
            'VU' => translate('Ni-Vanuatu'),
            'VA' => translate('Vatican'),
            'VE' => translate('Venezuelan'),
            'VN' => translate('Vietnamese'),
            'WF' => translate('Wallis or Futunan'),
            'EH' => translate('Sahrawi'),
            'YE' => translate('Yemeni'),
            'ZM' => translate('Zambian'),
            'ZW' => translate('Zimbabwean'),
            'AX' => translate('Aland Islander')
        ];
    }

    /**
     * Get a specific nationality adjective by its code
     *
     * @param string $code ISO 3166-1 alpha-2 code
     * @return string|null Nationality adjective or null if not found
     */
    public static function get(string $code): ?string
    {
        return self::all()[$code] ?? null;
    }

    /**
     * Check if a nationality code exists
     *
     * @param string $code Nationality code to check
     * @return bool True if nationality code exists
     */
    public static function has(string $code): bool
    {
        return array_key_exists($code, self::all());
    }

    /**
     * Get all nationality codes (without names)
     *
     * @return array<string> Array of nationality codes
     */
    public static function codes(): array
    {
        return array_keys(self::all());
    }

    /**
     * Get nationality count
     *
     * @return int Number of supported nationalities
     */
    public static function count(): int
    {
        return count(self::all());
    }
}
