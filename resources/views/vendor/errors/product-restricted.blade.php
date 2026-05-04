@extends('themes.main.layouts.single')
@section('noindex', true)
@section('title', 'product Temporarily Unavailable')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-pause-circle text-warning" style="font-size: 4rem;"></i>

                    <h2 class="mt-4 mb-3">{{ translate('product Temporarily Unavailable') }}</h2>

                    <p class="text-muted mb-4">
                        {{ translate('This product is currently under review and has been temporarily removed from public view.') }}
                    </p>

                    @if(isset($product))
                        <div class="alert alert-info text-start">
                            <div class="d-block"><strong>product ID:</strong> #{{ $product->id }}</div>
                            <div class="d-block"><strong>product Name:</strong> {{ $product->name }}</div>
                            <div class="d-block"><strong>Held From:</strong> {{ $product->restricted_at->format('M d, Y') }}</div>
                        </div>
                    @endif
                    @if(auth()->check() && auth()->id() == $product->author_id)
                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ translate('Your product has been held due to reports.
                            Please check your email for more information. If you think this is a mistake, please contact our support team') }} <a href="/contact-us">{{ translate('here') }}</a>
                        </div>
                    @endif
                    <div class="mt-4">
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>
                            {{ translate('Browse Other products') }}
                        </a>

                        <a href="{{ route('home') }}" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-home me-2"></i>
                            {{ translate('Go to Homepage') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


















