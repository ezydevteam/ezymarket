<div class="userpanel-card card-v border-0 rounded-4 shadow-sm py-5 px-4 max-w-600 mx-auto text-center">
    <div class="d-flex flex-column align-items-center py-5 px-4">
        <div class="icon-circle icon-circle-xl bg-danger-subtle mb-4">
            <i class="bi bi-{{ $icon ?? 'search' }} display-4 text-danger"></i>
        </div>
        <h4 class="fw-semibold">{{ translate($title ?? 'Nothing found!') }}</h4>
        <p class="text-gray-700 mx-auto">
            {{ translate($description ?? 'No results found for your query.') }}
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
            @if (!empty($btn_url ?? ''))
            <a href="{{ $btn_url ?? route('home') }}"
                class="btn btn-padding btn-{{ empty($modal_btn_text ?? '') ? 'primary' : 'outline-primary' }} rounded-pill fw-medium">
                {{ translate($btn_text ?? 'Back Home') }}
            </a>
            @endif

            @if (!empty($modal_btn_text ?? ''))
            <button type="button" class="btn btn-padding btn-primary rounded-pill fw-medium" data-bs-toggle="modal"
                data-bs-target="#{{ $modal_id ?? '' }}" @if(!empty($modal_action ?? '' ))
                data-action="{{ $modal_action }}" @endif>
                <i class="bi bi-{{ $modal_icon ?? 'plus-circle' }} me-2"></i>
                {{ translate($modal_btn_text) }}
            </button>
            @endif
        </div>
    </div>
</div>
