<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('vendor/installer/img/favicon.ico') }}" type="image/png" />
    <title>{{ translate_text('EasyMarket Installer') }} - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap/bootstrap-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/installer/css/app.min.css') }}" />
</head>

<body>
    <div class="codebay-installation-wizard py-5">
        <div class="container-sm">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="logo-section mb-4 text-center">
                        <h1 class="mb-0">
                            <a href="#" class="fw-bold gradient-text fs-2">Easy<span class="fw-light">Market</span></a>
                        </h1>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="codebay-steps">
                        <div class="codebay-steps-header">
                            <div class="codebay-steps-product {{ stepNumber(1) }}" data-step="1">
                                <div class="codebay-steps-product-icon">
                                    <i class="bi bi-check"></i>
                                    <div class="codebay-steps-product-number">1</div>
                                </div>
                                <div class="codebay-steps-product-text">{{ translate_text('Requirements') }}</div>
                            </div>
                            <div class="codebay-steps-product {{ stepNumber(2) }}" data-step="2">
                                <div class="codebay-steps-product-icon">
                                    <i class="bi bi-check"></i>
                                    <div class="codebay-steps-product-number">2</div>
                                </div>
                                <div class="codebay-steps-product-text">{{ translate_text('Permissions') }}</div>
                            </div>
                            <div class="codebay-steps-product {{ stepNumber(3) }}" data-step="3">
                                <div class="codebay-steps-product-icon">
                                    <i class="bi bi-check"></i>
                                    <div class="codebay-steps-product-number">3</div>
                                </div>
                                <div class="codebay-steps-product-text">{{ translate_text('License') }}</div>
                            </div>
                            <div class="codebay-steps-product {{ stepNumber(4) }}" data-step="4">
                                <div class="codebay-steps-product-icon">
                                    <i class="bi bi-check"></i>
                                    <div class="codebay-steps-product-number">4</div>
                                </div>
                                <div class="codebay-steps-product-text">{{ translate_text('Database') }}</div>
                            </div>
                            <div class="codebay-steps-product {{ stepNumber(5) }}" data-step="5">
                                <div class="codebay-steps-product-icon">
                                    <i class="bi bi-check"></i>
                                    <div class="codebay-steps-product-number">5</div>
                                </div>
                                <div class="codebay-steps-product-text">{{ translate_text('Import') }}</div>
                            </div>
                            <div class="codebay-steps-product {{ stepNumber(6) }}" data-step="6">
                                <div class="codebay-steps-product-icon">
                                    <i class="bi bi-check"></i>
                                    <div class="codebay-steps-product-number">6</div>
                                </div>
                                <div class="codebay-steps-product-text">{{ translate_text('Completed') }}</div>
                            </div>
                        </div>
                        @yield('content')
                        <div class="nav d-flex align-items-center justify-content-center small mb-3">
                            <a href="https://codecanyon.net/user/codebay27" target="_blank"
                                class="nav-link px-3 text-muted d-flex align-items-center">
                                <i class="bi bi-bag me-2"></i>
                                {{ translate_text('Purchase') }}
                            </a>
                            <a href="https://twitter.com/codebay27" target="_blank"
                                class="nav-link px-3 text-muted d-flex align-items-center">
                                <i class="bi bi-arrow-repeat me-2"></i>
                                {{ translate_text('Updates') }}
                            </a>
                            <a href="https://t.me/codebay27" target="_blank"
                                class="nav-link px-3 text-muted d-flex align-items-center">
                                <i class="bi bi-headset me-2"></i>
                                {{ translate_text('Support') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('vendor/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/bootstrap/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
    <script>
        "use strict";
        $(".remove-spaces").on('input', function() {
            $(this).val($(this).val().replace(/\s/g, ""));
        });
    </script>
</body>

</html>


















