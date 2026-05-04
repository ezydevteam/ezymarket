@extends('admin.layouts.app')
@section('section', translate('Settings'))
@section('title', translate('Captcha'))
@section('description', translate('Configure and manage captcha providers to protect your site from spam'))
@section('container', 'container-max-xl')
@section('content')
{{-- Captcha Providers Table --}}
<div class="card">
    <div class="table-responsive">
        <table class="table ezydev-table">
            <thead>
                <tr>
                    <th>{{ translate('ID') }}</th>
                    <th>{{ translate('Provider Name') }}</th>
                    <th class="text-center">{{ translate('Default') }}</th>
                    <th class="text-center">{{ translate('Status') }}</th>
                    <th class="text-center">{{ translate('Last Updated') }}</th>
                    <th class="text-end">{{ translate('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($captchas as $captchaProvider)
                <tr>
                    <td class="fw-bold text-muted">#{{ $captchaProvider->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.settings.captcha.edit', $captchaProvider->id) }}"
                                class="d-inline-block">
                                <div class="image-fluid image-md d-inline-block">
                                    <img src="{{ $captchaProvider->logo_url }}"
                                        alt="{{ translate($captchaProvider->name) }}">
                                </div>
                            </a>
                            <div>
                                <a href="{{ route('admin.settings.captcha.edit', $captchaProvider->id) }}"
                                    class="text-dark fw-bold text-decoration-none">
                                    {{ translate($captchaProvider->name) }}
                                </a>
                                <p class="text-muted small mb-0">
                                    {{ translate($captchaProvider->description) }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        @php $defaultCaptcha = $captchaProvider->isDefault(); @endphp
                        <span class="badge bg-text-{{ $defaultCaptcha ? 'green' : 'red' }}">
                            <i class="bi bi-{{ $defaultCaptcha ? 'check-circle' : 'x-circle' }} me-1"></i>
                            {{ translate($defaultCaptcha ? 'Yes' : 'No') }}
                        </span>
                    </td>
                    <td class="text-center">
                        @php $captchaActive = $captchaProvider->isActive(); @endphp
                        <span class="badge bg-text-{{ $captchaActive ? 'green' : 'red' }}">
                            <i class="bi bi-{{ $captchaActive ? 'check-circle' : 'x-circle' }} me-1"></i>
                            {{ translate($captchaActive ? 'Active' : 'Inactive') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <small class="text-muted">
                            {{ dateFormat($captchaProvider->updated_at) }}
                        </small>
                    </td>
                    <td class="text-end">
                        <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                            <x-dropdown.item href="{{ route('admin.settings.captcha.edit', $captchaProvider->id) }}"
                                icon="bi-gear" iconClass="text-primary">
                                {{ translate('Configure') }}
                            </x-dropdown.item>
                            @if (!$captchaProvider->isDefault())
                            <x-dropdown.item type="divider" />
                            <x-dropdown.item href="{{ route('admin.settings.captcha.default', $captchaProvider->id) }}"
                                icon="bi-star" color="primary" class="action-confirm" data-method="POST"
                                data-confirm="{{ translate('Are you sure want to make :captcha as a default captcha provider?', ['captcha' => $captchaProvider->name]) }}">
                                {{ translate('Make Default') }}
                            </x-dropdown.item>
                            @endif
                        </x-dropdown>
                    </td>
                </tr>
                @empty
                <x-empty message="{{ translate('No captcha providers found!') }}"
                    description="{{ translate('Configure captcha providers to protect your site from spam.') }}"
                    icon="bi-shield-check" />
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
