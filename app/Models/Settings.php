<?php

namespace App\Models;

use App\Classes\JsonUnicode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Settings extends JsonUnicode
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'object',
        ];
    }

    /**
     * Get settings by key.
     *
     * @param string $key
     * @return mixed
     */
    public static function selectSettings(string $key): mixed
    {
        $setting = self::where('key', $key)->first();

        return $setting?->value ?? false;
    }

    /**
     * Update settings by key with provided data.
     *
     * @param string $key
     * @param array $data
     * @return bool
     */
    public static function updateSettings(string $key, array $data): bool
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        $currentSettings = (array) $setting->value;
        $setting->value = array_merge(
            $currentSettings,
            array_intersect_key($data, $currentSettings)
        );

        return $setting->save();
    }

    /**
     * Get watermark position options.
     *
     * @return array<string, string>
     */
    public static function watermarkOptions(): array
    {
        return [
            'fill' => translate('Fill'),
            'top-left' => translate('Top Left'),
            'top' => translate('Top'),
            'top-right' => translate('Top Right'),
            'left' => translate('Left'),
            'center' => translate('Center'),
            'right' => translate('Right'),
            'bottom-left' => translate('Bottom Left'),
            'bottom' => translate('Bottom'),
            'bottom-right' => translate('Bottom Right'),
        ];
    }

    /**
     * Get available date format options.
     * Returns format strings as values for backward compatibility.
     *
     * @return array<string, string> Numeric key => format string
     */
    public static function dateFormats(): array
    {
        return [
            // Date only formats
            '0' => 'm-d-Y',
            '1' => 'd-m-Y',
            '2' => 'm/d/Y',
            '3' => 'd/m/Y',
            '4' => 'Y-m-d',
            '5' => 'Y/m/d',
            '6' => 'd.m.Y',

            // Short month formats
            '7' => 'M d, Y',
            '8' => 'd M, Y',

            // Full month formats
            '9' => 'F d, Y',
            '10' => 'd F, Y',

            // Date with 12-hour time
            '11' => 'm-d-Y h:i A',
            '12' => 'd-m-Y h:i A',
            '13' => 'm/d/Y h:i A',
            '14' => 'd/m/Y h:i A',
            '15' => 'M d, Y h:i A',
            '16' => 'F d, Y h:i A',
            '17' => 'd M, Y h:i A',
            '18' => 'd F, Y h:i A',

            // Date with 24-hour time
            '19' => 'Y-m-d H:i',
            '20' => 'Y/m/d H:i',
            '21' => 'd.m.Y - H:i',
            '22' => 'M d, Y H:i',
            '23' => 'F d, Y H:i',

            // Date with seconds
            '24' => 'Y-m-d H:i:s',
            '25' => 'Y/m/d H:i:s',
        ];
    }

    /**
     * Get a single timezone formatted name.
     *
     * @param string $code
     * @return string|null
     */
    public static function timezone(string $code): ?string
    {
        $timezones = self::timezones();
        return $timezones[$code] ?? null;
    }

    /**
     * Get available timezone options dynamically from PHP's native timezone list.
     * Generates formatted timezone strings with GMT offset and timezone identifier.
     *
     * @return array<string, string>
     */
    public static function timezones(): array
    {
        $timezones = [];

        foreach (timezone_identifiers_list() as $timezone) {
            try {
                // Create Carbon instance for the timezone to get current offset
                $carbon = Carbon::now($timezone);
                $offset = $carbon->format('P'); // Format: +05:00 or -08:00

                // Clean up the timezone name (replace underscores and dashes with spaces)
                $cleanName = str_replace(['_', '-'], ' ', $timezone);

                // Format: (GMT+05:00) Asia/Kolkata
                $timezones[$timezone] = "(GMT{$offset}) {$cleanName}";
            } catch (\Exception $e) {
                // Fallback for invalid timezones
                $timezones[$timezone] = str_replace('_', ' ', $timezone);
            }
        }

        return $timezones;
    }
}
