<?php

namespace App\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\SchemaOrg\Schema;
use Throwable;

/**
 * Schema.org JSON-LD Generator for EasyMarket
 *
 * Generates structured data markup for better SEO and rich snippets in search results.
 * Supports multiple schema types: Organization, Website, WebPage, Article, Product.
 *
 * Schema Types:
 * - Default: Organization + Website + WebPage (every page)
 * - Article: Article schema (blog posts, news)
 * - Product: Product + Offer + AggregateRating (product pages)
 *
 * Usage:
 * - Called via schema() helper function
 * - Automatically rendered in layout templates
 * - Validates data before generating schemas
 *
 * SEO Benefits:
 * - Rich snippets in search results (star ratings, prices, images)
 * - Knowledge graph eligibility for Google
 * - Enhanced CTR from search engines
 * - Voice search optimization
 *
 * @see https://schema.org/
 * @see https://developers.google.com/search/docs/advanced/structured-data/intro-structured-data
 */
class SchemaGenerator
{
    /**
     * Application settings (site name, contact info, etc.)
     */
    protected object $settings;

    /**
     * Theme settings (logos, images, branding)
     */
    protected object $themeSettings;

    /**
     * Schema.org vocabulary URL
     */
    private const SCHEMA_CONTEXT = 'https://schema.org';

    /**
     * Default price validity period (days)
     */
    private const PRICE_VALID_DAYS = 30;

    /**
     * Maximum description length for schemas
     */
    private const MAX_DESCRIPTION_LENGTH = 160;

    /**
     * Initialize generator with application and theme settings
     */
    public function __construct()
    {
        $this->settings = settings();
        $this->themeSettings = themeSettings();
    }

