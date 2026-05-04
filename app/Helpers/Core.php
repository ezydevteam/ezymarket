<?php

/**
 * Core Helper Functions
 *
 * Global helper functions for the EasyMarket application.
 * Organized into logical groups for better maintainability.
 *
 * @package App\Helpers
 * @author Codebay Team
 * @version 1.0.0
 */

// ============================================================================
// THIRD-PARTY PACKAGES
// ============================================================================

use Carbon\Carbon;
use Hashids\Hashids;
use Jenssegers\Date\Date;
use Mews\Purifier\Facades\Purifier;
use Spatie\Newsletter\Facades\Newsletter;
use Intervention\Image\ImageManager;

// ============================================================================
// LARAVEL FRAMEWORK
// ============================================================================

use Illuminate\Support\Facades\{
    App,
    Auth,
    Cache,
    Config,
    Cookie,
    File,
    Request,
    Route,
    Schema,
    Storage
};
use Illuminate\Support\Str;
use App\Enums\BadgeAlias;
// ============================================================================
// APPLICATION CLASSES
// ============================================================================

use App\Classes\{
    CountryList,
    Nationality,
    Localization,
    SchemaGenerator
};

// ============================================================================
// APPLICATION MODELS
// ============================================================================

use App\Models\{
    Addon,
    Badge,
    Captcha,
    Extension,
    MailTemplate,
    Settings,
    StorageDriver,
    User
};
use App\Models\Financial\{
    Currency,
    PaymentGateway,
};
use App\Models\Support\SupportPackage;
use App\Models\{
    Translate
};

// ============================================================================
// APPLICATION METHODS
// ============================================================================

use App\Methods\{EnvManager, ThumbnailGenerator};
use App\Cache\CacheManager;

/**
 * Core Helper Functions
 *
 * Organized into logical groups:
 * - System & Configuration
 * - Authentication & Authorization
 * - Admin & Editor
 * - Settings & Extensions
 * - File Management
 * - Text & Content Processing
 * - Localization & Translation
 * - Date & Time
 * - Currency & Pricing
 * - Utilities
 * - SEO & Meta
 * - Data Formatting
 * - Routes & Navigation
 *
 * @package App\Helpers
 */

// ============================================================================
// SYSTEM & CONFIGURATION
// ============================================================================

/**
 * Check if the application is in demo mode.
 *
 * @return bool
 */
function demoMode(): bool
{
    return config('system.demo_mode');
}

/**
 * Hide sensitive content in demo mode.
 *
 * @param mixed $content
 * @return mixed
 */
function hideInDemo(mixed $content = null): mixed
{
    if ($content && config('system.demo_mode')) {
        return translate('[Hidden In Demo]');
    }
    return $content;
}

/**
 * Generate asset URL with version parameter for cache busting.
 *
 * @param string $path
 * @return string
 */
function asset_with_version(string $path): string
{
    return asset($path . '?v=' . config('system.product.version'));
}

/**
 * Set environment variable.
 *
 * @param string $key
 * @param mixed $value
 * @param bool $quote
 * @return mixed
 */

if (!function_exists('setEnv')) {
    function setEnv(string $key, mixed $value, bool $quote = false): mixed
    {
        $env = new EnvManager();
        return $env->setKey($key, $value, $quote);
    }
}

/**
 * Get application settings.
 *
 * @param string|null $key
 * @return mixed
 */
function settings(?string $key = null): mixed
{
    // Return mock settings during testing to avoid database queries before migrations
    if (app()->runningUnitTests() && !Schema::hasTable('settings')) {
        $mockSettings = (object) [
            'actions' => (object) ['force_ssl' => false, 'email_verification' => false],
            'membership' => (object) ['status' => false],
            'maintenance' => (object) ['status' => false],
            'referral' => (object) ['status' => false],
            'mail' => (object) ['status' => false],
            'general' => (object) [
                'site_name' => 'Test Site',
                'site_url' => 'http://localhost',
                'contact_email' => 'test@example.com',
                'date_format' => 0
            ]
        ];
        return $key ? ($mockSettings->$key ?? null) : $mockSettings;
    }

    if (!empty($key)) {
        return Settings::selectSettings($key);
    }
    $settings = Settings::pluck('value', 'key')->all();
    return json_decode(json_encode($settings), false);
}

// ============================================================================
// AUTHENTICATION & AUTHORIZATION
// ============================================================================

/**
 * Get the authenticated user.
 *
 * @return User|null
 */
function authUser(): ?User
{
    return Auth::user();
}

/**
 * Get the authenticated admin.
 *
 * @return mixed
 */
function authAdmin(): mixed
{
    return Auth::guard('admin')->user();
}

/**
 * Get authenticated admin if they can manage system (Admin or Manager only).
 *
 * @return mixed
 */
function systemAuthAdmin(): mixed
{
    $admin = authAdmin();
    return ($admin && $admin->canManageSystem()) ? $admin : null;
}

/**
 * Get authenticated admin if they are super admin (Admin only).
 *
 * @return mixed
 */
function superAdmin(): mixed
{
    $admin = authAdmin();
    return ($admin && $admin->isAdmin()) ? $admin : null;
}



// ============================================================================
// ADMIN & EDITOR
// ============================================================================

/**
 * Get admin path from config.
 *
 * @return string
 */
function adminPath(): string
{
    return config('system.admin.path');
}

/**
 * Generate admin URL.
 *
 * @param string|null $path
 * @return string
 */
function adminUrl(?string $path = null): string
{
    $url = url(adminPath());
    if ($path) {
        $url = $url . '/' . $path;
    }
    return $url;
}

/**
 * Get site name from settings.
 *
 * @return string
 */
