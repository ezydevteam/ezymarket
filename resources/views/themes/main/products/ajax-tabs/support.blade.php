@php $seller = $product->seller; @endphp

@if($seller && $product->isSupported())
<div class="bg-light border rounded-3 p-3 mb-4">
    <div class="row align-items-center g-3">
        <div class="col-12 col-lg">
            <div class="row row-cols-auto align-items-center g-2">
                <div class="col">
                    <a href="{{ $seller->profile_link }}" class="user-avatar rounded">
                        <img src="{{ $seller->avatar_url }}" alt="{{ $seller->username }}">
                    </a>
                </div>
                <div class="col">
                    <h5 class="mb-0">
                        <a href="{{ $seller->profile_link }}" class="text-dark hover-primary-underline">{{ $seller->username }}</a>
                        {{ translate('supports this product') }}
                    </h5>
                    <p class="mb-0 fs-6">
                        <span class="text-muted small">
                            {{ translate("Read the seller's instructions below to know how you can get help.") }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-auto">
            <span class="badge bg-primary fw-normal p-2">
                <i class="bi bi-check-circle-fill me-1"></i>{{ translate('Supported') }}
            </span>
        </div>
    </div>
</div>
<div class="card rounded-3 bg-light-subtle border">
    <div class="card-body">
        {!! sanitizeRichText($product->support_instructions) !!}
    </div>
</div>
@else
<div class="alert alert-info py-5 text-center">
    <i class="bi bi-info-circle d-block mb-4 fs-2 text-muted"></i>
    {{ translate('Support is not available for this product.') }}
</div>
@endif
