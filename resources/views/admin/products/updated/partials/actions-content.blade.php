<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent py-3">
        <h5 class="card-title mb-0">
            <i class="bi bi-lightning me-2"></i>{{ translate('Review Update Request') }}
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <form action="{{ route('admin.products.updated.actions.update', $productUpdate->id) }}"
                    method="POST"
                    class="ajax-form">
                    @csrf
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn btn-success w-100 py-3 action-confirm"
                        data-confirm="{{ translate('Are you sure you want to approve this update?') }}">
                        <i class="bi bi-check-circle fs-4 d-block mb-1"></i>
                        {{ translate('Approve Update') }}
                    </button>
                </form>
            </div>
            <div class="col-md-6">
                <button type="button"
                    class="btn btn-danger w-100 py-3"
                    data-bs-toggle="collapse"
                    data-bs-target="#rejectForm">
                    <i class="bi bi-x-circle fs-4 d-block mb-1"></i>
                    {{ translate('Reject Update') }}
                </button>
            </div>
        </div>

        {{-- Reject Form --}}
        <div class="collapse mt-4" id="rejectForm">
            <div class="card card-body bg-light">
                <form action="{{ route('admin.products.updated.actions.update', $productUpdate->id) }}"
                    method="POST"
                    class="ajax-form">
                    @csrf
                    <input type="hidden" name="action" value="reject">
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Rejection Reason') }} <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required
                            placeholder="{{ translate('Provide a reason for rejecting this update...') }}"></textarea>
                        <small class="text-muted">{{ translate('This message will be sent to the seller.') }}</small>
                    </div>
                    <button type="submit" class="btn btn-danger action-confirm"
                        data-confirm="{{ translate('Are you sure you want to reject this update?') }}">
                        <i class="bi bi-send me-2"></i>{{ translate('Submit Rejection') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
