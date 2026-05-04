<x-modal id="createSellerLevelModal" :title="translate('Create New Level')" :icon="'bi bi-trophy'" :scrollable="true">
    <form id="createSellerLevelForm"
        action="{{ route('admin.settings.seller-levels.store') }}"
        method="POST"
        enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
                {{-- Name --}}
            <div class="col-12">
                <label class="form-label fw-bold">{{ translate('Level Name') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                    <input type="text" name="name" class="form-control" placeholder="{{ translate('e.g., Bronze Seller') }}" required autofocus>
                </div>
                <div class="form-text text-muted">{{ translate('Give this level a clear name.') }}</div>
            </div>

                {{-- Icon --}}
            <div class="col-12">
                <label class="form-label fw-bold">{{ translate('Level Icon') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <img id="attach-image-preview-create-level" src="{{ asset('images/default.svg') }}" width="24" height="24" style="object-fit: contain;">
                    </span>
                    <input type="text" class="form-control text-muted bg-white" value="{{ translate('Browse icon...') }}" readonly>
                    <button type="button" class="btn bg-text-primary attach-image-button" data-id="create-level">
                        <i class="bi bi-cloud-upload me-1"></i> {{ translate('Upload') }}
                    </button>
                    <input id="attach-image-targeted-input-create-level" type="file" name="icon" accept="image/png,image/jpg,image/jpeg,image/svg+xml" hidden>
                </div>
                <div class="form-text text-muted">{{ translate('Recommended: 64x64px. Max: 5MB.') }}</div>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">{{ translate('Min. Earnings') }} <span class="text-danger">*</span></label>
                    @include('admin.partials.input-price', [
                    'name' => 'min_earnings',
                    'integer' => true,
                    'required' => true,
                    'placeholder' => translate('0')
                ])
                    <div class="form-text text-muted">{{ translate('Target earnings.') }}</div>
            </div>

            <div class="col-12">
                    <label class="form-label fw-bold">{{ translate('Commission Fee (%)') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-percent"></i></span>
                    <input type="number" name="fees" class="form-control" id="createFeeInput" placeholder="0" min="0" max="100" step="0.01" required>
                </div>
                    <div class="form-text text-muted">{{ translate('Platform fee per sale.') }}</div>
            </div>
        </div>
    </form>
    <x-slot:footer>
            <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}
            </button>
            <button type="submit" id="createSellerLevelBtn" form="createSellerLevelForm" class="btn btn-primary flex-fill">
                <i class="bi bi-check-circle me-2"></i>{{ translate('Create Level') }}
            </button>
    </x-slot>
</x-modal>
