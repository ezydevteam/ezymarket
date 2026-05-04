<div wire:ignore.self wire:key="reportProductCommentModal" class="modal fade" id="reportProductCommentModal" tabindex="-1"
    aria-labelledby="reportProductCommentModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom px-4 py-3">
                <h2 class="modal-title fs-5" id="reportProductCommentModalLabel">
                    <i class="bi bi-flag text-danger me-2"></i>{{ translate('Report Comment') }}
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div wire:ignore class="product-comment card-bg border border-2 rounded-2 p-3 mb-4" id="reportCommentPreview">
                    <!-- Will be populated by JavaScript -->
                </div>
                <form wire:submit.prevent="sendCommentReport">
                    <!-- Reason Select -->
                    <div class="mb-3">
                        <label for="reportReason" class="form-label fw-500">
                            {{ translate('Report Reason') }} <span class="text-danger">*</span>
                        </label>
                        <select wire:model="reason" id="reportReason" class="form-select" required>
                            <option value="">{{ translate('Select a reason') }}</option>
                            @foreach($this->getReasonOptions() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Description -->
                    <div class="mb-4">
                        <label for="reportDescription" class="form-label fw-500">
                            {{ translate('Briefly describe the issue') }} <span class="text-danger">*</span>
                        </label>
                        <textarea wire:model="description" id="reportDescription" class="form-control" rows="4"
                            placeholder="{{ translate('Briefly describe the issue') }}"
                            maxlength="1000"></textarea>
                        <small class="form-text text-muted">{{ translate('Maximum 1000 characters') }}</small>
                    </div>
                    <div class="row justify-content-center g-3">
                        <div class="col-12 col-lg">
                            <button type="button" class="btn btn-dark-subtle btn-md w-100"
                                data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>{{ translate('Close') }}
                            </button>
                        </div>
                        <div class="col-12 col-lg">
                            <button type="submit" class="btn btn-danger btn-md w-100">
                                <i class="bi bi-check2-circle me-1"></i>{{ translate('Submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
