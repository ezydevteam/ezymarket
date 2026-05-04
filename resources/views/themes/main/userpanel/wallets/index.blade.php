@extends('themes.main.userpanel.layout')
@section('title', translate('My Wallet'))
@section('header_title', translate('My Wallet'))
@section('description', translate('Manage your funds, view transaction history, and handle deposits/payouts.'))

@section('content')
    <div class="wallet-container">
        <!-- Wallet Hero Section -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-xl-7">
                <div class="wallet-card">
                    <div class="wallet-card-bg">
                        <div class="wallet-circle circle-1"></div>
                        <div class="wallet-circle circle-2"></div>
                    </div>
                    <div class="wallet-card-content d-flex flex-column position-relative h-100 z-1 p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <span class="text-white-50 small text-uppercase ls-1 fw-bold">{{ translate('Current Balance') }}</span>
                                <h2 class="display-5 fw-bold text-white mb-0 mt-1 d-flex align-items-center gap-3">
                                    <span class="amount-masked">{{ str_repeat('•', 8) }}</span>
                                    <span class="amount-real d-none">{{ getAmount(authUser()->balance) }}</span>
                                    <span role="button" class="balance-toggle fs-5"
                                        id="balanceToggle" title="{{ translate('Toggle Balance Visibility') }}">
                                        <i class="bi bi-eye"></i>
                                    </span>
                                </h2>
                            </div>
                            <div class="wallet-chip">
                                <i class="bi bi-cpu text-white-50 fs-3"></i>
                            </div>
                        </div>
                        <div class="mt-auto d-flex justify-content-between align-items-end">
                            <div class="user-info">
                                <p class="text-white-50 small mb-1">{{ translate('Wallet Holder') }}</p>
                                <h6 class="text-uppercase text-white fw-light fs-14 mb-0 ls-1-5">{{ authUser()->full_name }}</h6>
                            </div>
                            <div class="wallet-type">
                                <span class="badge bg-white bg-opacity-10 text-white border-0 py-2 px-3 rounded-pill fs-12">
                                    <i class="bi bi-shield-check me-1"></i> {{ translate('Secure Wallet') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="text-gray-600 fw-bold mb-4">{{ translate('Quick Actions') }}</h5>
                        <div class="row g-3 flex-grow-1">
                            <div class="col-6">
                                <button type="button" data-bs-toggle="modal" data-bs-target="#depositModel"
                                    data-action="{{ route('user.wallet.modal.deposit') }}"
                                    class="btn bg-primary-subtle text-primary border-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 rounded-4 hover-lift">
                                    <div class="icon-circle icon-circle-md bg-primary text-white mb-3">
                                        <i class="bi bi-plus-lg fs-4"></i>
                                    </div>
                                    <span class="fw-bold">{{ translate('Deposit') }}</span>
                                </button>
                            </div>
                            @if (authUser()->isSeller())
                            <div class="col-6">
                                <a href="{{ route('user.payout.index') }}"
                                    class="btn bg-secondary-subtle text-secondary border-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 rounded-4 hover-lift">
                                    <div class="icon-circle icon-circle-md bg-secondary text-white mb-3">
                                        <i class="bi bi-send fs-4"></i>
                                    </div>
                                    <span class="fw-bold">{{ translate('Payout') }}</span>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Section -->
        <div class="transactions-section">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <h4 class="fw-bold mb-0">{{ translate('Transaction History') }}</h4>
                <div class="filter-wrapper">
                    <form action="{{ url()->current() }}" method="GET" class="d-flex gap-2">
                        <input type="date" name="date_from" class="form-control form-control-sm rounded-pill border-0 shadow-sm px-3" value="{{ request('date_from') }}">
                        <input type="date" name="date_to" class="form-control form-control-sm rounded-pill border-0 shadow-sm px-3" value="{{ request('date_to') }}">
                        <button class="btn btn-primary btn-sm icon-circle icon-circle-sm shadow-sm"><i class="bi bi-search scale-75"></i></button>
                        @if(request('date_from') || request('date_to'))
                            <a href="{{ url()->current() }}" title="{{ translate('Reset') }}"
                                class="btn btn-light btn-sm icon-circle icon-circle-sm shadow-sm">
                                <i class="bi bi-arrow-repeat scale-75"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            @if ($statements->count() > 0)
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="table-responsive">
                        <table class="table ezydev-table">
                            <thead>
                                <tr class="bg-white border-bottom-dashed">
                                    <th class="py-3 bg-white">{{ translate('Transaction Details') }}</th>
                                    @if (authUser()->isSeller())
                                        <th class="text-center py-3 bg-white">{{ translate('Breakdown') }}</th>
                                    @endif
                                    <th class="text-center py-3 bg-white">{{ translate('Amount') }}</th>
                                    <th class="text-end py-3 bg-white">{{ translate('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($statements as $statement)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="icon-circle icon-circle-md {{ $statement->type_badge_class }}">
                                                    <i class="{{ $statement->type_icon }} fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $statement->title }}</div>
                                                    <div class="text-muted small">#{{ $statement->id }} • {{ $statement->type_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        @if (authUser()->isSeller())
                                        @php
                                            $fee = $statement->seller_fee + $statement->buyer_fee;
                                            $label = $statement->type->value =='credit' ? 'Earn' : 'Deducted';
                                        @endphp
                                            <td class="py-3 text-center">
                                                <div class="d-flex flex-column small">
                                                    <span class="text-dark">{{ translate($label) }}: {{ getAmount($statement->amount ?? 0) }}</span>
                                                    <span class="text-gray-600 fs-12">{{ translate('Fee') }}: {{ getAmount($fee ?? 0) }}</span>
                                                </div>
                                            </td>
                                        @endif
                                        <td class="py-3 text-center">
                                            <span class="fw-medium text-{{ $statement->type->color() }}">
                                                <i class="bi bi-{{ ($statement->type->value =='credit') ? 'plus' : 'dash' }} me-1"></i>{{ getAmount(abs($statement->total)) }}
                                            </span>
                                        </td>
                                        <td class="pe-4 py-3 text-end">
                                            <div class="text-dark">{{ dateFormat($statement->created_at) }}</div>
                                            <div class="text-muted small">{{ $statement->created_at->diffForHumans() }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @themeInclude('userpanel.partials.pagination', ['items' => $statements])
            @else
                @themeInclude('userpanel.partials.empty', [
                    'title' => translate('No Transactions Yet!'),
                    'description' => translate(
                        'Your wallet history will appear here once you start earning or making deposits.'
                    ),
                    'icon' => 'receipt',
                    'modal_id' => 'depositModel',
                    'modal_btn_text' => translate('Make Your First Deposit'),
                    'modal_action' => route('user.wallet.modal.deposit'),
                ])
            @endif
        </div>
    </div>

    <x-modal id="depositModel" :header="false" />

    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/jquery/jquery.priceformat.min.js') }}"></script>
    @endpush
@endsection
