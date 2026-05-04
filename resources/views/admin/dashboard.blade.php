@extends('admin.layouts.full')
@section('title', translate('Dashboard'))
@section('content')

@if(!superAdmin())
@if (!@$settings->cronjob->last_execution)
<div class="alert alert-danger p-3 mb-4 border-0 shadow-sm">
    <div class="d-flex align-items-start">
        <i class="bi bi-exclamation-triangle fs-3 text-danger me-3"></i>
        <div class="flex-grow-1">
            <h6 class="alert-heading mb-2 fw-semibold">{{ translate("Cron Job Configuration Required") }}</h6>
            <p class="mb-2 small">
                {{ translate("Your automated tasks are not running properly. Cron jobs are essential for email delivery,
                badge updates, discount management, cache clearing, and Sitemap generation.") }}
            </p>
            <a href="{{ route('admin.system.cronjob.index') }}" class="btn btn-outline-danger btn-sm">
                {{ translate("Configure Cron Job") }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
@endif

@if (!@$settings->smtp->status)
<div class="alert alert-warning p-3 mb-4 border-0 shadow-sm">
    <div class="d-flex align-items-start">
        <i class="bi bi-info-circle fs-3 text-orange me-3"></i>
        <div class="flex-grow-1">
            <h6 class="alert-heading mb-2 fw-semibold">{{ translate("Email Configuration Required") }}</h6>
            <p class="mb-2 small">
                {{ translate("Email services are currently disabled. Configure SMTP settings to enable password
                recovery, notifications, and all email-dependent features.") }}
            </p>
            <a href="{{ route('admin.mail.settings.index') }}" class="btn text-orange btn-sm">
                {{ translate("Configure Mail Server") }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
@endif
@endif

{{-- Congratulations Banner Slider --}}
<div id="printHeaderRow" class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card text-white overflow-hidden h-100 position-relative"
            style="background: linear-gradient(135deg, rgb(from var(--primary-color) r g b / 0.75) 0%, rgba(91, 75, 201, 0.6) 100%);">
            {{-- Swiper Pagination --}}
            <div class="position-absolute top-0 end-0 p-3" style="z-index: 10;">
                <div class="dashboard-banner-swiper-pagi"></div>
            </div>

            <div class="swiper dashboard-banner-swiper h-100">
                <div class="swiper-wrapper">
                    {{-- Slide 1: Top Seller --}}
                    @if($topSeller['id'])
                    <div class="swiper-slide">
                        <div class="card-body position-relative d-flex flex-column h-100">
                            {{-- Top: Title & Subtitle --}}
                            <div class="mb-2">
                                <h6 class="mb-1">
                                    {{ translate(':name', ['name' => $topSeller['name']]) }} 🎉
                                </h6>
                                <p class="text-white-75 mb-0 small">{{ translate('Top seller of this month') }}</p>
                            </div>

                            {{-- Middle: Stats with Bullets --}}
                            <div class="flex-grow-1">
                                <div class="row">
                                    <div class="col-12 col-md-7">
                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                            <li class="d-flex align-items-center gap-2">
                                                <span class="text-white-75">•</span>
                                                <div class="text-white">
                                                    <span class="fw-semibold fs-5">{{
                                                        getCompactAmount($topSeller['total_sales']) }}</span>
                                                    <span class="text-white-75 small ms-1">{{ translate('total revenue')
                                                        }}</span>
                                                </div>
                                            </li>
                                            <li class="d-flex align-items-center gap-2">
                                                <span class="text-white-75">•</span>
                                                <div class="text-white">
                                                    <span class="fw-semibold fs-5">{{ $topSeller['sales_count']
                                                        }}</span>
                                                    <span class="text-white-75 small ms-1">{{ translate('total sales')
                                                        }}</span>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-12 col-md-5 d-none d-md-block text-end">
                                        <img src="{{ asset('images/illustrations/congrats.png') }}"
                                            alt="Congratulations" class="img-fluid" style="max-height: 100px;">
                                    </div>
                                </div>
                            </div>

                            {{-- Bottom: Actions --}}
                            <div class="mt-auto pt-2 border-top border-white border-opacity-25">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <a href="{{ route('admin.records.sales.index', ['seller' => $topSeller['id']]) }}"
                                        class="btn btn-sm btn-light opacity-75">
                                        <i class="bi bi-graph-up me-1"></i>{{ translate('View Sales') }}
                                    </a>
                                    @if($topSeller['congratulated'] ?? false)
                                    <span data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ translate('Already congratulated this month') }}">
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="bi bi-check-circle me-1"></i>{{ translate('Congratulated') }}
                                        </button>
                                    </span>
                                    @else
                                    <a href="{{ route('admin.dashboard.congrats.send', ['template' => 'top_seller', 'user_id' => $topSeller['id']]) }}"
                                        class="btn btn-sm btn-success action-confirm" data-method="GET"
                                        data-confirm="{{ translate('Are you sure you want to send congratulations message to :name?', ['name' => $topSeller['name']]) }}">
                                        <i class="bi bi-envelope-heart me-1"></i>{{ translate('Congratulate') }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Slide 2: Top Sold Product --}}
                    @if($topSoldProduct['id'])
                    <div class="swiper-slide">
                        <div class="card-body position-relative d-flex flex-column h-100">
                            {{-- Top: Title & Subtitle --}}
                            <div class="mb-2">
                                <h6 class="mb-1">
                                    {{ truncateText($topSoldProduct['name'], 40) }} 🏆
                                </h6>
                                <p class="text-white-75 mb-0 small">{{ translate('Top selling product this month') }}
                                </p>
                            </div>

                            {{-- Middle: Stats with Bullets --}}
                            <div class="flex-grow-1">
                                <div class="row">
                                    <div class="col-12 col-md-7">
                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                            <li class="d-flex align-items-center gap-2">
                                                <span class="text-white-75">•</span>
                                                <div class="text-white">
                                                    <span class="fw-semibold fs-5">{{
                                                        getCompactAmount($topSoldProduct['total_sales']) }}</span>
                                                    <span class="text-white-75 small ms-1">{{ translate('total
                                                        earnings') }}</span>
                                                </div>
                                            </li>
                                            <li class="d-flex align-items-center gap-2">
                                                <span class="text-white-75">•</span>
                                                <div class="text-white">
                                                    <span class="fw-semibold fs-5">{{ $topSoldProduct['sales_count']
                                                        }}</span>
                                                    <span class="text-white-75 small ms-1">{{ translate('total sales')
                                                        }}</span>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-12 col-md-5 d-none d-md-block text-end">
                                        <img src="{{ asset('images/illustrations/product-stats.png') }}"
                                            alt="{{ $topSoldProduct['name'] }}" class="img-fluid"
                                            style="max-height: 100px;">
                                    </div>
                                </div>
                            </div>

                            {{-- Bottom: Actions --}}
                            @php
                            $productSeller = \App\Models\Product\Product::find($topSoldProduct['id'])?->seller;
                            @endphp
                            <div class="mt-auto pt-2 border-top border-white border-opacity-25">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <a href="{{ route('admin.products.show', $topSoldProduct['id']) }}"
                                        class="btn btn-sm btn-light opacity-75">
                                        <i class="bi bi-box-seam me-1"></i>{{ translate('View Product') }}
                                    </a>
                                    @if($productSeller)
                                    @if($topSoldProduct['congratulated'] ?? false)
                                    <span data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ translate('Already congratulated this month') }}">
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="bi bi-check-circle me-1"></i>{{ translate('Congratulated') }}
                                        </button>
                                    </span>
                                    @else
                                    <a href="{{ route('admin.dashboard.congrats.send', ['template' => 'top_product', 'user_id' => $productSeller->id, 'product_id' => $topSoldProduct['id']]) }}"
                                        class="btn btn-sm btn-success action-confirm" data-method="GET"
                                        data-confirm="{{ translate('Are you sure you want to send congratulations message to :name?', ['name' => $productSeller->full_name]) }}">
                                        <i class="bi bi-envelope-heart me-1"></i>{{ translate('Congratulate') }}
                                    </a>
                                    @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Slide 3: Upcoming Birthdays --}}
                    @if($upcomingBirthdays->isNotEmpty())
                    <div class="swiper-slide">
                        <div class="card-body position-relative d-flex flex-column h-100">
                            {{-- Top: Title & Subtitle --}}
                            <div class="mb-2">
                                <h6 class="mb-1">
                                    {{ translate('Upcoming Birthdays!') }} 🎂
                                </h6>
                                <p class="text-white-75 mb-0 small">{{ translate('Next 3 days') }}</p>
                            </div>

                            {{-- Middle: User List with Bullets --}}
                            <div class="flex-grow-1">
                                <div class="row">
                                    <div class="col-12 col-md-7">
                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                            @foreach($upcomingBirthdays->take(2) as $user)
                                            @if($user->days_until !== null)
                                            <li class="d-flex align-items-center gap-2">
                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}"
                                                    class="rounded-circle" width="28" height="28"
                                                    style="object-fit: cover;">
                                                <div class="flex-grow-1 text-white">
                                                    <div class="small fw-medium">{{ $user->full_name }}</div>
                                                    <div class="small text-white-75" style="font-size: 0.75rem;">
                                                        @if($user->days_until == 0)
                                                        {{ translate('Today') }} 🎉
                                                        @elseif($user->days_until == 1)
                                                        {{ translate('Tomorrow') }}
                                                        @else
                                                        {{ translate('In :days days', ['days' => $user->days_until]) }}
                                                        @endif
                                                    </div>
                                                </div>
                                                @if($user->already_wished_today ?? false)
                                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ translate('Birthday wishes already sent') }}">
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                </span>
                                                @else
                                                <a href="{{ route('admin.dashboard.birthday.send', ['user_id' => $user->id]) }}"
                                                    class="btn btn-sm btn-success action-confirm" data-method="GET"
                                                    data-confirm="{{ translate('Are you sure you want to send birthday wishes to :name?', ['name' => $user->full_name]) }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ translate('Send birthday wishes') }}">
                                                    <i class="bi bi-gift"></i>
                                                </a>
                                                @endif
                                            </li>
                                            @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="col-12 col-md-5 d-none d-md-block text-end">
                                        <img src="{{ asset('images/illustrations/birthday.png') }}" alt="Birthday"
                                            class="img-fluid" style="max-height: 100px;">
                                    </div>
                                </div>
                            </div>

                            {{-- Bottom: Count & Actions --}}
                            @if($upcomingBirthdays->count() > 2)
                            <div class="mt-auto pt-2 border-top border-white border-opacity-25">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="text-white-75 small">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <strong class="text-white">{{ $upcomingBirthdays->count() }}</strong> {{
                                        translate('upcoming birthdays') }}
                                    </div>
                                    <a href="{{ route('admin.roles.users.index') }}"
                                        class="btn btn-sm btn-light opacity-75">
                                        <i class="bi bi-people me-1"></i>{{ translate('View All') }}
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <x-card id="statisticsCard" title="{{ translate('Statistics') }}" headerClass="border-0 pb-0" titleTag="h5"
            fullHeight>
            <x-slot:actions>
                <x-dropdown buttonClass="btn-icon text-muted">
                    <x-dropdown.item type="button" icon="bi-calendar-day" iconClass="text-danger"
                        class="stats-period-option dashboard-period-filter" data-period="today">
                        {{ translate('Today') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-week" iconClass="text-primary"
                        class="stats-period-option dashboard-period-filter" data-period="last_7_days">
                        {{ translate('Last 7 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-range" iconClass="text-purple"
                        class="stats-period-option dashboard-period-filter" data-period="last_28_days">
                        {{ translate('Last 28 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-month" iconClass="text-info"
                        class="stats-period-option dashboard-period-filter active" data-period="this_month">
                        {{ translate('This Month') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-success"
                        class="stats-period-option dashboard-period-filter" data-period="this_year">
                        {{ translate('This Year') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-infinity" iconClass="text-orange"
                        class="stats-period-option dashboard-period-filter" data-period="lifetime">
                        {{ translate('Lifetime') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="divider" />
                    <x-dropdown.item type="button" icon="bi-printer" iconClass="text-primary" id="printDashboard">
                        {{ translate('Print Full Report') }}
                    </x-dropdown.item>
                </x-dropdown>
            </x-slot:actions>

            <div class="row g-4 h-100">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="card-icon card-icon-md rounded-circle bg-text-purple">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 text-muted">
                            <h4 class="mb-0 fw-semibold" data-counter="sellers_sales">{{
                                getCompactAmount($statistics["sellers_sales"]) }}</h4>
                            <p class="mb-0 small">{{ translate('Sales') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="card-icon card-icon-md rounded-circle bg-text-yellow">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 text-muted">
                            <h4 class="mb-0 fw-semibold" data-counter="buyer_fees_seller_fees">{{
                                getCompactAmount($statistics["buyer_fees"] + $statistics["seller_fees"]) }}</h4>
                            <p class="mb-0 small">{{ translate('Fees') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="card-icon card-icon-md rounded-circle bg-text-green">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 text-muted">
                            <h4 class="mb-0 fw-semibold" data-counter="platform_total_revenues">{{
                                getCompactAmount($statistics["platform_total_revenues"]) }}</h4>
                            <p class="mb-0 small">{{ translate('Revenue') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="card-icon card-icon-md rounded-circle bg-text-violet">
                                <i class="bi bi-send"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 text-muted">
                            <h4 class="mb-0 fw-semibold" data-counter="payout_amount">{{
                                getCompactAmount($statistics["payout_amount"]) }}</h4>
                            <p class="mb-0 small">{{ translate('Payouts') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="card-icon card-icon-md rounded-circle bg-text-pink">
                                <i class="bi bi-cart"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 text-muted">
                            <h4 class="mb-0 fw-semibold" data-counter="total_products">{{
                                numberFormat($statistics["total_products"]) }}</h4>
                            <p class="mb-0 small">{{ translate('Products') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="card-icon card-icon-md rounded-circle bg-text-primary">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 text-muted">
                            <h4 class="mb-0 fw-semibold" data-counter="total_sellers">{{
                                numberFormat($statistics["total_sellers"]) }}</h4>
                            <p class="mb-0 small">{{ translate('Sellers') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="card-icon card-icon-md rounded-circle bg-text-orange">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 text-muted">
                            <h4 class="mb-0 fw-semibold" data-counter="total_refunds">{{
                                numberFormat($statistics["total_refunds"]) }}</h4>
                            <p class="mb-0 small">{{ translate('Refunds') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="card-icon card-icon-md rounded-circle bg-text-red">
                                <i class="bi bi-receipt"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 text-muted">
                            <h4 class="mb-0 fw-semibold" data-counter="buyer_tax_seller_tax">{{
                                getCompactAmount($statistics["buyer_tax"] + $statistics["seller_tax"]) }}</h4>
                            <p class="mb-0 small">{{ translate('Tax') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</div>
{{-- Admin Notes & Revenue --}}
<div class="row g-4 mb-4">
    {{-- Admin Notes --}}
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">{{ translate('My Notes') }}</h6>
                <div class="d-flex gap-2">
                    @if($adminNotes->count() > 1)
                    <button type="button" class="btn btn-sm btn-light btn-icon dashboard-notes-swiper-button-prev">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light btn-icon dashboard-notes-swiper-button-next">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($adminNotes->count() > 0)
                <div class="position-relative text-center rounded-2 mb-3"
                    style="background-color: rgb(from var(--primary-color) r g b / 0.1);">
                    <img src="{{ asset('images/illustrations/key-points.png') }}" alt="Notes"
                        class="object-fit-contain pt-3" style="max-height: 150px;">
                </div>
                <div class="swiper dashboard-notes-swiper">
                    <div class="swiper-wrapper">
                        @foreach($adminNotes as $note)
                        <div class="swiper-slide">
                            <h6 class="fw-medium mb-2">{{ $note->title }}</h6>
                            @php
                            $description = $note->description;
                            $isLong = strlen($description) > 200;
                            $shortDesc = $isLong ? substr($description, 0, 200) : $description;
                            @endphp
                            <div class="note-description-wrapper mb-3">
                                <p class="text-muted small mb-0 d-inline">
                                    <span class="note-text">{{ $shortDesc }}</span>
                                    @if($isLong)
                                    <span class="note-full-text" style="display: none;">{{ $description }}</span>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-primary note-toggle-btn"
                                        style="font-size: 0.813rem; vertical-align: baseline;">
                                        ...{{ translate('more') }}
                                    </button>
                                    @endif
                                </p>
                            </div>
                            @if ($note->date_time || $note->priority)
                            <div class="row g-2">
                                @if ($note->date_time)
                                <div class="col-md-6 d-flex align-items-center gap-2">
                                    <div class="card-icon bg-text-primary rounded-circle">
                                        <i class="bi bi-calendar4-event"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ translate('Date & Time')
                                            }}</div>
                                        <div class="small">{{ \Date::parse($note->date_time)->format('d M Y, H:i') }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if ($note->priority)
                                <div class="col-md-6 d-flex align-items-center gap-2">
                                    <div class="card-icon bg-text-red rounded-circle">
                                        <i class="bi bi-flag"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ translate('Priority') }}
                                        </div>
                                        <div class="small text-capitalize">{{ $note->priority }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-3 mt-3">
                    <button class="btn btn-light action-confirm active-note-delete-btn" data-action=""
                        data-method="DELETE"
                        data-confirm="{{ translate('Are you sure you want to delete this note?') }}"
                        data-bs-toggle="tooltip" title="{{ translate('Delete this note') }}">
                        <i class="bi bi-trash"></i>
                    </button>
                    <button class="btn bg-text-primary hover-opacity flex-fill" data-bs-toggle="modal"
                        data-bs-target="#createNoteModal">
                        <i class="bi bi-plus-circle me-2"></i>{{ translate('Create Note') }}
                    </button>
                </div>
                @else
                <x-empty message="No notes yet!" icon="bi-calendar-notes" />
                <button class="btn btn-light w-100 mt-3" data-bs-toggle="modal" data-bs-target="#createNoteModal">
                    <i class="bi bi-plus-circle me-2"></i>{{ translate('Create Note') }}
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Product Status Overview --}}
    <div class="col-12 col-lg-4">
        <div class="d-flex flex-column gap-4 h-100">
            <x-card id="productStatusCard" title="{{ translate('Product Status') }}"
                subtitle="{{ translate('This Month') }}" headerClass="border-0 pb-0" titleTag="h6" fullHeight>
                <x-slot:actions>
                    <x-dropdown buttonClass="btn-icon text-muted" id="productStatusDropdown">
                        <x-dropdown.item type="button" icon="bi-calendar-week" iconClass="text-primary"
                            class="product-status-option dashboard-period-filter" data-period="last_7_days">
                            {{ translate('Last 7 Days') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-calendar-range" iconClass="text-purple"
                            class="product-status-option dashboard-period-filter" data-period="last_28_days">
                            {{ translate('Last 28 Days') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-info"
                            class="product-status-option dashboard-period-filter active" data-period="this_month">
                            {{ translate('This Month') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-success"
                            class="product-status-option dashboard-period-filter" data-period="this_year">
                            {{ translate('This Year') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-infinity" iconClass="text-orange"
                            class="product-status-option dashboard-period-filter" data-period="lifetime">
                            {{ translate('Lifetime') }}
                        </x-dropdown.item>
                    </x-dropdown>
                </x-slot:actions>
                <div id="productStatusLoader" class="text-center py-4">
                    <x-loader :centered="true" />
                </div>

                <div id="productStatusContent" class="d-none">
                    {{-- Donut Chart --}}
                    <div class="d-flex align-items-center justify-content-center position-relative"
                        style="height: 220px;">
                        <canvas id="productStatusChart"></canvas>
                        <div class="position-absolute start-50 translate-middle text-center" style="top: 42%;">
                            <h4 class="mb-0 fw-medium" id="productStatusPercentage">0%</h4>
                            <small class="text-muted text-uppercase" id="productStatusLabel">{{ translate('Approved')
                                }}</small>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Product Issues --}}
            <x-card id="productIssuesCard" title="{{ translate('Product Issues') }}" headerClass="border-0 pb-0"
                titleTag="h6">

                <div id="productIssuesLoader" class="text-center py-4">
                    <x-loader :centered="true" />
                </div>

                <div id="productIssuesContent" class="d-none">
                    <div class="row g-3 align-items-center mb-2">
                        <div class="col-5">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span
                                    class="bg-text-orange d-flex align-items-center justify-content-center rounded-circle"
                                    style="width:30px; height:30px;">
                                    <i class="bi bi-flag"></i>
                                </span>
                                <span>{{ translate('Reported') }}</span>
                            </div>
                            <h5 class="mb-0" id="reportedProductsPercentage">0%</h5>
                            <small class="text-muted" id="reportedProductsCount">0</small>
                        </div>
                        <div class="col-2 text-center">
                            <div class="position-relative d-inline-block">
                                <div class="vr position-absolute top-0 start-50 translate-middle-x"
                                    style="height: 60px; margin-top: -15px; background-color: #d2dbe2;"></div>
                                <span class="badge bg-white text-muted fw-medium rounded-circle"
                                    style="font-size: 10px; padding: 4px; border: 1px solid #e5e7eb;">VS</span>
                            </div>
                        </div>
                        <div class="col-5 text-end">
                            <div class="d-flex align-items-center gap-2 justify-content-end mb-2">
                                <span>{{ translate('Restricted') }}</span>
                                <span class="d-flex align-items-center justify-content-center rounded-circle"
                                    style="width:30px; height:30px; background-color: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                    <i class="bi bi-shield-x"></i>
                                </span>
                            </div>
                            <h5 class="mb-0" id="restrictedProductsPercentage">0%</h5>
                            <small class="text-muted" id="restrictedProductsCount">0</small>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" id="productIssuesProgressBar" role="progressbar"
                            style="width: 50%; background: linear-gradient(90deg, #f59e0b 0%, #ef4444 100%);"></div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    {{-- User Role & Verification --}}
    <div class="col-12 col-lg-4">
        <div class="d-flex flex-column gap-4 h-100">
            {{-- User Role --}}
            <x-card id="userRoleCard" title="{{ translate('User Role') }}" subtitle="{{ translate('This Month') }}"
                headerClass="border-0 pb-0" titleTag="h6" fullHeight>
                <x-slot:actions>
                    <x-dropdown buttonClass="btn-icon text-muted" id="userRoleDropdown">
                        <x-dropdown.item type="button" icon="bi-calendar-week" iconClass="text-primary"
                            class="user-role-option dashboard-period-filter" data-period="last_7_days">
                            {{ translate('Last 7 Days') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-calendar-range" iconClass="text-purple"
                            class="user-role-option dashboard-period-filter" data-period="last_28_days">
                            {{ translate('Last 28 Days') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-info"
                            class="user-role-option dashboard-period-filter active" data-period="this_month">
                            {{ translate('This Month') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-success"
                            class="user-role-option dashboard-period-filter" data-period="this_year">
                            {{ translate('This Year') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-infinity" iconClass="text-orange"
                            class="user-role-option dashboard-period-filter" data-period="lifetime">
                            {{ translate('Lifetime') }}
                        </x-dropdown.item>
                    </x-dropdown>
                </x-slot:actions>
                <div id="userRoleLoader" class="text-center py-4">
                    <x-loader :centered="true" />
                </div>

                <div id="userRoleContent" class="d-none">
                    {{-- Donut Chart --}}
                    <div class="d-flex align-items-center justify-content-center position-relative"
                        style="height: 220px;">
                        <canvas id="userRoleChart"></canvas>
                        <div class="position-absolute start-50 translate-middle text-center" style="top: 42%;">
                            <h4 class="mb-0 fw-medium" id="userRolePercentage">0%</h4>
                            <small class="text-muted text-uppercase" id="userRoleLabel">{{ translate('Buyers')
                                }}</small>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Verified Users --}}
            <x-card id="userVerificationCard" title="{{ translate('Verified Users') }}" headerClass="border-0 pb-0"
                titleTag="h6">

                <div id="userVerificationLoader" class="text-center py-4">
                    <x-loader :centered="true" />
                </div>

                <div id="userVerificationContent" class="d-none">
                    <div class="row g-3 align-items-center mb-2">
                        <div class="col-5">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span
                                    class="bg-text-primary d-flex align-items-center justify-content-center rounded-circle"
                                    style="width:30px; height:30px;">
                                    <i class="bi bi-envelope-check"></i>
                                </span>
                                <span>{{ translate('Email') }}</span>
                            </div>
                            <h5 class="mb-0" id="emailVerifiedPercentage">0%</h5>
                            <small class="text-muted" id="emailVerifiedCount">0</small>
                        </div>
                        <div class="col-2 text-center">
                            <div class="position-relative d-inline-block">
                                <div class="vr position-absolute top-0 start-50 translate-middle-x"
                                    style="height: 60px; margin-top: -15px; background-color: #d2dbe2;"></div>
                                <span class="badge bg-white text-muted fw-medium rounded-circle"
                                    style="font-size: 10px; padding: 4px; border: 1px solid #e5e7eb;">VS</span>
                            </div>
                        </div>
                        <div class="col-5 text-end">
                            <div class="d-flex align-items-center gap-2 justify-content-end mb-2">
                                <span>{{ translate('ID') }}</span>
                                <span
                                    class="bg-text-green d-flex align-items-center justify-content-center rounded-circle"
                                    style="width:30px; height:30px;">
                                    <i class="bi bi-person-check"></i>
                                </span>
                            </div>
                            <h5 class="mb-0" id="idVerifiedPercentage">0%</h5>
                            <small class="text-muted" id="idVerifiedCount">0</small>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" id="userVerificationProgressBar" role="progressbar"
                            style="width: 50%; background: linear-gradient(90deg, var(--primary-color) 0%, #10b981 100%);">
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>

{{-- Charts Section --}}
<div class="row g-4 mb-4">
    {{-- Users Chart --}}
    <div class="col-12 col-lg-8">
        <x-card title="{{ translate('User Analytics') }}" subtitle="{{ translate('Registration Overview') }}"
            :headerBorder="false" titleTag="h6" fullHeight>
            <x-slot:actions>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="toggleUserCompare"
                        data-bs-toggle="tooltip" title="{{ translate('Compare Data') }}">
                        <i class="bi bi-bar-chart-line"></i>
                    </button>
                    <x-dropdown buttonClass="btn btn-sm btn-outline-primary">
                        <x-dropdown.item href="{{ route('admin.roles.users.index') }}" icon="bi-list-ul"
                            iconClass="text-primary" target="_blank">
                            {{ translate('View All Users') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="divider" />
                        <x-dropdown.item type="button" icon="bi-arrow-clockwise" color="success"
                            id="refreshUserAnalyticsCache">
                            {{ translate('Refresh Data') }}
                        </x-dropdown.item>
                    </x-dropdown>
                </div>
            </x-slot:actions>

            {{-- Tab Navigation --}}
            <div class="d-flex gap-2 mb-4 flex-wrap" role="group" id="singlePeriodTabs">
                <button type="button" id="user-btn-1"
                    class="btn btn-sm btn-primary user-analytics-tab active rounded-pill fw-normal" data-type="week">
                    {{ translate('Weekly') }}
                </button>
                <button type="button" id="user-btn-2"
                    class="btn btn-sm btn-outline-primary user-analytics-tab rounded-pill fw-normal" data-type="month">
                    {{ translate('Monthly') }}
                </button>
                <button type="button" id="user-btn-3"
                    class="btn btn-sm btn-outline-primary user-analytics-tab rounded-pill fw-normal" data-type="year">
                    {{ translate('Yearly') }}
                </button>
            </div>

            {{-- Comparison Mode Tabs --}}
            <div class="d-none gap-2 mb-4 flex-wrap" role="group" id="userCompareTabs">
                <button type="button" id="user-compare-btn-1"
                    class="btn btn-sm btn-primary user-compare-tab active rounded-pill fw-normal" data-type="week">
                    {{ translate('Weekly') }}
                </button>
                <button type="button" id="user-compare-btn-2"
                    class="btn btn-sm btn-outline-primary user-compare-tab rounded-pill fw-normal" data-type="month">
                    {{ translate('Monthly') }}
                </button>
                <button type="button" id="user-compare-btn-3"
                    class="btn btn-sm btn-outline-primary user-compare-tab rounded-pill fw-normal" data-type="year">
                    {{ translate('Yearly') }}
                </button>
            </div>

            {{-- Chart Container --}}
            <div class="chart-wrapper">
                <div id="userAnalyticsLoader" class="position-absolute top-50 start-50 translate-middle d-none">
                    <x-loader :centered="true" />
                </div>
                <canvas id="userAnalyticsChart" class="chart-line"></canvas>
            </div>

            {{-- Period Display --}}
            <div class="d-flex align-items-center justify-content-center gap-2 mt-3">
                <span class="badge bg-light text-dark fs-6 px-3 py-2" id="userAnalyticsPeriod">{{ translate('This Week')
                    }}</span>
                <div id="userNavigationButtons">
                    <button type="button" class="btn btn-sm btn-soft" id="viewPrevPeriod" data-bs-toggle="tooltip"
                        title="{{ translate('Previous') }}">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-soft" id="viewNextPeriod" data-bs-toggle="tooltip"
                        title="{{ translate('Next') }}">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Recently Registered Users --}}
    <div class="col-12 col-lg-4">
        <x-card title="{{ translate('Recently Registered') }}"
            subtitle="{{ translate('Total :count Registered', ['count' => $users->count() + $sellers->count()]) }}"
            headerClass="border-0 pb-0" titleTag="h6" fullHeight>

            {{-- Tab Navigation --}}
            <ul class="nav nav-tabs ezydev-tabs mb-3" id="recentlyRegisteredTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button type="button" role="tab" class="nav-link active" id="users-tab" data-bs-toggle="tab"
                        data-bs-target="#users-content">
                        {{ translate('Users') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button" role="tab" class="nav-link" id="sellers-tab" data-bs-toggle="tab"
                        data-bs-target="#sellers-content">
                        {{ translate('Sellers') }}
                    </button>
                </li>
            </ul>

            {{-- Tab Content --}}
            <div class="tab-content" id="recentlyRegisteredTabsContent">
                {{-- Users Tab --}}
                <div class="tab-pane fade show active" id="users-content" role="tabpanel">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($users as $user)
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <x-user :user="$user" :showEmail="false" avatarSize="sm">
                                <x-slot:afterName>
                                    <small class="text-muted d-block">
                                        @if($user->isBuyer())
                                        <span class="me-1" data-bs-toggle="tooltip"
                                            data-bs-title="{{ translate('This user has :purchase purchased product(s)', ['purchase' => $user->transactions_count ?? 0]) }}">
                                            {{ translate('Buyer') }}
                                        </span>
                                        <span class="dot dot-sm dot-light"></span>
                                        @endif
                                        {{ $user->created_at->diffForHumans() }}
                                    </small>
                                </x-slot:afterName>
                            </x-user>
                            <a href="{{ route('admin.roles.users.edit', $user->id) }}" class="btn btn-sm btn-light"
                                target="_blank">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                        @empty
                        <x-empty message="{{ translate('No users yet') }}" icon="bi-inbox" />
                        @endforelse
                    </div>
                </div>

                {{-- Sellers Tab --}}
                <div class="tab-pane fade" id="sellers-content" role="tabpanel">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($sellers as $seller)
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <x-user :user="$seller" :showEmail="false" avatarSize="sm">
                                <x-slot:afterName>
                                    <small class="text-muted d-block">
                                        @if($seller->products_count > 0)
                                        <span class="me-1">
                                            @php $totalProduct = $seller->products_count ?? 0; @endphp
                                            {{ translate(':product product(s)', ['product' =>
                                            numberFormat($totalProduct)]) }}
                                        </span>
                                        <span class="dot dot-sm dot-light"></span>
                                        @endif
                                        {{ $seller->created_at->diffForHumans() }}
                                    </small>
                                </x-slot:afterName>
                            </x-user>
                            <a href="{{ route('admin.roles.users.edit', $seller->id) }}" class="btn btn-sm btn-light"
                                target="_blank">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                        @empty
                        <x-empty message="{{ translate('No sellers yet') }}" icon="bi-inbox" />
                        @endforelse
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</div>

{{-- Sales Section --}}
<div class="row g-4 mb-4">
    {{-- Sales Analytics Chart --}}
    <div class="col-12 col-xl-8">
        <x-card title="{{ translate('Sales Analytics') }}" subtitle="{{ translate('Sales Overview') }}"
            headerClass="border-0 pb-0" titleTag="h6" fullHeight>
            <x-slot:actions>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="toggleSalesCompare"
                        data-bs-toggle="tooltip" title="{{ translate('Compare Data') }}">
                        <i class="bi bi-bar-chart-line"></i>
                    </button>
                    <x-dropdown buttonClass="btn btn-sm btn-outline-primary">
                        <x-dropdown.item href="{{ route('admin.records.sales.index') }}" icon="bi-list-ul"
                            iconClass="text-primary" target="_blank">
                            {{ translate('View All Sales') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="divider" />
                        <x-dropdown.item type="button" icon="bi-arrow-clockwise" color="success"
                            id="refreshSalesAnalyticsCache">
                            {{ translate('Refresh Data') }}
                        </x-dropdown.item>
                    </x-dropdown>
                </div>
            </x-slot:actions>

            {{-- Tab Navigation --}}
            <div class="d-flex gap-2 mb-4 flex-wrap" role="group" id="singleSalesPeriodTabs">
                <button type="button" id="sales-btn-1"
                    class="btn btn-sm btn-primary sales-analytics-tab active rounded-pill fw-normal" data-type="week">
                    {{ translate('Weekly') }}
                </button>
                <button type="button" id="sales-btn-2"
                    class="btn btn-sm btn-outline-primary sales-analytics-tab rounded-pill fw-normal" data-type="month">
                    {{ translate('Monthly') }}
                </button>
                <button type="button" id="sales-btn-3"
                    class="btn btn-sm btn-outline-primary sales-analytics-tab rounded-pill fw-normal" data-type="year">
                    {{ translate('Yearly') }}
                </button>
            </div>

            {{-- Comparison Mode Tabs --}}
            <div class="d-none gap-2 mb-4 flex-wrap" role="group" id="salesCompareTabs">
                <button type="button" id="sales-compare-btn-1"
                    class="btn btn-sm btn-primary sales-compare-tab active rounded-pill fw-normal" data-type="week">
                    {{ translate('Weekly') }}
                </button>
                <button type="button" id="sales-compare-btn-2"
                    class="btn btn-sm btn-outline-primary sales-compare-tab rounded-pill fw-normal" data-type="month">
                    {{ translate('Monthly') }}
                </button>
                <button type="button" id="sales-compare-btn-3"
                    class="btn btn-sm btn-outline-primary sales-compare-tab rounded-pill fw-normal" data-type="year">
                    {{ translate('Yearly') }}
                </button>
            </div>

            {{-- Chart Container --}}
            <div class="chart-wrapper">
                <div id="salesAnalyticsLoader" class="position-absolute top-50 start-50 translate-middle d-none">
                    <x-loader :centered="true" />
                </div>
                <canvas id="salesAnalyticsChart" class="chart-bar"></canvas>
            </div>

            {{-- Period Display --}}
            <div class="d-flex align-items-center justify-content-center gap-2 mt-3">
                <span class="badge bg-light text-dark fs-6 px-3 py-2" id="salesAnalyticsPeriod">{{ translate('This
                    Week') }}</span>
                <div id="salesNavigationButtons">
                    <button type="button" class="btn btn-sm btn-soft" id="viewPrevSalesPeriod" data-bs-toggle="tooltip"
                        title="{{ translate('Previous') }}">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-soft" id="viewNextSalesPeriod" data-bs-toggle="tooltip"
                        title="{{ translate('Next') }}">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </x-card>
    </div>
    {{-- Top Products --}}
    <div class="col-12 col-xl-4">
        <x-card title="{{ translate('Top Products') }}" subtitle="{{ translate('Performance Overview') }}"
            headerClass="border-0 pb-0" titleTag="h6" fullHeight>

            {{-- Tab Navigation --}}
            <ul class="nav nav-tabs ezydev-tabs mb-3" id="topProductsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button type="button" role="tab" class="nav-link active" id="top-selling-tab" data-bs-toggle="tab"
                        data-bs-target="#top-selling-content">
                        {{ translate('Top Selling') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button" role="tab" class="nav-link" id="top-rated-tab" data-bs-toggle="tab"
                        data-bs-target="#top-rated-content">
                        {{ translate('Top Rated') }}
                    </button>
                </li>
            </ul>

            {{-- Tab Content --}}
            <div class="tab-content" id="topProductsTabsContent">
                {{-- Top Selling Tab --}}
                <div class="tab-pane fade show active" id="top-selling-content" role="tabpanel">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($topSellingProducts as $topSellingProduct)
                        @php $product = $topSellingProduct->product; @endphp
                        <x-product :product="$product" class="d-flex align-items-center gap-3" :showCategory="false">
                            <x-slot:afterName>
                                <small class="text-muted d-block">
                                    {{ translate(':count sales', ['count' =>
                                    numberFormat($topSellingProduct->total_sales)]) }}
                                </small>
                            </x-slot:afterName>
                        </x-product>
                        @empty
                        <x-empty message="{{ translate('No sales yet') }}" icon="bi-inbox" />
                        @endforelse
                    </div>
                </div>

                {{-- Top Rated Tab --}}
                <div class="tab-pane fade" id="top-rated-content" role="tabpanel">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($topRatedProducts as $product)
                        <x-product :product="$product" class="d-flex align-items-center gap-3" :showCategory="false">
                            <x-slot:afterName>
                                <small class="text-orange d-block">
                                    @for($i = 1; $i <= 5; $i++) @if($i <=floor($product->reviews_avg_stars))
                                        <i class="bi bi-star-fill"></i>
                                        @elseif($i - 0.5 <= $product->reviews_avg_stars)
                                            <i class="bi bi-star-half"></i>
                                            @else
                                            <i class="bi bi-star"></i>
                                            @endif
                                            @endfor
                                            <span class="text-muted">({{ numberFormat($product->reviews_count)
                                                }})</span>
                                </small>
                            </x-slot:afterName>
                        </x-product>
                        @empty
                        <x-empty message="{{ translate('No reviews yet') }}" icon="bi-inbox" />
                        @endforelse
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Revenue Sources --}}
    <div class="col-12 col-lg-4">
        <x-card id="revenueSourceCard" title="{{ translate('Revenue Sources') }}"
            subtitle="{{ translate('Platform Revenue . This Month') }}" headerClass="border-0 pb-3" titleTag="h6"
            fullHeight>
            <x-slot:actions>
                <x-dropdown buttonClass="btn-icon text-muted" id="revenueSourceDropdown">
                    <x-dropdown.item type="button" icon="bi-calendar-week" iconClass="text-primary"
                        class="revenue-source-option dashboard-period-filter" data-period="last_7_days">
                        {{ translate('Last 7 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-range" iconClass="text-purple"
                        class="revenue-source-option dashboard-period-filter" data-period="last_28_days">
                        {{ translate('Last 28 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-info"
                        class="revenue-source-option dashboard-period-filter active" data-period="this_month">
                        {{ translate('This Month') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-success"
                        class="revenue-source-option dashboard-period-filter" data-period="this_year">
                        {{ translate('This Year') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-infinity" iconClass="text-orange"
                        class="revenue-source-option dashboard-period-filter" data-period="lifetime">
                        {{ translate('Lifetime') }}
                    </x-dropdown.item>
                </x-dropdown>
            </x-slot:actions>
            <div id="revenueSourceLoader" class="text-center py-4">
                <x-loader :centered="true" />
            </div>

            <div id="revenueSourceContent" class="d-none">
                {{-- Donut Chart --}}
                <div class="position-relative mb-4" style="height: 320px;">
                    <canvas id="revenueSourceChart"></canvas>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Expense Fields --}}
    <div class="col-12 col-lg-4">
        <x-card id="expensesTypeCard" title="{{ translate('Expense Fields') }}"
            subtitle="{{ translate('Seller Earnings . This Month') }}" headerClass="border-0 pb-3" titleTag="h6"
            fullHeight>
            <x-slot:actions>
                <x-dropdown buttonClass="btn-icon text-muted" id="expensesTypeDropdown">
                    <x-dropdown.item type="button" icon="bi-calendar-week" iconClass="text-primary"
                        class="expenses-type-option dashboard-period-filter" data-period="last_7_days">
                        {{ translate('Last 7 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-range" iconClass="text-purple"
                        class="expenses-type-option dashboard-period-filter" data-period="last_28_days">
                        {{ translate('Last 28 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-info"
                        class="expenses-type-option dashboard-period-filter active" data-period="this_month">
                        {{ translate('This Month') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-success"
                        class="expenses-type-option dashboard-period-filter" data-period="this_year">
                        {{ translate('This Year') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-infinity" iconClass="text-orange"
                        class="expenses-type-option dashboard-period-filter" data-period="lifetime">
                        {{ translate('Lifetime') }}
                    </x-dropdown.item>
                </x-dropdown>
            </x-slot:actions>
            <div id="expensesTypeLoader" class="text-center py-4">
                <x-loader :centered="true" />
            </div>

            <div id="expensesTypeContent" class="d-none">
                {{-- Donut Chart --}}
                <div class="position-relative mb-4" style="height: 300px;">
                    <canvas id="expensesTypeChart"></canvas>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Revenue & Expense --}}
    <div class="col-12 col-lg-4">
        <x-card id="revenueExpenseCard" title="{{ translate('Revenue & Expense') }}"
            subtitle="{{ translate('Comparison . This Month') }}" headerClass="border-0 pb-0" titleTag="h6" fullHeight>
            <x-slot:actions>
                <x-dropdown buttonClass="btn-icon text-muted" id="revenuePeriodDropdown">
                    <x-dropdown.item type="button" icon="bi-calendar-week" iconClass="text-primary"
                        class="revenue-period-option dashboard-period-filter" data-period="last_7_days">
                        {{ translate('Last 7 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-range" iconClass="text-purple"
                        class="revenue-period-option dashboard-period-filter" data-period="last_28_days">
                        {{ translate('Last 28 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-month" iconClass="text-info"
                        class="revenue-period-option dashboard-period-filter active" data-period="this_month">
                        {{ translate('This Month') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-success"
                        class="revenue-period-option dashboard-period-filter" data-period="this_year">
                        {{ translate('This Year') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-infinity" iconClass="text-orange"
                        class="revenue-period-option dashboard-period-filter" data-period="lifetime">
                        {{ translate('Lifetime') }}
                    </x-dropdown.item>
                </x-dropdown>
            </x-slot:actions>

            <div id="revenueExpenseLoader" class="text-center py-4">
                <x-loader :centered="true" />
            </div>

            <div id="revenueExpensesContent" class="d-none">
                {{-- Summary Stats --}}
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="flex-grow-1">
                                <h5 class="mb-0 fw-medium" id="revenueAmount">
                                    {{ getCompactAmount($statistics["platform_total_revenues"]) }}
                                </h5>
                                <div class="text-muted small">
                                    <span class="dot bg-purple opacity-75 me-1"></span>
                                    {{ translate('Revenue') }}
                                </div>
                            </div>
                            <span class="badge" id="revenueBadge">
                                <i class="bi bi-arrow-up"></i> <span id="revenueChange">0%</span>
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="flex-grow-1">
                                <h5 class="mb-0 fw-medium" id="expenseAmount">
                                    {{ getCompactAmount($statistics["platform_total_expenses"]) }}
                                </h5>
                                <div class="text-muted small">
                                    <span class="dot bg-orange opacity-75 me-1"></span>
                                    {{ translate('Expense') }}
                                </div>
                            </div>
                            <span class="badge" id="expenseBadge">
                                <i class="bi bi-arrow-up"></i> <span id="expenseChange">0%</span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Revenue & Expense Chart --}}
                <div class="chart-wrapper" style="height: 320px;">
                    <canvas id="revenueExpenseChart" class="chart-bar"></canvas>
                </div>
            </div>
        </x-card>
    </div>
</div>

{{-- Premium Analytics --}}
@if (isPremiumAvailable())
<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        <x-card title="{{ translate('Premium Analytics') }}" subtitle="{{ translate('Yearly Overview') }}"
            headerClass="border-0 pb-0" titleTag="h6">
            <x-slot:actions>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="toggleCompare"
                        data-bs-toggle="tooltip" title="{{ translate('Compare Data') }}">
                        <i class="bi bi-bar-chart-line"></i>
                    </button>
                    <x-dropdown buttonClass="btn btn-sm btn-outline-primary">
                        <x-dropdown.item href="{{ route('admin.records.premium-earnings.index') }}" icon="bi-list-ul"
                            iconClass="text-primary" target="_blank">
                            {{ translate('View Records') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="divider" />
                        <x-dropdown.item type="button" icon="bi-arrow-clockwise" color="success"
                            id="refreshAnalyticsCache">
                            {{ translate('Refresh Data') }}
                        </x-dropdown.item>
                    </x-dropdown>
                </div>
            </x-slot:actions>

            {{-- Tab Navigation --}}
            <div class="d-flex gap-2 mb-4 flex-wrap" role="group" id="singleYearTabs">
                <button type="button" id="premium-btn-1"
                    class="btn btn-sm btn-primary fw-normal analytics-tab active rounded-pill" data-type="sales">
                    {{ translate('Sales') }}
                </button>
                <button type="button" id="premium-btn-2"
                    class="btn btn-sm btn-outline-primary fw-normal analytics-tab rounded-pill" data-type="revenue">
                    {{ translate('Revenue') }}
                </button>
                <button type="button" id="premium-btn-3"
                    class="btn btn-sm btn-outline-primary fw-normal analytics-tab rounded-pill" data-type="members">
                    {{ translate('Members') }}
                </button>
            </div>

            {{-- Comparison Mode Tabs --}}
            <div class="d-none gap-2 mb-4 flex-wrap" role="group" id="compareTabs">
                <button type="button" id="premium-compare-btn-1"
                    class="btn btn-sm btn-primary fw-normal compare-tab active rounded-pill" data-type="sales">
                    {{ translate('Sales') }}
                </button>
                <button type="button" id="premium-compare-btn-2"
                    class="btn btn-sm btn-outline-primary fw-normal compare-tab rounded-pill" data-type="revenue">
                    {{ translate('Revenue') }}
                </button>
                <button type="button" id="premium-compare-btn-3"
                    class="btn btn-sm btn-outline-primary fw-normal compare-tab rounded-pill" data-type="members">
                    {{ translate('Members') }}
                </button>
            </div>

            {{-- Chart Container --}}
            <div class="chart-wrapper">
                <div id="premiumAnalyticsLoader" class="position-absolute top-50 start-50 translate-middle d-none">
                    <x-loader :centered="true" />
                </div>
                <canvas id="premiumAnalyticsChart" class="chart-bar"></canvas>
            </div>

            {{-- Year Display --}}
            <div class="d-flex align-items-center justify-content-center gap-2 mt-3">
                <span class="badge bg-light text-dark fs-6 px-3 py-2" id="premiumAnalyticsYear">
                    {{ date('Y') }}
                </span>
                <div id="yearNavigationButtons">
                    <button type="button" class="btn btn-sm btn-soft" id="viewPrevYear" data-bs-toggle="tooltip"
                        title="{{ translate('Previous year') }}">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-soft" id="viewNextYear" data-bs-toggle="tooltip"
                        title="{{ translate('Next year') }}">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Recent Premium Members --}}
    <div class="col-12 col-lg-4">
        <x-card title="{{ translate('Recent Premium Members') }}"
            subtitle="{{ translate('Total :count Members', ['count' => numberFormat($premiumMembersCount)]) }}"
            headerClass="border-0 pb-0" titleTag="h6" fullHeight>
            <x-slot:actions>
                <x-dropdown>
                    <x-dropdown.item href="{{ route('admin.premium.members.index') }}" icon="bi-arrow-right-circle"
                        iconClass="text-primary" target="_blank">
                        {{ translate('View All') }}
                    </x-dropdown.item>
                </x-dropdown>
            </x-slot:actions>

            <div class="d-flex flex-column gap-3">
                @forelse ($premiumMembers as $premium)
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <x-user :user="$premium->user" :showEmail="false" avatarSize="sm">
                        <x-slot:afterName>
                            <small class="text-muted d-block">
                                <a href="{{ route('admin.premium.plans.index', ['plan' => $premium->plan->id]) }}"
                                    class="text-dark hover-primary me-1" data-bs-toggle="tooltip"
                                    data-bs-title="{{ translate('Premium Plan') }}" target="_blank">{{
                                    $premium->plan->name }}</a>
                                <span class="dot dot-sm dot-light"></span>
                                {{ $premium->created_at->diffForHumans() }}
                            </small>
                        </x-slot:afterName>
                    </x-user>
                    <a href="{{ route('admin.premium.members.index', ['member' => $premium->user_id]) }}"
                        class="btn btn-sm btn-light" target="_blank">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
                @empty
                <x-empty message="{{ translate('No premium members yet') }}" icon="bi-gem" />
                @endforelse
            </div>
        </x-card>
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    {{-- Support Ticket & Refund Stats --}}
    <div class="col-12 col-lg-4">
        <div class="d-flex flex-column gap-4 h-100">
            {{-- Support Ticket --}}
            <x-card id="supportTicketCard" class="flex-1" title="{{ translate('Support Ticket') }}"
                subtitle="{{ translate('Last 7 Days') }}" headerClass="border-0 pb-0" titleTag="h6" fullHeight>
                <x-slot:actions>
                    <x-dropdown buttonClass="btn-icon text-muted" id="supportPeriodDropdown">
                        <x-dropdown.item type="button" icon="bi-calendar-week" iconClass="text-primary"
                            class="support-period-option dashboard-period-filter" data-period="last_7_days">
                            {{ translate('Last 7 Days') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-calendar-range" iconClass="text-purple"
                            class="support-period-option dashboard-period-filter" data-period="last_28_days">
                            {{ translate('Last 28 Days') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-calendar-month" iconClass="text-info"
                            class="support-period-option dashboard-period-filter active" data-period="this_month">
                            {{ translate('This Month') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-success"
                            class="support-period-option dashboard-period-filter" data-period="this_year">
                            {{ translate('This Year') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-infinity" iconClass="text-orange"
                            class="support-period-option dashboard-period-filter" data-period="lifetime">
                            {{ translate('Lifetime') }}
                        </x-dropdown.item>
                    </x-dropdown>
                </x-slot:actions>

                <div id="supportTicketLoader" class="text-center py-2">
                    <x-loader :centered="true" />
                </div>

                <div id="supportTicketContent" class="d-none">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 d-flex align-items-center">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="card-icon bg-text-primary">
                                            <i class="bi bi-ticket-perforated"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-medium text-muted">{{ translate('New Tickets') }}</h6>
                                        <p class="text-muted mb-0" id="newTickets">0</p>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="card-icon bg-text-green">
                                            <i class="bi bi-check-circle"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-medium text-muted">{{ translate('Open Tickets') }}</h6>
                                        <p class="text-muted mb-0" id="openTickets">0</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div style="height: 160px;">
                                <canvas id="supportGaugeChart" class="chart-doughnut" data-cutout="70%"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Refund Stats --}}
            <x-card id="refundStatsCard" title="{{ translate('Refund Overview') }}"
                subtitle="{{ translate('Last 7 Days') }}" headerClass="border-0 pb-0" titleTag="h6" fullHeight
                class="flex-1">
                <x-slot:actions>
                    <x-dropdown buttonClass="btn-icon text-muted" id="refundPeriodDropdown">
                        <x-dropdown.item type="button" icon="bi-calendar-week" iconClass="text-primary"
                            class="refund-period-option dashboard-period-filter" data-period="last_7_days">
                            {{ translate('Last 7 Days') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-calendar-range" iconClass="text-purple"
                            class="refund-period-option dashboard-period-filter" data-period="last_28_days">
                            {{ translate('Last 28 Days') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-calendar-month" iconClass="text-info"
                            class="refund-period-option dashboard-period-filter active" data-period="this_month">
                            {{ translate('This Month') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-success"
                            class="refund-period-option dashboard-period-filter" data-period="this_year">
                            {{ translate('This Year') }}
                        </x-dropdown.item>
                        <x-dropdown.item type="button" icon="bi-infinity" iconClass="text-orange"
                            class="refund-period-option dashboard-period-filter" data-period="lifetime">
                            {{ translate('Lifetime') }}
                        </x-dropdown.item>
                    </x-dropdown>
                </x-slot:actions>

                <div id="refundStatsLoader" class="text-center py-4">
                    <x-loader :centered="true" />
                </div>

                <div id="refundStatsContent" class="d-none">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 d-flex align-items-center">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="card-icon bg-text-red">
                                            <i class="bi bi-x-circle"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-medium text-muted">{{ translate('Declined') }}</h6>
                                        <p class="text-muted mb-0" id="refunndDeclined">0</p>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="card-icon bg-text-green">
                                            <i class="bi bi-check-circle"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-medium text-muted">{{ translate('Accepted') }}</h6>
                                        <p class="text-muted mb-0" id="refunndAccepted">0</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div style="height: 160px;">
                                <canvas id="refundGaugeChart" class="chart-doughnut" data-cutout="70%"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    {{-- Traffic Source Stats --}}
    <div class="col-12 col-lg-4">
        <x-card id="trafficSourceCard" title="{{ translate('Traffic Sources') }}"
            subtitle="{{ translate(':count Visitors . This Month', ['count' => $trafficSources['total_visitors']]) }}"
            headerClass="border-0 pb-0" titleTag="h6" fullHeight>
            <x-slot:actions>
                <x-dropdown buttonClass="btn-icon text-muted" id="trafficSourceDropdown">
                    <x-dropdown.item type="button" icon="bi-calendar-week" iconClass="text-primary"
                        class="traffic-period-option dashboard-period-filter" data-period="last_7_days">
                        {{ translate('Last 7 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-range" iconClass="text-purple"
                        class="traffic-period-option dashboard-period-filter" data-period="last_28_days">
                        {{ translate('Last 28 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-month" iconClass="text-info"
                        class="traffic-period-option dashboard-period-filter active" data-period="this_month">
                        {{ translate('This Month') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-success"
                        class="traffic-period-option dashboard-period-filter" data-period="this_year">
                        {{ translate('This Year') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-infinity" iconClass="text-orange"
                        class="traffic-period-option dashboard-period-filter" data-period="lifetime">
                        {{ translate('Lifetime') }}
                    </x-dropdown.item>
                </x-dropdown>
            </x-slot:actions>

            {{-- Loader --}}
            <div id="trafficSourceLoader" class="text-center py-4 d-none">
                <x-loader :centered="true" />
            </div>

            {{-- Content --}}
            <div class="d-flex flex-column gap-3" id="trafficSourceContent">
                @foreach($trafficSources['sources'] as $source)
                <div class="d-flex align-items-center gap-3" data-source="{{ $source['name'] }}">
                    <div class="flex-shrink-0">
                        <div class="card-icon card-icon-md rounded-circle bg-light">
                            <i class="bi {{ $source['icon'] }} text-muted source-icon"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-medium source-name">{{ translate($source['name']) }}</div>
                        <small class="text-muted source-description">{{ translate($source['description']) }}</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-medium text-muted source-count">{{ $source['formatted_count'] }}</div>
                        <small class="source-change {{ $source['is_positive'] ? 'text-success' : 'text-danger' }}">
                            <i class="bi bi-arrow-{{ $source['is_positive'] ? 'up' : 'down' }} source-arrow"></i> <span
                                class="source-percentage">{{ $source['is_positive'] ? '+' : '' }}{{
                                $source['percentage_change'] }}%</span>
                        </small>
                    </div>
                </div>
                @endforeach
            </div>
        </x-card>
    </div>
    {{-- Admin Login Activity --}}
    <div class="col-12 col-lg-4">
        <x-card title="{{ translate('Admin Login Activity') }}" subtitle="{{ translate('Recent Access Logs') }}"
            headerClass="border-0 pb-0" titleTag="h6" fullHeight>

            {{-- Tab Navigation --}}
            <ul class="nav nav-tabs ezydev-tabs mb-3" id="adminLoginTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="logged-in-tab" data-bs-toggle="tab"
                        data-bs-target="#logged-in-content" type="button" role="tab">
                        {{ translate('Logged In') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="failed-attempts-tab" data-bs-toggle="tab"
                        data-bs-target="#failed-attempts-content" type="button" role="tab">
                        {{ translate('Failed Attempts') }}
                    </button>
                </li>
            </ul>

            {{-- Tab Content --}}
            <div class="tab-content" id="adminLoginTabsContent">
                {{-- Logged In Tab --}}
                <div class="tab-pane fade show active" id="logged-in-content" role="tabpanel">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($adminLoginActivities['successful_logins'] as $login)
                        <div class="d-flex align-items-start gap-3">
                            <a href="{{ route('admin.roles.staff.edit', $login->admin_id) }}"
                                class="image-fluid image-md rounded flex-shrink-0" target="_blank">
                                <img src="{{ $login->admin_avatar }}" alt="{{ $login->admin_name }}">
                            </a>
                            <div class="flex-grow-1 min-w-0">
                                <a href="{{ route('admin.roles.staff.edit', $login->admin_id) }}"
                                    class="text-dark hover-primary d-block fw-medium" target="_blank">
                                    {{ $login->admin_name }}
                                    <span class="badge bg-text-primary p-1 ms-1" style="font-size: 0.68rem;">{{
                                        $login->role_name }}</span>
                                </a>
                                <small class="text-muted d-block">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $login->location ?: 'Unknown' }}
                                    <span class="mx-1">•</span>
                                    <i class="bi bi-hdd-network me-1"></i>{{ hideInDemo($login->ip_address) }}
                                </small>
                                <small class="text-muted d-block">
                                    <i class="bi bi-clock me-1"></i>{{ \Date::parse($login->created_at)->diffForHumans()
                                    }}
                                    @if($login->browser)
                                    <span class="mx-1">•</span>
                                    <i class="bi bi-globe2 me-1"></i>{{ $login->browser }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        @empty
                        <x-empty message="{{ translate('No login activity yet') }}" icon="bi-shield-check" />
                        @endforelse
                    </div>
                </div>

                {{-- Failed Attempts Tab --}}
                <div class="tab-pane fade" id="failed-attempts-content" role="tabpanel">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($adminLoginActivities['failed_attempts'] as $attempt)
                        <div class="d-flex align-items-start gap-3">
                            <div class="card-icon rounded-circle bg-text-red flex-shrink-0">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-medium">
                                    {{ $attempt->identifier ?: 'Unknown' }}
                                </div>
                                <small class="text-muted d-block">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $attempt->location ?: translate('Unknown Location') }}
                                    <span class="mx-1">•</span>
                                    <i class="bi bi-hdd-network me-1"></i>{{ hideInDemo($attempt->ip_address) }}
                                </small>
                                <small class="text-muted d-block">
                                    <i class="bi bi-clock me-1"></i>{{ \Date::parse($attempt->attempted_at)->format('d M
                                    Y, H:i') }}
                                    @if($attempt->browser)
                                    <span class="mx-1">•</span>
                                    <i class="bi bi-globe2 me-1"></i>{{ $attempt->browser }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        @empty
                        <x-empty message="{{ translate('No failed login attempts') }}"
                            description="{{ translate('Your admin panel is secure') }}" icon="bi-shield-check" />
                        @endforelse
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</div>

<div class="row g-4">
    {{-- Geographic Data --}}
    <div class="col-lg-8">
        <x-card id="geoChartCard" title="{{ translate('Purchasing Countries') }}"
            subtitle="{{ translate('This Month') }} ({{ now()->format('F') }})" headerClass="border-0 pb-0"
            titleTag="h6" fullHeight>
            <x-slot:actions>
                <x-dropdown buttonClass="btn-icon text-muted" id="geoPeriodDropdown">
                    <x-dropdown.item type="button" icon="bi-calendar-week" iconClass="text-primary"
                        class="geo-period-option period-option-active dashboard-period-filter"
                        data-period="last_7_days">
                        {{ translate('Last 7 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-range" iconClass="text-purple"
                        class="geo-period-option period-option-active dashboard-period-filter"
                        data-period="last_28_days">
                        {{ translate('Last 28 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-month" iconClass="text-info"
                        class="geo-period-option period-option-active dashboard-period-filter active"
                        data-period="this_month">
                        {{ translate('This Month') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-success"
                        class="geo-period-option period-option-active dashboard-period-filter" data-period="this_year">
                        {{ translate('This Year') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-infinity" iconClass="text-orange"
                        class="geo-period-option period-option-active dashboard-period-filter" data-period="lifetime">
                        {{ translate('Lifetime') }}
                    </x-dropdown.item>
                </x-dropdown>
            </x-slot:actions>
            <div class="chart-wrapper" style="height: 450px; max-height: 450px;">
                <div id="geoChartLoader" class="position-absolute top-50 start-50 translate-middle d-none">
                    <x-loader :centered="true" />
                </div>
                <div id="geoChartContainer" style="width: 100%; height: 100%;"></div>
            </div>
        </x-card>
    </div>

    {{-- Top Countries --}}
    <div class="col-lg-4">
        <x-card id="countrySalesCard" title="{{ translate('Top Purchasing Countries') }}"
            subtitle="{{ translate('This Month') }}" headerClass="border-0 pb-0" titleTag="h6" fullHeight>
            <x-slot:actions>
                <x-dropdown buttonClass="btn-icon text-muted" id="countryPeriodDropdown">
                    <x-dropdown.item type="button" icon="bi-calendar-week" iconClass="text-primary"
                        class="country-period-option dashboard-period-filter" data-period="last_7_days">
                        {{ translate('Last 7 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-range" iconClass="text-purple"
                        class="country-period-option dashboard-period-filter" data-period="last_28_days">
                        {{ translate('Last 28 Days') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar-month" iconClass="text-info"
                        class="country-period-option dashboard-period-filter active" data-period="this_month">
                        {{ translate('This Month') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-calendar3" iconClass="text-success"
                        class="country-period-option dashboard-period-filter" data-period="this_year">
                        {{ translate('This Year') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="button" icon="bi-infinity" iconClass="text-orange"
                        class="country-period-option dashboard-period-filter" data-period="lifetime">
                        {{ translate('Lifetime') }}
                    </x-dropdown.item>
                </x-dropdown>
            </x-slot:actions>

            <div id="countryAnalyticsLoader" class="text-center py-4">
                <x-loader :centered="true" />
            </div>

            <div id="countryAnalyticsContent" class="d-none">
                <div class="d-flex flex-column gap-2" id="countryAnalyticsItems">
                    {{-- Country items will be populated here by JavaScript --}}
                    <template id="countryItemTemplate">
                        <div class="d-flex align-items-center gap-3 country-item" data-country="">
                            <div class="dashboard-country-wrapper rounded-circle flex-shrink-0">
                                <img src="" alt="" class="dashboard-country-flag-img country-flag">
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-medium mb-0 country-amount"></div>
                                <small class="text-muted country-name"></small>
                            </div>
                            <span class="badge country-badge">
                                <i class="country-arrow"></i> <span class="country-percentage"></span>
                            </span>
                        </div>
                    </template>
                </div>
            </div>
        </x-card>
    </div>
</div>

{{-- Create Note Modal --}}
<x-modal id="createNoteModal" title="{{ translate('Create Note') }}" icon="bi-plus-circle" :size="'md'">
    <form id="createNoteForm" action="{{ route('admin.dashboard.notes.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">{{ translate('Title') }} <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" maxlength="100"
                placeholder="{{ translate('Enter note title') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ translate('Description') }} <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control" rows="4" maxlength="250"
                placeholder="{{ translate('Enter note description') }}" required></textarea>
            <small class="text-muted">{{ translate('Maximum 250 characters') }}</small>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ translate('Date & Time') }}</label>
            <input type="datetime-local" name="date_time" class="form-control" min="{{ now()->format('Y-m-d\TH:i') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">{{ translate('Priority') }}</label>
            <select name="priority" class="form-select selectpicker"
                data-placeholder="{{ translate('Select priority level') }}">
                <option value="low">{{ translate('Low') }}</option>
                <option value="medium" selected>{{ translate('Medium') }}</option>
                <option value="high">{{ translate('High') }}</option>
            </select>
        </div>

        <x-slot name="footer">
            <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal"><i
                    class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}</button>
            <button type="submit" id="createNoteBtn" form="createNoteForm" class="btn btn-primary flex-fill">
                <i class="bi bi-plus-circle me-2"></i>{{ translate('Create') }}
            </button>
        </x-slot>
    </form>
</x-modal>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/libs/swiper/swiper-bundle.min.css') }}">
@endpush

@push('scripts_libs')
<script src="{{ asset('vendor/libs/swiper/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('vendor/libs/chartjs/chart.min.js') }}"></script>
<script src="{{ asset('vendor/libs/chartjs/chartjs-datalabels.min.js') }}"></script>
<script src="{{ asset('vendor/libs/geochart/geochart.min.js') }}"></script>
<script src="{{ asset_with_version('vendor/admin/js/dashboard.js') }}"></script>
@endpush

@push('scripts')
<script>
    (function ($) {
        "use strict";

        // Override CommonTranslations with server-side translations
        window.CommonTranslations = {
            sales: '{{ translate("Sales") }}',
            revenue: '{{ translate("Revenue") }}',
            earnings: '{{ translate("Earnings") }}',
            members: '{{ translate("Members") }}',
            visitors: '{{ translate("Visitors") }}',
            today: '{{ translate("Today") }}',
            yesterday: '{{ translate("Yesterday") }}',
            last7Days: '{{ translate("Last 7 Days") }}',
            last28Days: '{{ translate("Last 28 Days") }}',
            thisMonth: '{{ translate("This Month") }}',
            thisYear: '{{ translate("This Year") }}',
            lifetime: '{{ translate("Lifetime") }}',
            weekly: '{{ translate("Weekly") }}',
            monthly: '{{ translate("Monthly") }}',
            yearly: '{{ translate("Yearly") }}',
            loadFailed: '{{ translate("Failed to load data") }}',
            loading: '{{ translate("Loading...") }}',
            noData: '{{ translate("No data available!") }}',
            hideComparison: '{{ translate("Hide Comparison") }}'
        };

        // Initialize Dashboard Print Manager
        const dashboardPrintManager = new DashboardPrintManager({
            printButtonId: 'printDashboard',
            headerRowId: 'printHeaderRow',
            logoUrl: '{{ themeSettings()->general->logo_dark }}',
            siteName: '{{ @$settings->general->site_name }}',
            assetBaseUrl: '{{ asset("") }}'
        });
        dashboardPrintManager.init();

        // Initialize Congratulations Swiper
        const dashboardBannerSwiper = new Swiper('.dashboard-banner-swiper', {
            slidesPerView: 1,
            spaceBetween: 0,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            loop: true,
            speed: 800,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            pagination: {
                el: '.dashboard-banner-swiper-pagi',
                clickable: true,
            },
        });

        // Set global loader style for JavaScript
        window.loaderStyle = '{{ @$settings->general->loader_style ?? "dots" }}';

        // Initialize Bootstrap tooltips for congratulation buttons
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // Initialize Statistics Manager
        const statisticsManager = new StatisticsManager({
            apiUrl: '{{ route("admin.dashboard.statistics") }}',
            cardId: 'statisticsCard',
            defaultPeriod: 'lifetime',
            currencySymbol: '{{ defaultCurrency()->symbol }}',
            currencyPosition: {{ defaultCurrency()-> position }}
    });

    // Initialize Product Status Manager
    const productStatusManager = new DonutChartManager({
        apiUrl: '{{ route("admin.dashboard.product-status") }}',
        cardId: 'productStatusCard',
        canvasId: 'productStatusChart',
        loaderId: 'productStatusLoader',
        contentId: 'productStatusContent',
        percentageId: 'productStatusPercentage',
        labelId: 'productStatusLabel',
        defaultPeriod: 'this_month',
        enableCache: true,
        cacheTTL: 5 * 60 * 1000, // 5 minutes
        translations: {
            segment1: '{{ translate("Approved") }}',
            segment2: '{{ translate("Pending") }}',
            segment3: '{{ translate("Rejected") }}',
            segment4: '{{ translate("Resubmitted") }}',
            displayLabel: '{{ translate("Approved") }}',
        }
    });

    // Initialize User Role Manager
    const userRoleManager = new DonutChartManager({
        apiUrl: '{{ route("admin.dashboard.user-role") }}',
        cardId: 'userRoleCard',
        canvasId: 'userRoleChart',
        loaderId: 'userRoleLoader',
        contentId: 'userRoleContent',
        percentageId: 'userRolePercentage',
        labelId: 'userRoleLabel',
        defaultPeriod: 'this_month',
        optionClass: 'user-role-option',
        enableCache: true,
        cacheTTL: 5 * 60 * 1000,
        translations: {
            segment1: '{{ translate("User") }}',
            segment2: '{{ translate("Buyer") }}',
            segment3: '{{ translate("Seller") }}',
            displayLabel: '{{ translate("Buyer") }}',
        },
        colors: {
            segment1: '#6366f1',
            segment2: '#06b6d4',
            segment3: '#10b981'
        }
    });

    // Initialize Product Issues Stats
    const productIssuesManager = new DualStatsManager({
        apiUrl: '{{ route("admin.dashboard.product-issues") }}',
        loaderId: 'productIssuesLoader',
        contentId: 'productIssuesContent',
    });

    // Initialize User Verification Stats
    const userVerificationManager = new DualStatsManager({
        apiUrl: '{{ route("admin.dashboard.user-verification") }}',
        loaderId: 'userVerificationLoader',
        contentId: 'userVerificationContent',
    });

    // Initialize User Analytics Manager using AnalyticsManager
    const userAnalyticsUrl = "{{ route('admin.dashboard.user-analytics') }}";
    const userComparisonUrl = "{{ route('admin.dashboard.user-comparison') }}";
    const userAnalytics = new AnalyticsManager({
        canvasId: 'userAnalyticsChart',
        analyticsUrl: userAnalyticsUrl,
        comparisonUrl: userComparisonUrl,
        loaderId: 'userAnalyticsLoader',
        yearDisplayId: 'userAnalyticsPeriod',
        currentYear: {{ date('Y') }},
    defaultType: 'week',
        isPeriodBased: true,  // Enable period-based mode (week/month/year with offset)
            periodOffset: 0,
                dataFormat: 'number',  // Format data as numbers, not currency
                    translations: {
        subtitlePrefix: '{{ translate("Registration") }}',
        }
    });

    // Handle refresh cache button
    $('#refreshUserAnalyticsCache').on('click', function (e) {
        e.preventDefault();
        userAnalytics.refreshData(function () {
            toastr.success('{{ translate("User analytics cache cleared and data refreshed") }}');
        });
    });

    // Initialize Sales Analytics Manager
    const salesAnalyticsUrl = "{{ route('admin.dashboard.sales-analytics') }}";
    const salesComparisonUrl = "{{ route('admin.dashboard.sales-comparison') }}";
    const salesAnalytics = new AnalyticsManager({
        canvasId: 'salesAnalyticsChart',
        analyticsUrl: salesAnalyticsUrl,
        comparisonUrl: salesComparisonUrl,
        loaderId: 'salesAnalyticsLoader',
        yearDisplayId: 'salesAnalyticsPeriod',
        currentYear: {{ date('Y') }},
    defaultType: 'week',
        isPeriodBased: true,  // Enable period-based mode
            periodOffset: 0,
                dataFormat: 'number',  // Format data as numbers
                    translations: {
        subtitlePrefix: '{{ translate("Sales") }}',
        },
    colors: {
        primary: '#10b981',
            success: '#10b981',
                info: '#06b6d4'
    }
    });

    // Handle refresh cache button
    $('#refreshSalesAnalyticsCache').on('click', function (e) {
        e.preventDefault();
        salesAnalytics.refreshData(function () {
            toastr.success('{{ translate("Sales analytics cache cleared and data refreshed") }}');
        });
    });

    @if (isPremiumAvailable())
        // Initialize Analytics Manager with caching enabled
        const premiumAnalyticsUrl = "{{ route('admin.dashboard.premium-analytics') }}";
    const premiumComparisonUrl = "{{ route('admin.dashboard.premium-comparison') }}";
    const premiumAnalytics = new AnalyticsManager({
        canvasId: 'premiumAnalyticsChart',
        analyticsUrl: premiumAnalyticsUrl,
        comparisonUrl: premiumComparisonUrl,
        loaderId: 'premiumAnalyticsLoader',
        yearDisplayId: 'premiumAnalyticsYear',
        currentYear: {{ date('Y') }},
    defaultType: 'sales',
        currencySymbol: '{{ defaultCurrency()->symbol }}',
            translations: {
        subtitlePrefix: '{{ translate("Yearly") }}',
        }
    });

    // Handle refresh cache button
    $('#refreshAnalyticsCache').on('click', function (e) {
        e.preventDefault();
        premiumAnalytics.refreshData(function () {
            toastr.success('{{ translate("Premium analytics cache cleared and data refreshed") }}');
        });
    });
    @endif

    // Initialize Country Analytics Manager
    const countryAnalytics = new CountryAnalyticsManager({
        containerId: 'countryAnalyticsContent',
        loaderId: 'countryAnalyticsLoader',
        cardId: 'countrySalesCard',
        apiUrl: '{{ route("admin.dashboard.country-analytics") }}',
        defaultPeriod: 'this_month',
        currencySymbol: '{{ defaultCurrency()->symbol }}'
    });

    // Initialize Support Tracker Manager
    const supportTicket = new GaugeChartManager({
        apiUrl: '{{ route("admin.dashboard.support-ticket") }}',
        cardId: 'supportTicketCard',
        contentId: 'supportTicketContent',
        loaderId: 'supportTicketLoader',
        canvasId: 'supportGaugeChart',
        newItemId: 'newTickets',
        openItemId: 'openTickets',
        totalItemId: 'totalTickets',
        periodOptionClass: 'support-period-option',
        chartColor: '#0d6efd',
        defaultPeriod: 'this_month',
        translations: {
            labelText: '{{ translate("Completed") }}',
        }
    });

    // Initialize Refund Stats Manager
    const refundStatsManager = new GaugeChartManager({
        apiUrl: '{{ route("admin.dashboard.refund-stats") }}',
        cardId: 'refundStatsCard',
        contentId: 'refundStatsContent',
        loaderId: 'refundStatsLoader',
        canvasId: 'refundGaugeChart',
        newItemId: 'refunndDeclined',
        openItemId: 'refunndAccepted',
        totalItemId: null,
        periodOptionClass: 'refund-period-option',
        defaultPeriod: 'this_month',
        chartColor: '#10b981',
        translations: {
            labelText: '{{ translate("Accepted") }}',
        }
    });

    // Initialize Revenue Source Manager
    const revenueSourceManager = new DonutChartManager({
        apiUrl: '{{ route("admin.dashboard.revenue-source") }}',
        cardId: 'revenueSourceCard',
        canvasId: 'revenueSourceChart',
        loaderId: 'revenueSourceLoader',
        contentId: 'revenueSourceContent',
        percentageId: 'revenueSourcePercentage',
        labelId: 'revenueSourceLabel',
        defaultPeriod: 'this_month',
        currencySymbol: '{{ defaultCurrency()->symbol }}',
        currencyPosition: {{ defaultCurrency()-> position }},
    optionClass: 'revenue-source-option',
        cutoutPercentage: '0',
            enableCache: true,
                cacheTTL: 5 * 60 * 1000, // 5 minutes
                    translations: {
        segment1: '{{ translate("Buyer Fees") }}',
            segment2: '{{ translate("Seller Fees") }}',
                segment3: '{{ translate("Support") }}',
                    segment4: @if (isPremiumAvailable()) '{{ translate("Premium") }}' @else '' @endif,
        subtitlePrefix: '{{ translate("Platform Revenue .") }}',
        },
    colors: {
        segment1: '#ef4444',
            segment2: '#6366f1',
                segment3: '#f59e0b',
                    segment4: '#10b981'
    }
    });

    // Initialize Expenses Type Manager
    const expensesTypeManager = new DonutChartManager({
        apiUrl: '{{ route("admin.dashboard.expenses-type") }}',
        cardId: 'expensesTypeCard',
        canvasId: 'expensesTypeChart',
        loaderId: 'expensesTypeLoader',
        contentId: 'expensesTypeContent',
        percentageId: 'expensesTypePercentage',
        labelId: 'expensesTypeLabel',
        defaultPeriod: 'this_month',
        currencySymbol: '{{ defaultCurrency()->symbol }}',
        currencyPosition: {{ defaultCurrency()-> position }},
    optionClass: 'expenses-type-option',
        cutoutPercentage: '0',
            translations: {
        segment1: '{{ translate("Sales") }}',
            segment2: '{{ translate("Support") }}',
                segment3: '{{ translate("Referral") }}',
                    segment4: @if (isPremiumAvailable()) '{{ translate("Premium") }}' @else '' @endif,
        subtitlePrefix: '{{ translate("Seller Earnings .") }}',
        },
    colors: {
        segment1: '#ef4444',
            segment2: '#f59e0b',
                segment3: '#8b5cf6',
                    segment4: '#10b981'
    }
    });

    // Initialize Revenue & Expense Manager
    const revenueExpenseManager = new combinedBarsManager({
        apiUrl: '{{ route("admin.dashboard.revenue-expense") }}',
        cardId: 'revenueExpenseCard',
        contentId: 'revenueExpensesContent',
        loaderId: 'revenueExpenseLoader',
        defaultPeriod: 'this_month',
        currencySymbol: '{{ defaultCurrency()->symbol }}',
        currencyPosition: {{ defaultCurrency()-> position }}
    });

    // Initialize Traffic Source Manager
    const trafficSourceManager = new TrafficSourceManager({
        apiUrl: '{{ route("admin.dashboard.traffic-sources") }}',
        cardId: 'trafficSourceCard',
        contentId: 'trafficSourceContent',
        loaderId: 'trafficSourceLoader',
        defaultPeriod: 'this_month'
    });

    // Initialize Geographic Chart Manager
    const geoChartManager = new GeoChartManager({
        apiUrl: '{{ route("admin.dashboard.geo-chart") }}',
        containerId: 'geoChartContainer',
        cardId: 'geoChartCard',
        defaultPeriod: 'this_month'
    });

    // Initialize Notes Swiper
    @if ($adminNotes -> isNotEmpty())
        @php
    $notesData = $adminNotes -> map(fn($note) => [
        'id'  => $note -> id,
        'url' => route('admin.dashboard.notes.delete', $note -> id),
    ]) -> values();
    @endphp

    document.addEventListener('DOMContentLoaded', function () {
        const notesData = @json($notesData);
        const $deleteBtn = $('.active-note-delete-btn');

        if (!notesData.length) return;

        // Set initial delete action
        $deleteBtn.attr('data-action', notesData[0].url);

        // Initialize Swiper only for multiple notes
        if (notesData.length > 1) {
            new Swiper('.dashboard-notes-swiper', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: true,
                navigation: {
                    nextEl: '.dashboard-notes-swiper-button-next',
                    prevEl: '.dashboard-notes-swiper-button-prev',
                },
                on: {
                    slideChange() {
                        const noteData = notesData[this.realIndex];
                        if (noteData) $deleteBtn.attr('data-action', noteData.url);
                    },
                },
            });
        }

        // Toggle note description
        $(document).on('click', '.note-toggle-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $btn = $(this);
            const $wrapper = $btn.closest('.note-description-wrapper');
            const $noteText = $wrapper.find('.note-text');
            const $fullText = $wrapper.find('.note-full-text');
            const isExpanded = $fullText.is(':visible');

            $fullText.stop(true, true)[isExpanded ? 'slideUp' : 'slideDown'](200);
            $noteText.toggle(isExpanded);
            $btn.text(isExpanded ? '...{{ translate('More') }}' : '...{{ translate('Less') }}');
        });

        // Collapse on outside click (only touch expanded ones)
        $(document).on('click', function (e) {
            if ($(e.target).closest('.note-description-wrapper').length) {
                return;
            }

            $('.note-description-wrapper').each(function () {
                const $wrapper = $(this);
                const $fullText = $wrapper.find('.note-full-text');

                if (!$fullText.is(':visible')) return;

                const $noteText = $wrapper.find('.note-text');
                const $btn = $wrapper.find('.note-toggle-btn');

                $fullText.stop(true, true).slideUp(200);
                $noteText.show();
                $btn.text('...{{ translate('More') }}');
            });
        });
    });

    @endif

    // Initialize all managers in batch
    const managers = [
        { instance: statisticsManager, config: null },
        { instance: productStatusManager, config: null },
        { instance: productIssuesManager, config: null },
        { instance: userRoleManager, config: null },
        { instance: userVerificationManager, config: null },
        {
            instance: userAnalytics, config: {
                singleYearTabs: 'singlePeriodTabs',
                compareTabs: 'userCompareTabs',
                prevYearBtn: 'viewPrevPeriod',
                nextYearBtn: 'viewNextPeriod',
                toggleCompareBtn: 'toggleUserCompare',
                yearNav: 'userNavigationButtons',
                analyticsTab: '.user-analytics-tab',
                compareTab: '.user-compare-tab'
            }
        },
        {
            instance: salesAnalytics, config: {
                singleYearTabs: 'singleSalesPeriodTabs',
                compareTabs: 'salesCompareTabs',
                prevYearBtn: 'viewPrevSalesPeriod',
                nextYearBtn: 'viewNextSalesPeriod',
                toggleCompareBtn: 'toggleSalesCompare',
                yearNav: 'salesNavigationButtons',
                analyticsTab: '.sales-analytics-tab',
                compareTab: '.sales-compare-tab'
            }
        },
        @if (isPremiumAvailable()) { instance: premiumAnalytics, config: null },
    @endif
    { instance: countryAnalytics, config: null },
    { instance: supportTicket, config: null },
    { instance: refundStatsManager, config: null },
    { instance: revenueSourceManager, config: null },
    { instance: expensesTypeManager, config: null },
    { instance: revenueExpenseManager, config: null },
    { instance: trafficSourceManager, config: null },
    { instance: geoChartManager, config: null }
    ];

    // Initialize each manager with its config
    managers.forEach(({ instance, config }) => {
        config ? instance.init(config) : instance.init();
    });

    // Initialize create note modal form
    initAjaxModalForm({
        formSelector: '#createNoteForm',
        modalSelector: '#createNoteModal',
        submitButtonSelector: '#createNoteBtn',
        successMessage: '{{ translate("Note Created Successfully") }}',
        loadingText: '{{ translate("Creating...") }}',
    });

}) (jQuery);
</script>
@endpush
