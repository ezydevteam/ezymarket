@php $data = (object)($data ?? []); @endphp

@if(($data->blogCategories ?? collect())->count() > 0)
<div id="{{ $data->uniqueId }}" class="blog-categories {{ $isFullWidth ? $data->containerClass : '' }}">
    @themeInclude('blocks.home.partials.block-title', ['data' => $data])
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3 justify-content-{{ $data->alignment }}">
        @foreach($data->blogCategories as $category)
        <div class="col">
            <a href="{{ route('blog.category', $category->slug) }}"
                class="card blog-category-card h-100 {{ $data->styleClass }}">
                <div
                    class="card-body d-flex flex-column align-items-center justify-content-center text-center p-3">
                    <div class="mb-2 text-primary">
                        <i class="bi bi-folder2-open fs-3"></i>
                    </div>
                    <h6 class="card-title text-dark mb-1">{{ $category->name }}</h6>
                    <small class="text-muted">{{ $category->articles_count }} {{ translate('Articles') }}</small>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif
