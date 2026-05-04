<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Product\{ProductStatus, ProductHistoryTitle};
use App\Events\{ProductSubmitted, ProductResubmitted, ProductUpdated};
use App\Models\Product\{Product, ProductCategory, ProductHistory, ProductSubCategory, ProductUpdate};
use App\Models\{User, UploadedFile};
use App\Facades\Notification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;

/**
 * Service for handling product submission workflows:
 * creating, updating, drafting, and publishing products.
 */
class ProductSubmissionService
{
    private const MAX_EXTRA_FEATURES = 6;

    private array $imageMimeTypes = ['image/png', 'image/jpg', 'image/jpeg'];
    private array $videoMimeTypes = ['video/mp4', 'video/webm', 'video/mov'];
    private array $audioMimeTypes = ['audio/mp3', 'audio/mpeg', 'audio/wav'];

    // =========================================================================
    // PUBLIC API
    // =========================================================================

    /**
     * Store a new product (full submission).
     */
    public function storeProduct(Request $request, User $seller): Product
    {
        $productSettings = settings('product');
        $category = ProductCategory::where('slug', $request->category)->first();

        if (!$category) {
            throw new Exception(translate('Please select a valid category.'));
        }

        $this->validateForStore($request, $productSettings, $category);

        $prepared = $this->prepareProductData($request, $category, $productSettings);
        $productFiles = $this->handleFiles($request, $category, required: true);
        $this->validateFilesNotDuplicated($productFiles);

        $status = @$productSettings->adding_require_review
            ? ProductStatus::PENDING
            : ProductStatus::APPROVED;

        $historyTitle = @$productSettings->adding_require_review
            ? ProductHistoryTitle::SUBMISSION
            : ProductHistoryTitle::TRUST_SUBMISSION;

        // Reuse draft if available, otherwise create new product
        $product = null;
        if ($request->filled('draft_id')) {
            $product = Product::withoutGlobalScopes()->where('id', $request->draft_id)
                ->where('seller_id', $seller->id)
                ->draft()->first();
        }
        
        $product = $product ?? new Product();
        $product->seller_id = $seller->id;
        $product->name = $this->generateUniqueName($request->name, $product->id);
        $product->slug = $this->generateUniqueSlug($request->slug, $product->id);
        $product->category_id = $category->id;
        $product->sub_category_id = $prepared['sub_category_id'];
        $product->status = $status;
        $this->assignProductFields($product, $request, $prepared, $productFiles, $productSettings);
        $product->price_updated_at = Carbon::now();
        $product->save();

        if ($productFiles->preview_image) {
            thumbnailGenerator()->generate($productFiles->preview_image);
        }

        $this->createHistory($product->id, $seller->id, $historyTitle);
        $this->handleFileDeletion($request);

        event(new ProductSubmitted($product));

        return $product;
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Request $request, Product $product, User $seller): Product
    {
        $productSettings = settings('product');
        $category = $product->category;

        $this->validateForUpdate($request, $product, $productSettings, $category);

        $prepared = $this->prepareProductData($request, $category, $productSettings, $product);
        $productFiles = $this->handleFiles($request, $category, required: false);
        $this->validateFilesNotDuplicated($productFiles);

        if ($productFiles->preview_image) {
            thumbnailGenerator()->generate($productFiles->preview_image);
        }

        // Handle price (locked during discount)
        if (!$product->hasDiscount()) {
            $regularPrice = $request->regular_license_price;
            $extendedPrice = $request->extended_license_price;
            $priceUpdatedAt = ($regularPrice != $product->regular_price || $extendedPrice != $product->extended_price)
                ? Carbon::now()
                : $product->price_updated_at;
        } else {
            $regularPrice = $product->regular_price;
            $extendedPrice = $product->extended_price;
            $priceUpdatedAt = $product->price_updated_at;
        }

        // Review-required flow for approved products
        if (@$productSettings->updating_require_review) {
            if (!$product->isApproved()) {
                // Soft rejected / resubmitted → direct update
                $slug = $this->generateUniqueSlug($request->slug, $product->id);
                $status = ProductStatus::RESUBMITTED;
                $historyTitle = ProductHistoryTitle::RESUBMISSION;
                $toastrMessage = translate('Your product has been resubmitted successfully');
            } else {
                // Approved → create ProductUpdate for review
                if ($product->hasUpdate()) {
                    throw new Exception(translate('You have a pending update please wait until we processed.'));
                }

                $updateRegularPrice = $regularPrice == $product->regular_price ? null : $regularPrice;
                $updateExtendedPrice = $extendedPrice == $product->extended_price ? null : $extendedPrice;

                $productUpdate = new ProductUpdate();
                $productUpdate->seller_id = $product->seller_id;
                $productUpdate->product_id = $product->id;
                $productUpdate->name = $request->name;
                $productUpdate->description = $prepared['description'];
                $productUpdate->category_id = $product->category_id;
                $productUpdate->sub_category_id = $prepared['sub_category_id'];
                $productUpdate->support_package_id = $prepared['support_package_id'];
                $productUpdate->options = $prepared['options'];
                $productUpdate->version = $request->version;
                $productUpdate->demo_link = $request->demo_link;
                $productUpdate->tags = is_array($request->tags) ? implode(',', $request->tags) : $request->tags;
                $productUpdate->preview_type = $product->preview_type;
                $productUpdate->preview_image = $productFiles->preview_image;
                $productUpdate->preview_video = $productFiles->preview_video;
                $productUpdate->preview_audio = $productFiles->preview_audio;
                if ($productFiles->main_file) {
                    $productUpdate->main_file = $productFiles->main_file;
                }
                $productUpdate->gallery = $productFiles->gallery;
                $productUpdate->regular_price = $updateRegularPrice;
                $productUpdate->extended_price = $updateExtendedPrice;
                $productUpdate->is_supported = $request->support;
                $productUpdate->support_instructions = $this->handleSupportInstructions($request->support_instructions, (bool)$request->support);
                $productUpdate->purchasing_status = $prepared['purchasing'];
                $productUpdate->is_free = $prepared['free'];
                $productUpdate->regular_price_label = @$productSettings->price_label_status ? $request->regular_price_label : null;
                $productUpdate->extended_price_label = @$productSettings->price_label_status ? $request->extended_price_label : null;
                $productUpdate->regular_extra_features = @$productSettings->additional_features_status ? ($prepared['regular_extra_features'] ?? null) : null;
                $productUpdate->extended_extra_features = @$productSettings->additional_features_status ? ($prepared['extended_extra_features'] ?? null) : null;
                $productUpdate->has_custom_services = @$productSettings->custom_services_status ? ($request->has_custom_services ? true : false) : false;
                $productUpdate->custom_services = @$productSettings->custom_services_status ? ($request->custom_services ?? null) : null;
                $productUpdate->save();

                $this->createHistory($product->id, $seller->id, ProductHistoryTitle::UPDATE_SUBMISSION);
                $this->handleFileDeletion($request, $product);

                event(new ProductUpdated($productUpdate));

                return $product;
            }
        } else {
            $slug = $this->generateUniqueSlug($request->input('slug', $product->slug), $product->id);
            $status = ProductStatus::APPROVED;
            $historyTitle = ProductHistoryTitle::TRUST_UPDATE;
            $toastrMessage = translate('Your product has been updated successfully');
        }

        $updated = false;
        $productClone = clone $product;

        $product->name = $this->generateUniqueName($request->name, $product->id);
        $product->slug = $slug;
        $this->assignProductFields($product, $request, $prepared, $productFiles, $productSettings);

        if ($productFiles->preview_image) {
            $product->preview_image = $productFiles->preview_image;
        }
        if ($productFiles->preview_video) {
            $product->preview_video = $productFiles->preview_video;
        }
        if ($productFiles->preview_audio) {
            $product->preview_audio = $productFiles->preview_audio;
        }
        if ($productFiles->main_file) {
            $product->main_file = $productFiles->main_file;
            if ($status === ProductStatus::APPROVED) {
                $product->last_updated_at = Carbon::now();
                $updated = true;
            }
        }
        if ($productFiles->gallery) {
            $product->gallery = $productFiles->gallery;
        }

        $product->regular_price = $regularPrice;
        $product->extended_price = $extendedPrice;
        $product->status = $status;
        $product->price_updated_at = $priceUpdatedAt;
        $product->update();

        $this->createHistory($product->id, $seller->id, $historyTitle);
        $this->handleFileDeletion($request, $productClone);

        if (!$productClone->isResubmitted()) {
            event(new ProductResubmitted($product));
        }

        if (!@$productSettings->updating_require_review) {
            Notification::sendProductUpdateStatusNotification($product, 'updated');
        }

        return $product;
    }

