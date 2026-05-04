@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('Support Packages'))
@section('content')
    <x-datatable
        id="supportPackagesTable"
        :items="$supportPackages"
        tableClass="sortable-table"
        emptyMessage="{{ translate('No support packages found') }}"
        emptyDescription="{{ translate('Create packages to offer support durations to your customers.') }}"
        emptyIcon="bi-box-seam"
        emptyButton="{{ translate('Create Package') }}"
        emptyButtonModal="#createSupportPackageModal"
        :hasSortable="true"
    >
        <thead>
            <tr>
                <th class="no-sort">
                    <i class="bi bi-arrows-move"></i>
                </th>
                <th>{{ translate('Package Name') }}</th>
                <th>{{ translate('Public Title') }}</th>
                <th class="text-center">{{ translate('Duration') }}</th>
                <th class="text-center">{{ translate('Pricing') }}</th>
                <th class="text-center">{{ translate('Created Date') }}</th>
                <th class="text-end no-sort">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="sortable-table-tbody">
            @foreach ($supportPackages as $package)
                <tr data-id="{{ $package->id }}">
                    <td>
                        <div class="sortable-table-handle">
                            <i class="bi bi-grip-vertical"></i>
                        </div>
                    </td>
                    <td>
                        <span role="button" class="text-reset fw-semibold edit-support-package-btn" data-id="{{ $package->id }}">
                            {{ $package->name }}
                        </span>
                    </td>
                    <td>{{ $package->title }}</td>
                    <td class="text-center">
                       {{ $package->days }} {{ translate('Days') }}
                    </td>
                    <td class="text-center">
                        @php
                            $percentage = $package->rate['percentage'] ?? 0;
                            $fixed = $package->rate['fixed'] ?? 0;
                        @endphp
                        @if($percentage > 0 && $fixed > 0)
                            {{ $percentage }}% + {{ getAmount($fixed) }}
                        @elseif($percentage > 0)
                             {{ $percentage }}%
                        @elseif($fixed > 0)
                            {{ getAmount($fixed) }}
                        @else
                            <span class="badge bg-text-green">{{ translate('Free') }}</span>
                        @endif
                    </td>
                    <td class="text-center text-muted">{{ dateFormat($package->created_at) }}</td>
                    <td class="text-end">
                        <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                            <x-dropdown.item
                                type="button"
                                icon="bi bi-pencil-square"
                                iconClass="text-primary me-2"
                                class="edit-support-package-btn"
                                data-id="{{ $package->id }}">
                                {{ translate('Edit Details') }}
                            </x-dropdown.item>

                            @if (!$package->isFree())
                                <x-dropdown.item type="divider" />
                                <x-dropdown.item
                                    href="{{ route('admin.settings.support-packages.destroy', $package->id) }}"
                                    icon="bi bi-trash"
                                    color="danger"
                                    class="action-confirm"
                                    data-method="DELETE"
                                    data-confirm="{{ translate('Are you sure you want to delete this package?') }}">
                                    {{ translate('Delete') }}
                                </x-dropdown.item>
                            @endif
                        </x-dropdown>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>

    {{-- Create Modal --}}
    @include('admin.settings.support-packages.partials.create-modal')

    {{-- Edit Modal --}}
    <x-modal id="editSupportPackageModal" :title="translate('Edit Support Package')" :icon="'bi bi-box-seam'" :scrollable="true">
        <x-loader id="modalLoader" centered />
        <div id="modalContent" class="d-none"></div>

         <x-slot:footer>
            <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}
            </button>
            <button type="submit" id="editSupportPackageBtn" form="editSupportPackageForm" class="btn btn-primary flex-fill">
                <i class="bi bi-check-circle me-2"></i> {{ translate('Save Changes') }}
            </button>
        </x-slot>
    </x-modal>

@endsection

@push('top_scripts')
    <script>
        "use strict";
        config.translates.searchPlaceholder = "{{ translate('Search Packages') }}";
        const sortingRoute = "{{ route('admin.settings.support-packages.sortable') }}";
    </script>
@endpush

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>
    <script>
        const tableElement = document.getElementById('supportPackagesTable');
        if (tableElement) {
            const customButtons = [
                {
                    text: '<i class="bi bi-plus-lg me-1"></i> {{ translate("Create Package") }}',
                    className: 'btn btn-primary',
                    action: function(e, dt, node, config) {
                        $('#createSupportPackageModal').modal('show');
                    }
                }
            ];

            $(tableElement).data('custom-buttons', customButtons);
        }
    </script>
@endpush

@push ('scripts')
    <script>
        "use strict";

         // Initialize AJAX modal content loader for edit
        initAjaxModalContent({
            triggerSelector: '.edit-support-package-btn',
            modalId: 'editSupportPackageModal',
            contentId: 'modalContent',
            urlBuilder: (id) => `{{ url('admin/settings/support-packages') }}/${id}/edit-modal`,
            errorMessage: '{{ translate("Failed to load package details") }}',
            iconClass: 'bi-box-seam'
        });

        // Initialize AJAX form handler for edit
        initAjaxModalForm({
            formSelector: '#editSupportPackageForm',
            modalSelector: '#editSupportPackageModal',
            submitButtonSelector: '#editSupportPackageBtn',
            loadingText: '{{ translate("Updating...") }}',
            useDelegation: true
        });

        // Initialize create modal form
        initAjaxModalForm({
            formSelector: '#createSupportPackageForm',
            modalSelector: '#createSupportPackageModal',
            submitButtonSelector: '#createSupportPackageBtn',
            loadingText: '{{ translate("Creating...") }}',
        });
    </script>
@endpush