function getSiteName(): string
{
    return settings('general')->site_name ?? config('app.name', 'Ezymarket');
}

/**
 * Get site logo from settings.
 *
 * @return string
 */
function getSiteLogo(string $type = 'logo_light'): string
{
    return asset(themeSettings()->general->$type ?? 'storage/logo.png');
}

/**
 * Get site favicon from settings.
 *
 * @return string
 */
function getSiteFavicon(): string
{
    return asset(themeSettings()->general->favicon ?? 'storage/favicon.png');
}

/**
 * Limit counter display (limits to +9).
 *
 * @param int $counter
 * @return string
 */
function limitCounter(int $counter, int $limit = 9): string
{
    return $counter > $limit ? '+' . $limit : (string) $counter;
}

// ============================================================================
// SETTINGS & EXTENSIONS
// ============================================================================

/**
 * Get extension by alias with caching.
 *
 * @param string $alias
 * @return Extension|null
 */
function getExtension(string $alias): ?Extension
{
    $cache = CacheManager::scope('extension_');
    return $cache->rememberForever($alias, function () use ($alias) {
        return Extension::where('alias', $alias)->first();
    });
}

/**
 * Get captcha provider by alias with caching.
 *
 * @param string $alias
 * @return Captcha|null
 */
function getCaptcha(string $alias): ?Captcha
{
    $cache = CacheManager::scope('captcha_');
    return $cache->rememberForever($alias, function () use ($alias) {
        return Captcha::where('alias', $alias)->first();
    });
}

/**
 * Get captcha validation rules for the active provider.
 *
 * @return array
 */
function captchaRules(): array
{
    return app(\App\Methods\CaptchaValidator::class)->validate();
}

/**
 * Generate addon badge HTML.
 *
 * @param string $alias
 * @return string|null
 */
function addonBadge(string $alias): ?string
{
    if (config('system.demo_mode')) {
        $addon = Addon::where('alias', $alias)->first();
        if ($addon) {
            return '<span class="badge bg-primary py-1 px-2 ms-2"><small>' . translate('Addon') . '</small></span>';
        }
    }
    return null;
}

/**
 * Check if addon is active.
 *
 * @param string $alias
 * @param string|null $version
 * @return bool
 */
function isAddonActive(string $alias, ?string $version = null): bool
{
    $addon = Addon::where('alias', $alias)->first();
    if ($addon) {
        if ($addon->hasNoStatus() || $addon->isActive()) {
            return true;
        }
    }
    return false;
}


// ============================================================================
// FILE MANAGEMENT
// ============================================================================

/**
 * Create unique filename for uploaded files.
 *
 * @param \Illuminate\Http\UploadedFile $file
 * @param string|null $name
 * @return string
 */
function createUniqueFilename($file, ?string $name = null): string
{
    if (!empty($name)) {
        $filename = $name . '.' . $file->getClientOriginalExtension();
    } else {
        $filename = Str::random(15) . '_' . time() . '.' . $file->getClientOriginalExtension();
    }
    return $filename;
}

/**
 * Upload image with optional resizing.
 *
 * @param mixed $image
 * @param string $location
 * @param string|null $size
 * @param string|null $specificName
 * @param string|null $old
 * @return string
 */
function imageUpload($image, string $location, ?string $size = null, ?string $specificName = null, ?string $old = null): string
{
    makeDirectory(public_path($location));
    if (!empty($old)) {
        removeFile(public_path($old));
    }
    $filename = createUniqueFilename($image, $specificName);

    if (!empty($size)) {
        // Use Intervention Image v3 API
        $manager = ImageManager::gd();
        $processedImage = $manager->read($image);

        // Parse size (e.g., "120x120")
        $newSize = explode('x', strtolower($size));
        $width = (int) $newSize[0];
        $height = (int) $newSize[1];

        // Resize only if dimensions are different
        if ($processedImage->width() != $width || $processedImage->height() != $height) {
            $processedImage->resize($width, $height);
        }

        // Save the processed image - v3 requires encoder instance
        $extension = strtolower($image->getClientOriginalExtension());
        if (in_array($extension, ['png'])) {
            $encoded = $processedImage->toPng();
        } elseif (in_array($extension, ['gif'])) {
            $encoded = $processedImage->toGif();
        } elseif (in_array($extension, ['webp'])) {
            $encoded = $processedImage->toWebp();
        } else {
            // Default to JPEG for jpg, jpeg and other formats
            $encoded = $processedImage->toJpeg();
        }

        file_put_contents(public_path($location . $filename), $encoded);
    } else {
        $image->move(public_path($location), $filename);
    }

    return $location . $filename;
}

/**
 * Upload file to public directory.
 *
 * @param mixed $file
 * @param string $location
 * @param string|null $specificName
 * @param string|null $old
 * @return string
 */
function fileUpload($file, string $location, ?string $specificName = null, ?string $old = null): string
{
    makeDirectory(public_path($location));
    if (!empty($old)) {
        removeFile(public_path($old));
    }
    $filename = createUniqueFilename($file, $specificName);
    $file->move(public_path($location), $filename);
    return $location . $filename;
}

/**
 * Upload file to storage disk.
 *
 * @param mixed $file
 * @param string $location
 * @param string $disk
 * @param string|null $specificName
 * @param string|null $old
 * @return string
 */
function storageFileUpload($file, string $location, string $disk, ?string $specificName = null, ?string $old = null): string
{
    if (!empty($old)) {
        removeFileFromStorage($old, $disk);
    }
    $filename = createUniqueFilename($file, $specificName);
    $filePath = $location . $filename;
    Storage::disk($disk)->put($filePath, fopen($file, 'r+'));
    return $filePath;
}

