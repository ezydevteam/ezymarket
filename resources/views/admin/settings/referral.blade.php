@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('Referral Settings'))
@section('container', 'container-max-lg')
@section('content')
    <form id="referralSettingsForm" action="{{ route('admin.settings.referral.update') }}" method="POST">
        @csrf

        <div class="card">
            <div class="card-body p-4">
                <div class="row g-4">
                    {{-- Status --}}
                    <div class="col-12">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="card-icon card-icon-md bg-text-green">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-1">{{ translate('Referral System') }}</h4>
                                        <p class="mb-0 text-muted small">{{ translate('Allow users to earn commissions by referring new customers.') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <x-switch
                                    id="referralStatus"
                                    name="referral[status]"
                                    value="1"
                                    :checked="@$settings->referral->status"
                                    size="xl"
                                    :showLabel="false"
                                    onLabel="{{ translate('Enabled') }}"
                                    offLabel="{{ translate('Disabled') }}"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-12"><hr class="my-0 text-muted-light"></div>

                    {{-- Percentage --}}
                    <div class="col-lg-6">
                        <label class="form-label mb-2 fw-bold">{{ translate('Commission Percentage') }}</label>
                         <div class="input-group">
                            <input type="number" name="referral[percentage]"
                                class="form-control"
                                min="1" max="100" step="any"
                                value="{{ @$settings->referral->percentage }}"
                                placeholder="10" required>
                            <span class="input-group-text px-3">
                                <i class="fa-solid fa-percent"></i>
                            </span>
                        </div>
                         <div class="form-text mt-2">
                             {{ translate('Percentage a referrer earns from referred purchases.') }}
                         </div>
                    </div>

                     {{-- Simple Note --}}
                    <div class="col-lg-6">
                         <div class="alert alert-light border mb-0">
                            <h6 class="fw-bold mb-2">
                                <i class="fa fa-info-circle me-1"></i>{{ translate('Calculation Note') }}
                            </h6>
                             <ul class="mb-0 ps-3 small text-muted">
                                <li>{{ translate('Calculated from gross sale amount.') }}</li>
                                <li>{{ translate('Excludes Seller Fee and Tax.') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <hr class="my-4 text-muted-light">

                {{-- Submit --}}
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-md btn-primary">
                        <i class="bi bi-save me-2"></i> {{ translate('Save Changes') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection


















