@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('Product Settings'))
@section('content')
    <form id="productSettingsForm" action="{{ route('admin.settings.product.update') }}" method="POST">
        @csrf
        <div class="row g-4">
            {{-- General Configuration --}}
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-gear text-primary me-2"></i>
                            {{ translate('General Configuration') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label">{{ translate('Maximum Tags') }}</label>
                                <div class="input-group">
                                    <input type="number" name="product[maximum_tags]" class="form-control" min="1" max="100" value="{{ @$settings->product->maximum_tags }}" required>
                                    <span class="input-group-text text-muted">{{ translate('Tags') }}</span>
                                </div>
                                <div class="form-text">{{ translate('Limit the number of tags a seller can add.') }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Minimum Price') }}</label>
                                @include('admin.partials.input-price', ['name' => 'product[minimum_price]', 'value' => @$settings->product->minimum_price])
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Maximum Price') }}</label>
                                @include('admin.partials.input-price', ['name' => 'product[maximum_price]', 'value' => @$settings->product->maximum_price])
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">{{ translate('Maximum Drafts') }}</label>
                                <div class="input-group">
                                    <input type="number" name="product[maximum_drafts]" class="form-control" min="1" max="100" value="{{ @$settings->product->maximum_drafts }}" required>
                                    <span class="input-group-text text-muted">{{ translate('Drafts') }}</span>
                                </div>
                                <div class="form-text">{{ translate('Limit the number of drafts a seller can have.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Display Settings --}}
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-display text-danger me-2"></i>
                            {{ translate('Product Submission Settings') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            @php
                                $productSubmissionSettings = [
                                    'price_label_status' => 'Enable Price Label',
                                    'additional_features_status' => 'Enable Additional Features',
                                    'custom_services_status' => 'Enable Custom Services',
                                    'terms_conditions_status' => 'Enable Terms & Conditions',
                                ];
                            @endphp

                            @foreach($productSubmissionSettings as $key => $label)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between p-3 border rounded h-100">
                                    <span class="fw-medium">{{ translate($label) }}</span>
                                    <x-switch
                                        name="product[{{ $key }}]"
                                        :checked="@$settings->product->{$key}"
                                        :showLabel="false"
                                        size="lg"
                                        onLabel="{{ translate('Yes') }}"
                                        offLabel="{{ translate('No') }}"
                                    />
                                </div>
                            </div>
                            @endforeach

                            @if (!@$settings->cronjob->last_execution)
                            <div class="col-12">
                                <div class="d-flex p-3 rounded align-items-center bg-text-orange">
                                    <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                                    <div>
                                        <h6 class="mb-1 text-warning-emphasis">{{ translate('Cron Job Required') }}</h6>
                                        <p class="mb-0 small text-warning-emphasis">{{ translate('Refresh products every 24 hours.') }}</p>
                                    </div>
                                    <a href="{{ route('admin.system.cronjob.index') }}" class="btn btn-warning btn-sm ms-auto">{{ translate('Setup') }}</a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Features Grid --}}
             <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-sliders2 text-success me-2"></i>
                            {{ translate('Product Features') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            @php
                                $features = [
                                    'adding_require_review' => 'Adding products requires review',
                                    'updating_require_review' => 'Updating products requires review',
                                    'free_product_option' => 'Allow free products',
                                    'free_product_total_downloads' => 'Show downloads count (Free)',
                                    'free_products_require_login' => 'Login required for free downloads',
                                    'changelogs_status' => 'Enable Changelogs',
                                    'reviews_status' => 'Enable Reviews',
                                    'comments_status' => 'Enable Comments',
                                    'support_status' => 'Enable Support System',
                                    'external_file_link_option' => 'Allow External File Links',
                                    'buy_now_button' => 'Show "Buy Now" Button',
                                ];
                            @endphp

                            @foreach($features as $key => $label)
                            <div class="col-md-6 col-lg-4 col-xl-3">
                                <div class="d-flex align-items-center justify-content-between p-3 border rounded h-100">
                                    <span class="fw-medium">{{ translate($label) }}</span>
                                    <x-switch
                                        name="product[{{ $key }}]"
                                        :checked="@$settings->product->{$key}"
                                        :showLabel="false"
                                        size="lg"
                                        onLabel="{{ translate('Yes') }}"
                                        offLabel="{{ translate('No') }}"
                                    />
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- File Configuration --}}
            <div class="col-12">
                 <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark text-info me-2"></i>
                            {{ translate('File & Upload Limits') }}
                        </h5>
                    </div>
                     <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-muted small">{{ translate('Max Files per Up.') }}</label>
                                <input type="number" name="product[max_files]" class="form-control" min="{{ @$settings->product->maximum_screenshots + 2 }}" value="{{ @$settings->product->max_files }}" required>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-muted small">{{ translate('Max File Size (MB)') }}</label>
                                <div class="input-group">
                                    <input type="number" name="product[max_file_size]" class="form-control" min="1" step="any" value="{{ @$settings->product->max_file_size / 1048576 }}" required>
                                    <span class="input-group-text">MB</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-muted small">{{ translate('Unused File Expiry') }}</label>
                                <div class="input-group">
                                    <input type="number" name="product[file_duration]" class="form-control" min="1" value="{{ @$settings->product->file_duration }}" required>
                                    <span class="input-group-text">Hours</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                 <label class="form-label text-muted small">{{ translate('Convert Images to WEBP') }}</label>
                                 <select name="product[convert_images_webp]" class="form-select">
                                    <option value="0" @selected(@$settings->product->convert_images_webp == 0)>{{ translate('Disabled') }}</option>
                                    <option value="1" @selected(@$settings->product->convert_images_webp == 1)>{{ translate('Enabled') }}</option>
                                 </select>
                            </div>
                             <div class="col-md-6">
                                <label class="form-label text-muted small">{{ translate('Max Preview Image Dimensions (Width x Height)') }}</label>
                                <div class="input-group">
                                    <input type="number" name="product[max_preview_img_width]" class="form-control" placeholder="Width" value="{{ @$settings->product->max_preview_img_width ?? 1920 }}" required>
                                    <span class="input-group-text">x</span>
                                    <input type="number" name="product[max_preview_img_height]" class="form-control" placeholder="Height" value="{{ @$settings->product->max_preview_img_height ?? 1080 }}" required>
                                    <span class="input-group-text">px</span>
                                </div>
                             </div>
                        </div>
                     </div>
                 </div>
            </div>

            {{-- Discount System --}}
            <div class="col-12">
                 <div class="card">
                     <div class="card-header">
                         <div class="row align-items-center">
                            <div class="col-md-9">
                                <h5 class="mb-0">
                                    <i class="bi bi-tag text-purple me-2"></i>
                                    {{ translate('Discount System') }}
                                </h5>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <x-switch
                                    name="product[discount_status]"
                                    id="discountStatus"
                                    :checked="@$settings->product->discount_status"
                                    :showLabel="false"
                                    onLabel="{{ translate('Enabled') }}"
                                    offLabel="{{ translate('Disabled') }}"
                                    data-slide-toggle="#productDiscountSettings"
                                />
                            </div>
                        </div>
                     </div>

                     <div class="card-body p-4 d-none" id="productDiscountSettings">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">{{ translate('Max Discount %') }}</label>
                                <div class="input-group">
                                    <input type="number" name="product[discount_max_percentage]" class="form-control" min="1" max="90" value="{{ @$settings->product->discount_max_percentage }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ translate('Max Duration') }}</label>
                                <div class="input-group">
                                    <input type="number" name="product[discount_max_days]" class="form-control" min="0" max="365" value="{{ @$settings->product->discount_max_days }}">
                                    <span class="input-group-text">{{ translate('Days') }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ translate('Interval Between Discounts') }}</label>
                                <div class="input-group">
                                    <input type="number" name="product[discount_interval]" class="form-control" min="0" max="365" value="{{ @$settings->product->discount_interval }}">
                                    <span class="input-group-text">{{ translate('Days') }}</span>
                                </div>
                            </div>
                        </div>
                     </div>
                 </div>
            </div>

            <div class="col-12 text-end">
                 <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill">
                    <i class="bi bi-save me-2"></i>
                    {{ translate('Save Changes') }}
                </button>
            </div>
        </div>
    </form>
@endsection
