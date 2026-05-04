<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-lg-8 border-lg-end">
                <div class="p-4">
                    <div class="d-flex align-items-center gap-4">
                        <div class="position-relative">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}"
                                class="image-fluid image-xl rounded border border-3 border-white shadow-sm">
                            @if($user->isActive())
                                <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-2 border-white rounded-circle"
                                title="{{ translate('Active User') }}"></span>
                            @else
                                <span class="position-absolute bottom-0 end-0 p-2 bg-danger border border-2 border-white rounded-circle"
                                title="{{ translate('Suspended User') }}"></span>
                            @endif
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">{{ $user->full_name }}</h4>
                            <p class="text-muted mb-2">
                                @if ( $user->firstname || $user->lastname )
                                    {{ '@' . $user->username }} <span class="mx-1 text-gray-300">|</span>
                                @endif
                                <i class="bi bi-geo-alt me-1"></i>{{ $user->location ?? translate('N/A') }}
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                @php
                                    $buyer = $user->isBuyer();
                                    $seller = $user->isSeller();
                                    $featured = $user->isFeaturedSeller();
                                    $exclusive = $user->isExclusiveSeller();
                                    $roleTitle = $buyer ? translate('Buyer') : ($seller ? translate('Seller') : translate('User'));
                                @endphp
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">
                                    {{ $roleTitle }}
                                </span>
                                @if ($seller && $featured)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3"
                                    title="{{ translate('The seller has one or more product is featured') }}">
                                        {{ translate('Featured') }}
                                    </span>
                                @elseif ($seller && $exclusive)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3"
                                    title="{{ translate('The seller sells exclusive products') }}">
                                        {{ translate('Exclusive') }}
                                    </span>
                                @endif
                                @if($user->isPremiumMember())
                                    <span class="badge bg-orange-subtle text-orange border border-orange-subtle rounded-pill px-3">
                                        <i class="bi bi-gem me-1"></i>{{ translate('Premium Member') }}
                                    </span>
                                @endif
                                @if($user->isIdVerified())
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">
                                        <i class="bi bi-check-circle me-1"></i>{{ translate('ID Verified') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <div class="row g-3">
                            <div class="col-sm-auto me-4">
                                <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Email Address') }}</p>
                                <p class="mb-0 fw-medium">{{ hideInDemo($user->email) }}</p>
                            </div>
                            <div class="col-sm-auto me-4">
                                <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Joined') }}</p>
                                <p class="mb-0 fw-medium">{{ dateFormat($user->created_at, 'M d, Y') }}</p>
                            </div>
                            <div class="col-sm-auto">
                                <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Last Active') }}</p>
                                <p class="mb-0 fw-medium {{ $user->last_active_at ? 'text-primary' : 'text-danger' }}">
                                    {{ $user->last_active_at ? timeAgo($user->last_active_at) : translate('Long time ago') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="d-flex flex-column justify-content-between p-4 h-100">
                    @if ($seller)
                        <div class="row g-4 mb-4 text-center">
                            <div class="col-4">
                                <div class="stats-item">
                                    <h3 class="fw-bold mb-0">{{ numberFormat($user->products_count) }}</h3>
                                    <p class="text-muted small mb-0">{{ translate('Products') }}</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stats-item">
                                    <h3 class="fw-bold mb-0">{{ numberFormat($user->total_sales) }}</h3>
                                    <p class="text-muted small mb-0">{{ translate('Total Sales') }}</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stats-item">
                                    <h3 class="fw-bold mb-0 {{ $user->refunds_as_seller_count > 0 ? 'text-danger' : '' }}">
                                        {{ numberFormat($user->refunds_as_seller_count) }}
                                    </h3>
                                    <p class="text-muted small mb-0">{{ translate('Refunded') }}</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="stats-item p-3 bg-white rounded-3 shadow-none border text-center">
                                    <p class="text-muted small mb-1 fw-bold uppercase">{{ translate('Total Earnings') }}</p>
                                    <h3 class="fw-bold text-success mb-0">{{ getAmount($user->total_earnings) }}</h3>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row g-4 mb-4 text-center">
                            @if ($buyer)
                            <div class="col-6">
                                <div class="stats-item">
                                    <h3 class="fw-bold mb-0">{{ numberFormat($user->purchases_count) }}</h3>
                                    <p class="text-muted small mb-0">{{ translate('Purchases') }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-item">
                                    <h3 class="fw-bold mb-0 {{ $user->refunds_count > 0 ? 'text-danger' : '' }}">
                                        {{ numberFormat($user->refunds_count) }}
                                    </h3>
                                    <p class="text-muted small mb-0">{{ translate('Refund Requests') }}</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="stats-item p-3 bg-white rounded-3 border">
                                    <p class="text-muted small mb-1 fw-bold uppercase">{{ translate('Total Spent') }}</p>
                                    <h3 class="fw-bold text-primary mb-0">{{ getAmount($counters['total_transactions_amount']) }}</h3>
                                </div>
                            </div>
                            @else
                            <div class="col-12">
                                <p class="text-muted py-3 mb-0">{{ translate('The user doesn\'t have any purchase or selling history') }}</p>
                            </div>
                            @endif
                        </div>
                    @endif
                    <div class="d-flex align-items-center gap-2 mt-auto">
                        @php
                            $is_active = $user->isActive();
                            $text = $is_active ? 'Suspend' : 'Activate';
                            $icon = $is_active ? 'slash-circle' : 'check-circle';
                            $color = $is_active ? 'danger' : 'success';
                        @endphp

                        <button class="btn bg-{{ $color }}-subtle text-{{ $color }} w-100 fw-bold action-confirm"
                            data-action="{{ route('admin.roles.users.status.update', $user->id) }}"
                            data-method="POST"
                            data-text="{{ translate('Are you sure want to ' . $text . ' this user?') }}">
                            <i class="bi bi-{{ $icon }} me-2"></i>{{ translate($text . ' User') }}
                        </button>

                        <button type="button" class="btn bg-primary-subtle text-primary" data-bs-target="#sendMailModal"
                            data-bs-toggle="modal" title="{{ translate('Send email') }}">
                            <i class="bi bi-envelope"></i>
                        </button>

                        @include('admin.roles.users.includes.quick-menu')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