    /**
     * Save a product as a draft.
     */
    public function saveDraft(Request $request, User $seller): Product
    {
        // Check draft limit
        $draftCount = Product::where('seller_id', $seller->id)->draft()->count();
        $productSettings = settings('product');

        // If resuming an existing draft, allow it
        $draftId = $request->input('draft_id');
        $existingDraft = null;
        if ($draftId) {
            $existingDraft = Product::where('id', $draftId)
                ->where('seller_id', $seller->id)
                ->draft()->first();
        }

        if (!$existingDraft && $draftCount >= @$productSettings->maximum_drafts) {
            throw new Exception(translate('You can only have :max drafts at a time.', ['max' => @$productSettings->maximum_drafts]));
        }

        // Light validation for drafts
        $rules = [
            'name' => ['required', 'string', 'block_patterns', 'max:150'],
            'slug' => ['nullable', 'string', 'block_patterns', 'max:150'],
            'description' => ['nullable'],
            'category' => ['nullable', 'string', 'exists:product_categories,slug'],
            'sub_category' => ['nullable', 'string', 'exists:product_sub_categories,slug'],
            'version' => ['nullable', 'regex:/^\d+\.\d+(\.\d+)*$/', 'block_patterns', 'max:100'],
            'demo_link' => ['nullable', 'block_patterns', 'url'],
            'tags' => ['nullable', 'block_patterns'],
            'regular_license_price' => ['nullable', 'numeric'],
            'extended_license_price' => ['nullable', 'numeric'],
            'regular_price_label' => ['nullable', 'string', 'block_patterns', 'max:100'],
            'extended_price_label' => ['nullable', 'string', 'block_patterns', 'max:100'],
            'regular_extra_features' => ['nullable', 'block_patterns'],
            'extended_extra_features' => ['nullable', 'block_patterns'],
            'has_custom_services' => ['nullable', 'boolean'],
            'custom_services' => ['nullable', 'string', 'block_patterns', 'max:1000'],
            'options' => ['nullable', 'array'],
            'support_package_id' => ['nullable', 'exists:support_packages,id'],
        ];
        $validator = Validator::make($request->only(array_keys($rules)), $rules);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        $category = null;
        if ($request->filled('category')) {
            $category = ProductCategory::where('slug', $request->category)->first();
        }

        $description = $request->description ? $this->handleDescription($request->description, required: false) : null;
        $regularExtraFeatures = $this->convertTextareaToArray($request->regular_extra_features);
        $extendedExtraFeatures = $this->convertTextareaToArray($request->extended_extra_features);

        $draft = $existingDraft ?? new Product();
        $draft->seller_id = $seller->id;
        $draft->name = $this->generateUniqueName($request->name, $existingDraft?->id);
        $draft->slug = $request->slug ? $this->generateUniqueSlug($request->slug, $existingDraft?->id) : null;
        $draft->description = $description;
        $draft->category_id = $category?->id ?? $draft->category_id;
        $draft->status = ProductStatus::DRAFT;
        $draft->tags = is_array($request->tags) ? implode(',', $request->tags) : $request->tags;
        $draft->version = $request->version;
        $draft->demo_link = $request->demo_link;
        $draft->regular_price = $request->regular_license_price ?? 0;
        $draft->extended_price = $request->extended_license_price ?? 0;
        $draft->is_supported = $request->support ?? false;
        $draft->support_package_id = $request->support ? $request->support_package_id : null;
        $draft->support_instructions = $request->support_instructions ? $this->handleSupportInstructions($request->support_instructions, false) : null;
        $draft->purchasing_status = $request->purchasing_status ?? true;
        $draft->is_free = $request->free_product ?? false;
        $draft->regular_price_label = $request->regular_price_label;
        $draft->extended_price_label = $request->extended_price_label;
        $draft->regular_extra_features = $regularExtraFeatures ?: null;
        $draft->extended_extra_features = $extendedExtraFeatures ?: null;
        $draft->has_custom_services = $request->has_custom_services ? true : false;
        $draft->custom_services = $request->custom_services;

        if ($category) {
            if ($request->has('options')) {
                $draft->options = $this->handleOptions($request, $category, required: false);
            }

            $productFiles = $this->handleFiles($request, $category, required: false, strict: false);
            $draft->preview_type = $productFiles->preview_type;
            if ($productFiles->preview_image) {
                $draft->preview_image = $productFiles->preview_image;
            }
            if ($productFiles->preview_video) {
                $draft->preview_video = $productFiles->preview_video;
            }
            if ($productFiles->preview_audio) {
                $draft->preview_audio = $productFiles->preview_audio;
            }
            if ($productFiles->main_file) {
                $draft->main_file = $productFiles->main_file;
            }
            if ($productFiles->gallery) {
                $draft->gallery = $productFiles->gallery;
            }
        }

        if ($request->filled('sub_category')) {
            $subCategory = ProductSubCategory::where('slug', $request->sub_category)->first();
            $draft->sub_category_id = $subCategory?->id;
        }

        $draft->save();

        return $draft;
    }

