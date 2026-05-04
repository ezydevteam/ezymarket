<form id="editSellerLevelForm"
    action="{{ route('admin.settings.seller-levels.update', $sellerLevel->id) }}"
    method="POST"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3">
            {{-- Name --}}
        <div class="col-12">
            <label class="form-label fw-bold">{{ translate('Level Name') }} <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                <input type="text" name="name" class="form-control" value="{{ $sellerLevel->name }}" placeholder="{{ translate('e.g., Bronze Seller') }}" required autofocus>
            </div>
            <div class="form-text text-muted">{{ translate('Give this level a clear name.') }}</div>
        </div>

            {{-- Icon --}}
        <div class="col-12">
            <label class="form-label fw-bold">{{ translate('Level Icon') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <img id="attach-image-preview-edit-level-{{ $sellerLevel->id }}" src="{{ $sellerLevel->icon ? $sellerLevel->icon_url : asset('images/default.svg') }}" width="24" height="24" style="object-fit: contain;">
                </span>
                <input type="text" class="form-control text-muted bg-white" value="{{ translate('Browse icon...') }}" readonly>
                <button type="button" class="btn bg-text-primary attach-image-button" data-id="edit-level-{{ $sellerLevel->id }}">
                    <i class="bi bi-cloud-upload me-1"></i> {{ translate('Upload') }}
                </button>
                <input id="attach-image-targeted-input-edit-level-{{ $sellerLevel->id }}" type="file" name="icon" accept="image/png,image/jpg,image/jpeg,image/svg+xml" hidden>
            </div>
            <div class="form-text text-muted">{{ translate('Recommended: 64x64px. Max: 5MB.') }}</div>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold">{{ translate('Min. Earnings') }}
                @if (!$sellerLevel->isDefault())
                    <span class="text-danger">*</span>
                @endif
            </label>
            @if ($sellerLevel->isDefault())
                <div class="input-group">
                    <span class="input-group-text text-muted"><i class="bi bi-lock-fill"></i></span>
                    <input type="text" class="form-control text-muted" value="{{ getAmount($sellerLevel->min_earnings) }}" disabled>
                </div>
                <div class="form-text text-muted">{{ translate('Default level always starts at 0.') }}</div>
            @else
                @include('admin.partials.input-price', [
                    'name' => 'min_earnings',
                    'value' => $sellerLevel->min_earnings,
                    'integer' => true,
                    'required' => true,
                    'placeholder' => translate('0')
                ])
                <div class="form-text text-muted">{{ translate('Target earnings.') }}</div>
            @endif
        </div>

        <div class="col-12">
                <label class="form-label fw-bold">{{ translate('Commission Fee (%)') }} <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-percent"></i></span>
                <input type="number" name="fees" class="form-control" id="editFeeInput" value="{{ $sellerLevel->fees }}" placeholder="0" min="0" max="100" step="0.01" required>
            </div>
                <div class="form-text text-muted">{{ translate('Platform fee per sale.') }}</div>
        </div>
    </div>
</form>

