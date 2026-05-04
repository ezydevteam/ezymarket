@if ($model->restored_at)
    <div class="alert alert-info border-0 rounded-4 bg-info-subtle mb-4 alert-dismissible fade show restoration-notice"
        role="alert" id="restoration-notice-{{ $model->id }}">
        <div class="d-flex gap-3 align-items-center">
            <div class="icon-circle bg-info text-white d-flex align-items-center justify-content-center flex-shrink-0">
                <i class="bi bi-clock-history fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1 fw-bold text-info-emphasis">{{ translate('Notice: Record Restored') }}</h6>
                <div class="text-info-emphasis small">
                    {{ translate('This record was restored by the administrator on :date.', ['date' => dateFormat($model->restored_at)]) }}
                </div>
            </div>
            <div class="ms-auto text-end flex-shrink-0">
                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 fw-medium"
                    data-action="{{ route('user.restoration.dismiss', [$type ?? $model->getMorphClass(), $model->id]) }}"
                    data-dismiss-restoration>
                    {{ translate('Dismiss') }}
                </button>
            </div>
        </div>
    </div>
@endif

