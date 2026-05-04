@extends('admin.layouts.app')

@section('section', translate('Chatbox Settings'))
@section('title', translate('Chatbox Settings'))
@section('back', route('admin.dashboard'))
@section('save', 'Chatbox-settings-btn')
@section('container', 'container-max-lg')

@section('content')
<div class="row">
    <div class="col-12">
        <form id="ezydev-form" action="{{ route('admin.chatbox.store') }}" method="POST">
            @csrf

            {{-- ============ 1. Global Availability ============ --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        {{ translate('Global Chatbox Availability') }}
                    </h5>
                </div>
                <div class="card-body">
                    <label class="form-label">
                        {{ translate('Enable Chatbox System') }}
                    </label>
                    <input type="checkbox" name="chatbox_system_enabled" value="1" data-toggle="toggle" {{
                        ($chatboxSettings->status ?? true) ? 'checked' : '' }}>
                    <div class="form-text">
                        {{ translate('Turn the entire real-time messaging feature on or off for everyone.') }}
                    </div>
                </div>
            </div>

            {{-- ============ 2. Content Filters ============ --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        {{ translate('Message Filtering Rules') }}
                    </h5>
                </div>
                <div class="card-body">
                    {{-- banned keywords --}}
                    <div class="mb-4">
                        <label class="form-label">
                            {{ translate('Banned Keywords / Phrases') }}
                        </label>
                        <textarea name="keywords[]" class="form-control" id="keywords" rows="16"
                            placeholder="{{ translate('Type each keyword and press Enter') }}">{{ isset($chatboxSettings->banned_keywords)
                                        ? implode(',', $chatboxSettings->banned_keywords)
                                        : '' }}</textarea>
                        <div class="form-text">
                            {{ translate('Comma seperated. Messages containing any of these words will be unsent.') }}
                        </div>
                    </div>

                    {{-- fixed filters (always forced on but still visible) --}}
                    <div class="row">
                        @php
                        $filterService = app(\App\Services\MessageFilterService::class);
                        $blocked = [
                        'block_links' => $filterService->blockLinks,
                        'block_emails' => $filterService->blockEmails,
                        'block_phones' => $filterService->blockPhones,
                        'block_social_media' => $filterService->blockSocialMedia,
                        'block_addresses' => $filterService->blockAddresses,
                        ];
                        $filters = [
                        'block_links' => translate('Block Links'),
                        'block_emails' => translate('Block E-mail Addresses'),
                        'block_phones' => translate('Block Phone Numbers'),
                        'block_social_media' => translate('Block Social-media Handles & URLs'),
                        'block_addresses' => translate('Block Physical Addresses'),
                        ];
                        @endphp

                        @foreach($filters as $field => $label)
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ translate($label) }}</label>
                            <input type="checkbox" disabled data-toggle="toggle" {{ !empty($blocked[$field]) ? 'checked'
                                : '' }}>
                            <div class="form-text">
                                {{ translate('Always enforced (cannot be disabled)') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ============ 3. Current Summary ============ --}}
            @if($chatboxSettings)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        {{ translate('Current Configuration Summary') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-2">
                                    {{ translate('Chatbox Status') }}
                                </h6>
                                @if($chatboxSettings->status)
                                <span class="badge bg-success">
                                    {{ translate('Enabled') }}
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    {{ translate('Disabled') }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-2">
                                    {{ translate('Banned Keywords') }}
                                </h6>
                                @if(!empty($chatboxSettings->banned_keywords))
                                @foreach($chatboxSettings->banned_keywords as $word)
                                <span class="badge bg-danger me-1 mb-1">{{ $word }}</span>
                                @endforeach
                                @else
                                <span class="badge bg-secondary">
                                    {{ translate('None') }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    /* -----------------------------------------------
       Convert textarea into a tag-style input
       (You can replace this with Tagify or any plugin)
    ------------------------------------------------ */
    document.addEventListener('DOMContentLoaded', function () {
        const txt = document.getElementById('keywords');
        // simple UX helper: split by comma on blur
        txt.addEventListener('blur', function () {
            this.value = this.value
                .split(',')
                .map(v => v.trim())
                .filter(v => v.length)
                .join(',');
        });
    });
</script>
@endpush
