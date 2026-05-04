@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('section', translate('My products'))
@section('title', $product->name)

@section('content')
    @php
        $config = [
            'max_discount_percentage' => $settings->product->discount_max_percentage ?? null,
            'translates' => [
                'max_discount_percentage_error' => translate(
                    'The maximum discount percentage should be less or equal :percentage%',
                    ['percentage' => $settings->product->discount_max_percentage ?? 0]
                ),
            ],
            'buyer_fee' => [
                'regular' => $product->category->regular_buyer_fee ?? 0,
                'extended' => $product->category->extended_buyer_fee ?? 0,
            ],
            'prices' => [
                'regular' => $product->regular_price ?? 0,
                'extended' => $product->extended_price ?? 0,
            ],
        ];
    @endphp

    <div class="ajax-tabs" data-config="{{ json_encode($config) }}">
        @themeInclude('userpanel.products.includes.tabs-nav')
        <div class="ajax-tabs-content">
            @if (!$product->isRestricted())
                @if ($product->isApproved())
                    @if (!$product->hasDiscount())
                        <form action="{{ route('user.product.discount.create', $product->id) }}" class="ajax-form" method="POST">
                            @csrf
                            <div class="d-flex flex-column gap-4">
                                <!-- Regular Price Discount -->
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="icon-circle icon-circle-md bg-danger-subtle text-danger me-3">
                                                <i class="bi bi-tag fs-5"></i>
                                            </div>
                                            <h5 class="mb-0 fw-bold">{{ translate('Regular Price Discount') }}</h5>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-12 col-md-6 col-lg-3">
                                                @themeInclude('userpanel.partials.input-price', [
                                                    'label' => translate('Current Price'),
                                                    'id' => 'regular-license-price',
                                                    'value' => $product->regular_price,
                                                    'disabled' => true,
                                                ])
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-3">
                                                <label class="form-label text-gray-700 fw-medium fs-14">{{ translate('Discount Percentage') }}</label>
                                                <div class="input-group">
                                                    <input id="regular-license-percentage" type="number" name="regular_percentage"
                                                        placeholder="0" min="1"
                                                        max="{{ @$settings->product->discount_max_percentage }}"
                                                        class="form-control form-control-md rounded-start-3" required>
                                                    <span class="input-group-text px-3 bg-light text-gray-700 border-start-0 rounded-end-3 fs-14">%</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-3">
                                                @themeInclude('userpanel.partials.input-price', [
                                                    'label' => translate('Buyer Fee'),
                                                    'value' => $product->category->regular_buyer_fee,
                                                    'disabled' => true,
                                                ])
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-3">
                                                @themeInclude('userpanel.partials.input-price', [
                                                    'label' => translate('Total Purchase Price'),
                                                    'id' => 'regular-license-purchase-price',
                                                    'value' => 0,
                                                    'disabled' => true,
                                                ])
                                            </div>
                                        </div>
                                        <div class="mt-3 p-2 px-3 bg-light rounded-3 d-inline-flex align-items-center text-gray-600 fs-13">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <span>{{ translate('Maximum allowed discount is :percentage%', ['percentage' => @$settings->product->discount_max_percentage]) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Extended Price Discount -->
                                @if ($product->hasExtendedPrice())
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-3">
                                                <i class="bi bi-tags fs-5"></i>
                                            </div>
                                            <h5 class="mb-0 fw-bold">{{ translate('Extended Price Discount') }}</h5>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-12 col-md-6 col-lg-3">
                                                @themeInclude('userpanel.partials.input-price', [
                                                    'label' => translate('Current Price'),
                                                    'id' => 'extended-license-price',
                                                    'value' => $product->extended_price,
                                                    'disabled' => true,
                                                ])
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-3">
                                                <label class="form-label text-gray-700 fw-medium fs-14">{{ translate('Discount Percentage') }}</label>
                                                <div class="input-group">
                                                    <input id="extended-license-percentage" type="number"
                                                        name="extended_percentage" placeholder="0" min="1"
                                                        max="{{ @$settings->product->discount_max_percentage }}"
                                                        class="form-control form-control-md rounded-start-3">
                                                    <span class="input-group-text px-3 bg-light text-gray-700 border-start-0 rounded-end-3 fs-14">%</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-3">
                                                @themeInclude('userpanel.partials.input-price', [
                                                    'label' => translate('Buyer Fee'),
                                                    'value' => $product->category->extended_buyer_fee,
                                                    'disabled' => true,
                                                ])
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-3">
                                                @themeInclude('userpanel.partials.input-price', [
                                                    'label' => translate('Total Purchase Price'),
                                                    'id' => 'extended-license-purchase-price',
                                                    'value' => 0,
                                                    'disabled' => true,
                                                ])
                                            </div>
                                        </div>
                                        <div class="mt-3 p-2 px-3 bg-light rounded-3 d-inline-flex align-items-center text-gray-600 fs-13">
                                            <i class="bi bi-info-circle me-2 text-secondary"></i>
                                            <span>{{ translate('Maximum allowed discount is :percentage%', ['percentage' => @$settings->product->discount_max_percentage]) }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Discount Period -->
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="icon-circle icon-circle-md bg-secondary-subtle text-secondary me-3">
                                                <i class="bi bi-calendar-event fs-5"></i>
                                            </div>
                                            <h5 class="mb-0 fw-bold">{{ translate('Discount Period') }}</h5>
                                        </div>

                                        <div class="row g-4 mb-4">
                                            <div class="col-12 col-md-6">
                                                <label class="form-label text-gray-700 fw-medium fs-14">{{ translate('Start Date') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0 rounded-start-3 px-3"><i class="bi bi-calendar3"></i></span>
                                                    <input type="date" name="starting_at" class="form-control form-control-md border-start-0 rounded-end-3"
                                                        value="{{ old('starting_at') }}" required>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label text-gray-700 fw-medium fs-14">{{ translate('End Date') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0 rounded-start-3 px-3"><i class="bi bi-calendar-check"></i></span>
                                                    <input type="date" name="ending_at" class="form-control form-control-md border-start-0 rounded-end-3"
                                                        value="{{ old('ending_at') }}" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-3 bg-light rounded-4 border border-dashed text-gray-700 fs-14">
                                            <div>
                                                <i class="bi bi-calendar-check text-info me-1"></i>
                                               {{ translate('The starting date cannot be in the past.') }}
                                            </div>
                                            <div>
                                                <i class="bi bi-calendar-check text-info me-1"></i>
                                                {{ translate('Maximum discount duration is :days days.', ['days' => @$settings->product->discount_max_days]) }}
                                            </div>
                                            <div>
                                                <i class="bi bi-exclamation-triangle text-info me-1"></i>
                                                {{ translate('You can only create one discount campaign at a time.') }}
                                            </div>
                                            <div>
                                                <i class="bi bi-exclamation-triangle text-info me-1"></i>
                                                {{ translate('You can not delete discount campaign once it is active.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end mt-2 mb-4">
                                    <button type="submit" class="btn btn-primary btn-md btn-modern action-confirm">
                                        <i class="bi bi-check2-circle me-2"></i>{{ translate('Create Discount Campaign') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        @php $discount = $product->discount; @endphp
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-0">
                                <!-- Status Banner -->
                                <div class="bg-danger-subtle bg-opacity-75 p-3 px-4 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center text-danger">
                                        <div class="icon-circle icon-circle-sm bg-danger text-white me-3">
                                            <i class="bi bi-megaphone fs-6"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ translate('This product is scheduled for discount') }}</h6>
                                            @if ($discount->isActive())
                                                <span class="fs-12 opacity-75">{{ $discount->isWithinGracePeriod() ?
                                                        translate('Campaign will be active within a few seconds') :
                                                        translate('Campaign is currently active and visible to public') }}
                                                </span>
                                            @else
                                                <span class="fs-12 opacity-75">{{ translate('Campaign is currently inactive') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($discount->isDeletable())
                                        <div class="d-flex align-items-center gap-2" id="grace-period-wrapper"
                                            @if ($discount->isActive() && $discount->isWithinGracePeriod())
                                                data-grace-seconds="{{ $discount->getGraceSecondsRemaining() }}"
                                            @endif>

                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 action-confirm" id="grace-delete-btn"
                                                data-action="{{ route('user.product.discount.delete', $product->id) }}"
                                                data-method="DELETE"
                                                data-text="{{ translate('Are you sure you want to delete this discount?') }}">
                                                <i class="bi bi-trash3 me-1"></i> {{ translate('Remove Campaign') }}
                                                @if ($discount->isActive() && $discount->isWithinGracePeriod())
                                                <span class="badge bg-white text-danger border border-light rounded-pill px-2 py-1 fs-11 ms-2" id="grace-countdown">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <span id="grace-seconds">{{ $discount->getGraceSecondsRemaining() }}</span>s
                                                </span>
                                            @endif
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-4">
                                    <div class="row g-4">
                                        <!-- Regular Discount Info -->
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="p-3 rounded-3 bg-light border border-dashed h-100">
                                                <label class="form-label text-gray-700 fs-12 text-uppercase fw-bold mb-2">{{ translate('Regular License') }}</label>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                     <span class="badge bg-primary-subtle text-primary px-2 py-1 rounded-pill fs-12">-{{ $discount->regular_percentage }}%</span>
                                                </div>
                                                <div class="d-flex align-items-baseline gap-2">
                                                    <span class="fs-5 fw-bold text-gray-800">{{ getAmount($discount->price->regular, 0) }}</span>
                                                    <span class="text-gray-700 text-decoration-line-through">{{ getAmount($product->price->regular, 0) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Extended Discount Info (Conditional) -->
                                        @if ($discount->hasExtended())
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <div class="p-3 rounded-3 bg-light border border-dashed h-100">
                                                    <label class="form-label text-gray-700 fs-12 text-uppercase fw-bold mb-2">{{ translate('Extended License') }}</label>
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fs-12">-{{ $discount->extended_percentage }}%</span>
                                                    </div>
                                                    <div class="d-flex align-items-baseline gap-2">
                                                        <span class="fs-5 fw-bold text-gray-800">{{ getAmount($discount->price->extended, 0) }}</span>
                                                        <span class="text-gray-700 text-decoration-line-through">{{ getAmount($product->price->extended, 0) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Period Info -->
                                        <div class="col-12 col-md-6 {{ $discount->hasExtended() ? 'col-lg-4' : 'col-lg-8' }}">
                                            <div class="p-3 rounded-3 bg-light border border-dashed h-100 d-flex flex-column justify-content-center">
                                                <label class="form-label text-gray-700 fs-12 text-uppercase fw-bold mb-2">{{ translate('Discount Period') }}</label>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-circle icon-circle-xs bg-white text-gray-600 me-2 shadow-sm">
                                                        <i class="bi bi-calendar3 fs-12"></i>
                                                    </div>
                                                    <span class="fs-14 fw-medium text-gray-700">
                                                        {{ dateFormat($discount->starting_at, 'd M Y') }} - {{ dateFormat($discount->ending_at, 'd M Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="alert alert-info border-0 rounded-4 p-4 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle icon-circle-md bg-info-subtle text-info me-3">
                                <i class="bi bi-info-circle fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">{{ translate('Option Restricted') }}</h6>
                                <p class="mb-0 text-gray-700 fs-14">{{ translate('This feature is only available for products that have been approved.') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                @themeInclude('userpanel.partials.restricted')
            @endif
        </div>
    </div>
@endsection

