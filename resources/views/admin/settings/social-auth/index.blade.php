@extends('admin.layouts.app')
@section('section', translate('Settings'))
@section('title', translate('Social Authentication'))
@section('container', 'container-max-xl')
@section('content')
{{-- Social Auth Table --}}
<div class="card">
    <div class="table-responsive">
        <table class="table ezydev-table sortable-table">
            <thead>
                <tr>
                    <th><i class="bi bi-arrows-move"></i></th>
                    <th>{{ translate('Provider Name') }}</th>
                    <th class="text-center">{{ translate('Status') }}</th>
                    <th class="text-center">{{ translate('Last Updated') }}</th>
                    <th class="text-end">{{ translate('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="sortable-table-tbody">
                @forelse ($socialAuths as $socialAuth)
                <tr data-id="{{ $socialAuth->id }}">
                    <td>
                        <span class="sortable-table-handle text-muted">
                            <i class="bi bi-grip-vertical"></i>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.settings.social-auth.edit', $socialAuth->id) }}"
                                class="d-inline-block">
                                <div class="image-fluid image-sm d-inline-block">
                                    <img src="{{ $socialAuth->logo_url }}" alt="{{ translate($socialAuth->name) }}">
                                </div>
                            </a>
                            <div>
                                <a href="{{ route('admin.settings.social-auth.edit', $socialAuth->id) }}"
                                    class="text-dark fw-bold text-decoration-none">
                                    {{ translate($socialAuth->name) }}
                                </a>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        @php $socialAuthActive = $socialAuth->isActive(); @endphp
                        <span class="badge bg-text-{{ $socialAuthActive ? 'green' : 'red' }}">
                            <i class="bi bi-{{ $socialAuthActive ? 'check-circle' : 'x-circle' }} me-1"></i>
                            {{ translate($socialAuthActive ? 'Active' : 'Inactive') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <small class="text-muted">
                            {{ dateFormat($socialAuth->updated_at) }}
                        </small>
                    </td>
                    <td class="text-end">
                        <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                            <x-dropdown.item href="{{ route('admin.settings.social-auth.edit', $socialAuth->id) }}"
                                icon="bi-gear" iconClass="text-primary">
                                {{ translate('Configure') }}
                            </x-dropdown.item>
                        </x-dropdown>
                    </td>
                </tr>
                @empty
                <x-empty message="{{ translate('No social authentication providers found!') }}"
                    description="{{ translate('Configure social login providers to enable social authentication.') }}"
                    icon="bi-share" />
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('top_scripts')
<script>
    const sortingRoute = "{{ route('admin.settings.social-auth.sortable') }}";
</script>
@endpush

@push('styles_libs')
<link rel="stylesheet" href="{{ asset('vendor/libs/jquery/jquery-ui.min.css') }}">
@endpush

@push('scripts_libs')
<script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>
@endpush
@endsection
