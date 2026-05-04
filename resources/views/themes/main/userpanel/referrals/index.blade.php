@extends('themes.main.userpanel.layout')
@section('title', translate('Referral Program'))
@section('container', 'userpanel-container-xl')

@section('content')
<div class="row g-4">
    {{-- Hero Section --}}
    @themeInclude('userpanel.referrals.partials.hero')

    {{-- Stats Grid --}}
    @themeInclude('userpanel.referrals.partials.stats')

    {{-- How it works --}}
    <div class="col-12 my-5">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-dark">{{ translate('How it works') }}</h4>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-3">
                <div class="text-center">
                    <div class="icon-circle icon-circle-md mx-auto mb-3 text-white bg-primary fs-4 fw-bold">1</div>
                    <h6 class="fw-bold mb-2">{{ translate('Invite Friends') }}</h6>
                    <p class="text-gray-700 small">{{ translate('Send them your unique referral link.') }}</p>
                </div>
            </div>
            <div class="col-md-1 d-none d-md-flex align-items-center justify-content-center">
                <i class="bi bi-arrow-right text-muted opacity-50 fs-3"></i>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="icon-circle icon-circle-md mx-auto mb-3 text-white bg-primary fs-4 fw-bold">2</div>
                    <h6 class="fw-bold mb-2">{{ translate('They Sign Up') }}</h6>
                    <p class="text-gray-700 small">{{ translate('When they register and buy something.') }}</p>
                </div>
            </div>
            <div class="col-md-1 d-none d-md-flex align-items-center justify-content-center">
                <i class="bi bi-arrow-right text-muted opacity-50 fs-3"></i>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="icon-circle icon-circle-md mx-auto mb-3 text-white bg-primary fs-4 fw-bold">3</div>
                    <h6 class="fw-bold mb-2">{{ translate('You Get Paid') }}</h6>
                    <p class="text-gray-700 small">{{ translate('Earn instant commission on their spend.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Referral List --}}
    <div class="col-12">
        <div class="card card-body border-0 shadow-sm rounded-4 p-0 overflow-hidden bg-white bg-opacity-75">
            <div class="d-flex justify-content-between align-items-center p-4 border-0 border-bottom border-dashed">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-activity me-2 text-primary"></i>{{
                    translate('Referral Activity') }}</h5>
                @if ($referrals->count() > 0 || request()->input('search'))
                <form action="{{ url()->current() }}" method="GET" class="d-flex gap-2">
                    <div class="userpanel-search-input-wrapper">
                        <i class="bi bi-search userpanel-search-icon"></i>
                        <input type="text" name="search" class="userpanel-search-input"
                            placeholder="{{ translate('Search referrals...') }}" value="{{ request('search') }}">
                    </div>
                </form>
                @endif
            </div>

            @if ($referrals->count() > 0)
            <div class="table-responsive">
                <table class="table ezydev-table">
                    <thead>
                        <tr>
                            <th class="ps-4">{{ translate('User Details') }}</th>
                            <th>{{ translate('Joined Date') }}</th>
                            <th class="text-end pe-4">{{ translate('Total Commissions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($referrals as $referral)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar user-avatar-sm rounded">
                                        <img src="{{ $referral->user->avatar_url }}"
                                            alt="{{ $referral->user->username }}">
                                    </div>
                                    <div class="lh-sm">
                                        <a href="{{ $referral->user->profile_link }}"
                                            class="text-dark fw-medium d-block hover-primary">{{
                                            $referral->user->username }}</a>
                                        <small class="text-muted">{{$referral->user->email}}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column small">
                                    <span class="text-dark fw-medium">{{ dateFormat($referral->created_at) }}</span>
                                    <span class="text-muted xsmall">{{ $referral->created_at->diffForHumans() }}</span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold">
                                    + {{ getAmount($referral->earnings) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            @themeInclude('userpanel.partials.empty', [
                'title' => translate('No Referrals Yet!'),
                'description' => translate(
                    'Start sharing your link to collect referrals and earn passive income from their purchases on the platform.'
                ),
                'icon' => 'people',
                'btn_text' => translate('Back to Dashboard'),
                'btn_url' => route('user.index'),
            ])
            @endif
        </div>
    </div>
    @if ($referrals->hasPages())
    @themeInclude('userpanel.partials.pagination', ['items' => $referrals])
    @endif
</div>
@endsection
