@php
$isExpired = $activePurchase->isSupportExpired();
$expiryDate = $activePurchase->support_expiry_at ? $activePurchase->support_expiry_at->format('d M, Y') : null;
$paidPackage = $product->supportPackage;
$basePrice = ($activePurchase->license_type === \App\Enums\LicenseType::EXTENDED)
? $product->price->extended
: $product->price->regular;
@endphp

<div class="purchased-view-modern">
    {{-- Purchased Licenses Section --}}
    <div class="purchased-section mb-4">
        <h6 class="text-uppercase fw-bold text-gray-700 mb-2 fs-12">
            {{ translate('Purchased licenses') }}
        </h6>
        <div class="card bg-light-subtle border-dashed rounded-3 p-3">
            <div class="d-flex align-items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 40px; height: 40px;">
                        <i class="bi bi-file-earmark-check fs-5"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <p class="mb-1 fw-medium text-dark">
                        {{ translate('You have 1 license for this product.') }}
                    </p>
                    <div class="d-flex flex-wrap gap-3 mt-2">
                        <a href="{{ route('user.purchase.download', $activePurchase->id) }}"
                            class="text-primary fw-semibold small hover-underline">
                            <i class="bi bi-download me-1"></i>{{ translate('Download') }}
                        </a>
                        <span class="text-muted">|</span>
                        <a href="{{ route('user.purchase.license', $activePurchase->id) }}" target="_blank"
                            class="text-primary fw-semibold small hover-underline">
                            <i class="bi bi-file-earmark-text me-1"></i>{{ translate('View License') }}
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Support Status Section --}}
    <div class="support-section mb-4">
        <div
            class="card border rounded-3 overflow-hidden {{ $isExpired ? 'bg-danger-subtle border-danger-subtle' : 'bg-success-subtle border-success-subtle' }}">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2 text-dark fw-bold">
                        <i
                            class="bi {{ $isExpired ? 'bi-shield-slash text-danger' : 'bi-shield-check text-success' }} fs-5"></i>
                        <span>{{ translate('Support') }}</span>
                    </div>
                    <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-success' }} rounded-pill px-3">
                        {{ $isExpired ? translate('Expired') : translate('Active') }}
                    </span>
                </div>

                @if(!$activePurchase->support_expiry_at)
                <div class="text-muted small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    {{ translate('No active support for this purchase') }}
                </div>
                @if(@$settings->product->support_status && $product->isSupported() && $product->isPurchasingEnabled() &&
                $paidPackage)
                <form action="{{ route('user.purchase.support.purchase', $activePurchase->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <input type="hidden" name="support" value="{{ $paidPackage->id }}">
                        <div class="p-2 border rounded bg-white small">
                            {{ $paidPackage->title }} ({{ getAmount($paidPackage->calculatePrice($basePrice)) }})
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
                        <i class="bi bi-cart-plus me-2"></i>{{ translate('Buy support') }}
                    </button>
                </form>
                @endif
                @elseif($isExpired)
                <div class="text-danger fw-medium mb-3 small d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle"></i>
                    {{ translate('Support expired on :date', ['date' => $expiryDate]) }}
                </div>
                @if(@$settings->product->support_status && $product->isSupported() && $product->isPurchasingEnabled() &&
                $paidPackage)
                <form action="{{ route('user.purchase.support.extend', $activePurchase->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <input type="hidden" name="support" value="{{ $paidPackage->id }}">
                        <div class="p-2 border rounded bg-white small">
                            {{ $paidPackage->title }} ({{ getAmount($paidPackage->calculatePrice($basePrice)) }})
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
                        <i class="bi bi-arrow-repeat me-2"></i>{{ translate('Renew support') }}
                    </button>
                </form>
                @endif
                @else
                <div class="text-success fw-medium mb-3 small d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history"></i>
                    {{ translate('Supported until :date', ['date' => $expiryDate]) }}
                </div>
                @if(@$settings->product->support_status && $product->isSupported() && $product->isPurchasingEnabled() &&
                $paidPackage)
                <form action="{{ route('user.purchase.support.extend', $activePurchase->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <input type="hidden" name="support" value="{{ $paidPackage->id }}">
                        <div class="p-2 border rounded bg-white small">
                            {{ $paidPackage->title }} ({{ getAmount($paidPackage->calculatePrice($basePrice)) }})
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100 rounded-pill py-2">
                        <i class="bi bi-arrow-repeat me-2"></i>{{ translate('Extend support') }}
                    </button>
                </form>
                @endif
                @endif
            </div>

            <div class="card-footer bg-transparent border-0 pt-0 pb-3 text-center">
                <a href="{{ route('page', $settings->product->support_policy_slug ?? 'product-support-policy') }}"
                    target="_blank" class="text-muted small hover-primary-underline">
                    {{ translate('How does support work?') }}<i class="bi bi-box-arrow-up-right fs-10 ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Rate Item Section --}}
    @php
    $userReview = $product->reviews()->where('user_id', authUser()?->id)->first();
    $hasReviewed = (bool)$userReview;
    $userRating = $hasReviewed ? $userReview->stars : 0;
    @endphp
    <div class="rating-section pt-3 border-top border-light">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="text-uppercase fw-bold {{ $hasReviewed ? 'text-gray-700' : 'text-gray-200' }} mb-0 fs-12">
                {{ $hasReviewed ? translate('You rated :stars stars', ['stars' => $userRating]) : translate('Rate this
                product') }}
            </h6>
            <div class="user-rating-stars {{ $hasReviewed ? 'text-warning' : 'text-gray-700' }}"
                data-already-reviewed="{{ $hasReviewed ? 'true' : 'false' }}"
                data-review-msg="{{ translate('You have already reviewed this product.') }}">
                <div class="d-flex gap-1">
                    @for($i=1; $i<=5; $i++) <i
                        class="bi bi-star-fill {{ !$hasReviewed ? 'cursor-pointer star-hover fs-5' : 'fs-6' }}"
                        data-value="{{ $i }}" style="{{ ($hasReviewed && $i > $userRating) ? 'opacity: 0.25;' : '' }}">
                        </i>
                        @endfor
                </div>
            </div>
        </div>
    </div>
</div>
