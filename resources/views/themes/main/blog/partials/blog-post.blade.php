@php $isList = ($viewStyle ?? '') === 'list'; @endphp

<div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden transition-all hover-shadow {{ $isList ? 'flex-md-row' : '' }}">
    <div class="{{ $isList ? 'col-md-4' : '' }} position-relative">
        @if ($showCategory ?? true)
            <div class="position-absolute top-0 m-3 z-1 {{ $isList ? 'start-0' : 'end-0' }}">
                <a class="badge bg-primary text-white shadow-sm px-2 py-1 rounded-pill fw-medium"
                    href="{{ $blogArticle->category->view_link }}">
                    {{ $blogArticle->category->name }}
                </a>
            </div>
        @endif
        <a href="{{ $blogArticle->view_link }}" class="d-block h-100">
            <img src="{{ $blogArticle->image_link }}" alt="{{ $blogArticle->title }}"
                class="object-fit-cover {{ $isList ? 'h-100 w-100' : 'card-img-top' }}"
                style="min-height: 200px;">
        </a>
    </div>
    <div class="card-body d-flex flex-column {{ $isList ? 'col-md-8' : '' }}">

        <h5 class="card-title h6 fw-bold mb-2">
            <a href="{{ $blogArticle->view_link }}" class="text-dark hover-primary">
                {{ truncateText($blogArticle->title, 65) }}
            </a>
        </h5>

        <div class="d-flex flex-wrap gap-2 text-muted small mb-3">
            @if ($showAuthor ?? true)
                <span><i class="bi bi-person me-1"></i>
                    {{ $blogArticle->author?->username ?? translate('Admin') }}
                </span>
            @endif
            @if ($showDate ?? true)
                <span><i class="bi bi-clock-history me-1"></i>
                    {{ $blogArticle->created_at->diffForHumans() }}
                </span>
            @endif
        </div>

        <p class="card-text text-secondary small mb-3">
            {{ truncateText($blogArticle->short_description, 100) }}
        </p>

        @if ($showReadMore ?? true)
            <div class="mt-auto">
                <a href="{{ $blogArticle->view_link }}"
                    class="btn btn-link p-0 text-primary fw-bold small text-uppercase text-decoration-none">
                    {{ translate('Read More') }} <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        @endif
    </div>
</div>
