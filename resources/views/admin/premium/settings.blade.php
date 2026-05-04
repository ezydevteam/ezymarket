@extends('admin.layouts.form')
@section('section', translate('Subscriptions'))
@section('title', translate('Subscription Settings'))
@section('description', translate('Manage your subscription settings here. You can enable or disable subscription
features and set the terms for subscriptions.'))
@section('container', 'container-max-lg')
@section('content')
<form id="ezydev-form" action="{{ route('admin.premium.settings.update') }}" method="POST">
    @csrf
    <div class="card mb-4">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-lg-8">
                    <h5 class="mb-0">{{ translate('Subscription Status') }}</h5>
                    <div class="form-text mt-2">{{ translate('Enable or disable subscription features') }}</div>
                </div>
                <div class="col-lg-4">
                    <div class="ezydev-switch-wrapper-xl">
                        <input type="hidden" name="premium[status]" value="0">
                        <input id="premium_status" class="ezydev-switch-input" type="checkbox" name="premium[status]"
                            value="1" {{ @$settings->premium->status ? 'checked' : '' }}>
                        <label class="ezydev-switch-label" for="premium_status">
                            <span class="ezydev-switch-slider">
                                <span class="ezydev-switch-button">
                                    <span class="ezydev-switch-on">{{ translate('Enabled') }}</span>
                                    <span class="ezydev-switch-off">{{ translate('Disabled') }}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label">{{ translate('Terms & Conditions Slug') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
                        <input type="text" name="premium[terms_link]" class="form-control"
                            value="{{ @$settings->premium->terms_link }}">
                    </div>
                    <small class="form-text text-muted"><i class="fa fa-info-circle me-1"></i>{{ translate('Set the slug
                        for the subscription terms and conditions page. For example, if you set it to
                        "subscription-terms", the URL will be yourdomain.com/subscription-terms') }}</small>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ translate('Recommended Package Label') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-star"></i></span>
                        <input type="text" name="premium[recommended_package_label]" class="form-control"
                            placeholder="{{ translate('e.g., Best Value, Most Popular, Recommended') }}"
                            value="{{ @$settings->premium->recommended_package_label }}">
                    </div>
                    <small class="form-text text-muted"><i class="fa fa-info-circle me-1"></i>{{ translate('Set a custom
                        label that will be displayed on recommended/featured packages (e.g., "Best Value", "Most
                        Popular")') }}</small>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