/**
 * Remove file from storage disk.
 *
 * @param string $path
 * @param string $disk
 * @return bool
 */
function removeFileFromStorage(string $path, string $disk): bool
{
    $disk = Storage::disk($disk);
    if ($disk->exists($path)) {
        $disk->delete($path);
    }
    return true;
}

/**
 * Remove file from filesystem.
 *
 * @param string $path
 * @return bool
 */
function removeFile(string $path): bool
{
    if (File::exists($path)) {
        File::delete($path);
    }
    return true;
}

/**
 * Remove directory recursively.
 *
 * @param string $path
 * @return bool
 */
function removeDirectory(string $path): bool
{
    if (File::exists($path)) {
        File::deleteDirectory($path);
    }
    return true;
}

/**
 * Create directory recursively.
 *
 * @param string $path
 * @return bool
 */
function makeDirectory(string $path): bool
{
    if (!File::exists($path)) {
        File::makeDirectory($path, 0775, true);
    }
    return true;
}

/**
 * Get storage driver by alias or default.
 *
 * @param string|null $alias
 * @return StorageDriver|null
 */
function storageDriver(?string $alias = null): ?StorageDriver
{
    if ($alias) {
        $driver = StorageDriver::where('alias', $alias)->first();
    } else {
        $driver = StorageDriver::active()->first();
    }
    return $driver;
}

/**
 * Get file URL from storage driver.
 *
 * For local storage: returns URL via /storage symlink (for public disk files ONLY)
 * For cloud storage: returns cloud URL (for public files ONLY)
 *
 * WARNING: This function should ONLY be used for public files (images, videos, audio).
 * Private files (documents, invoices) should be served through authenticated controllers.
 *
 * Security: Returns null for private files to prevent direct URL access.
 * Private files must be accessed through authenticated download routes.
 *
 * @param string $filePath
 * @return string|null Returns null if file is private/not found
 */
function storageUrl(string $filePath): ?string
{
    $driver = storageDriver();

    if (!$driver || $driver->isLocal()) {
        if (Storage::disk('public')->exists($filePath)) {
            return asset('storage/' . $filePath);
        }

        return null;
    }

    if (str_starts_with($filePath, 'private/')) {
        return null;
    }

    return $driver->credentials->url . '/' . $filePath;
}

/**
 * Read file from storage driver (local or cloud).
 *
 * For local storage: checks both 'public' disk (storage/app/public) and 'local' disk (storage/app/private)
 * For cloud storage: uses the configured cloud disk (S3, DigitalOcean, Cloudflare R2)
 *
 * @param string $filePath
 * @return string
 * @throws \Exception
 */
function readFromStorage(string $filePath): string
{
    $driver = storageDriver();

    if (!$driver || $driver->isLocal()) {
        if (Storage::disk('public')->exists($filePath)) {
            $contents = Storage::disk('public')->get($filePath);
            if ($contents !== null) {
                return $contents;
            }
        }

        if (Storage::disk('local')->exists($filePath)) {
            $contents = Storage::disk('local')->get($filePath);
            if ($contents !== null) {
                return $contents;
            }
        }
        throw new \Exception("File not found: {$filePath}");
    }

    $contents = Storage::disk($driver->alias)->get($filePath);

    if ($contents === null) {
        throw new \Exception("File not found in {$driver->alias}: {$filePath}");
    }

    return $contents;
}

/**
 * Write file to storage driver (local or cloud).
 *
 * For local storage: writes to 'public' disk (storage/app/public) or 'local' disk (storage/app/private)
 * For cloud storage: writes to root (public) or 'private/' folder based on $isPrivate flag
 *
 * @param string $filePath
 * @param string $contents
 * @param bool $isPrivate Whether file should be private (uses 'local' disk for local, 'private/' prefix for cloud)
 * @return bool
 */
function writeToStorage(string $filePath, string $contents, bool $isPrivate = false): bool
{
    $driver = storageDriver();

    if (!$driver || $driver->isLocal()) {
        $disk = $isPrivate ? 'local' : 'public';
        return Storage::disk($disk)->put($filePath, $contents);
    }

    if ($isPrivate && !str_starts_with($filePath, 'private/')) {
        $filePath = 'private/' . $filePath;
    }

    return Storage::disk($driver->alias)->put($filePath, $contents);
}

/**
 * Delete file from storage driver (local or cloud).
 *
 * For local storage: checks and deletes from 'public' disk and 'local' disk
 * For cloud storage: deletes from the configured cloud disk (supports both public and private files)
 *
 * @param string $filePath
 * @return bool
 */
function deleteFromStorage(string $filePath): bool
{
    $driver = storageDriver();

    if (!$driver || $driver->isLocal()) {
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }

        return true;
    }

    return Storage::disk($driver->alias)->delete($filePath);
}

/**
 * Check if image matches specified size.
 *
 * @param mixed $image
 * @param string $size
 * @return bool
 */
function checkImageSize($image, string $size): bool
{
    $sizeArray = explode('x', strtolower($size));
    $requiredWidth = (int) $sizeArray[0];
    $requiredHeight = (int) $sizeArray[1];

    // Use Intervention Image v3 API
    $manager = ImageManager::gd();
    $processedImage = $manager->read($image);

    return $processedImage->width() === $requiredWidth && $processedImage->height() === $requiredHeight;
}

// ============================================================================
// TEXT & CONTENT PROCESSING
// ============================================================================

/**
 * Truncate text to specified length with optional HTML stripping.
 *
 * @param string $text
 * @param int $limit
 * @param string $end
 * @param bool $stripTags
 * @return string
 */
function truncateText(string $text, int $limit = 100, string $end = '...', bool $stripTags = false): string
{
    if ($stripTags) {
        $text = strip_tags($text);
    }
    return Str::limit($text, $limit, $end);
}

