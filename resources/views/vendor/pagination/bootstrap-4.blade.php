@if ($paginator->hasPages())
<div class="d-flex justify-content-center align-items-center mt-4 mb-2">
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-modern mb-0 gap-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="{{ translate('pagination.previous') }}">
                    <span class="page-link rounded-circle d-flex align-items-center justify-content-center border-0 bg-light text-muted"
                        aria-hidden="true">
                        <i class="bi bi-chevron-left fs-6"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a href="{{ $paginator->previousPageUrl() }}"
                        class="page-link rounded-circle d-flex align-items-center justify-content-center border-0 shadow-sm transition-all"
                        rel="prev" aria-label="{{ translate('pagination.previous') }}">
                        <i class="bi bi-chevron-left fs-6"></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span
                            class="page-link rounded-circle d-flex align-items-center justify-content-center border-0 bg-transparent text-muted"
                            aria-hidden="true">
                            {{ $element }}
                        </span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span
                                    class="page-link rounded-circle d-flex align-items-center justify-content-center border-0 shadow-sm bg-primary text-white fw-medium"
                                    aria-hidden="true">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a href="{{ $url }}"
                                    class="page-link rounded-circle d-flex align-items-center justify-content-center border-0 bg-light text-dark hover-primary fw-medium transition-all">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a href="{{ $paginator->nextPageUrl() }}"
                        class="page-link rounded-circle d-flex align-items-center justify-content-center border-0 shadow-sm transition-all"
                        rel="next" aria-label="{{ translate('pagination.next') }}">
                        <i class="bi bi-chevron-right fs-6"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="{{ translate('pagination.next') }}">
                    <span class="page-link rounded-circle d-flex align-items-center justify-content-center border-0 bg-light text-muted"
                        aria-hidden="true">
                        <i class="bi bi-chevron-right fs-6"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
</div>
@endif











