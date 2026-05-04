@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('section', translate('Editing - :product', ['product' => $product->name]))
@section('title', $product->name)

@section('content')
@php
$config = [
    'buyer_fee' => [
        'regular' => $category->regular_buyer_fee,
        'extended' => $category->extended_buyer_fee,
    ],
    'max_tags' => intval(@$settings->product->maximum_tags),
    'load_files_route' => route('user.product.files.load', hash_encode($category->id)),
    'category_data_route' => route('user.product.category.data', ':slug'),
    'upload' => [
        'url' => route('user.product.upload', hash_encode($category->id)),
        'max_files' => (int) ($settings->product->max_files ?? 0) - ($uploadedFiles ? $uploadedFiles->count() : 0),
        'max_file_size' => (int) ($settings->product->max_file_size ?? 0),
        'allowed_types' => $category->getAllowedFileTypes(),
    ],
];
@endphp

<div id="productSubmission" class="ajax-tabs">
    @themeInclude('userpanel.products.includes.tabs-nav')
    <div class="ajax-tabs-content">
        <div class="row g-4">
            @if (!$product->isRestricted())
            <div class="col-lg-8">
                <form action="{{ route('user.product.update', $product->id) }}" id="productSubmissionForm" method="POST"
                    data-config="{{ json_encode($config) }}">
                    @csrf
                    <div class="product-submission-card mb-4">
                        <div class="card-v-header">
                            <h5 class="submission-title">
                                <span class="title-icon"><i class="bi bi-file-text"></i>
                                </span>{{ translate('Name & Description') }}
                            </h5>
                        </div>
                        <div class="card-v-body">
                            <div class="row g-3 mb-3">
                                <div class="col-12">
                                    <label class="form-label fw-medium">{{ translate('Product Name') }}<span
                                            class="text-danger ms-1">*</span></label>
                                    <input type="text" name="name" id="create_slug"
                                    data-slug="{{ route('user.product.slug') }}"
                                    class="form-control form-control-md"
                                    maxlength="150" value="{{ old('name', $product->name) }}">
                                </div>
                                @if($product->isNeedsRevision())
                                <div class="col-12" id="slugWrapper">
                                    <label class="form-label fw-medium">{{ translate('Slug') }}<span
                                            class="text-danger ms-1">*</span></label>
                                    <input type="text" name="slug" id="show_slug" class="form-control" maxlength="150"
                                        placeholder="{{ translate('product-slug') }}"
                                        value="{{ old('slug', $product->slug) }}">
                                    <div class="form-text">{{ translate('The slug is used to identify the product in the
                                         URL.') }}</div>
                                </div>
                                @else
                                <input type="hidden" name="slug" id="show_slug" value="{{ $product->slug }}">
                                @endif
                                <div class="col-12">
                                    <label class="form-label fw-medium">{{ translate('Description') }}<span
                                            class="text-danger ms-1">*</span></label>
                                    <textarea id="productSubmitDescription" name="description"
                                        class="ckeditor">{!! sanitizeRichText(old('description', $product->description)) !!}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="product-submission-card mb-4">
                        <div class="card-v-header">
                            <h5 class="submission-title"><span class="title-icon"><i class="bi bi-tags"></i>
                                </span>{{ translate('Category & Attributes') }}</h5>
                        </div>
                        <div class="card-v-body">
                            <div class="row g-4 mb-3">
                                <div class="col-lg-6">
                                    <label class="form-label fw-medium">{{ translate('Category') }}</label>
                                    <select class="form-select form-select-md selectpicker" id="productCategorySelect" disabled>
                                        @foreach ($categories as $mainCategory)
                                        <option value="{{ $mainCategory->slug }}" @selected($category->id ==
                                            $mainCategory->id)>
                                            {{ $mainCategory->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6" id="subCategoryWrapper">
                                    <label class="form-label fw-medium">{{ translate('Sub Category') }}</label>
                                    <select name="sub_category" id="productSubCategorySelect" class="form-select form-select-md selectpicker"
                                        title="{{ translate('Select a Sub Category') }}" data-live-search="true" data-size="5">
                                        <option value="">--</option>
                                        @foreach ($category->subCategories ?? [] as $subCayegory)
                                        <option value="{{ $subCayegory->slug }}" @selected($product->subCategory &&
                                            $product->subCategory->id == $subCayegory->id)>
                                            {{ $subCayegory->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12" id="categoryOptionsWrapper">
                                    <div class="row g-4">
                                        @if (!empty($category->options))
                                        @foreach ($category->options as $categoryOption)
                                <div class="col-lg-6">
                                    @php
                                        $isMultiple = $categoryOption['type'] == \App\Models\Product\ProductCategory::MULTIPLE_SELECT;
                                        $isRequired = isset($categoryOption['is_required']) && $categoryOption['is_required'];
                                        $optId = $categoryOption['id'];
                                        $optName = $categoryOption['name'];
                                    @endphp
                                    <label class="form-label fw-medium">{{ $optName }}</label>
                                    <select
                                        name="options[{{ $optId }}]{{ $isMultiple ? '[]' : '' }}"
                                        class="form-select form-select-md selectpicker" title="--" {{
                                        $isMultiple ? 'multiple' : '' }}
                                        {{ $isRequired ? 'required' : '' }}>
                                        @if (!$isRequired)
                                        <option value="">--</option>
                                        @endif
                                        @foreach ($categoryOption['options'] ?? [] as $option)
                                        @php
                                        $oldValue = old("options.{$optId}");
                                        if (is_null($oldValue)) {
                                            $productOptions = $product['options'] ?? [];
                                            $oldValue = $productOptions[$optName] ?? null;
                                        }

                                        if ($isMultiple) {
                                            $selected = is_array($oldValue) && in_array((string)$option, array_map('strval',
                                            $oldValue), true);
                                        } else {
                                            $selected = !is_array($oldValue) && (string)$oldValue === (string)$option;
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
                                        placeholder="{{ translate('1.0 or 1.0.1') }}" value="{{ $product->version }}">
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label fw-medium">{{ translate('Preview Link') }}</label>
                                    <input type="url" name="demo_link" class="form-control form-control-md"
                                        placeholder="{{ translate('https://www.example.com/preview') }}"
                                        value="{{ $product->demo_link }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">{{ translate('Tags') }}</label>
                                    <input id="product-tags" type="text" name="tags" value="{{ $product->tags }}"
                                        required>
                                    <div class="form-text">
                                        {{ translate('Type tag and click enter, maximum :maximum_tags tags.', ['maximum_tags' =>
                                        @$settings->product->maximum_tags]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="filesBoxWrapper">
                        @themeInclude('userpanel.products.includes.files-box', ['product' => $product, 'category' =>
                        $category, 'uploadedFiles' => $uploadedFiles])
                    </div>
                    <div class="product-submission-card mb-4">
                        <div class="card-v-header d-flex align-items-center justify-content-between">
                            <h5 class="submission-title"><span class="title-icon">
                                <i class="bi bi-cash-stack"></i></span>{{ translate('Product Pricing') }}</h5>
                            @if (@$settings->links->licenses_terms_link)
                            <a class="text-gray-600 small hover-primary-underline" href="{{ @$settings->links->licenses_terms_link }}"
                                target="_blank">{{ translate('Licenses terms') }}<i class="bi bi-chevron-right small ms-1"></i></a>
                            @endif
                        </div>
                        <div class="card-v-body">
                            @if (!$product->hasDiscount())
                            <div class="row g-4 mb-3">
                                <div class="col-md-12 col-lg-4 col-xxl-5">
                                    @themeInclude('userpanel.partials.input-price', [
                                    'label' => translate('Regular Price'),
                                    'id' => 'regular-license-price',
                                    'name' => 'regular_license_price',
                                    'value' => $product->regular_price,
                                    'min' => @$settings->product->minimum_price,
                                    'max' => @$settings->product->maximum_price,
                                    'required' => true,
                                    ])
                                </div>
                                <div class="col-md-12 col-lg-4 col-xxl-3">
                                    @themeInclude('userpanel.partials.input-price', [
                                    'label' => translate('Buyer fee'),
                                    'id' => 'regular-buyer-fee',
                                    'value' => $category->regular_buyer_fee,
                                    'disabled' => true,
                                    ])
                                </div>
                                <div class="col-md-12 col-lg-4 col-xxl-4">
                                    @themeInclude('userpanel.partials.input-price', [
                                    'label' => translate('Purchase price'),
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
                                    'value' => $product->extended_price,
                                    'min' => 0,
                                    'max' => @$settings->product->maximum_price,
                                    'required' => true,
                                    ])
                                </div>
                                <div class="col-md-12 col-lg-4 col-xxl-3">
                                    @themeInclude('userpanel.partials.input-price', [
                                    'label' => translate('Buyer fee'),
                                    'id' => 'extended-buyer-fee',
                                    'value' => $category->extended_buyer_fee,
                                    'disabled' => true,
                                    ])
                                </div>
                                <div class="col-md-12 col-lg-4 col-xxl-4">
                                    @themeInclude('userpanel.partials.input-price', [
                                    'label' => translate('Purchase price'),
                                    'id' => 'extended-license-purchase-price',
                                    'value' => 0,
                                    'disabled' => true,
                                    ])
                                </div>
                            </div>
                            <div class="form-text">{{ translate('Enter 0 to disable extended price') }}</div>
                            @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <span>{{ translate('The price can not be updated while the product is on discount')
                                    }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @if(@$settings->product->price_label_status)
                    <div class="product-submission-card mb-4">
                        <div class="card-v-header">
                            <h5 class="submission-title"><span class="title-icon"><i class="bi bi-tag"></i>
                                </span>{{ translate('Price Label') }}</h5>
                        </div>
                        <div class="card-v-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="regular_price_label" class="form-label fw-medium">{{ translate('Regular Price
                                        Label') }}</label>
                                    <input type="text" class="form-control form-control-md" id="regular_price_label"
                                        name="regular_price_label" maxlength="40" placeholder="For single use"
                                        value="{{ $product->regular_price_label }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="extended_price_label" class="form-label fw-medium">{{ translate('Extended Price
                                        Label') }}</label>
                                    <input type="text" class="form-control form-control-md" id="extended_price_label"
                                        name="extended_price_label" maxlength="40" placeholder="For extended use"
                                        value="{{ $product->extended_price_label }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if (@$settings->product->free_product_option && !$product->isPremium())
                    <div class="product-submission-card mb-4">
                        <div class="card-v-header">
                            <h5 class="submission-title"><span class="title-icon"><i class="bi bi-currency-exchange"></i>
                                </span>{{ translate('Product Selling Option') }}</h5>
                        </div>
                        <div class="card-v-body">
                            <p>{{ translate('You can allow downloading your product for free.') }}</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="support-option-card" data-free="no">
                                        <div class="d-flex align-items-center">
                                            <input type="radio" name="free_product"
                                                class="form-check-input free-product-option me-3" id="op1" value="0"
                                                @checked(!$product->isFree())>
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
                                                @checked($product->isFree())>
                                            <div>
                                                <h6 class="mb-1">{{ translate('Free product') }}</h6>
                                                <p class="text-muted mb-0">{{ translate('User may not purchase') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 d-none purchasing-option">
                                <p>{{ translate('You can also allow the purchase option along with the free download in
                                    case anyone wants to purchase the product.') }}</p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="support-option-card">
                                            <div class="d-flex align-items-center">
                                                <input type="radio" name="purchasing_status"
                                                    class="form-check-input me-3" id="opg1" value="1"
                                                    @checked($product->isPurchasingEnabled())>
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
                                                <input type="radio" name="purchasing_status"
                                                    class="form-check-input me-3" id="opg2" value="0"
                                                    @checked(!$product->isPurchasingEnabled())>
                                                <div>
                                                    <h6 class="mb-1">{{ translate('Disable Purchasing') }}</h6>
                                                    <p class="text-muted mb-0">{{ translate('Completely free') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
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
                                            <input type="radio" name="support"
                                                class="form-check-input support-option me-3" id="support1" value="0"
                                                @checked(!$product->isSupported())>
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
                                            <input type="radio" name="support"
                                                class="form-check-input support-option me-3" id="support2" value="1"
                                                @checked($product->isSupported())>
                                            <div class="support-label" for="support2">
                                                <h6 class="mb-1">{{ translate('With Support') }}</h6>
                                                <p class="text-muted mb-0">{{ translate('Support after sales') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="support-instructions mt-4 {{ $product->isSupported() ? '' : 'd-none' }}" id="supportInstructions">

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
                                                    old('support_package_id', $product->support_package_id))>
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
                                    <div class="form-text">
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
                                    placeholder="{{ translate('Write instructions for buyers to get support...') }}">{!! sanitizeRichText($product->support_instructions) !!}</textarea>
                            </div>
                        </div>
                    </div>
                    @if(@$settings->product->additional_features_status)
                    <div class="product-submission-card mb-4">
                        <div class="card-v-header">
                            <h5 class="submission-title"><span class="title-icon"><i class="bi bi-gem"></i>
                                </span>{{ translate('Additional Features') }}</h5>
                        </div>
                        <div class="card-v-body">
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <label for="regular_extra_features" class="form-label fw-medium">
                                        {{ translate('Regular Price Additional Features') }}
                                    </label>
                                    <textarea class="form-control" id="regular_extra_features"
                                        name="regular_extra_features" rows="6"
                                        placeholder="{{ translate('Lifetime free update, Single projects allowed, ...') }}">{{ $product->getRegularExtraFeaturesString() }}</textarea>

                                    <p class="form-text">
                                        {{ translate('Comma separated and maximum 6 features allowed.') }}
                                    </p>
                                </div>
                                <div class="col-lg-6">
                                    <label for="extended_extra_features" class="form-label fw-medium">
                                        {{ translate('Extended Price Additional Features') }}
                                    </label>
                                    <textarea class="form-control" id="extended_extra_features"
                                        name="extended_extra_features" rows="6"
                                        placeholder="{{ translate('Unlimited projects allowed, Money back guarantee, ...') }}">{{ $product->getExtendedExtraFeaturesString() }}</textarea>
                                    <p class="form-text">
                                        {{ translate('Comma separated and maximum 6 features allowed.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if(@$settings->product->custom_services_status)
                    <div class="product-submission-card mb-4">
                        <div class="card-v-header">
                            <h5 class="submission-title"><span class="title-icon"><i class="bi bi-list-task"></i>
                                </span>{{ translate('Custom Services') }}</h5>
                        </div>
                        <div class="card-v-body">
                            <div class="form-group d-flex align-items-center justify-content-between">
                                <label class="form-label fw-medium">
                                    <i class="bi bi-cash-coin me-2"></i>{{ translate('Are you available for custom
                                    services?') }}
                                </label>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input fs-5" type="checkbox" role="switch"
                                        id="customServicesSwitch" name="has_custom_services" value="1"
                                        @checked($product->hasCustomServices())>
                                </div>
                            </div>
                            <div class="custom-services-form d-none mt-4" id="customServicesGroup">
                                <label for="custom_services" class="form-label fw-medium">
                                    <i class="bi bi-journal-text me-2"></i>{{ translate('Brief Description') }}
                                </label>
                                <textarea class="form-control form-control-md" id="custom_services"
                                    name="custom_services" rows="5"
                                    placeholder="{{ translate('Describe the custom services you offer for this product...') }}">{{ $product->custom_services }}</textarea>

                                <small class="form-text mt-2">
                                    {{ translate('Examples: Custom modifications, installation support, training,
                                    consultation, etc.') }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="product-submission-card">
                        <div class="card-v-body d-flex align-items-center justify-content-between">
                            <span class="text-gray-600 small">
                                <i class="bi bi-info-circle me-1"></i>
                                {!! translate('Need help? <a href="' . route('contact.index') . '"
                                    target="_blank" class="fw-medium text-reset hover-primary">Get support</a>.') !!}
                            </span>
                            <button type="submit" class="btn btn-lg btn-primary btn-modern rounded-pill px-5 action-confirm"><i
                                    class="bi bi-box-arrow-in-up fs-5 me-2"></i>
                                {{ @$settings->product->updating_require_review ? translate('Submit for Review') : translate('Update Product') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-4">
                @themeInclude('userpanel.products.includes.sidebar')
            </div>
            @else
                @themeInclude('userpanel.partials.restricted')
            @endif
        </div>
    </div>
</div>
@endsection