/**
 * Sanitize HTML content with optional line breaks and empty handling.
 *
 * @param string $content HTML content to sanitize
 * @param bool $preserveLineBreaks Convert newlines to <br> tags
 * @param bool $allowEmpty Return empty string instead of null
 * @return string|null Sanitized content or null if empty
 */
function sanitizeHtml(string $content, bool $preserveLineBreaks = false, bool $allowEmpty = true): ?string
{
    $cleaned = Purifier::clean($content);

    if (empty($cleaned) && !$allowEmpty) {
        return null;
    }

    return $preserveLineBreaks ? nl2br($cleaned) : $cleaned;
}

/**
 * Sanitize rich text content with multiple layers of security (Defense in Depth).
 * Decodes entities, strips non-whitelisted tags, kills dangerous patterns/typos,
 * and finally purifies attributes.
 *
 * @param string|null $content
 * @return string
 */
function sanitizeRichText(?string $content): string
{
    if (empty($content)) {
        return '';
    }

    // Step 1: Strip all tags except our whitelist
    $allowedTags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><a><img><table><tr><td><th><thead><tbody><div><span><pre><code>';
    $decoded = strip_tags($content, $allowedTags);

    // Layer 3: Regex for any remaining dangerous patterns (typos, obfuscation)
    $dangerous = ['script', 'scritp', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'meta', 'link', 'base', 'applet', 'svg', 'math'];
    foreach ($dangerous as $tag) {
        $decoded = preg_replace('/<\/?' . $tag . '[^>]*>/im', '', $decoded);
    }

    // Layer 4: Final pass with HTML Purifier for attribute-level sanitization
    return Purifier::clean($decoded, 'rich_text');
}

// ============================================================================
// LOCALIZATION & TRANSLATION
// ============================================================================

/**
 * Get current locale.
 *
 * @return string
 */
function getLocale(): string
{
    return App::getLocale();
}

/**
 * Get text direction.
 *
 * @return string
 */
function getDirection(): string
{
    return config('app.direction');
}

/**
 * Translate key with replacements.
 *
 * @param string $key
 * @param array<string, mixed> $replace
 * @return string
 */
function translate(string $key, array $replace = []): string
{
    if (config('system.install.complete')) {
        $slug = sha1($key);
        $cache = CacheManager::scope('translation_');
        $translation = $cache->rememberForever($slug, function () use ($key) {
            return Translate::where('key', $key)->first();
        });

        if (!$translation) {
            $translation = new Translate();
            $translation->key = $key;
            $translation->value = $key;
            $translation->save();
            $cache->put($slug, $translation);
        }
        $translatedText = $translation->value;
    } else {
        $translatedText = $key;
    }

    foreach ($replace as $placeholder => $value) {
        $translatedText = str_replace(':' . $placeholder, $value, $translatedText);
    }

    return $translatedText;
}

/**
 * Get active languages with details.
 *
 * @return array
 */
function getActiveLanguages(): array
{
    $simpleLanguages = getLanguageSwiter();
    $languages = [];

    // Map language codes to country flags
    $flagMap = [
        'en' => 'us',
        'es' => 'es',
        'fr' => 'fr',
        'de' => 'de',
        'ar' => 'sa',
        'bn' => 'bd',
        'hi' => 'in',
        'vi' => 'vn',
        // Add more as needed
    ];

    foreach ($simpleLanguages as $code => $name) {
        $languages[$code] = [
            'name' => $name,
            'flag' => $flagMap[$code] ?? $code,
            'code' => $code
        ];
    }

    return $languages;
}

/**
 * Get available language options.
 *
 * @return array<string, string>
 */
function getLanguageSwiter(): array
{
    $langPath = base_path('lang');
    $languages = [];

    $languageNames = [
        'en' => 'English',
        'es' => 'Español',
        'fr' => 'Français',
        'de' => 'Deutsch',
        'ar' => 'العربية',
        'bn' => 'Bangla',
        'hi' => 'Hindi',
        'vi' => 'Vietnamese',
    ];

    // Check for directory-based translations
    if (is_dir($langPath)) {
        $directories = array_filter(glob($langPath . '/*'), 'is_dir');
        foreach ($directories as $dir) {
            $locale = basename($dir);
            $languages[$locale] = $languageNames[$locale] ?? strtoupper($locale);
        }
    }

    // Check for JSON-based translations
    $jsonFiles = glob($langPath . '/*.json');
    foreach ($jsonFiles as $file) {
        $locale = basename($file, '.json');
        if (!isset($languages[$locale])) {
            $languages[$locale] = $languageNames[$locale] ?? strtoupper($locale);
        }
    }

    // Fallback if nothing found
    if (empty($languages)) {
        $languages = ['en' => 'English'];
    }

    return $languages;
}

// ============================================================================
// DATE & TIME
// ============================================================================

/**
 * Format date according to settings.
 *
 * @param mixed $date
 * @param string|null $format
 * @param bool $includeTime
 * @return string
 */
function dateFormat($date, ?string $format = null, bool $includeTime = false): string
{
    if (!$date) {
        return '-';
    }

    Date::setLocale(getLocale());
    if (!$format) {
        $format = Settings::dateFormats()[@settings('general')->date_format];
    }

    if ($includeTime) {
        $format .= ' h:i a';
    }

    $dateFormat = Date::parse($date)->format($format);
    return $dateFormat;
}

/**
 * Format date according to settings.
 *
 * @param mixed $date
 * @return string
 */
function timeAgo($date): string
{
    if (!$date) {
        return '-';
    }

    Date::setLocale(getLocale());
    return Date::parse($date)->diffForHumans();
}

