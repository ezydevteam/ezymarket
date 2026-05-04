@props(['model', 'restoreRoute', 'deleteRoute'])

@if($model->trashed() && !$model->isArchivedByAdmin())
    <div class="alert alert-warning text-center mb-4 p-3 shadow-sm" role="alert">
        <div class="alert-icon mb-2">
            <i class="bi bi-archive text-danger fs-1"></i>
        </div>
        <div class="alert-content">
            <h5 class="alert-heading mb-1 fw-bold">
                {{ translate('Record Archived') }}
            </h5>
            <p class="mb-0">
                {{ translate('This record was archived by the user') }}
                <span class="fw-semibold">{{ $model->archivedBy?->username ?? translate('System') }}</span>
                {{ translate('on') }}
                <span class="fw-semibold">{{ $model->deleted_at?->format('M d, Y H:i') }}</span>.
                <p class="mt-1 mb-0 opacity-75 text-wrap">
                   {{ translate('It is currently hidden from the user dashboard but remains accessible to administrators.') }}
                </p>
            </p>
        </div>
        <div class="mt-3 d-flex flex-wrap justify-content-center gap-2">
            @if(isset($deleteRoute))
                <button type="button"
                    class="btn btn-outline-danger btn-sm fw-bold px-4 shadow-sm action-confirm"
                    data-action="{{ $deleteRoute }}"
                    data-method="DELETE"
                    data-confirm="{{ translate('Are you sure you want to move this archived record to the administrative trash?') }}">
                    <i class="bi bi-trash me-1"></i>
                    {{ translate('Delete Record') }}
                </button>
            @endif

            @if(isset($restoreRoute))
                <button type="button"
                    class="btn btn-warning text-dark btn-sm fw-bold px-4 shadow-sm action-confirm"
                    data-action="{{ $restoreRoute }}"
                    data-method="POST"
                    data-confirm="{{ translate('Are you sure you want to restore this record to :user\'s dashboard?', ['user' => $model->archivedBy?->username ?? translate('System')]) }}">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                    {{ translate('Restore Record') }}
                </button>
            @endif
        </div>
    </div>
@endif