    /**
     * Render JSON-LD schema markup
     *
     * @param mixed $__env View environment (Laravel View or Factory)
     * @param string|null $method Schema type (null=default, 'article', 'product')
     * @param array $options Additional data (article, product, etc.)
     * @return string HTML script tags with JSON-LD
     */
    public function render($__env, ?string $method = null, array $options = []): string
    {
        try {
            // Determine schema handler method
            $handlerMethod = $method
                ? 'handle' . Str::studly($method) . 'Schema'
                : 'handleDefaultSchema';

            // Validate handler exists
            if (!method_exists($this, $handlerMethod)) {
                Log::warning("Schema handler not found: {$handlerMethod}");
                return '';
            }

            // Generate schemas
            $schemas = $this->{$handlerMethod}($__env, $options);

            // Validate schemas array
            if (!is_array($schemas) || empty($schemas)) {
                return '';
            }

            return $this->renderSchemaScripts($schemas);
        } catch (Throwable $e) {
            Log::error('Schema generation failed', [
                'method' => $method,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return '';
        }
    }

    /**
     * Render schema arrays as HTML script tags
     *
     * @param array $schemas Array of schema arrays
     * @return string HTML script tags
     */
    private function renderSchemaScripts(array $schemas): string
    {
        $output = '';

        foreach ($schemas as $schema) {
            if (!is_array($schema) || empty($schema)) {
                continue;
            }

            // Encode with unescaped slashes for URLs
            $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($json === false) {
                Log::warning('Failed to encode schema as JSON');
                continue;
            }

            // Fix URL-encoded curly braces (from route parameters)
            $json = str_replace(['%7B', '%7D'], ['{', '}'], $json);

            $output .= '<script type="application/ld+json">' . $json . '</script>' . PHP_EOL;
        }

        return $output;
    }

    /**
     * Generate default schemas (Organization + Website + WebPage)
     *
     * Used on all pages that don't have specific schema types.
     * Provides foundational structured data for the entire site.
     *
     * @param mixed $__env View environment
     * @param array $options Additional options
     * @return array Array of schema arrays
     */
    public function handleDefaultSchema($__env, array $options = []): array
    {
        // Validate settings availability
        if (!$this->hasValidSettings()) {
            return [];
        }

        $schemas = [];

        // Organization schema (company/brand info)
        $schemas[] = $this->buildOrganizationSchema()->toArray();

        // Website schema (site-wide search action)
        $schemas[] = $this->buildWebsiteSchema()->toArray();

        // WebPage schema (current page info)
        $schemas[] = $this->buildWebPageSchema($__env)->toArray();

        return $schemas;
    }

    /**
     * Generate Article schema for blog posts and news
     *
     * Enables rich snippets with author, publish date, and featured image.
     *
     * @param mixed $__env View environment
     * @param array $options Must contain 'article' key
     * @return array Array of schema arrays
     */
    public function handleArticleSchema($__env, array $options = []): array
    {
        if (!isset($options['article']) || !$this->hasValidSettings()) {
            return [];
        }

        $article = $options['article'];

        $articleSchema = Schema::article()
            ->headline($this->sanitizeText($article->title))
            ->author(Schema::organization()->name($this->settings->general->site_name))
            ->datePublished($article->created_at->toIso8601String())
            ->dateModified($article->updated_at->toIso8601String())
            ->mainEntityOfPage($article->view_link)
            ->image($this->getArticleImage($article))
            ->publisher(
                Schema::organization()
                    ->name($this->settings->general->site_name)
                    ->logo(Schema::imageObject()->url($this->getLogoUrl()))
            );

        // Add description if available
        if (!empty($article->short_description)) {
            $articleSchema->description(
                $this->sanitizeText($article->short_description, self::MAX_DESCRIPTION_LENGTH)
            );
        }

        return [$articleSchema->toArray()];
    }

    /**
     * Generate Product schema for marketplace items
     *
     * Enables rich product snippets with price, availability, ratings, and reviews.
     * Critical for e-commerce SEO and conversion rates.
     *
     * @param mixed $__env View environment
     * @param array $options Must contain 'product' key
     * @return array Array of schema arrays
     */
    public function handleProductSchema($__env, array $options = []): array
    {
        if (!isset($options['product']) || !$this->hasValidSettings()) {
            return [];
        }

        $product = $options['product'];

        // Build base product schema
        $productSchema = Schema::product()
            ->name($this->sanitizeText($product->name))
            ->description($this->getProductDescription($product))
            ->image($product->getImageLink())
            ->url($product->view_link)
            ->sku((string) $product->id)
            ->mpn("EZM-{$product->id}");

        // Add category if available
        if (isset($product->category) && !empty($product->category->name)) {
            $productSchema->category($this->sanitizeText($product->category->name));
        }

        // Add brand/seller
        if (isset($product->seller) && !empty($product->seller->full_name)) {
            $productSchema->brand(
                Schema::brand()->name($this->sanitizeText($product->seller->full_name))
            );
        }

        // Add offer (price, availability)
        $productSchema->offers($this->buildOfferSchema($product));

        // Add aggregate rating (if product has reviews)
        if ($this->hasReviews($product)) {
            $productSchema->aggregateRating($this->buildAggregateRatingSchema($product));

            // Add latest review
            $latestReview = $product->reviews->first();
            if ($latestReview) {
                $productSchema->review($this->buildReviewSchema($latestReview));
            }
        }

        return [$productSchema->toArray()];
    }

    /**
     * Build Organization schema
     *
     * @return \Spatie\SchemaOrg\Organization
     */
    private function buildOrganizationSchema()
    {
        $schema = Schema::organization()
            ->name($this->settings->general->site_name ?? config('app.name'))
            ->url(url('/'))
            ->logo($this->getLogoUrl());

        // Add contact point if email available
        if ($this->hasContactEmail()) {
            $schema->contactPoint([
                Schema::contactPoint()
                    ->email($this->settings->general->contact_email)
                    ->contactType('Customer Service')
                    ->availableLanguage($this->getAvailableLanguages()),
            ]);
        }

        return $schema;
    }

    /**
     * Build Website schema with site-wide search
     *
     * @return \Spatie\SchemaOrg\WebSite
     */
    private function buildWebsiteSchema()
    {
        return Schema::webSite()
            ->name($this->settings->general->site_name ?? config('app.name'))
            ->url(url('/'))
            ->potentialAction(
                Schema::searchAction()
                    ->target(route('products.index', ['search' => '{search_term_string}']))
                    ->setProperty('query-input', 'required name=search_term_string')
            );
    }

    /**
     * Build WebPage schema for current page
     *
     * @param mixed $__env View environment
     * @return \Spatie\SchemaOrg\WebPage
     */
    private function buildWebPageSchema($__env)
    {
        $schema = Schema::webPage()
            ->name(metaTitle($__env))
            ->url(url()->current());

        // Add description if available
        $description = $__env->yieldContent('description');
        if (!empty($description)) {
            $schema->description($this->sanitizeText($description, self::MAX_DESCRIPTION_LENGTH));
        }

        // Add publisher
        $schema->publisher(
            Schema::organization()->name($this->settings->general->site_name)
        );

        return $schema;
    }

    /**
     * Build Offer schema for product pricing
     *
     * @param object $product Product model
     * @return \Spatie\SchemaOrg\Offer
     */
    private function buildOfferSchema(object $product)
    {
        $currency = defaultCurrency();

        return Schema::offer()
            ->price(number_format($product->price->regular, 2, '.', ''))
            ->priceCurrency($currency->code ?? 'USD')
            ->priceValidUntil(Carbon::now()->addDays(self::PRICE_VALID_DAYS)->toDateString())
            ->availability('https://schema.org/InStock')
            ->itemCondition('https://schema.org/NewCondition')
            ->url($product->view_link);
    }

    /**
     * Build AggregateRating schema for product reviews
     *
     * @param object $product Product model with reviews
     * @return \Spatie\SchemaOrg\AggregateRating
     */
    private function buildAggregateRatingSchema(object $product)
    {
        return Schema::aggregateRating()
            ->ratingValue(number_format($product->avg_reviews, 2, '.', ''))
            ->reviewCount((int) $product->total_reviews)
            ->bestRating(5)
            ->worstRating(1);
    }

    /**
     * Build Review schema for individual review
     *
     * @param object $review Review model
     * @return \Spatie\SchemaOrg\Review
     */
    private function buildReviewSchema(object $review)
    {
        $schema = Schema::review()
            ->reviewRating(
                Schema::rating()
                    ->ratingValue((int) $review->stars)
                    ->bestRating(5)
                    ->worstRating(1)
            )
            ->datePublished($review->created_at->toIso8601String());

        // Add author if available
        if (isset($review->user) && !empty($review->user->username)) {
            $schema->author(
                Schema::person()->name($this->sanitizeText($review->user->username))
            );
        }

        // Add review body if available
        if (!empty($review->comment)) {
            $schema->reviewBody($this->sanitizeText($review->comment, 500));
        }

        return $schema;
    }

    /**
     * Get article image URL (fallback to social image)
     *
     * @param object $article Article model
     * @return string Image URL
     */
    private function getArticleImage(object $article): string
    {
        if (!empty($article->image) && method_exists($article, 'getImageLink')) {
            return $article->getImageLink();
        }

        return asset($this->themeSettings->general->social_image ?? 'images/default-social.jpg');
    }

    /**
     * Get product description (sanitized, truncated)
     *
     * @param object $product Product model
     * @return string Sanitized description
     */
    private function getProductDescription(object $product): string
    {
        $description = strip_tags($product->description ?? '');
        return $this->sanitizeText($description, self::MAX_DESCRIPTION_LENGTH);
    }

    /**
     * Get logo URL from theme settings
     *
     * @return string Logo URL
     */
    private function getLogoUrl(): string
    {
        return asset($this->themeSettings->general->logo_dark ?? 'images/logo.png');
    }

    /**
     * Get available languages for contact point
     *
     * @return array Language codes
     */
    private function getAvailableLanguages(): array
    {
        if (function_exists('getLanguageSwiter')) {
            return array_keys(getLanguageSwiter());
        }

        return [config('app.locale', 'en')];
    }

    /**
     * Sanitize text for schema output
     *
     * @param string $text Text to sanitize
     * @param int|null $maxLength Maximum length (null = no limit)
     * @return string Sanitized text
     */
    private function sanitizeText(string $text, ?int $maxLength = null): string
    {
        // Remove HTML tags
        $text = strip_tags($text);

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        // Truncate if needed
        if ($maxLength && mb_strlen($text) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength - 3) . '...';
        }

        return $text;
    }

    /**
     * Check if settings are valid
     *
     * @return bool
     */
    private function hasValidSettings(): bool
    {
        return is_object($this->settings)
            && isset($this->settings->general)
            && is_object($this->themeSettings)
            && isset($this->themeSettings->general);
    }

    /**
     * Check if contact email is available
     *
     * @return bool
     */
    private function hasContactEmail(): bool
    {
        return isset($this->settings->general->contact_email)
            && !empty($this->settings->general->contact_email)
            && filter_var($this->settings->general->contact_email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Check if product has reviews
     *
     * @param object $product Product model
     * @return bool
     */
    private function hasReviews(object $product): bool
    {
        return method_exists($product, 'hasReviews')
            ? $product->hasReviews()
            : (isset($product->total_reviews) && $product->total_reviews > 0);
    }

    /**
     * Generate schema markup for specific type (static helper)
     *
     * @param mixed $__env View environment
     * @param string|null $type Schema type
     * @param array $options Additional data
     * @return string HTML script tags
     */
    public static function generate($__env, ?string $type = null, array $options = []): string
    {
        return app(self::class)->render($__env, $type, $options);
    }
}
