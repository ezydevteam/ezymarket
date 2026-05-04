<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;

class ProductController extends Controller
{
    use HandlesValidation;

    /**
     * Display the product settings page.
     * @return View
     */
    public function index(): View
    {
        return view('admin.settings.product');
    }

    /**
     * Update the product settings.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'product.maximum_tags' => ['required', 'integer', 'min:1', 'max:100'],
            'product.minimum_price' => ['required', 'numeric', 'min:0'],
            'product.maximum_price' => ['required', 'numeric', 'min:0'],
            'product.maximum_drafts' => ['required', 'integer', 'min:1', 'max:100'],
            'product.max_files' => ['required', 'integer', 'min:1'],
            'product.max_file_size' => ['required', 'numeric', 'min:0.1'],
            'product.file_duration' => ['required', 'integer', 'min:1'],
            'product.convert_images_webp' => ['required', 'boolean'],
            'product.max_preview_img_width' => ['required', 'integer', 'min:100'],
            'product.max_preview_img_height' => ['required', 'integer', 'min:100'],
        ];

        if ($request->has('product.discount_status')) {
            $rules['product.discount_max_percentage'] = ['required', 'integer', 'min:1', 'max:90'];
            $rules['product.discount_max_days'] = ['required', 'integer', 'min:0', 'max:365'];
            $rules['product.discount_interval'] = ['required', 'integer', 'min:0', 'max:365'];
        } else {
            $rules['product.discount_max_percentage'] = ['nullable', 'integer', 'min:1', 'max:90'];
            $rules['product.discount_max_days'] = ['nullable', 'integer', 'min:0', 'max:365'];
            $rules['product.discount_interval'] = ['nullable', 'integer', 'min:0', 'max:365'];
        }

        $validator = $this->validateRequestWithoutInput($request, $rules);

        if ($validator instanceof RedirectResponse) {
            return $validator;
        }

        $productSettings = $request->input('product');

        // Convert Max File Size from MB to Bytes
        if (isset($productSettings['max_file_size'])) {
            $productSettings['max_file_size'] = (int) ($productSettings['max_file_size'] * 1048576);
        }

        // Handle Checkbox/Boolean Fields
        $booleanFields = [
            'adding_require_review',
            'updating_require_review',
            'free_product_option',
            'free_product_total_downloads',
            'free_products_require_login',
            'changelogs_status',
            'reviews_status',
            'comments_status',
            'support_status',
            'external_file_link_option',
            'buy_now_button',
            'discount_status',
            'price_label_status',
            'additional_features_status',
            'custom_services_status',
            'terms_conditions_status',
        ];

        foreach ($booleanFields as $field) {
            $productSettings[$field] = isset($productSettings[$field]) ? 1 : 0;
        }

        // Save Settings
        // We manually fetch and save to allow new keys (like max_preview_img_width) to be added
        // without being filtered out by Settings::updateSettings's strict key checking.
        $settings = Settings::where('key', 'product')->firstOrFail();
        $currentSettings = (array) $settings->value;
        $settings->value = array_merge($currentSettings, $productSettings);
        $settings->save();

        return $this->updatedBack();
    }
}
