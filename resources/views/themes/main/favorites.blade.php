@extends('themes.main.layouts.single')
@section('noindex', true)
@section('header_title', translate('Your Favorite List'))
@section('title', translate('Favorites lists'))
@section('description', translate('Browse your favorite products and add them to your cart'))
@section('container', 'container container-default')
@section('header_style', $favoriteCount > 0 ? 'centered' : 'no_header')

@section('main')
 <div class="section">
    @if ($favoriteCount > 0 || request()->input('query'))
        <div class="product-list-view">
            <div class="col-lg-8 mx-auto">
                <div id="product-list-container"
                    class="product-list-stack d-flex flex-column gap-3">
                    @foreach ($favorites as $favorite)
                        @themeInclude('products.partials.list-product', [
                            'product' => $favorite->product
                        ])
                    @endforeach
                </div>
                {{ $favorites->links() }}
            </div>
        </div>
    @else
        <div class="modern-card p-5 text-center max-w-600 mx-auto overflow-hidden transition-all">
            <div class="mb-4">
                <div class="empty-favorites-icon-wrapper scale-up d-inline-block">
                    <div class="d-flex align-items-center justify-content-center icon-circle-xl bg-danger-subtle text-danger shadow-sm border border-danger-subtle">
                        <i class="bi bi-heart display-4"></i>
                    </div>
                </div>
            </div>
            <h3 class="fw-bold mb-3">{{ translate('Your Favorite List is Empty') }}</h3>
            <p class="text-muted mb-5 px-md-5 fs-15">
                {{ translate('You haven\'t saved any items yet. Browse our marketplace and tap the heart icon on products you love to keep track of them here!') }}
            </p>
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a href="{{ route('products.index') }}"
                    class="btn btn-primary btn-lg btn-modern rounded-pill shadow-sm fw-semibold px-5">
                    <i class="bi bi-bag-plus me-2"></i>{{ translate('Browse Products') }}
                </a>
                <a href="{{ route('home') }}"
                    class="btn btn-light btn-lg btn-modern rounded-pill border fw-semibold px-4">
                    {{ translate('Go Home') }}
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
