@extends('themes.main.layouts.single')
@section('header_title', translate('All Categories'))
@section('description', translate('Browse all product categories and subcategories to find exactly what you need.'))
@section('title', translate('All Categories'))
@section('breadcrumbs', Breadcrumbs::render('categories'))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', 'categories'))

@section('main')
@if ($categories->count() > 0)
<div class="category-index-wrapper">
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        @foreach ($categories as $category)
        <div class="col">
            <div class="category-card card h-100 border-0 shadow-sm transition-all overflow-hidden">
                <div class="card-body p-4 d-flex flex-column h-100">
                    <a href="{{ $category->link }}" class="category-card-header d-flex align-items-center gap-3 mb-4">
                        <div
                            class="category-card-icon rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm">
                            @if($category->icon)
                            <i class="{{ $category->icon }} fs-4 text-primary"></i>
                            @else
                            <i class="bi bi-folder-plus fs-4 text-primary"></i>
                            @endif
                        </div>
                        <div class="overflow-hidden">
                            <h5 class="category-card-title mb-0 text-dark fw-semibold">{{ $category->name }}</h5>
                            <span class="text-gray-700 fs-13">
                                {{ translate($category->products_count === 1 ? ':count product' : ':count products',
                                ['count' => $category->products_count]) }}
                            </span>
                        </div>
                    </a>

                    <div class="category-card-subcategories">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($category->subCategories as $subCategory)
                            <a href="{{ $subCategory->link }}"
                                class="subcategory-tag btn btn-light btn-sm rounded-pill fs-13 border hover-border-primary">
                                {{ $subCategory->name }}
                                <span class="fs-12 ms-1 text-muted">({{ $subCategory->products_count }})</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
<div class="mt-4 d-flex justify-content-center">
    {{ $categories->links() }}
</div>
@else
<div class="card border-0 shadow-sm p-5 text-center rounded-4 mb-5">
    <div class="mb-3">
        <i class="bi bi-search display-1 text-muted opacity-25"></i>
    </div>
    <h4 class="text-dark">{{ translate('No categories found') }}</h4>
    <p class="text-muted">{{ translate('Try checking back soon for new updates.') }}</p>
</div>
@endif
@endsection
