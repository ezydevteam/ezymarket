@extends('admin.layouts.form')
@section('title', translate('Financial Settings'))
@section('container', 'container-max-lg')
@section('content')
<form id="ezydev-form" action="{{ route('admin.financial.settings') }}" method="POST">
    @csrf
    {{-- Deposit Settings --}}
    <div class="card mb-3">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h6 class="mb-1">{{ translate('Account Deposit Status') }}</h6>
                    <small class="text-muted">{{ translate('Allow users to deposit funds') }}</small>
                </div>
                <div class="col-lg-4">
                    <div class="ezydev-switch-wrapper-xl">
                        <input type="hidden" name="deposit[status]" value="0">
                        <input id="deposit_status" class="ezydev-switch-input codebay-toggle-switch" type="checkbox"
                            name="deposit[status]" value="1" data-toggle-target="#depositDetails" {{
                            @$settings->deposit->status ? 'checked' : '' }}>
                        <label class="ezydev-switch-label" for="deposit_status">
                            <span class="ezydev-switch-slider">
                                <span class="ezydev-switch-button">
                                    <span class="ezydev-switch-on">{{ translate('Enabled') }}</span>
                                    <span class="ezydev-switch-off">{{ translate('Disabled') }}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                </div>
                <div id="depositDetails" class="col-12 mt-3">
                    @include('admin.partials.input-price', [
                    'label' => translate('Minimum Deposit Amount'),
                    'name' => 'deposit[minimum]',
                    'input_classes' => 'form-control-md',
                    'value' => @$settings->deposit->minimum,
                    'min' => 0,
                    ])
                    <small class="text-muted">{{ translate('Leave 0 for no minimum limit') }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Payout Settings --}}
    <div class="card">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h6 class="mb-1">{{ translate('Payout Request Status') }}</h6>
                    <small class="text-muted">{{ translate('Allow users to request payouts') }}</small>
                </div>
                <div class="col-lg-4">
                    <div class="ezydev-switch-wrapper-xl">
                        <input type="hidden" name="payout[status]" value="0">
                        <input id="payout_status" class="ezydev-switch-input codebay-toggle-switch" type="checkbox"
                            name="payout[status]" value="1" data-toggle-target="#payoutDetails" {{
                            @$settings->payout->status ? 'checked' : '' }}>
                        <label class="ezydev-switch-label" for="payout_status">
                            <span class="ezydev-switch-slider">
                                <span class="ezydev-switch-button">
                                    <span class="ezydev-switch-on">{{ translate('Enabled') }}</span>
                                    <span class="ezydev-switch-off">{{ translate('Disabled') }}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                </div>
                <div id="payoutDetails" class="col-12 mt-3">
                    @include('admin.partials.input-price', [
                    'label' => translate('Minimum Payout Amount'),
                    'name' => 'payout[minimum]',
                    'input_classes' => 'form-control-md',
                    'value' => @$settings->payout->minimum,
                    'min' => 0,
                    ])
                    <small class="text-muted">{{ translate('Leave 0 to use individual payout method settings')
                        }}</small>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
