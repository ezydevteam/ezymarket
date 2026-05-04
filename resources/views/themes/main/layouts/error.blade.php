@php
    $isUserPanelError = authUser() && (request()->is('user*') || request()->routeIs('user.*'))
        && !request()->is('user/*/profile*') && !request()->routeIs('profile.*');
@endphp

@extends($isUserPanelError ? 'themes.main.userpanel.layout' : 'themes.main.layouts.app')
@section('body_class', 'error-page')

@section($isUserPanelError ? 'content' : 'body_content')
    @if ($isUserPanelError)
        <div class="userpanel-container userpanel-container-md py-4">
            <div class="card-v border-0 shadow-sm rounded-4 my-5 p-5 text-center bg-white">
                <div class="py-5">
                    <h1 class="display-1 fw-bold text-gradient mb-2">@yield('code')</h1>
                    <h2 class="fw-bold mb-3">@yield('title')</h2>
                    <div class="col-lg-9 m-auto">
                        <p class="text-gray-700 mb-4 lh-base">@yield('message')</p>
                    </div>
                    <div class="mt-2">
                        @if (authUser()?->isSeller())
                            <a href="{{ route('user.dashboard') }}" class="btn btn-primary btn-md rounded-pill px-5 hover-lift">
                                <i class="bi bi-grid-1x2-fill me-2"></i>{{ translate('Go to Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('user.purchase.index') }}" class="btn btn-primary btn-md rounded-pill px-5 hover-lift">
                                <i class="bi bi-box-seam-fill me-2"></i>{{ translate('Go to My Purchases') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <section class="section py-4">
            <div class="container container-boxed">
                <div class="card-v border-0 shadow-sm rounded-4 my-5 p-5 text-center bg-white">
                    <div class="row align-items-center justify-content-center py-5">
                        <div class="col-lg-8 text-center">
                            <h1 class="display-1 text-gradient fw-bold">@yield('code')</h1>
                            <h2 class="fw-bold mb-3">@yield('title')</h2>
                            <div class="col-lg-9 m-auto">
                                <p class="text-gray-700 mb-4 lh-base">@yield('message')</p>
                            </div>
                            <a href="{{ url('/') }}"
                                class="btn btn-primary btn-md rounded-pill px-5 hover-lift">
                                <i class="bi bi-house-door-fill me-2 fs-5"></i>
                                <span class="fw-semibold">{{ translate('Return to Home') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection
