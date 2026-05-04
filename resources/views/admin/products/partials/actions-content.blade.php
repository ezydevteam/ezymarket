<div class="row g-4">
    @if ($product->isPendingReview())
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent px-4 py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>{{ translate('Review Actions') }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <form action="{{ route('admin.products.actions.update', $product->id) }}"
                                method="POST"
                                class="ajax-form">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success w-100 py-3 action-confirm"
                                    data-text="{{ translate('Are you sure you want to approve this product?') }}">
                                    <i class="bi bi-check-circle fs-4 d-block mb-1"></i>
                                    {{ translate('Approve') }}
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <button type="button"
                                class="btn btn-warning w-100 py-3"
                                data-bs-toggle="collapse"
                                data-bs-target="#revisionForm">
                                <i class="bi bi-exclamation-circle fs-4 d-block mb-1"></i>
                                {{ translate('Request Revision') }}
                            </button>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('admin.products.actions.update', $product->id) }}"
                                method="POST"
                                class="ajax-form">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-danger w-100 py-3 action-confirm"
                                    data-text="{{ translate('Are you sure you want to reject this product? The seller will not be able to resubmit.') }}">
                                    <i class="bi bi-x-circle fs-4 d-block mb-1"></i>
                                    {{ translate('Reject') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Revision Form --}}
                    <div class="collapse mt-4" id="revisionForm">
                        <div class="card card-body bg-light">
                            <form action="{{ route('admin.products.actions.update', $product->id) }}"
                                method="POST"
                                class="ajax-form">
                                @csrf
                                <input type="hidden" name="action" value="needs_revision">
                                <div class="mb-3">
                                    <label class="form-label">{{ translate('Revision Reason') }} <span class="text-danger">*</span></label>
                                    <textarea name="reason" class="form-control" rows="4" required
                                        placeholder="{{ translate('Explain what needs to be fixed...') }}"></textarea>
                                    <small class="text-muted">{{ translate('This message will be sent to the seller.') }}</small>
                                </div>
                                <button type="submit" class="btn btn-warning action-confirm"
                                    data-text="{{ translate('Are you sure want to send this product for revision?') }}">
                                    <i class="bi bi-send me-2"></i>{{ translate('Send for Revision') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (!$product->isPendingReview())
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent px-4 py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-arrow-repeat me-2"></i>{{ translate('Change Status') }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ translate('Change the product status without notifying the seller. Use this for administrative purposes.') }}
                    </div>

                    <form action="{{ route('admin.products.actions.status', $product->id) }}"
                        method="POST"
                        class="ajax-form">
                        @method('PUT')
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label">{{ translate('Status') }}</label>
                                <select name="status" class="form-select form-select-lg selectpicker">
                                    @foreach (\App\Models\Product\Product::getStatusOptions() as $key => $value)
                                        @if ($key == \App\Enums\Product\ProductStatus::DRAFT->value)
                                            @continue
                                        @endif
                                        <option value="{{ $key }}" @selected($product->status->value == $key)>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit"
                                    class="btn btn-lg btn-primary action-confirm w-100"
                                    data-text="{{ translate('Are you sure want to change the product status from :status?', ['status' => $product->status->label()]) }}">
                                    <i class="bi bi-check-circle me-2"></i>{{ translate('Update Status') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