/**
 * Convert date from d-m-Y format to Y-m-d format.
 *
 * @param string $date Date in d-m-Y format
 * @return string Date in Y-m-d format
 */
function convertDateFormat(string $date): string
{
    return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
}

/**
 * Generate chart dates between two dates.
 *
 * @param \Carbon\Carbon $startDate
 * @param \Carbon\Carbon $endDate
 * @param string $format
 * @return \Illuminate\Support\Collection
 */
function chartDates($startDate, $endDate, string $format = 'Y-m-d')
{
    $dates = collect();
    $startDate = $startDate->copy();
    for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
        $dates->put($date->format($format), 0);
    }
    return $dates;
}

/**
 * Generate a range of months from a given date to now.
 *
 * @param \Carbon\Carbon|string $date
 * @return array
 */
function generateMonthRangeFromDate($date): array
{
    $startDate = $date instanceof Carbon ? $date : Carbon::parse($date);
    $now = Carbon::now();

    $dates = [];
    $current = $startDate->copy()->startOfMonth();

    while ($current->lte($now)) {
        $key = $current->format('Y-m');
        $value = $current->format('F Y'); // e.g., "October 2025"

        $dates[] = [
            'key' => $key,
            'value' => $value,
        ];

        $current->addMonth();
    }

    return array_reverse($dates); // Most recent first
}

/**
 * Parse date string to Carbon instance.
 *
 * @param string|null $date
 * @param string $format
 * @return \Carbon\Carbon|null
 */
function parseCarbonDate(?string $date, string $format = 'd/m/Y'): ?Carbon
{
    if (!$date) {
        return null;
    }

    try {
        return Carbon::createFromFormat($format, $date);
    } catch (\Carbon\Exceptions\InvalidFormatException $e) {
        toastr()->error(translate('Invalid date format. Please use :format format.', ['format' => $format]));
        return null;
    }
}

/**
 * Get available periods for entity.
 *
 * @param mixed $entity
 * @return \Illuminate\Support\Collection
 */
function getAvailablePeriods($entity)
{
    $daysSinceCreation = Date::now()->diffInDays($entity->created_at);

    $periods = [
        [
            "key" => "lifetime",
            "value" => translate('Lifetime'),
        ],
        [
            "key" => "last_28_days",
            "value" => translate('Last 28 Days'),
        ]
    ];

    if ($daysSinceCreation >= 90) {
        $periods[] = [
            "key" => "last_90_days",
            "value" => translate('Last 90 Days'),
        ];
    }

    if ($daysSinceCreation >= 90) {
        $periods[] = [
            "key" => "last_6_months",
            "value" => translate('Last 6 Months'),
        ];
    }

    if ($daysSinceCreation >= 365) {
        $periods[] = [
            "key" => "last_1_year",
            "value" => translate('Last 1 Year'),
        ];
    }

    $periods[] = [
        "key" => "separator",
        "value" => "──────────",
    ];

    $startMonth = Date::parse($entity->created_at)->startOfMonth();
    $currentMonth = Date::now()->startOfMonth();
    $months = [];

    while ($startMonth->lte($currentMonth)) {
        $months[] = [
            "key" => $startMonth->format('Y-m'),
            "value" => $startMonth->format('F Y'),
        ];
        $startMonth->addMonth();
    }

    $months = collect($months)->sortByDesc('key')->toArray();
    $allOptions = array_merge($periods, $months);

    return collect($allOptions);
}

// ============================================================================
// CURRENCY & PRICING
// ============================================================================

/**
 * Get currency by code, or default/first available.
 *
 * @param string|null $code Currency code (e.g., 'USD'). If null, returns default currency.
 * @return Currency
 */
function currency(?string $code = null): Currency
{
    if ($code) {
        $currency = Currency::where('code', $code)->first();
        if ($currency) {
            return $currency;
        }
    }

    // Return default currency
    $defaultCurrency = Currency::where('is_default', true)->first();

    if ($defaultCurrency) {
        return $defaultCurrency;
    }

    // Fallback to first currency
    return Currency::first();
}

/**
 * Get all currencies.
 *
 * @return \Illuminate\Database\Eloquent\Collection
 */
function currencies()
{
    return Currency::all();
}

/**
 * Get default currency.
 *
 * @return Currency
 */
function defaultCurrency(): Currency
{
    return currency();
}

/**
 * Get current user's active currency.
 * Returns currency from config (set by middleware) or falls back to default.
 *
 * @return Currency
 */
function currentCurrency(): Currency
{
    $currencyCode = config('app.currency');
    return $currencyCode ? currency($currencyCode) : defaultCurrency();
}

/**
 * Get current currency symbol.
 *
 * @return string
 */
function currency_symbol(): string
{
    return currentCurrency()->symbol;
}

/**
 * Format price to 2 decimals.
 *
 * @param float $price
 * @return string
 */
function price(float $price): string
{
    return number_format($price, 2);
}

/**
 * Format amount with custom options.
 *
 * @param float $amount
 * @param int $decimals
 * @param string $decimalSeparator
 * @param string $thousandsSeparator
 * @param bool $hideNegativeDecimals
 * @return string
 */
function amountFormat(float $amount, int $decimals = 2, string $decimalSeparator = '.', string $thousandsSeparator = '', bool $hideNegativeDecimals = false): string
{
    if ($hideNegativeDecimals && intval($amount) == $amount) {
        return number_format($amount, 0, $decimalSeparator, $thousandsSeparator);
    }

    return number_format((float) $amount, $decimals, $decimalSeparator, $thousandsSeparator);
}

/**
 * Get formatted amount in compact format (K, M, B) with currency symbol.
 *
 * @param float|null $amount
 * @param int $decimals
 * @return string
 */
