@extends('admin.layouts.app')
@section('section', translate('Faker'))
@section('title', translate('Faker Tools'))
@section('container', 'container-max-xl')
@section('content')
    <div class="row g-3">
        <div class="col-lg-6">
            <a href="{{ route('admin.faker.tools.tool', 'users') }}" class="text-dark">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="row g-4 align-items-center">
                            <div class="col-2 text-center">
                                <i class="fa-solid fa-users text-primary fa-3x"></i>
                            </div>
                            <div class="col">
                                <h5 class="mb-2">{{ translate('Fake Users') }}</h5>
                                <p class="mb-2">{{ translate('Generate fake users for your website.') }}</p>
                                <button class="btn btn-outline-primary btn-sm">
                                    {{ translate('Get Started') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-6">
            <a href="{{ route('admin.faker.tools.tool', 'followers') }}" class="text-dark">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="row g-4 align-items-center">
                            <div class="col-2 text-center">
                                <i class="fa-solid fa-user-plus text-secondary fa-3x"></i>
                            </div>
                            <div class="col">
                                <h5 class="mb-2">{{ translate('Fake Followers') }}</h5>
                                <p class="mb-2">{{ translate('Generate fake followers for a specific user.') }}</p>
                                <button class="btn btn-outline-secondary btn-sm">
                                    {{ translate('Get Started') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-6">
            <a href="{{ route('admin.faker.tools.tool', 'product-sales') }}" class="text-dark">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="row g-4 align-items-center">
                            <div class="col-2 text-center">
                                <i class="fa-solid fa-basket-shopping text-danger fa-3x"></i>
                            </div>
                            <div class="col">
                                <h5 class="mb-2">{{ translate('Fake product Sales') }}</h5>
                                <p class="mb-2">{{ translate('Generate fake sales for a specific product.') }}</p>
                                <button class="btn btn-outline-danger btn-sm">
                                    {{ translate('Get Started') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-6">
            <a href="{{ route('admin.faker.tools.tool', 'product-comments') }}" class="text-dark">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="row g-4 align-items-center">
                            <div class="col-2 text-center">
                                <i class="fa-solid fa-comments text-success fa-3x"></i>
                            </div>
                            <div class="col">
                                <h5 class="mb-2">{{ translate('Fake product Comments') }}</h5>
                                <p class="mb-2">{{ translate('Generate fake comments for a specific product.') }}</p>
                                <button class="btn btn-outline-success btn-sm">
                                    {{ translate('Get Started') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-6">
            <a href="{{ route('admin.faker.tools.tool', 'product-reviews') }}" class="text-dark">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="row g-4 align-items-center">
                            <div class="col-2 text-center">
                                <i class="fa-solid fa-star-half-stroke text-warning fa-3x"></i>
                            </div>
                            <div class="col">
                                <h5 class="mb-2">{{ translate('Fake product Reviews') }}</h5>
                                <p class="mb-2">
                                    {{ translate('Generate fake reviews for a specific product.') }}
                                </p>
                                <button class="btn btn-outline-warning btn-sm">
                                    {{ translate('Get Started') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-6">
            <a href="{{ route('admin.faker.tools.tool', 'blog-comments') }}" class="text-dark">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="row g-4 align-items-center">
                            <div class="col-2 text-center">
                                <i class="fa-solid fa-comment-dots text-info fa-3x"></i>
                            </div>
                            <div class="col">
                                <h5 class="mb-2">{{ translate('Fake Blog Comments') }}</h5>
                                <p class="mb-2">
                                    {{ translate('Generate fake comments for a specific blog article.') }}
                                </p>
                                <button class="btn btn-outline-info btn-sm">
                                    {{ translate('Get Started') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection


















