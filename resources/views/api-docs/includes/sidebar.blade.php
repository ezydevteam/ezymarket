<aside class="sidebar">
    <div class="sidebar-toggle-btn">
        <i class="fa fa-chevron-right"></i>
    </div>
    <div class="sidebar-inner">
        <div class="mb-5">
            <a href="{{ route('home') }}" class="logo">
                <img src="{{ getSiteLogo('logo_dark') }}" alt="{{ getSiteName() }}">
            </a>
        </div>
        <div class="nav flex-column nav-pills">
            <div class="sidebar-group-links">
                <h6 class="mb-3">{{ translate('Introduction') }}</h6>
                <ul class="ps-0 ms-0">
                    <li>
                        <button class="active" data-bs-toggle="pill" data-bs-target="#overview" type="button"
                            role="tab" aria-controls="overview" aria-selected="true">
                            {{ translate('Overview') }}
                        </button>
                    </li>
                    <li>
                        <button class="sidebar-group-link" data-bs-toggle="pill" data-bs-target="#authentication"
                            type="button" role="tab" aria-controls="authentication" aria-selected="false">
                            {{ translate('Authentication') }}
                        </button>
                    </li>
                </ul>
            </div>
            <div class="sidebar-group-links">
                <h6 class="mb-3">{{ translate('Account') }}</h6>
                <ul class="ps-0 ms-0">
                    <li>
                        <button class="sidebar-group-link" data-bs-toggle="pill" data-bs-target="#account-details"
                            type="button" role="tab" aria-controls="account-details" aria-selected="false">
                            {{ translate('Get Account Details') }}
                        </button>
                    </li>
                </ul>
            </div>
            <div class="sidebar-group-links">
                <h6 class="mb-3">{{ translate('products') }}</h6>
                <ul class="ps-0 ms-0">
                    <li>
                        <button class="sidebar-group-link" data-bs-toggle="pill" data-bs-target="#products-all"
                            type="button" role="tab" aria-controls="products-all" aria-selected="false">
                            {{ translate('Get All products') }}
                        </button>
                    </li>
                    <li>
                        <button class="sidebar-group-link" data-bs-toggle="pill" data-bs-target="#products-item"
                            type="button" role="tab" aria-controls="products-item" aria-selected="false">
                            {{ translate('Get An product Details') }}
                        </button>
                    </li>
                </ul>
            </div>
            <div class="sidebar-group-links">
                <h6 class="mb-3">{{ translate('Purchases') }}</h6>
                <ul class="ps-0 ms-0">
                    <li>
                        <button class="sidebar-group-link" data-bs-toggle="pill" data-bs-target="#purchases-validation"
                            type="button" role="tab" aria-controls="purchases-validation" aria-selected="false">
                            {{ translate('Purchase Validation') }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</aside>


