function getCompactAmount(?float $amount, int $decimals = 2): string
{
    if (is_null($amount)) {
        $amount = 0;
    }

    // Get current currency from config or default
    $currencyCode = config('app.currency');
    $currentCurrency = $currencyCode ? currency($currencyCode) : defaultCurrency();
    $baseCurrency = defaultCurrency();

    // Apply currency conversion if rates differ
    if ($baseCurrency->rate !== $currentCurrency->rate) {
        $amount = $amount * $currentCurrency->rate;
    }

    $symbol = $currentCurrency->symbol;
    $compactAmount = $amount;
    $suffix = '';

    // Determine suffix based on amount
    if ($amount >= 1000000000) {
        $compactAmount = $amount / 1000000000;
        $suffix = 'B';
    } elseif ($amount >= 1000000) {
        $compactAmount = $amount / 1000000;
        $suffix = 'M';
    } elseif ($amount >= 1000) {
        $compactAmount = $amount / 1000;
        $suffix = 'K';
    }

    // Format the amount
    if ($suffix !== '') {
        $formattedAmount = number_format($compactAmount, $decimals, '.', '');
        // Remove trailing zeros after decimal point
        $formattedAmount = rtrim(rtrim($formattedAmount, '0'), '.');
    } else {
        $formattedAmount = number_format($compactAmount, 0, '.', ',');
    }

    // Position: 1 = Before price, 2 = After price
    if ($currentCurrency->position === Currency::BEFORE_PRICE) {
        return $symbol . $formattedAmount . $suffix;
    } else {
        return $formattedAmount . $suffix . $symbol;
    }
}

/**
 * Get formatted amount with currency symbol.
 *
 * @param float|null $amount
 * @param int $decimals
 * @param string $decimalSeparator
 * @param string $thousandsSeparator
 * @param bool $hideNegativeDecimals
 * @return string
 */
function getAmount(?float $amount, int $decimals = 2, string $decimalSeparator = '.', string $thousandsSeparator = ',', bool $hideNegativeDecimals = false): string
{
    if (!$amount) {
        return '0';
    }

    // Get current currency from config or default
    $currencyCode = config('app.currency');
    $currentCurrency = $currencyCode ? currency($currencyCode) : defaultCurrency();
    $baseCurrency = defaultCurrency();

    // Apply currency conversion if rates differ
    if ($baseCurrency->rate !== $currentCurrency->rate) {
        $amount = $amount * $currentCurrency->rate;
    }

    $amount = amountFormat($amount, $decimals, $decimalSeparator, $thousandsSeparator, $hideNegativeDecimals);
    $symbol = $currentCurrency->symbol;

    // Position: 1 = Before price, 2 = After price
    if ($currentCurrency->position === Currency::BEFORE_PRICE) {
        return $symbol . $amount;
    } else {
        return $amount . $symbol;
    }
}

/**
 * Get numeric amount with optional ceiling and symbol.
 *
 * @param float $amount
 * @param bool $applyCeil
 * @param int $decimals
 * @param bool $showSymbol
 * @return string|float
 */
function getNumericAmount(float $amount, bool $applyCeil = true, int $decimals = 0, bool $showSymbol = false)
{
    if (!$amount) {
        return '0';
    }

    // Get current currency from config or default
    $currencyCode = config('app.currency');
    $currentCurrency = $currencyCode ? currency($currencyCode) : defaultCurrency();
    $baseCurrency = defaultCurrency();

    // Apply currency conversion if rates differ
    if ($baseCurrency->rate !== $currentCurrency->rate) {
        $amount = $amount * $currentCurrency->rate;
    }

    if ($applyCeil) {
        $amount = ceil((float) $amount * 2) / 2;
    }

    if ($decimals > 0) {
        $amount = number_format((float) $amount, $decimals, '.', '');
    }

    $symbol = $currentCurrency->symbol;

    return $showSymbol ? $symbol . $amount : $amount;
}

/**
 * Get payment gateway by alias.
 *
 * @param string $alias
 * @return PaymentGateway|null
 */
function paymentGateway(string $alias): ?PaymentGateway
{
    return PaymentGateway::where('alias', $alias)->active()->first();
}

// ============================================================================
// UTILITIES
// ============================================================================

/**
 * Mask an email address for privacy (e.g., xyz...@gmail.com).
 *
 * @param string $email
 * @return string
 */
function maskEmail(string $email): string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }

    $parts = explode('@', $email);
    $username = $parts[0];
    $domain = $parts[1];

    $visible = substr($username, 0, 3);
    return $visible . '...@' . $domain;
}

/**
 * Get client IP address.
 *
 * @return string
 */
function getIp(): string
{
    $ip = null;
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
    } else {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $ip = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
    }
    return $ip;
}

/**
 * Get countries list or specific country.
 *
 * @param string|null $code
 * @return mixed
 */
function countries(?string $code = null)
{
    return $code ? CountryList::get($code) : CountryList::all();
}

/**
 * Get nationalities list or specific nationality.
 *
 * @param string|null $code
 * @return mixed
 */
function nationalities(?string $code = null)
{
    return $code ? Nationality::get($code) : Nationality::all();
}

/**
 * Get languages list or specific language.
 *
 * @param string|null $code
 * @return mixed
 */
function languages(?string $code = null)
{
    return $code ? Localization::get($code) : Localization::all();
}

/**
 * Get timezones list or specific timezone.
 *
 * @param string|null $code
 * @return mixed
 */
function timezones(?string $code = null)
{
    return $code ? Settings::timezone($code) : Settings::timezones();
}

/**
 * Get country flag URL.
 *
 * @param string $country
 * @return string
 */
