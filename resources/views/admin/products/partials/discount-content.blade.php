@php $discount = $product->discount; @endphp
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent px-4 py-3">
                <h5 class="card-title mb-0">
                    <i class="bi bi-{{ $discount ? 'pencil-square' : 'percent' }} me-2"></i>{{ $discount ?
                    translate('Edit Discount') : translate('Add Discount') }}
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.products.discount.store', $product->id) }}" method="POST"
                    class="ajax-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ translate('Regular License Discount') }} <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="regular_percentage" class="form-control"
                                    value="{{ old('regular_percentage', $discount?->regular_percentage) }}" min="1"
                                    max="99" required placeholder="{{ translate('e.g. 20') }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">{{ translate('Original price:') }} {{
                                getAmount($product->price->regular, 0) }}</small>
                        </div>

                        @if ($product->hasExtendedPrice())
                        <div class="col-12">
                            <label class="form-label">{{ translate('Extended License Discount') }}</label>
                            <div class="input-group">
                                <input type="number" name="extended_percentage" class="form-control"
                                    value="{{ old('extended_percentage', $discount?->extended_percentage) }}" min="1"
                                    max="99" placeholder="{{ translate('e.g. 25') }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">{{ translate('Original price:') }} {{
                                getAmount($product->price->extended, 0) }}</small>
                        </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label">{{ translate('Start Date') }} <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="starting_date" class="form-control"
                                value="{{ old('starting_date', $discount?->starting_at?->format('Y-m-d')) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ translate('End Date') }} <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="ending_date" class="form-control"
                                value="{{ old('ending_date', $discount?->ending_at?->format('Y-m-d')) }}" required>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="discountActive"
                                    value="1" {{ old('is_active', $discount?->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="discountActive">{{ translate('Activate discount
                                    immediately') }}</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-{{ $discount ? 'check2-circle' : 'plus-circle' }} me-2"></i>{{ $discount
                                ? translate('Update Discount') : translate('Create Discount') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Current Discount Display --}}
    <div class="col-lg-7">
        @if ($discount)
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent px-4 py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-percent me-2"></i>{{ translate('Current Discount') }}
                </h5>
                <span class="badge bg-{{ ($discount->isActive()) ? 'success' : 'danger' }}">
                    {{ translate(($discount->isActive()) ? 'Active' : 'Inactive') }}
                </span>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    {{-- Regular License --}}
                    <div class="col-12">
                        <div class="border rounded-4 p-3 h-100">
                            <h6 class="mb-3">{{ translate('Regular License') }}</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ translate('Discount') }}</span>
                                <span class="badge bg-primary py-2 fs-6">{{ $discount->regular_percentage }}%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ translate('Price') }}</span>
                                <div class="fs-6">
                                    <span class="text-decoration-line-through text-muted me-2">{{
                                        getAmount($product->price->regular, 0) }}</span>
                                    <span class="fw-bold text-success">{{ getAmount($discount->price->regular, 0)
                                        }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Extended License --}}
                    @if ($discount->hasExtended())
                    <div class="col-12">
                        <div class="border rounded-4 p-3 h-100">
                            <h6 class="mb-3">{{ translate('Extended License') }}</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ translate('Discount') }}</span>
                                <span class="badge bg-primary fs-6">{{ $discount->extended_percentage }}%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ translate('Price') }}</span>
                                <div>
                                    <span class="text-decoration-line-through text-muted me-2">{{
                                        getAmount($product->price->extended, 0) }}</span>
                                    <span class="fw-bold text-success">{{ getAmount($discount->price->extended, 0)
                                        }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Duration --}}
                    <div class="col-12">
                        <div class="bg-light border rounded-4 p-3">
                            <div class="row text-center">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block">{{ translate('Start Date') }}</small>
                                    <span class="fw-semibold">{{ dateFormat($discount->starting_at, 'd M Y') }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">{{ translate('End Date') }}</small>
                                    <span class="fw-semibold">{{ dateFormat($discount->ending_at, 'd M Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="col-12">
                        <div class="d-flex gap-3">
                            <button data-action="{{ route('admin.products.discount.update-status', $product->id) }}"
                                class="btn btn-md flex-fill action-confirm bg-{{ $discount->isActive() ? 'warning' : 'success' }}-subtle text-{{ $discount->isActive() ? 'dark' : 'success' }}"
                                data-confirm="{{ translate('Are you sure want to :status this discount?', ['status' => $discount->isActive() ? translate('deactivate') : translate('activate')]) }}"
                                data-method="PUT">
                                <i class="bi bi-{{ $discount->isActive() ? 'pause-circle' : 'play-circle' }} me-2"></i>{{
                                translate(':status', ['status' => $discount->isActive() ? translate('Deactivate
                                Discount') : translate('Activate Discount')]) }}
                            </button>
                            <button data-action="{{ route('admin.products.discount.remove', $product->id) }}"
                                class="btn btn-md bg-danger-subtle text-danger flex-fill action-confirm"
                                data-confirm="{{ translate('Are you sure want to remove this discount? This action can not be undone.') }}"
                                data-method="DELETE">
                                <i class="bi bi-trash me-2"></i>{{ translate('Remove Discount') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center px-4 py-5">
                <i class="bi bi-tag text-muted display-3"></i>
                <h5 class="mt-3 text-muted">{{ translate('No Active Discount!') }}</h5>
                <p class="text-muted mb-0">{{ translate('Use the form to create a discount for this product.') }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
