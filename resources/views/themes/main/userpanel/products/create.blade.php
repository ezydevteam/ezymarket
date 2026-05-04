@extends('themes.main.userpanel.layout')
@section('title', translate('New Product Submission'))
@section('header_title', translate('New Product Submission'))
@section('description', translate('Submit your product to the marketplace'))
@section('back', route('user.product.drafts'))
@section('container', 'userpanel-container-md')

@section('content')
<div id="productSubmission" class="product-submission-section">

    @php
    $productSettings = settings('product');
    $config = [
        'buyer_fee' => [
            'regular' => $category?->regular_buyer_fee ?? 0,
            'extended' => $category?->extended_buyer_fee ?? 0,
        ],
        'max_tags' => intval(@$productSettings->maximum_tags),
        'load_files_route' => $category ? route('user.product.files.load', hash_encode($category->id)) : '',
        'save_draft_route' => route('user.product.save_draft'),
        'category_data_route' => route('user.product.category.data', ':slug'),
        'maximum_drafts' => (int) @$productSettings->maximum_drafts,
        'current_drafts_count' => (int) @$draftCount,
        'upload' => $category ? [
            'url' => route('user.product.upload', hash_encode($category->id)),
            'max_files' => (int) ($productSettings->max_files ?? 0) - ($uploadedFiles ? $uploadedFiles->count() : 0),
            'max_file_size' => (int) ($productSettings->max_file_size ?? 0),
            'allowed_types' => $category->getAllowedFileTypes(),
        ] : null,
    ];
    @endphp

    <form action="{{ route('user.product.store') }}" id="productSubmissionForm" method="POST"
        data-config="{{ json_encode($config) }}">
        @csrf

        @if(!isset($draft) && @$draftCount >= @$productSettings->maximum_drafts)
        <div class="alert alert-warning border-0 rounded-4 mb-4">
            <div class="d-flex">
                <div class="flex-shrink-0">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="alert-heading fw-bold mb-1">{{ translate('Draft Limit Reached') }}</h6>
                    <p class="mb-0 small">{{ translate('You have reached the maximum limit of :max drafts. Auto-save is disabled and you will not be able to save this product as a draft until you delete or publish existing ones.', ['max' => @$productSettings->maximum_drafts]) }}</p>
                </div>
            </div>
        </div>
        @endif

        @if(isset($draft))
        <input type="hidden" name="draft_id" value="{{ $draft->id }}">
        @endif

        <!-- Simple Success Message -->
        <div id="draftSuccessMsg" class="simple-success-message">{{ translate('Saved') }}</div>

        <!-- Section 1: Meta Information -->
        <div id="section1">
            <!-- Name and Description -->
            <div class="product-submission-card mb-4">
                <div class="card-v-header">
                    <h5 class="submission-title">
                        <span class="title-icon"><i class="bi bi-file-text"></i>
                        </span>{{ translate('Name & Description') }}
                    </h5>
                </div>
                <div class="card-v-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">{{ translate('Product Name') }}<span
                                    class="text-danger ms-1">*</span></label>
                            <input type="text" name="name" id="create_slug"
                                class="form-control form-control-md product-title-input"
                                data-slug="{{ route('user.product.slug') }}" maxlength="150"
                                value="{{ old('name', $draft->name ?? '') }}"
                                placeholder="{{ translate('Enter product name') }}" autocomplete="off">
                            <p class="form-text mb-0 d-none" id="titleCharCount"></p>
                        </div>
                        <div class="col-12 {{ old('slug', $draft->slug ?? '') ? '' : 'd-none' }}" id="slugWrapper">
                            <label class="form-label fw-medium">{{ translate('Slug') }}<span
                                    class="text-danger ms-1">*</span></label>
                            <input type="text" name="slug" id="show_slug" class="form-control" maxlength="150"
                                placeholder="{{ translate('product-slug') }}"
                                value="{{ old('slug', $draft->slug ?? '') }}">
                            <div class="form-text">{{ translate('The slug is used to identify the product in the URL.')
                                }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">{{ translate('Description') }}<span
                                    class="text-danger ms-1">*</span></label>
                                <textarea id="productSubmitDescription" name="description" class="ckeditor"
                                placeholder="{{ translate('Describe your product in detail...')
                                }}">{!! sanitizeRichText(old('description', $draft->description ?? '')) !!}</textarea>
                            <p class="form-text mb-0 d-none" id="descriptionCharCount"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category and Attributes -->
            <div class="product-submission-card mb-4">
                <div class="card-v-header">
                    <h5 class="submission-title"><span class="title-icon"><i class="bi bi-tags"></i>
                    </span>{{ translate('Category & Attributes') }}</h5>
                </div>
                <div class="card-v-body">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <label class="form-label fw-medium">{{ translate('Category') }}<span
                                    class="text-danger ms-1">*</span></label>
                            <select name="category" id="productCategorySelect"
                                class="form-select form-select-md selectpicker" data-size="5"
                                title="{{ translate('Select a Category') }}" data-live-search="true" required>
                                @foreach ($categories as $mainCategory)
                                <option value="{{ $mainCategory->slug }}" @selected($category?->id ==
                                    $mainCategory->id)>
                                    {{ $mainCategory->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6" id="subCategoryWrapper">
                            <label class="form-label fw-medium">{{ translate('Sub Category') }}</label>
                            <select name="sub_category" id="productSubCategorySelect"
                                class="form-select form-select-md selectpicker"
                                title="{{ translate('Select a Sub Category') }}" data-live-search="true" data-size="5">
                                @if($category)
                                @foreach ($category->subCategories ?? [] as $subCategory)
                                <option value="{{ $subCategory->slug }}" @selected(old('sub_category', $draft->
                                    subCategory->slug ?? '') == $subCategory->slug)>
                                    {{ $subCategory->name }}
                                </option>
                                @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="col-12" id="categoryOptionsWrapper">
                            <div class="row g-4">
                                @if ($category && !empty($category->options))
                                @foreach ($category->options as $categoryOption)
                                @php
                                    $isMultiple = $categoryOption['type'] == \App\Models\Product\ProductCategory::MULTIPLE_SELECT;
                                    $isRequired = isset($categoryOption['is_required']) && $categoryOption['is_required'];
                                    $optId = $categoryOption['id'];
                                    $optName = $categoryOption['name'];
                                @endphp
                                <div class="col-lg-6">
                                    <label class="form-label fw-medium">{{ $optName }}{!!
                                        $isRequired ? '<span class="text-danger ms-1">*</span>' : ''
                                        !!}</label>
                                    <select
                                        name="options[{{ $optId }}]{{ $isMultiple ? '[]' : '' }}"
                                        class="form-select form-select-md selectpicker"
                                        title="{{ translate('Select one or more') }}" {{ $isMultiple ?
                                        'multiple' : '' }} data-size="5" data-live-search="true"
                                        {{ $isRequired ? 'required' : '' }}>
                                        @if (!$isRequired)
                                        <option value="">--</option>
                                        @endif
                                        @foreach ($categoryOption['options'] ?? [] as $option)
                                        @php
                                        $oldValues = old("options.{$optId}");

                                        if (is_null($oldValues)) {
                                            $oldValues = $draft->options[$optName] ?? null;
                                        }

                                        if ($isMultiple) {
                                            $selected = is_array($oldValues) && in_array((string)$option,
                                            array_map('strval', $oldValues), true);
                                        } else {
                                            $selected = !is_array($oldValues) && (string)$oldValues === (string)$option;
                                        }
                                        @endphp
                                        <option value="{{ $option }}" @selected($selected)>
                                            {{ $option }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label fw-medium">{{ translate('Version') }}</label>
                            <input type="text" name="version" class="form-control form-control-md"
                                placeholder="1.0 or 1.0.1" value="{{ old('version', $draft->version ?? '') }}">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label fw-medium">{{ translate('Preview Link') }}</label>
                            <input type="url" name="demo_link" class="form-control form-control-md"
                                placeholder="https://www.example.com/preview"
                                value="{{ old('demo_link', $draft->demo_link ?? '') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">{{ translate('Tags') }}<span
                                    class="text-danger ms-1">*</span></label>
                            <input type="text" name="tags" class="tags" id="product-tags"
                                placeholder="{{ translate('Type tag and press Enter...') }}"
                                value="{{ old('tags', $draft->tags ?? '') }}">
                            <div class="form-text">{{ translate('Type tag and click enter, maximum :maximum_tags
                                tags.', ['maximum_tags' =>
                                @$settings->product->maximum_tags]) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Files Upload -->
        <div id="section2">
            <div id="filesBoxWrapper">
                @themeInclude('userpanel.products.includes.files-box')
            </div>
        </div>

        <!-- Section 3: Pricing -->
        <div id="section3">
            <div class="product-submission-card price-input-section mb-4">
                <div class="card-v-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="submission-title"><span class="title-icon"><i class="bi bi-cash-stack"></i>
                        </span>{{ translate('Product Pricing') }}</h5>
                        @if (@$settings->links->licenses_terms_link)
                        <a class="text-gray-600 small hover-primary-underline" href="{{ @$settings->links->licenses_terms_link }}"
                            target="_blank">{{ translate('Licenses terms') }}<i class="bi bi-chevron-right small ms-1"></i></a>
                        @endif
                    </div>
                </div>
                <div class="card-v-body">
                    <div class="row g-4">
                        <div class="col-md-12 col-lg-4 col-xxl-5">
                            @themeInclude('userpanel.partials.input-price', [
                            'label' => translate('Regular Price'),
                            'id' => 'regular-license-price',
                            'name' => 'regular_license_price',
                            'min' => @$settings->product->minimum_price,
                            'max' => @$settings->product->maximum_price,
                            'required' => true,
                            'star_required' => true,
                            'value' => old('regular_license_price', $draft->regular_price ?? '')
                            ])
                        </div>
                        <div class="col-md-12 col-lg-4 col-xxl-3">
                            @themeInclude('userpanel.partials.input-price', [
                            'label' => translate('Buyer fee'),
                            'id' => 'regular-buyer-fee',
                            'value' => $category?->regular_buyer_fee ?? 0,
                            'disabled' => true,
                            ])
                        </div>
                        <div class="col-md-12 col-lg-4 col-xxl-4">
                            @themeInclude('userpanel.partials.input-price', [
                            'label' => translate('Total Purchase price'),
                            'id' => 'regular-license-purchase-price',
                            'value' => 0,
                            'disabled' => true,
                            ])
                        </div>
                        <div class="col-md-12 col-lg-4 col-xxl-5">
                            @themeInclude('userpanel.partials.input-price', [
                            'label' => translate('Extended Price'),
                            'id' => 'extended-license-price',
                            'name' => 'extended_license_price',
                            'min' => 0,
                            'max' => @$settings->product->maximum_price,
                            'required' => false,
                            'value' => old('extended_license_price', $draft->extended_price ?? ''),
                            ])
                        </div>
                        <div class="col-md-12 col-lg-4 col-xxl-3">
                            @themeInclude('userpanel.partials.input-price', [
                            'label' => translate('Buyer fee'),
                            'id' => 'extended-buyer-fee',
                            'value' => $category?->extended_buyer_fee ?? 0,
                            'disabled' => true,
                            ])
                        </div>
                        <div class="col-md-12 col-lg-4 col-xxl-4">
                            @themeInclude('userpanel.partials.input-price', [
                            'label' => translate('Total Purchase price'),
                            'id' => 'extended-license-purchase-price',
                            'value' => 0,
                            'disabled' => true,
                            ])
                        </div>
                    </div>
                    <div class="form-text">{{ translate('Enter 0 to disable extended price') }}</div>
                </div>
            </div>

            @if(@$settings->product->price_label_status)
            <!-- Price Label -->
            <div class="product-submission-card mb-4">
                <div class="card-v-header">
                    <h5 class="submission-title"><span class="title-icon"><i class="bi bi-tag"></i>
                    </span>{{ translate('Price Label') }}</h5>
                </div>
                <div class="card-v-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="regular_price_label" class="form-label fw-medium">{{ translate('Regular Price Label')
                                }}</label>
                            <input type="text" class="form-control form-control-md" id="regular_price_label"
                                name="regular_price_label" maxlength="40" placeholder="For single use"
                                value="{{ old('regular_price_label', $draft->regular_price_label ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="extended_price_label" class="form-label fw-medium">{{ translate('Extended Price Label')
                                }}</label>
                            <input type="text" class="form-control form-control-md" id="extended_price_label"
                                name="extended_price_label" maxlength="40" placeholder="For extended use"
                                value="{{ old('extended_price_label', $draft->extended_price_label ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Free product Option -->
            <div class="product-submission-card mb-4">
                <div class="card-v-header">
                    <h5 class="submission-title"><span class="title-icon"><i class="bi bi-currency-exchange"></i>
                    </span>{{ translate('Product Selling Option') }}</h5>
                </div>
                <div class="card-v-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="support-option-card" data-free="no">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="free_product"
                                        class="form-check-input free-product-option me-3" id="op1" value="0"
                                        @checked(old('free_product', $draft->is_free ?? 0) == 0)>
                                    <div>
                                        <h6 class="mb-1">{{ translate('Paid product') }}</h6>
                                        <p class="text-muted mb-0">{{ translate('User must purchase') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="support-option-card" data-free="yes">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="free_product"
                                        class="form-check-input free-product-option me-3" id="op2" value="1"
                                        @checked(old('free_product', $draft->is_free ?? 0) == 1)>
                                    <div>
                                        <h6 class="mb-1">{{ translate('Free product') }}</h6>
                                        <p class="text-muted mb-0">{{ translate('User may not purchase') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="mt-4 {{ old('free_product', $draft->is_free ?? 0) == 1 ? '' : 'd-none' }} purchasing-option">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="support-option-card">
                                    <div class="d-flex align-items-center">
                                        <input type="radio" name="purchasing_status" class="form-check-input me-3"
                                            id="opg1" value="1" @checked(old('purchasing_status',
                                            $draft->purchasing_status ?? 1) == 1)>
                                        <div>
                                            <h6 class="mb-1">{{ translate('Enable Purchasing') }}</h6>
                                            <p class="text-muted mb-0">{{ translate('Both free & paid') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="support-option-card">
                                    <div class="d-flex align-items-center">
                                        <input type="radio" name="purchasing_status" class="form-check-input me-3"
                                            id="opg2" value="0" @checked(old('purchasing_status',
                                            $draft->purchasing_status ?? 1) == 0)>
                                        <div>
                                            <h6 class="mb-1">{{ translate('Disable Purchasing') }}</h6>
                                            <p class="text-muted mb-0">{{ translate('Completely free') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="form-text mb-0">{{ translate('You can also allow the purchase option along
                            with the free download in case anyone wants to purchase the product.') }}</p>
                    </div>
                </div>
            </div>
            <!-- Support Options -->
            <div class="product-submission-card mb-4">
                <div class="card-v-header">
                    <h5 class="submission-title"><span class="title-icon"><i class="bi bi-life-preserver"></i>
                    </span>{{ translate('Support After Sales') }}</h5>
                </div>
                <div class="card-v-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="support-option-card" data-support="no">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="support" class="form-check-input support-option me-3"
                                        id="support1" value="0" @checked(old('support', $draft->is_supported ?? 0) ==
                                    0)>
                                    <div class="support-label" for="support1">
                                        <h6 class="mb-1">{{ translate('No Support') }}</h6>
                                        <p class="text-muted mb-0">{{ translate('No support after sales') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="support-option-card" data-support="yes">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="support" class="form-check-input support-option me-3"
                                        id="support2" value="1" @checked(old('support', $draft->is_supported ?? 0) ==
                                    1)>
                                    <div class="support-label" for="support2">
                                        <h6 class="mb-1">{{ translate('With Support') }}</h6>
                                        <p class="text-muted mb-0">{{ translate('Support after sales') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="support-instructions mt-4 {{ old('support', $draft->is_supported ?? 0) == 1 ? '' : 'd-none' }}"
                        id="supportInstructions">

                        @php $supportPackages = supportPackages(); @endphp
                        @if (@$settings->product->support_status && $supportPackages->count() > 0)
                        <div class="support-package-pricing p-3 border-0 bg-light rounded-4 mb-3">
                            <div class="row g-4">
                                <!-- Support Period Selection -->
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">{{ translate('Choose Support Package') }}</label>
                                    <select name="support_package_id" class="form-select selectpicker" id="supportPackageSelect">
                                        @foreach ($supportPackages as $supportPackage)
                                        <option class="support-package-options" value="{{ $supportPackage->id }}"
                                            data-rate-percentage="{{ $supportPackage->getPercentage() }}"
                                            data-rate-fixed="{{ $supportPackage->getFixed() }}"
                                            data-title="{{ $supportPackage->name }}" @selected($supportPackage->id ==
                                            old('support_package_id', $draft->support_package_id ?? ''))>
                                            {{ $supportPackage->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Regular Support Price -->
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">{{ translate('Regular License Support Price') }}</label>
                                    <div class="input-group input-group-md">
                                        <span class="input-group-text">{{ currency_symbol() }}</span>
                                        <input type="text" id="regularSupportPriceDisplay" class="form-control"
                                            value="0.00" disabled readonly>
                                    </div>
                                </div>

                                <!-- Extended Support Price -->
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">{{ translate('Extended License Support Price') }}</label>
                                    <div class="input-group input-group-md">
                                        <span class="input-group-text">{{ currency_symbol() }}</span>
                                        <input type="text" id="extendedSupportPriceDisplay" class="form-control"
                                            value="0.00" disabled readonly>
                                    </div>
                                </div>
                            </div>
                            @php
                                $freePackage = freeSupportPackage();
                                $freeDays = $freePackage?->days ?? 0;
                                $totalDays = 365;
                                $paidDays = max($totalDays - $freeDays, 0);

                                // convert to months (approx)
                                $freeMonths = round($freeDays / 30.44);
                                $paidMonths = round($paidDays / 30.44);
                            @endphp
                            <div class="form-text mt-2 text-success">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                {{ translate(':duration free support will be included by default. e.g for :total months package, :free months free + :paid months paid support.', [
                                    'duration' => $freePackage ? $freePackage->name : 'Standard',
                                    'total' => round($totalDays / 30),
                                    'free' => $freeMonths,
                                    'paid' => $paidMonths,
                                ]) }}
                            </div>
                        </div>
                        @endif

                        <label class="form-label fw-semibold"><i class="bi bi-file-text me-2"></i>{{ translate('Support
                            Instructions') }}<span class="text-danger ms-1">*</span></label>
                        <textarea id="support_instructions_editor" name="support_instructions"
                            class="form-control support-instructions-text ckeditor" data-editor-type="basic"
                            rows="6"
                            placeholder="{{ translate('Write instructions for buyers to get support...') }}">{!! sanitizeRichText(old('support_instructions', $draft->support_instructions ?? '')) !!}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Additional Options -->
        <div id="section4">
            @if(@$settings->product->additional_features_status)
            <!-- Extra features -->
            <div class="product-submission-card mb-4">
                <div class="card-v-header">
                    <h5 class="submission-title"><span class="title-icon"><i class="bi bi-gem"></i>
                    </span>{{ translate('Additional Features') }}</h5>
                </div>
                <div class="card-v-body">
                    <div class="form-group d-flex align-items-center justify-content-between">

                        <label class="form-label fw-medium">
                            <i class="bi bi-diagram-3 me-2"></i>{{ translate('Product has additional features?') }}
                        </label>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input fs-3" type="checkbox" role="switch"
                                id="additionalFeatureSwitch" name="is_additional_features" value="1" {{
                                old('is_additional_features', ($draft->regular_extra_features ??
                            $draft->extended_extra_features ?? null)) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="additional-features-form {{ old('is_additional_features', ($draft->regular_extra_features ?? $draft->extended_extra_features ?? null)) ? '' : 'd-none' }} mt-4"
                        id="additionalFeatureGroup">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <label for="regular_extra_features" class="form-label fw-medium">
                                    {{ translate('Regular Price Additional Features') }}
                                </label>
                                <textarea class="form-control additional-feature-text" id="regular_extra_features"
                                    name="regular_extra_features" rows="6"
                                    placeholder="{{ translate('Lifetime free update, Single projects allowed, ...') }}">{{ old('regular_extra_features') ? (is_array(old('regular_extra_features')) ? implode(', ', old('regular_extra_features')) : old('regular_extra_features')) : (isset($draft) && is_array($draft->regular_extra_features) ? implode(', ', $draft->regular_extra_features) : '') }}</textarea>

                                <p class="form-text">
                                    {{ translate('Comma separated and maximum 6 features allowed.') }}
                                </p>
                            </div>
                            <div class="col-lg-6">
                                <label for="extended_extra_features" class="form-label fw-medium">
                                    {{ translate('Extended Price Additional Features') }}
                                </label>
                                <textarea class="form-control additional-features-text" id="extended_extra_features"
                                    name="extended_extra_features" rows="6"
                                    placeholder="{{ translate('Unlimited projects allowed, Money back guarantee, ...') }}">{{ old('extended_extra_features') ? (is_array(old('extended_extra_features')) ? implode(', ', old('extended_extra_features')) : old('extended_extra_features')) : (isset($draft) && is_array($draft->extended_extra_features) ? implode(', ', $draft->extended_extra_features) : '') }}</textarea>
                                <p class="form-text">
                                    {{ translate('Comma separated and maximum 6 features allowed.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if(@$settings->product->custom_services_status)
            <!-- Custom Services Section -->
            <div class="product-submission-card mb-4">
                <div class="card-v-header">
                    <h5 class="submission-title"><span class="title-icon"><i class="bi bi-list-task"></i>
                    </span>{{ translate('Custom Services') }}</h5>
                </div>

                <div class="card-v-body">
                    <!-- Bootstrap Form Switch -->
                    <div class="form-group d-flex align-items-center justify-content-between">

                        <label class="form-label fw-medium">
                            <i class="bi bi-cash-coin me-2"></i>{{ translate('Are you available for custom services?')
                            }}
                        </label>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input fs-3" type="checkbox" role="switch" id="customServicesSwitch"
                                name="has_custom_services" value="1" {{ old('has_custom_services',
                                $draft->has_custom_services ?? 0) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <!-- Custom Services Description (Hidden by default) -->
                    <div class="custom-services-form {{ old('has_custom_services', $draft->has_custom_services ?? 0) ? '' : 'd-none' }} mt-4"
                        id="customServicesGroup">
                        <label for="custom_services" class="form-label fw-medium">
                            <i class="bi bi-journal-text me-2"></i>{{ translate('Brief Description') }}
                            <span class="text-danger ms-1">*</span>
                        </label>
                        <textarea class="form-control form-control-md custom-services-text" id="custom_services"
                            name="custom_services" rows="5"
                            placeholder="{{ translate('Describe the custom services you offer for this product...') }}">{{ old('custom_services', $draft->custom_services ?? '') }}</textarea>

                        <small class="form-text mt-2">
                            {{ translate('Examples: Custom modifications, installation support, training, consultation,
                            etc.') }}
                        </small>
                    </div>
                </div>
            </div>
            @endif

            @if(@$settings->product->terms_conditions_status)
            <div class="product-submission-card">
                <div class="card-v-header">
                    <h5 class="submission-title"><span class="title-icon"><i class="bi bi-file-text"></i>
                    </span>{{ translate('Terms & Conditions') }}</h5>
                </div>
                <div class="card-v-body">
                    <div class="terms-content p-3 border rounded-2 overflow-y-auto" style="max-height: 200px;">
                        <p class="small text-dark fw-medium">
                            {{ translate('By submitting an product to our marketplace, you agree to the following terms
                            and conditions:') }}
                        </p>
                        <ul class="small text-gray-700 mb-0">
                            <li>{{ translate('You own or have the legal right to distribute the submitted content') }}
                            </li>
                            <li>{{ translate('Your product does not infringe on any copyright, trademark, or
                                intellectual property rights') }}</li>
                            <li>{{ translate('You agree to provide support as specified in your submission') }}</li>
                            <li>{{ translate('The product will be reviewed before being published') }}</li>
                            <li>{{ translate('We reserve the right to reject products that don\'t meet our quality
                                standards') }}</li>
                            <li>{{ translate('You understand the revenue sharing model and fees') }}</li>
                            <li>{{ translate('You agree to our general platform terms of service') }}</li>
                        </ul>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input border-2" type="checkbox" value="1" id="termsCheck" @required(@$settings->product->terms_conditions_status)>
                        <label class="form-check-label small" for="termsCheck">
                            {{ translate('I have read and agree to the terms and conditions') }}
                        </label>
                    </div>
                </div>
            </div>
            @endif

            <div class="product-submission-cards mt-4 mb-4">
                <div class="card-v-body text-center">
                    <x-captcha />
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Action Bar -->
        <div class="submission-sticky-bar">
            <div class="sticky-bar-info d-none d-lg-block">
                <span class="text-gray-600 small">
                    <i class="bi bi-info-circle me-1"></i>
                   {!! translate('Need help? <a href="' . route('contact.index') . '"
                    target="_blank" class="fw-medium text-reset hover-primary">Get support</a>.') !!}
                </span>
            </div>
            <div class="submission-actions d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-modern btn-sm rounded-pill px-3" id="saveDraftBtn">
                    <i class="bi bi-save2 me-2"></i>{{ translate('Save Draft') }}
                </button>
                <button type="submit" class="btn btn-primary btn-modern btn-sm rounded-pill fw-medium px-3" id="productSubmitBtn">
                    <i class="bi bi-check2-circle me-2"></i>{{ translate('Submit Product') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