function countryFlag(string $country): string
{
    $country = strtoupper($country);
    return "https://flagsapi.com/{$country}/flat/64.png";
}

/**
 * Encode ID to hash.
 *
 * @param int $id
 * @param int $length
 * @return string
 */
function hash_encode(int $id, int $length = 12): string
{
    $hashids = new Hashids('', $length);
    return $hashids->encode($id);
}

/**
 * Decode hash to ID.
 *
 * @param string $id
 * @param int $length
 * @return array
 */
function hash_decode(string $id, int $length = 12): array
{
    $hashids = new Hashids('', $length);
    return $hashids->decode($id);
}

/**
 * Fetch URL contents via cURL.
 *
 * @param string $URL
 * @return string|false
 */
function curl_get_file_contents(string $URL)
{
    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_URL, $URL);
    $contents = curl_exec($c);
    curl_close($c);

    if ($contents) {
        return $contents;
    } else {
        return false;
    }
}

/**
 * Track view count with cookie-based deduplication.
 *
 * @param mixed $query
 * @param string $alias
 * @return void
 */
function trackView($query, string $alias): void
{
    $key = sha1($alias);
    $viewed = Cookie::get($key) ? json_decode(Cookie::get($key), true) : [];

    if (!in_array($query->id, $viewed)) {
        $query->increment('total_views');
        $viewed[] = $query->id;
        Cookie::queue($key, json_encode($viewed), 1440);
    }
}

/**
 * Register email for newsletter.
 *
 * @param string $email
 * @return void
 */
function registerForNewsletter(string $email): void
{
    if (!demoMode()) {
        $newsletterSettings = settings('newsletter');
        Config::set('newsletter.driver_arguments.api_key', $newsletterSettings->api_key);
        Config::set('newsletter.lists.subscribers.id', $newsletterSettings->audience_id);

        if (!Newsletter::isSubscribed($email)) {
            Newsletter::subscribe($email);
        }
    }
}

// ============================================================================
// SEO & META
// ============================================================================

/**
 * Generate meta title with different formats.
 *
 * @param mixed $env Blade environment
 * @param string $format Format type: 'theme' (default) or 'admin'
 * @return string
 */
function metaTitle($env, string $format = 'theme'): string
{
    $siteName = getSiteName();
    $pageTitle = trim($env->yieldContent('title') ?: '');
    $pageSection = trim($env->yieldContent('section') ?: '');

    if ($format === 'admin' || $format === 'editor') {
        $title = $pageTitle ? ' — ' . $pageTitle : '';
        $section = $pageSection ? ' — ' . $pageSection : '';
        return $siteName . $section . $title;
    }

    // Theme format (default): "Title - Section | Site Name" or "Site Name - Title - Section" (homepage)
    $titleParts = array_filter([$pageSection, $pageTitle]);
    $combinedTitle = implode(' - ', $titleParts);

    if (Request::is('/')) {
        return $combinedTitle ? "{$siteName} - {$combinedTitle}" : $siteName;
    }

    return $combinedTitle ? "{$combinedTitle} | {$siteName}" : $siteName;
}

/**
 * Generate schema markup.
 *
 * @param mixed $__env
 * @param string|null $method
 * @param array<string, mixed> $options
 * @return mixed
 */
function schema($__env, ?string $method = null, array $options = [])
{
    return app(SchemaGenerator::class)->render($__env, $method, $options);
}

// ============================================================================
// DATA FORMATTING
// ============================================================================

/**
 * Format number with abbreviations (K, M, B, T).
 *
 * @param float $number
 * @return string
 */
function numberFormat(float $number): string
{
    if (!$number) {
        return '0';
    }

    $abbreviations = [12 => 'T', 9 => 'B', 6 => 'M', 3 => 'K', 0 => ''];
    foreach ($abbreviations as $exponent => $suffix) {
        if (abs($number) >= pow(10, $exponent)) {
            $scaledNumber = $number / pow(10, $exponent);
            $decimalPlaces = ($exponent >= 3 && $number % 1000 != 0) ? 1 : 0;
            $number = number_format($scaledNumber, $decimalPlaces) . $suffix;
            break;
        }
    }
    return (string) $number;
}

/**
 * Format bytes to human-readable file size.
 *
 * @param int|null $bytes
 * @param int $precision
 * @return string|int
 */
function formatFileSize(?int $bytes, int $precision = 2)
{
    if ($bytes) {
        $sizeUnits = [
            translate('B'),
            translate('KB'),
            translate('MB'),
            translate('GB'),
            translate('TB'),
            translate('PB')
        ];
        $unitIndex = floor(log($bytes, 1024));
        $convertedSize = round($bytes / pow(1024, $unitIndex), $precision);
        return sprintf('%s %s', $convertedSize, $sizeUnits[$unitIndex]);
    }
    return $bytes;
}

/**
 * Hex to RGBA helper
 */
function hex2rgba($color, $opacity = false)
{
    $default = 'rgb(0,0,0)';

    if (empty($color))
        return $default;

    if ($color[0] == '#') {
        $color = substr($color, 1);
    }

    if (strlen($color) == 6) {
        $hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
    } elseif (strlen($color) == 3) {
        $hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
    } else {
        return $default;
    }

    $rgb =  array_map('hexdec', $hex);

    if ($opacity) {
        if (abs($opacity) > 1)
            $opacity = 1.0;
        $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
    } else {
        $output = 'rgb(' . implode(",", $rgb) . ')';
    }

    return $output;
}

// ============================================================================
// ROUTES & NAVIGATION
// ============================================================================

/**
 * Get appropriate errors layout based on context.
 *
 * @return string
 */