    /**
     * Publish a draft product (validate completeness and transition status).
     */
    public function publishDraft(Product $product): Product
    {
        $productSettings = settings('product');

        // 1. Basic Fields & Security Patterns
        $v = Validator::make([
            'name' => $product->name,
            'description' => $product->description,
            'category_id' => $product->category_id,
            'tags' => $product->tags,
            'version' => $product->version,
            'demo_link' => $product->demo_link,
        ], [
            'name' => ['required', 'string', 'block_patterns', 'unique:products,name,' . $product->id, 'max:150'],
            'description' => ['required'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'tags' => ['required', 'block_patterns'],
            'version' => ['nullable', 'regex:/^\d+\.\d+(\.\d+)*$/', 'max:100'],
            'demo_link' => ['nullable', 'url', 'block_patterns'],
        ]);

        if ($v->fails()) {
            throw new Exception($v->errors()->first());
        }

        if ($product->name === 'Untitled Draft') {
            throw new Exception(translate('Please provide a descriptive name for your product'));
        }

        // 2. Price Validation
        if ($product->is_free) {
            if ($product->regular_price > 0 || $product->extended_price > 0) {
                 // Should be 0 for free products, but we mainly care about paid ones
            }
        } else {
            $minPrice = @$productSettings->minimum_price ?? 0;
            $maxPrice = @$productSettings->maximum_price ?? 999999;

            if ($product->regular_price < $minPrice || $product->regular_price > $maxPrice) {
                throw new Exception(translate('Regular license price should be between :min and :max', [
                    'min' => price((float) $minPrice),
                    'max' => price((float) $maxPrice)
                ]));
            }

            // We use zero/null to disable extended price so ignore min price
            if ($product->extended_price > $maxPrice) {
                throw new Exception(translate('Extended license price should not exceed :max', [
                    'max' => price((float) $maxPrice)
                ]));
            }
        }

        // 3. Category Options (Shared required checks)
        $categoryOptions = $product->category->options ?? [];
        foreach ($categoryOptions as $categoryOption) {
            if (isset($categoryOption['is_required']) && $categoryOption['is_required']) {
                $optionValue = $product->options[$categoryOption['name']] ?? null;
                if (empty($optionValue)) {
                    throw new Exception(translate(':field is required', ['field' => $categoryOption['name']]));
                }
            }
        }

        // 4. Counts & Limits
        $this->enforceFeatureLimits($product->regular_extra_features, $product->extended_extra_features);
        $this->enforceTagLimits($product->tags, $productSettings);

        // 5. File Presence
        if (empty($product->preview_image)) {
            throw new Exception(translate('Preview image is required'));
        }
        if (empty($product->main_file)) {
            throw new Exception(translate('Main file is required'));
        }

        // Deep validation of file qualities (dimensions, etc.) before publishing
        $this->validateFileQualities($product);

        // Set status
        $product->status = @$productSettings->adding_require_review
            ? ProductStatus::PENDING
            : ProductStatus::APPROVED;

        $product->price_updated_at = Carbon::now();
        $product->save();

        $historyTitle = @$productSettings->adding_require_review
            ? ProductHistoryTitle::SUBMISSION
            : ProductHistoryTitle::TRUST_SUBMISSION;

        $this->createHistory($product->id, $product->seller_id, $historyTitle);

        if ($product->preview_image) {
            thumbnailGenerator()->generate($product->preview_image);
        }

        event(new ProductSubmitted($product));

        return $product;
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================

    private function validateForStore(Request $request, $productSettings, ProductCategory $category): void
    {
        $minPrice = @$productSettings->minimum_price ?? 0;
        $maxPrice = @$productSettings->maximum_price ?? 999999;
        $rules = [
            'name' => ['required', 'string', 'block_patterns', 'max:150'],
            'slug' => ['required', 'string', 'block_patterns', 'max:150', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['required'],
            'category' => ['required', 'string', 'exists:product_categories,slug'],
            'sub_category' => ['nullable', 'string', 'exists:product_sub_categories,slug'],
            'version' => ['nullable', 'regex:/^\d+\.\d+(\.\d+)*$/', 'max:100'],
            'demo_link' => ['nullable', 'url', 'block_patterns'],
            'tags' => ['required', 'block_patterns'],
            'regular_license_price' => ['required', 'numeric', 'min:' . $minPrice, 'max:' . $maxPrice],
            'extended_license_price' => ['required', 'numeric', 'max:' . $maxPrice],
            'free_product' => ['nullable', 'boolean'],
            'purchasing_status' => ['nullable', 'boolean'],
            'regular_price_label' => ['nullable', 'string', 'block_patterns', 'max:100'],
            'extended_price_label' => ['nullable', 'string', 'block_patterns', 'max:100'],
            'regular_extra_features' => ['nullable', 'block_patterns'],
            'extended_extra_features' => ['nullable', 'block_patterns'],
            'has_custom_services' => ['nullable', 'boolean'],
        ] + captchaRules();

        if (@$productSettings->external_file_link_option) {
            $rules['main_file_source'] = ['required', 'boolean'];
            if ($request->boolean('main_file_source')) {
                $rules['main_file'] = ['required', 'url'];
            }
        } else {
            $request->merge(['main_file_source' => 0]);
        }

        if (@$productSettings->support_status) {
            $rules['support'] = ['required', 'boolean'];
            if ($request->boolean('support')) {
                $rules['support_instructions'] = ['required', 'string', 'max:5000'];
                $rules['support_package_id'] = ['required', 'exists:support_packages,id'];
            } else {
                $request->merge([
                    'support_instructions' => null,
                    'support_package_id' => null,
                ]);
            }
        } else {
            $request->merge([
                'support' => 0,
                'support_instructions' => null,
                'support_package_id' => null,
            ]);
        }

        if ($request->has_custom_services == 1 && @$productSettings->custom_services_status) {
            $rules['custom_services'] = ['required', 'string', 'block_patterns', 'max:1000'];
        } else {
            $request->merge([
                'has_custom_services' => 0,
                'custom_services' => null,
            ]);
        }

        if (!@$productSettings->price_label_status) {
            $request->merge([
                'regular_price_label' => null,
                'extended_price_label' => null,
            ]);
        }

        if (!@$productSettings->additional_features_status) {
            $request->merge([
                'regular_extra_features' => null,
                'extended_extra_features' => null,
            ]);
        }

        $validator = Validator::make($request->only(array_keys($rules)), $rules);
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        $this->enforceTagLimits($request->tags, $productSettings);
        $this->enforceFeatureLimits($request->regular_extra_features, $request->extended_extra_features);
    }

    private function validateForUpdate(Request $request, Product $product, $productSettings, ProductCategory $category): void
    {
        $rules = [
            'name' => ['required', 'string', 'block_patterns', 'max:150'],
            'description' => ['required'],
            'version' => ['nullable', 'regex:/^\d+\.\d+(\.\d+)*$/', 'max:100'],
            'demo_link' => ['nullable', 'url', 'block_patterns'],
            'tags' => ['required', 'block_patterns'],
            'slug' => ['required', 'string', 'block_patterns', 'max:150', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'free_product' => ['nullable', 'boolean'],
            'purchasing_status' => ['nullable', 'boolean'],
            'regular_price_label' => ['nullable', 'string', 'block_patterns', 'max:100'],
            'extended_price_label' => ['nullable', 'string', 'block_patterns', 'max:100'],
            'regular_extra_features' => ['nullable', 'block_patterns'],
            'extended_extra_features' => ['nullable', 'block_patterns'],
            'has_custom_services' => ['nullable', 'boolean'],
        ] + captchaRules();

        if ($productSettings->external_file_link_option) {
            $rules['main_file_source'] = ['required', 'boolean'];
            if ($request->boolean('main_file_source')) {
                $rules['main_file'] = ['nullable', 'url'];
            }
        } else {
            $request->merge(['main_file_source' => 0]);
        }

        if (@$productSettings->support_status) {
            $rules['support'] = ['required', 'boolean'];
            if ($request->boolean('support')) {
                $rules['support_instructions'] = ['required', 'string', 'max:5000'];
                $rules['support_package_id'] = ['required', 'exists:support_packages,id'];
            } else {
                $request->merge([
                    'support_instructions' => null,
                    'support_package_id' => null,
                ]);
            }
        } else {
            $request->merge([
                'support' => 0,
                'support_instructions' => null,
                'support_package_id' => null,
            ]);
        }

        if ($request->has_custom_services == 1 && @$productSettings->custom_services_status) {
            $rules['custom_services'] = ['required', 'string', 'block_patterns', 'max:1000'];
        } else {
            $request->merge([
                'has_custom_services' => 0,
                'custom_services' => null,
            ]);
        }

        if (!@$productSettings->price_label_status) {
            $request->merge([
                'regular_price_label' => null,
                'extended_price_label' => null,
            ]);
        }

        if (!@$productSettings->additional_features_status) {
            $request->merge([
                'regular_extra_features' => null,
                'extended_extra_features' => null,
            ]);
        }

        if (!$product->hasDiscount()) {
            $minPrice = @$productSettings->minimum_price ?? 0;
            $maxPrice = @$productSettings->maximum_price ?? 999999;

            $rules['regular_license_price'] = ['required', 'numeric', 'min:' . $minPrice, 'max:' . $maxPrice];
            $rules['extended_license_price'] = ['required', 'numeric', 'max:' . $maxPrice];
        }

        $validator = Validator::make($request->only(array_keys($rules)), $rules);
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        $this->enforceTagLimits($request->tags, $productSettings);
        $this->enforceFeatureLimits($request->regular_extra_features, $request->extended_extra_features);
    }

    // =========================================================================
    // DATA PREPARATION
    // =========================================================================

    private function prepareProductData(Request $request, ProductCategory $category, $productSettings, ?Product $product = null): array
    {
        $free = false;
        $purchasing = true;
        if (@$productSettings->free_product_option) {
            if ($product && $product->isPremium()) {
                // Don't allow free for premium members on update
            } elseif ($request->free_product) {
                $free = true;
                $purchasing = ($request->purchasing_status == true);
            }
        }

        $regularExtraFeatures = @$productSettings->additional_features_status ? $this->convertTextareaToArray($request->regular_extra_features) : null;
        $extendedExtraFeatures = @$productSettings->additional_features_status ? $this->convertTextareaToArray($request->extended_extra_features) : null;

        $description = $this->handleDescription($request->description);
        $options = $this->handleOptions($request, $category);

        $subCategoryId = null;
        if ($request->has('sub_category') && !is_null($request->sub_category)) {
            $subCategory = ProductSubCategory::where('slug', $request->sub_category)->first();
            if (!$subCategory) {
                throw new Exception(translate('Please select a valid sub-category.'));
            }
            $subCategoryId = $subCategory->id;
        }

        $supportInstructions = $this->handleSupportInstructions($request->support_instructions, (bool)$request->support);

        return [
            'description' => $description,
            'options' => $options,
            'sub_category_id' => $subCategoryId,
            'regular_extra_features' => $regularExtraFeatures,
            'extended_extra_features' => $extendedExtraFeatures,
            'support_instructions' => $supportInstructions,
            'support_package_id' => $request->support ? $request->support_package_id : null,
            'free' => $free,
            'purchasing' => $purchasing,
        ];
    }

    private function assignProductFields(Product $product, Request $request, array $prepared, object $productFiles, $productSettings): void
    {
        $product->description = $prepared['description'];
        $product->sub_category_id = $prepared['sub_category_id'];
        $product->options = $prepared['options'];
        $product->version = $request->version;
        $product->demo_link = $request->demo_link;
        $product->tags = is_array($request->tags) ? implode(',', $request->tags) : $request->tags;
        $product->preview_type = $productFiles->preview_type;

        if ($productFiles->preview_image) {
            $product->preview_image = $productFiles->preview_image;
        }
        if ($productFiles->preview_video) {
            $product->preview_video = $productFiles->preview_video;
        }
        if ($productFiles->preview_audio) {
            $product->preview_audio = $productFiles->preview_audio;
        }
        if ($productFiles->main_file) {
            $product->main_file = $productFiles->main_file;
        }
        if ($productFiles->gallery) {
            $product->gallery = $productFiles->gallery;
        }

        $product->regular_price = $request->regular_license_price;
        $product->extended_price = $request->extended_license_price;
        $product->is_supported = $request->support;
        $product->support_package_id = $prepared['support_package_id'];
        $product->support_instructions = $prepared['support_instructions'];
        $product->purchasing_status = $prepared['purchasing'];
        $product->is_free = $prepared['free'];
        $product->regular_price_label = @$productSettings->price_label_status ? $request->regular_price_label : null;
        $product->extended_price_label = @$productSettings->price_label_status ? $request->extended_price_label : null;
        $product->regular_extra_features = @$productSettings->additional_features_status ? ($prepared['regular_extra_features'] ?: null) : null;
        $product->extended_extra_features = @$productSettings->additional_features_status ? ($prepared['extended_extra_features'] ?: null) : null;
        $product->has_custom_services = @$productSettings->custom_services_status ? ($request->has_custom_services ? true : false) : false;
        $product->custom_services = @$productSettings->custom_services_status ? $request->custom_services : null;
    }

    // =========================================================================
    // FILE HANDLING
    // =========================================================================

    /**
     * Validate and resolve all file references from the request.
     */
    public function handleFiles(Request $request, ProductCategory $category, bool $required = true, bool $strict = true): object
    {
        $sellerId = authUser()->id;
        $productSettings = settings('product');

        $response = [
            'preview_type' => 'image',
            'preview_image' => null,
            'preview_video' => null,
            'preview_audio' => null,
            'main_file' => null,
            'gallery' => null,
        ];

        // Preview image
        if ($request->filled('preview_image')) {
            $previewImage = UploadedFile::where('seller_id', $sellerId)
                ->where('id', hash_decode($request->preview_image))->notExpired()->first();
            if (!$previewImage) {
                throw new Exception(translate('One or more of the selected files are expired or not exist'));
            }
            if (!in_array($previewImage->mime_type, $this->imageMimeTypes)) {
                throw new Exception(translate('Preview image should be :mimes', [
                    'mimes' => implode(', ', $this->imageMimeTypes)
                ]));
            }

            if ($strict) {
                $previewImageFileSize = $category->preview_file_size ?? @$productSettings->max_file_size;
                if ($previewImage->size > $previewImageFileSize) {
                    throw new Exception(translate('Preview image size should not exceed :size', [
                        'size' => formatFileSize($previewImageFileSize)
                    ]));
                }
            }

            $manager = ImageManager::gd();
            $originalMemoryLimit = ini_get('memory_limit');
            ini_set('memory_limit', '512M');
            try {
                $fileContents = readFromStorage($previewImage->path);
                $image = $manager->read($fileContents);
            } catch (\Throwable $e) {
                ini_set('memory_limit', $originalMemoryLimit);
                throw new Exception(translate('Unable to read the preview image. Please try uploading again.'));
            }
            ini_set('memory_limit', $originalMemoryLimit);

            if ($strict) {
                if ($category->isImagePreview()) {
                    $previewImageMaxWidth = @$productSettings->max_preview_img_width ?? 1200;
                    $previewImageMaxHeight = @$productSettings->max_preview_img_height ?? 800;
                    if ($image->width() != $previewImageMaxWidth || $image->height() != $previewImageMaxHeight) {
                        throw new Exception(translate('Preview image size should be :dimensions px', [
                            'dimensions' => $previewImageMaxWidth . 'x' . $previewImageMaxHeight,
                        ]));
                    }
                } else {
                    $thumbnailMaxSize = 120;
                    if ($image->width() != $thumbnailMaxSize || $image->height() != $thumbnailMaxSize) {
                        throw new Exception(translate('Thumbnail image size should be :dimensions px', [
                            'dimensions' => $thumbnailMaxSize . 'x' . $thumbnailMaxSize,
                        ]));
                    }
                    if ($image->width() !== $image->height()) {
                        throw new Exception(translate('Thumbnail image should be square (e.g. :dimensions px)', [
                            'dimensions' => 120 . 'x' . 120,
                        ]));
                    }
                }
            }

            $response['preview_image'] = $previewImage->path;
        } else {
            if ($required) {
                throw new Exception(translate(':field Cannot be empty', ['field' => 'Preview image']));
            }
        }

        // Preview video
        if ($category->isVideoPreview()) {
            if ($request->filled('preview_video')) {
                $previewVideo = UploadedFile::where('seller_id', $sellerId)
                    ->where('id', hash_decode($request->preview_video))->notExpired()->first();

                if (!$previewVideo) {
                    throw new Exception(translate('One or more of the selected files are expired or not exist'));
                }

                if (!in_array($previewVideo->mime_type, $this->videoMimeTypes)) {
                    throw new Exception(translate('Video preview should be :mimes', [
                        'mimes' => implode(', ', $this->videoMimeTypes)
                    ]));
                }

                if ($strict) {
                    $previewVideoFileSize = $category->max_preview_file_size ?? @$productSettings->max_file_size;
                    if ($previewVideo->size > $previewVideoFileSize) {
                        throw new Exception(translate('Video preview file size should not exceed :size', [
                            'size' => formatFileSize($previewVideoFileSize)
                        ]));
                    }
                }

                $response['preview_type'] = 'video';
                $response['preview_video'] = $previewVideo->path;
            } elseif ($required) {
                throw new Exception(translate(':field Cannot be empty', ['field' => 'Video preview']));
            }
        }

        // Preview audio
        if ($category->isAudioPreview()) {
            if ($request->filled('preview_audio')) {
                $previewAudio = UploadedFile::where('seller_id', $sellerId)
                    ->where('id', hash_decode($request->preview_audio))->notExpired()->first();

                if (!$previewAudio) {
                    throw new Exception(translate('One or more of the selected files are expired or not exist'));
                }

                if (!in_array($previewAudio->mime_type, $this->audioMimeTypes)) {
                    throw new Exception(translate('Audio preview should be :mimes', [
                        'mimes' => implode(', ', $this->audioMimeTypes)
                    ]));
                }

                if ($strict) {
                    $previewAudioFileSize = $category->max_preview_file_size ?? @$productSettings->max_file_size;
                    if ($previewAudio->size > $previewAudioFileSize) {
                        throw new Exception(translate('Audio preview file size should not exceed :size', [
                            'size' => formatFileSize($previewAudioFileSize)
                        ]));
                    }
                }

                $response['preview_type'] = 'audio';
                $response['preview_audio'] = $previewAudio->path;
            } elseif ($required) {
                throw new Exception(translate(':field Cannot be empty', ['field' => 'Audio preview']));
            }
        }

        // Main file
        if ($request->filled('main_file')) {
            if (!$request->boolean('main_file_source')) {
                $mainFile = UploadedFile::where('seller_id', $sellerId)
                    ->where('id', hash_decode($request->main_file))->notExpired()->first();

                if (!$mainFile) {
                    throw new Exception(translate('One or more of the selected files are expired or not exist'));
                }

                $mainFileTypes = explode(',', $category->main_file_types);
                if (!in_array($mainFile->extension, $mainFileTypes)) {
                    throw new Exception(translate('Main files should be :types', [
                        'types' => $category->main_file_types
                    ]));
                }

                if ($strict) {
                    $mainFileSize = $category->main_file_size ?? @$productSettings->max_file_size;
                    if ($mainFile->size > $mainFileSize) {
                        throw new Exception(translate('Main file size should not exceed :size', [
                            'size' => formatFileSize($mainFileSize)
                        ]));
                    }
                }

                $response['main_file'] = [
                    'type' => 'local',
                    'path' => $mainFile->path,
                    'name' => $mainFile->name ?? null,
                    'source' => null,
                ];
            } else {
                $response['main_file'] = [
                    'type' => 'external',
                    'path' => $request->main_file,
                    'name' => null,
                    'source' => null,
                ];
            }
        } elseif ($required) {
            throw new Exception(translate(':field Cannot be empty', ['field' => 'Main file']));
        }

        // Gallery
        if ($category->isImagePreview()) {
            if ($request->filled('gallery')) {
                if ($required && count($request->gallery) < 0) {
                    throw new Exception(translate(':field Cannot be empty', ['field' => 'Gallery']));
                }

                $gallery = [];
                foreach ($request->gallery as $galleryItem) {
                    $galleryFile = UploadedFile::where('seller_id', $sellerId)
                        ->where('id', hash_decode($galleryItem))->notExpired()->first();

                    if (!$galleryFile) {
                        throw new Exception(translate('One or more of the selected files are expired or not exist'));
                    }

                    if (!in_array($galleryFile->mime_type, $this->imageMimeTypes)) {
                        throw new Exception(translate('Gallery images should be :mimes', [
                            'mimes' => implode(', ', $this->imageMimeTypes)
                        ]));
                    }

                    $gallery[] = $galleryFile->path;
                }

                if ($strict) {
                    $maxGalleryImagesLimit = (int) ($category->gallery_images_count ?? (@$productSettings->max_files ?? 10));
                    if (count($gallery) > $maxGalleryImagesLimit) {
                        throw new Exception(translate('Maximum allowed gallery images is :maximum', [
                            'maximum' => $maxGalleryImagesLimit
                        ]));
                    }
                }

                $response['gallery'] = $gallery;
            }
        }

        return (object) $response;
    }

    /**
     * Validate the quality (dimensions, etc.) of files currently saved to a product.
     * This is used during the publication process for drafts.
     */
    public function validateFileQualities(Product $product): void
    {
        $category = $product->category;
        $productSettings = settings('product');
        $sellerId = $product->seller_id;
        $manager = ImageManager::gd();

        // 1. Preview Image Quality & Size
        if ($product->preview_image) {
            try {
                // Check Size (using file info from DB if possible, but here we check actual path)
                // Actually, it's better to fetch the UploadedFile record to get the size without reading full content
                $uploadedFile = UploadedFile::where('seller_id', $sellerId)->where('path', $product->preview_image)->first();
                $previewImageFileSize = $category->preview_file_size ?? @$productSettings->max_file_size;
                if ($uploadedFile && $uploadedFile->size > $previewImageFileSize) {
                     throw new Exception(translate('Preview image size should not exceed :size', ['size' => formatFileSize($previewImageFileSize)]));
                }

                $fileContents = readFromStorage($product->preview_image);
                $image = $manager->read($fileContents);

                if ($category->isImagePreview()) {
                    $maxWidth = @$productSettings->max_preview_img_width ?? 1200;
                    $maxHeight = @$productSettings->max_preview_img_height ?? 800;
                    if ($image->width() !== $maxWidth && $image->height() !== $maxHeight) {
                        throw new Exception(translate('Preview image size must be :dimensions px (Current: :current)', [
                            'dimensions' => $maxWidth . 'x' . $maxHeight,
                            'current' => $image->width() . 'x' . $image->height()
                        ]));
                    }
                } else {
                    $thumbnailSize = 120;
                    if ($image->width() !== $thumbnailSize || $image->height() !== $thumbnailSize) {
                        throw new Exception(translate('Thumbnail image size must be :dimensions px', ['dimensions' => $thumbnailSize . 'x' . $thumbnailSize]));
                    }
                    if ($image->width() !== $image->height()) {
                        throw new Exception(translate('Thumbnail image must be square'));
                    }
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            } catch (\Throwable $e) {
                throw new Exception(translate('Unable to verify preview image quality.'));
            }
        }

        // 2. Main File Size
        if ($product->main_file && $product->isMainFileLocal()) {
            $uploadedFile = UploadedFile::where('seller_id', $sellerId)->where('path', $product->main_file['path'])->first();
            $mainFileSize = $category->main_file_size ?? @$productSettings->max_file_size;
            if ($uploadedFile && $uploadedFile->size > $mainFileSize) {
                throw new Exception(translate('Main file size should not exceed :size', ['size' => formatFileSize($mainFileSize)]));
            }
        }

        // 3. Gallery Image Qualities & Count
        if ($category->isImagePreview() && is_array($product->gallery)) {
            $maxGalleryImagesLimit = (int) ($category->gallery_images_count ?? (@$productSettings->max_files ?? 10));
            if (count($product->gallery) > $maxGalleryImagesLimit) {
                throw new Exception(translate('Maximum allowed gallery images is :maximum', [
                    'maximum' => $maxGalleryImagesLimit
                ]));
            }

            foreach ($product->gallery as $galleryImagePath) {
                try {
                    $fileContents = readFromStorage($galleryImagePath);
                    $image = $manager->read($fileContents);
                } catch (\Throwable $e) {
                    throw new Exception(translate('One or more gallery images are unreadable.'));
                }
            }
        }
    }

    private function validateFilesNotDuplicated(object $productFiles): void
    {
        $values = collect([
            $productFiles->preview_type,
            $productFiles->preview_image,
            $productFiles->preview_video,
            $productFiles->preview_audio,
            $productFiles->main_file,
        ])->filter();

        if ($values->unique()->count() !== $values->count()) {
            throw new Exception(translate('You cannot use the same file in two different fields'));
        }
    }

    /**
     * Clean up uploaded file records after product insert/update.
     */
    public function handleFileDeletion(Request $request, ?Product $product = null): void
    {
        if ($request->filled('preview_image')) {
            if ($product) {
                $product->deletePreviewImage();
            }
            $this->deleteUploadedFile($request->preview_image);
        }

        if ($request->filled('preview_video')) {
            if ($product) {
                $product->deletePreviewVideo();
            }
            $this->deleteUploadedFile($request->preview_video);
        }

        if ($request->filled('preview_audio')) {
            if ($product) {
                $product->deletePreviewAudio();
            }
            $this->deleteUploadedFile($request->preview_audio);
        }

        if ($request->filled('main_file')) {
            if ($product && $product->isMainFileExternal()) {
                $product->deleteMainFile();
            }
            if ($request->main_file_source == false) {
                $this->deleteUploadedFile($request->main_file);
            }
        }

        if ($request->filled('gallery')) {
            if ($product) {
                $product->deleteGallery();
            }
            foreach ($request->gallery as $galleryItem) {
                $this->deleteUploadedFile($galleryItem);
            }
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function handleDescription(string $description, bool $required = true): string
    {
        $description = sanitizeRichText($description);

        $textContent = strip_tags($description);
        if ($required && empty(trim($textContent))) {
            throw new Exception(translate('Description cannot be empty'));
        }

        return $description;
    }

    public function handleSupportInstructions(?string $instructions, bool $required = true): ?string
    {
        if (empty($instructions)) {
            if ($required) {
                throw new Exception(translate('Support instructions are required when support is enabled.'));
            }
            return null;
        }

        $instructions = sanitizeRichText($instructions);

        $textContent = strip_tags($instructions);
        if ($required && empty(trim($textContent))) {
            throw new Exception(translate('Support instructions cannot be empty.'));
        }

        return $instructions;
    }

    public function handleOptions(Request $request, ProductCategory $category, bool $required = true): ?array
    {
        $options = null;
        $categoryOptions = $category->options ?? [];
        if (count($categoryOptions) > 0) {
            $options = [];
            foreach ($categoryOptions as $categoryOption) {
                $optId = $categoryOption['id'];
                $optName = $categoryOption['name'];
                $isMultiple = $categoryOption['type'] == ProductCategory::MULTIPLE_SELECT;
                $isRequired = isset($categoryOption['is_required']) && $categoryOption['is_required'];

                $option = isset($request->options[$optId]) ? $request->options[$optId] : null;
                $allowedOptions = $categoryOption['options'] ?? [];
                if ($isMultiple) {
                    $requestOptions = $option ? $option : [];
                    if ($required && $isRequired && count($requestOptions) < 1) {
                        throw new Exception(translate(':field Cannot be empty', ['field' => $optName]));
                    }
                    foreach ($requestOptions as $requestOption) {
                        if ($requestOption && !in_array($requestOption, $allowedOptions)) {
                            throw new Exception(translate('Something went wrong, please refresh the page and try again.'));
                        }
                    }
                } else {
                    $requestOption = $option ? $option : null;
                    if ($required && $isRequired && empty($requestOption)) {
                        throw new Exception(translate(':field Cannot be empty', ['field' => $optName]));
                    }
                    if ($requestOption && !in_array($requestOption, $allowedOptions)) {
                        throw new Exception(translate('Something went wrong, please refresh the page and try again.'));
                    }
                }
                if ($option) {
                    $options[$optName] = $option;
                }
            }
        }
        return $options;
    }

    public function convertTextareaToArray($textareaValue): array
    {
        if (is_array($textareaValue)) {
            return array_slice(array_filter($textareaValue, function ($item) {
                return !empty(trim($item));
            }), 0, 6);
        }

        if (!$textareaValue || trim($textareaValue) === '') {
            return [];
        }

        return array_slice(
            array_filter(
                array_map('trim', explode(',', $textareaValue)),
                function ($item) {
                    return !empty($item);
                }
            ),
            0,
            self::MAX_EXTRA_FEATURES
        );
    }

    private function deleteUploadedFile(string $fileId): void
    {
        $uploadedFile = UploadedFile::where('id', hash_decode($fileId))
            ->notExpired()->first();
        if ($uploadedFile) {
            $uploadedFile->delete();
        }
    }

    public function createHistory(int $productId, int $sellerId, ProductHistoryTitle $title, ?string $message = null): void
    {
        $productHistory = new ProductHistory();
        $productHistory->seller_id = $sellerId;
        $productHistory->product_id = $productId;
        $productHistory->title = $title;
        $productHistory->body = $message;
        $productHistory->save();
    }

    /**
     * Generate a unique name for a product.
     */
    private function generateUniqueName(string $name, ?int $ignoreId = null): string
    {
        $originalName = $name;
        $count = 1;

        while (Product::withoutGlobalScopes()->withTrashed()->where('name', $name)->when($ignoreId, function ($query, $id) {
            return $query->where('id', '!=', $id);
        })->exists()) {
            $name = $originalName . '-' . $count++;
        }

        return $name;
    }

    /**
     * Generate a unique slug for a product.
     */
    private function generateUniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $originalSlug = $slug;
        $count = 1;

        while (Product::withoutGlobalScopes()->withTrashed()->where('slug', $slug)->when($ignoreId, function ($query, $id) {
            return $query->where('id', '!=', $id);
        })->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    /**
     * Enforce tag limits based on product settings.
     */
    private function enforceTagLimits(mixed $tags, $productSettings): void
    {
        if (empty($tags)) return;

        $tagsArray = is_array($tags) ? $tags : explode(',', (string) $tags);
        $maxTags = (int) (@$productSettings->maximum_tags ?? 10);

        if (count($tagsArray) > $maxTags) {
            throw new Exception(translate('You can add up to :max tags', ['max' => $maxTags]));
        }
    }

    /**
     * Enforce limits on extra features.
     */
    private function enforceFeatureLimits(mixed $regular, mixed $extended): void
    {
        $regularArray = $this->convertTextareaToArray($regular);
        $extendedArray = $this->convertTextareaToArray($extended);

        if (count($regularArray) > self::MAX_EXTRA_FEATURES || count($extendedArray) > self::MAX_EXTRA_FEATURES) {
            throw new Exception(translate('Maximum :max extra features allowed', ['max' => self::MAX_EXTRA_FEATURES]));
        }
    }
}
