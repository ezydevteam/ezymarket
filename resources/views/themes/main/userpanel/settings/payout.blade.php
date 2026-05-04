@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('title', translate('Settings'))
@section('menu', translate('Payout'))
@section('content')
    <div class="ajax-tabs">
        @themeInclude('userpanel.settings.includes.tabs-nav')
        <div class="ajax-tabs-content">
            <div class="card-v px-4 py-3 shadow-sm rounded-4 mb-4">
                <div class="card-v-header border-0 p-0 mb-4">
                    <div class="d-flex align-items-center justify-content-between border-bottom-dashed pb-2">
                        <div>
                            <h5 class="mb-0 fw-bold">
                                <span class="icon-circle icon-circle-sm bg-primary-subtle text-primary me-2">
                                    <i class="bi bi-credit-card-2-front"></i>
                                </span>
                                {{ translate('Payout Settings') }}
                            </h5>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary px-3" form="payoutUpdateForm">
                                <i class="bi bi-save me-1"></i>
                                {{ translate('Save Changes') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    {{-- Form Column --}}
                    <div class="col-lg-7">
                        <form action="{{ route('user.settings.payout.update') }}" method="POST" class="ajax-form" id="payoutUpdateForm">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-gray-700 small text-uppercase mb-2">{{ translate('Payout Method') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 py-2">
                                        <i class="bi bi-wallet2 text-muted"></i>
                                    </span>
                                    <select name="payout_method" class="selectpicker border-start-0 ps-0"
                                        data-live-search="true" title="{{ translate('Choose payout method') }}" required>
                                        @foreach ($payoutMethods as $payoutMethod)
                                            <option value="{{ $payoutMethod->id }}" @selected($payoutMethod->id == $user->payout_method_id)>
                                                {{ $payoutMethod->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-text text-gray-600">
                                    {{ translate('Select how you would like to receive your earnings.') }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-gray-700 small text-uppercase mb-2">{{ translate('Payout Account Details') }}</label>
                                <textarea name="payout_account" class="form-control bg-light bg-opacity-25"
                                          rows="6" placeholder="{{ translate('Enter your withdrawal details (e.g., Email for PayPal, IBAN for Bank Transfer, etc.)') }}" required>{{ $user->payout_account }}</textarea>
                                <div class="form-text text-gray-600">
                                    {{ translate('Please ensure you provide accurate information to avoid payment delays.') }}
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Info Column --}}
                    <div class="col-lg-5">
                        <div class="px-4 py-3 border rounded-4 bg-light-subtle h-100">
                            <h6 class="fw-bold mb-3 d-flex align-items-center">
                                <span class="icon-circle icon-circle-sm bg-white text-primary me-2 shadow-sm">
                                    <i class="bi bi-info-circle-fill text-primary"></i>
                                </span>
                                {{ translate('Withdrawal Limits') }}
                            </h6>

                            <div class="accordion dashboard-accordion border-0 bg-transparent" id="payoutMethodsAccordion">
                                @foreach ($payoutMethods as $payoutMethod)
                                    <div class="accordion-item border-0 bg-transparent mb-2">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed rounded-3 shadow-none bg-white border"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#method-{{ $payoutMethod->id }}">
                                                <span class="fw-semibold small">{{ $payoutMethod->name }}</span>
                                            </button>
                                        </h2>
                                        <div id="method-{{ $payoutMethod->id }}" class="accordion-collapse collapse"
                                             data-bs-parent="#payoutMethodsAccordion">
                                            <div class="accordion-body p-3 bg-white border border-top-0 rounded-bottom-3 small">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-gray-800 fw-semibold">{{ translate('Minimum Payout') }}:</span>
                                                    <span class="fw-bold text-dark">{{ getAmount($payoutMethod->amount_limit['min']) }}</span>
                                                </div>
                                                @if($payoutMethod->hasFees())
                                                    <div class="d-flex justify-content-between mb-3 pb-2 border-0 border-bottom border-dashed text-danger">
                                                        <span>{{ translate('Processing Fee') }}:</span>
                                                        <span class="fw-bold">
                                                            @if($payoutMethod->fees_type === 'percentage')
                                                                {{ $payoutMethod->fees_value }}%
                                                            @else
                                                                {{ getAmount($payoutMethod->fees_value) }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif

                                                @if ($payoutMethod->instructions)
                                                    <div class="mt-2 p-3 bg-light rounded-3">
                                                        <div class="fw-bold mb-1 text-success small">
                                                            <i class="bi bi-journal-text me-1"></i>
                                                            {{ translate('Instructions') }}:
                                                        </div>
                                                        <div class="text-gray-600 fs-13">
                                                            {!! $payoutMethod->instructions !!}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