function errorsLayout(): string
{
    if (config('system.install.complete')) {
        if ((request()->segment(1) == adminPath()) && authAdmin()) {
            return 'admin.layouts.errors';
        } else {
            return themeManager()->getActiveThemeViewPrefix() . '.layouts.error';
        }
    }

    return 'errors.layout';
}

/**
 * Check if currently on specified route(s).
 *
 * @param string|array<int, string> $routes
 * @return bool
 */
function isOnPage($routes): bool
{
    if (!is_array($routes)) {
        $routes = [$routes];
    }

    $currentRoute = Route::currentRouteName();

    foreach ($routes as $route) {
        if ($currentRoute === $route || str_starts_with($currentRoute, $route . '.')) {
            return true;
        }
    }

    return false;
}

// ============================================================================
// MODELS & ENTITIES
// ============================================================================

/**
 * Get mail template by alias.
 *
 * @param string $alias
 * @return MailTemplate|null
 */
function mailTemplate(string $alias): ?MailTemplate
{
    return MailTemplate::where('alias', $alias)->first();
}

/**
 * Get featured product badge.
 *
 * @return Badge|null
 */
function featuredProductBadge(): ?Badge
{
    return Badge::where('alias', BadgeAlias::FEATURED_PRODUCT)->first();
}

/**
 * Get all support packages.
 *
 * @return Collection
 */
function supportPackages()
{
    return SupportPackage::notFree()->get();
}

/**
 * Get free support package.
 *
 * @return SupportPackage|null
 */
function freeSupportPackage()
{
    return SupportPackage::free()->first() ?? null;
}

/**
 * Get default support package.
 *
 * @return SupportPackage|null
 */
function defaultSupportPackage(): ?SupportPackage
{
    return SupportPackage::free()->first() ?? null;
}

/**
 * Get ThumbnailGenerator instance
 *
 * @return ThumbnailGenerator
 */
function thumbnailGenerator(): ThumbnailGenerator
{
    return app(ThumbnailGenerator::class);
}

/**
 * Check if premium membership feature is available
 */
function isPremiumAvailable(): bool
{
    return get_license_type(2) && @settings('premium')->status;
}

/**
 * Get social media platforms configuration
 *
 * @return array
 */
function getSocialPlatforms(): array
{
    return config('socials.platforms', []);
}

/**
 * Format external URL to ensure it is absolute.
 *
 * @param string|null $url
 * @return string
 */
function formatExternalUrl(?string $url): string
{
    if (empty($url) || $url === '#') {
        return '#';
    }

    if (
        preg_match('/^(http|https|ftp|mailto|tel):/i', $url) ||
        str_starts_with($url, '//')
    ) {
        return $url;
    }

    return 'https://' . ltrim($url, '/');
}

/**
 * Format a social media URL from a username.
 *
 * @param string $platform
 * @param string|null $username
 * @return string|null
 */
function socialProfileUrl(string $platform, ?string $username): ?string
{
    if (empty($username)) {
        return null;
    }

    // If it already looks like a valid URL or protocol handler, return formatExternalUrl
    if (preg_match('/^(http|https|ftp|mailto|tel|weixin):/i', $username) || str_starts_with($username, '//') || str_contains($username, '.com/')) {
        return formatExternalUrl($username);
    }

    // Clean up username/handle
    $username = ltrim(trim($username), '@/');

    return match (strtolower($platform)) {
        'facebook' => "https://facebook.com/{$username}",
        'x', 'twitter' => "https://x.com/{$username}",
        'youtube' => "https://youtube.com/@{$username}",
        'linkedin' => "https://linkedin.com/in/{$username}",
        'instagram' => "https://instagram.com/{$username}",
        'pinterest' => "https://pinterest.com/{$username}",
        'telegram' => "https://t.me/{$username}",
        'whatsapp' => "https://wa.me/" . preg_replace('/[^0-9]/', '', $username),
        'wechat' => "weixin://dl/chat?{$username}",
        default => "https://{$platform}.com/{$username}",
    };
}

// ============================================================================
// ASSET MINIFICATION
// ============================================================================

/**
 * Minify a CSS string by stripping comments, extra whitespace and newlines.
 *
 * @param string $css
 * @return string
 */
function minifyCss(string $css): string
{
    // Remove comments
    $css = preg_replace('!/\*.*?\*/!s', '', $css);
    // Collapse whitespace
    $css = preg_replace('/\s+/', ' ', $css);
    // Remove spaces around structural characters
    $css = str_replace(
        [' { ', ' } ', '{ ', ' {', '} ', ' }', ': ', ' :', '; ', ' ;', ', ', ' ,', '> ', ' >', '~ ', ' ~', '+ ', ' +'],
        ['{',   '}',   '{',  '{',  '}',  '}',  ':',  ':',  ';',  ';',  ',',  ',',  '>',  '>',  '~',  '~',  '+',  '+'],
        $css
    );
    return trim($css);
}

/**
 * Minify a JavaScript string by stripping comments and extra whitespace.
 *
 * Note: This is a lightweight minifier for inline JS snippets only.
 * Do NOT use on complex JS with regex literals or template strings.
 *
 * @param string $js
 * @return string
 */
function minifyJs(string $js): string
{
    // Remove single-line comments (but not URLs like http://)
    $js = preg_replace('#(?<!:)//[^\n]*#', '', $js);
    // Remove multi-line comments
    $js = preg_replace('!/\*.*?\*/!s', '', $js);
    // Collapse whitespace
    $js = preg_replace('/\s+/', ' ', $js);
    // Remove spaces around structural characters
    $js = preg_replace('/\s*([{}();,=<>!&|:?+\-\/*])\s*/', '$1', $js);
    return trim($js);
}
