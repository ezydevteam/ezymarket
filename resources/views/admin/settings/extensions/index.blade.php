@extends('admin.layouts.app')
@section('section', translate('Settings'))
@section('title', translate('Extensions'))
@section('description', translate('Configure and manage extensions to enhance your site\'s functionality.'))
@section('container', 'container-max-xl')
@section('content')
{{-- Extensions Table --}}
<div class="card">
    <div class="table-responsive">
        <table class="table ezydev-table">
            <thead>
                <tr>
                    <th>{{ translate('ID') }}</th>
                    <th>{{ translate('Extension Name') }}</th>
                    <th class="text-center">{{ translate('Status') }}</th>
                    <th class="text-center">{{ translate('Last Updated') }}</th>
                    <th class="text-end">{{ translate('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($extensions as $extension)
                <tr>
                    <td class="fw-bold text-muted">#{{ $extension->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.settings.extensions.edit', $extension->id) }}"
                                class="d-inline-block">
                                <div class="image-fluid image-md d-inline-block">
                                    <img src="{{ $extension->logo_url }}" alt="{{ translate($extension->name) }}">
                                </div>
                            </a>
                            <div>
                                <a href="{{ route('admin.settings.extensions.edit', $extension->id) }}"
                                    class="text-dark fw-bold text-decoration-none">
                                    {{ translate($extension->name) }}
                                </a>
                                <p class="text-muted small mb-0">
                                    {{ translate($extension->description) }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        @php $extensionActive = $extension->isActive(); @endphp
                        <span class="badge bg-text-{{ $extensionActive ? 'green' : 'red' }}">
                            <i class="bi bi-{{ $extensionActive ? 'check-circle' : 'x-circle' }} me-1"></i>
                            {{ translate($extensionActive ? 'Active' : 'Inactive') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <small class="text-muted">
                            {{ dateFormat($extension->updated_at) }}
                        </small>
                    </td>
                    <td class="text-end">
                        <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                            <x-dropdown.item href="{{ route('admin.settings.extensions.edit', $extension->id) }}"
                                icon="bi-gear" iconClass="text-primary">
                                {{ translate('Configure') }}
                            </x-dropdown.item>
                            <x-dropdown.item type="divider" />
                            <x-dropdown.item type="button"
                                icon="{{ $extensionActive ? 'bi-toggle-off' : 'bi-toggle-on' }}"
                                color="{{ $extensionActive ? 'danger' : 'success' }}" class="change-extension-status"
                                data-id="{{ $extension->id }}" data-is-active="{{ $extensionActive ? 0 : 1 }}">
                                {{ translate($extensionActive ? 'Disable' : 'Enable') }}
                            </x-dropdown.item>
                        </x-dropdown>
                    </td>
                </tr>
                @empty
                <x-empty message="{{ translate('No extensions found!') }}"
                    description="{{ translate('Install and manage extensions to enhance your site\'s functionality.') }}"
                    icon="bi-puzzle" />
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    'use strict';

    $(document).on('click', '.change-extension-status', function () {
        const button = $(this);
        const extensionId = button.data('id');
        const newStatus = button.data('is-active');

        $.ajax({
            url: '{{ route('admin.settings.extensions.change-status') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: extensionId,
                is_active: newStatus
            },
            beforeSend: function () {
                button.prop('disabled', true);
            },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message);
                    button.prop('disabled', false);
                }
            },
            error: function (xhr) {
                const message = xhr.responseJSON?.message || 'An error occurred';
                toastr.error(message);
                button.prop('disabled', false);
            }
        });
    });
</script>
@endpush
