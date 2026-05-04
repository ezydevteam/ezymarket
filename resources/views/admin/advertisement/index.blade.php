@extends('admin.layouts.full')
@section('title', translate('Advertisements'))
@section('content')
    <x-datatable
        :items="$advertisements"
        emptyMessage="{{ translate('No advertisements found') }}"
        emptyDescription="{{ translate('All advertisement configurations will appear here') }}"
        emptyIcon="bi-badge-ad"
    >
        <thead>
            <tr>
                <th>{{ translate('ID') }}</th>
                <th>{{ translate('Ad Position') }}</th>
                <th class="text-center">{{ translate('Size') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-center">{{ translate('Last update') }}</th>
                <th class="text-end no-sort">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($advertisements as $ad)
                <tr>
                    <td>#{{ $ad->id }}</td>
                    <td>
                        <a href="{{ route('admin.ads.edit', $ad->id) }}" class="text-dark">
                            <i class="bi bi-badge-ad me-2"></i>{{ translate($ad->position) }}
                        </a>
                    </td>
                    <td class="text-center">{{ $ad->size ?? '--' }}</td>
                    <td class="text-center">
                        <span class="badge bg-text-{{ $ad->isActive() ? 'green' : 'red' }}">
                            <i class="bi bi-{{ $ad->isActive() ? 'check-circle' : 'x-circle' }} me-1"></i>
                            {{ translate($ad->isActive() ? 'Active' : 'Inactive') }}
                        </span>
                    </td>
                    </td>
                    <td class="text-center">{{ dateFormat($ad->updated_at) }}</td>
                    <td>
                        <div class="text-end">
                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                <x-dropdown.item href="{{ route('admin.ads.edit', $ad->id) }}"
                                    icon="bi-pencil-square" iconClass="text-primary">
                                    {{ translate('Edit Details') }}
                                </x-dropdown.item>
                            </x-dropdown>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>
@endsection

@push('top_scripts')
    <script>
        "use strict";
        config.translates.searchPlaceholder = "{{ translate('Search Advertisements') }}";
    </script>
@endpush














