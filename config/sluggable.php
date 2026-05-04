<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Slug Source Field(s)
    |--------------------------------------------------------------------------
    |
    | Attribute(s) to build the slug from. Can be a single field or array.
    |
    | Single field example:
    |     'source' => 'name'
    |     Builds slug from: $model->name
    |
    | Multiple fields example:
    |     'source' => ['name', 'company']
    |     Builds slug from: $model->name . ' ' . $model->company
    |
    | For EasyMarket:
    | - Products: 'title' or 'name'
    | - Categories: 'name'
    | - Users/Sellers: ['first_name', 'last_name'] or 'username'
    | - Blog posts: 'title'
    |
    | NULL = uses model's toString() method
    |
    | Note: Custom getters work too! Can use model accessors.
    |
    */

    'source' => null,

    /*
    |--------------------------------------------------------------------------
    | Maximum Slug Length
    |--------------------------------------------------------------------------
    |
    | Maximum length of generated slugs (in characters).
    | NULL = no length restrictions
    |
    | Recommended for EasyMarket:
    | - Products: 100-150 characters (good for SEO, readable URLs)
    | - Categories: 50-80 characters (shorter paths)
    | - Blog posts: 100 characters (standard blog URL length)
    |
    | Database consideration: Ensure slug column is varchar(255) or larger
    |
    | SEO Best Practice: Keep under 100 chars for better search rankings
    | Google typically displays 50-60 characters in search results
    |
    | Example with maxLength=50:
    | "Amazing Digital Product With Very Long Title For EasyMarket"
    | becomes: "amazing-digital-product-with-very-long-title"
    |
    */

    'maxLength' => env('SLUGGABLE_MAX_LENGTH', null),

    /*
    |--------------------------------------------------------------------------
    | Keep Words Intact
    |--------------------------------------------------------------------------
    |
    | When truncating to maxLength, avoid splitting words in half.
    |
    | TRUE:  "my awesome product" -> "my-awesome" (word-aware)
    | FALSE: "my awesome product" -> "my-awesom" (truncates at exact length)
    |
    | Recommendation for EasyMarket: TRUE (better readability)
    |
    | This ensures product URLs always look professional and complete.
    |
    */

    'maxLengthKeepWords' => true,

    /*
    |--------------------------------------------------------------------------
    | Slug Generation Method
    |--------------------------------------------------------------------------
    |
    | Function/method to generate slugs from source strings.
    |
    | NULL = Use cocur/slugify package (default, handles Unicode well)
    |
    | Custom method example:
    |     'method' => function($string, $separator) {
    |         return preg_replace('/[^a-z0-9]+/i', $separator, $string);
    |     }
    |
    | Callable example:
    |     'method' => ['Str', 'slug']  // Use Laravel's Str::slug()
    |
    | For EasyMarket:
    | Using App\Methods\SlugTransliterator::class for multilingual support
    | This handles non-Latin characters (Arabic, Chinese, Cyrillic, etc.)
    |
    | ✅ Current: SlugTransliterator::class (best for international marketplace)
    |
    | Examples:
    | - "Product Name" -> "product-name"
    | - "Продукт" -> "produkt" (transliterated)
    | - "产品" -> "chan-pin" (transliterated)
    |
    */

    'method' => [App\Methods\SlugTransliterator::class, 'slug'],

    /*
    |--------------------------------------------------------------------------
    | Slug Separator
    |--------------------------------------------------------------------------
    |
    | Character to separate words in slugs.
    |
    | Options:
    | '-' (hyphen)     - Most common, SEO-friendly (RECOMMENDED)
    | '_' (underscore) - Alternative, less common
    | '.' (period)     - Rarely used
    |
    | For EasyMarket: Keep '-' (hyphen)
    | Google treats hyphens as word separators in URLs
    | Underscores are treated as word joiners (worse for SEO)
    |
    | Example:
    | "My Product" -> "my-product" (with hyphen)
    | "My Product" -> "my_product" (with underscore)
    |
    */

    'separator' => '-',

    /*
    |--------------------------------------------------------------------------
    | Enforce Unique Slugs
    |--------------------------------------------------------------------------
    |
    | Ensure all slugs are unique by appending incremental numbers.
    |
    | TRUE:  If "my-product" exists, next is "my-product-2", then "my-product-3"
    | FALSE: Allows duplicate slugs (NOT recommended for URLs)
    |
    | For EasyMarket: Keep TRUE
    |
    | Why unique slugs matter:
    | - Each product/category needs distinct URL
    | - Prevents routing conflicts
    | - Required for proper SEO (no duplicate content)
    |
    | Example scenario:
    | - Seller A uploads "WordPress Theme"     -> /products/wordpress-theme
    | - Seller B uploads "WordPress Theme"     -> /products/wordpress-theme-2
    | - Seller C uploads "WordPress Theme Pro" -> /products/wordpress-theme-pro
    |
    */

    'unique' => true,

    /*
    |--------------------------------------------------------------------------
    | Custom Unique Suffix Method
    |--------------------------------------------------------------------------
    |
    | Custom logic for generating unique suffixes.
    | NULL = use default incremental integers (2, 3, 4, ...)
    |
    | Custom closure example:
    |     'uniqueSuffix' => function($slug, $separator, $similarSlugs) {
    |         return $separator . uniqid();  // Append unique ID
    |     }
    |
    | For EasyMarket: NULL (default incremental is clear and user-friendly)
    |
    | Default behavior is best for:
    | - User understanding (easy to see it's a similar product)
    | - Short URLs
    | - Predictable patterns
    |
    | Custom suffixes might be useful for:
    | - Adding seller ID: "product-name-seller123"
    | - Adding random hash: "product-name-a4b2c1"
    | - Adding date: "product-name-2025"
    |
    */

    'uniqueSuffix' => null,

    /*
    |--------------------------------------------------------------------------
    | First Unique Suffix
    |--------------------------------------------------------------------------
    |
    | Starting number for unique slug suffixes.
    |
    | Default: 2
    |
    | Slug sequence:
    | - my-product     (original, no suffix)
    | - my-product-2   (first duplicate)
    | - my-product-3   (second duplicate)
    | - my-product-4   ...
    |
    | Why start at 2?
    | The first slug has no number, so the first duplicate is logically "2"
    |
    | You could start at 1:
    | - my-product
    | - my-product-1
    | - my-product-2
    |
    | For EasyMarket: Keep 2 (standard convention)
    |
    */

    'firstUniqueSuffix' => 2,

    /*
    |--------------------------------------------------------------------------
    | Include Trashed Models
    |--------------------------------------------------------------------------
    |
    | Check trashed (soft-deleted) models when enforcing unique slugs.
    | Only applies if model uses SoftDeletes trait.
    |
    | FALSE: New slug can duplicate a trashed product's slug (RECOMMENDED)
    | TRUE:  Slugs must be unique across active AND deleted products
    |
    | For EasyMarket: FALSE
    |
    | Why FALSE is better for marketplace:
    | - If seller deletes "my-product", another seller can use that slug
    | - Slug namespace recycling (good for popular terms)
    | - Deleted products are gone, URLs can be reused
    |
    | Why you might use TRUE:
    | - Want to prevent confusion if product is restored
    | - Maintain slug history permanently
    | - Avoid SEO issues from URL recycling
    |
    | Example with FALSE:
    | 1. Product "WordPress Theme" created -> /products/wordpress-theme
    | 2. Product deleted (soft delete)
    | 3. New product "WordPress Theme" -> /products/wordpress-theme (reuses slug)
    |
    */

    'includeTrashed' => false,

    /*
    |--------------------------------------------------------------------------
    | Reserved Slug Names
    |--------------------------------------------------------------------------
    |
    | Slug names that can NEVER be used (prevents route conflicts).
    |
    | NULL = no reserved names
    |
    | Static array example:
    |     'reserved' => ['admin', 'api', 'login', 'register', 'logout'],
    |
    | Closure example:
    |     'reserved' => function($model) {
    |         return ['add', 'edit', 'delete', 'create'];
    |     }
    |
    | For EasyMarket, consider reserving:
    | - Route names: 'admin', 'api', 'dashboard', 'checkout', 'cart'
    | - Actions: 'add', 'edit', 'delete', 'create', 'update', 'remove'
    | - Special pages: 'login', 'register', 'logout', 'profile', 'settings'
    | - System: 'system', 'config', 'debug', 'test'
    |
    | Example problem without reserved names:
    | Product titled "Login" would create URL: /products/login
    | This conflicts with your /login route!
    |
    | With reserved: Product "Login" becomes /products/login-2
    |
    | Recommendation: Define reserved names in model-specific config
    | or use global reserved list for critical routes.
    |
    */

    'reserved' => null,

    /*
    |--------------------------------------------------------------------------
    | Update Slugs on Model Update
    |--------------------------------------------------------------------------
    |
    | Regenerate slug when model is updated (if source field changes).
    |
    | FALSE: Slug is generated once and never changes (RECOMMENDED)
    | TRUE:  Slug updates every time source field changes
    |
    | For EasyMarket: Keep FALSE (critical for SEO)
    |
    | ⚠️ WARNING: Setting this to TRUE can break:
    | - External links to your products
    | - Search engine indexed URLs
    | - Bookmarked pages
    | - Shared social media links
    | - Your SEO rankings (URL changes reset page authority)
    |
    | When you might use TRUE:
    | - Draft products not yet published
    | - Internal admin URLs (not public-facing)
    | - Testing/development environments
    |
    | Best practice for EasyMarket:
    | - Keep FALSE
    | - If seller changes product title, keep original slug
    | - Only allow manual slug editing for admins
    | - Show warning if manually changing published product slug
    |
    | Example with FALSE:
    | 1. Create product "WordPress Plugin" -> slug: "wordpress-plugin"
    | 2. Update title to "WP Plugin Pro" -> slug: still "wordpress-plugin"
    | 3. All old links still work!
    |
    */

    'onUpdate' => false,

    /*
    |--------------------------------------------------------------------------
    | Slug Engine Options
    |--------------------------------------------------------------------------
    |
    | Configuration options for cocur/slugify package (if used).
    |
    | Available options:
    | - 'lowercase' => true/false (convert to lowercase)
    | - 'separator' => '-' (word separator)
    | - 'rulesets' => ['default', 'turkish', 'burmese', etc.]
    | - 'trim' => true/false (trim separator from ends)
    |
    | For EasyMarket multilingual marketplace:
    |     'slugEngineOptions' => [
    |         'lowercase' => true,
    |         'rulesets' => [
    |             'default',      // Latin characters
    |             'turkish',      // Turkish characters (ğ, ş, ı, etc.)
    |             'russian',      // Cyrillic
    |             'arabic',       // Arabic script
    |             'chinese',      // Chinese characters
    |         ],
    |     ]
    |
    | Leave empty to use default configuration.
    | Your SlugTranslation class likely handles this already.
    |
    */

    'slugEngineOptions' => [],

];


















