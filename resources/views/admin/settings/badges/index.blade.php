@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('User Badges'))
@section('content')
    <x-datatable
        id="badgesTable"
        :items="$badges"
        tableClass="datatable2"
        emptyMessage="{{ translate('No badges found!') }}"
        emptyDescription="{{ translate('Create your first badge to recognize user achievements and membership status') }}"
        emptyIcon="bi-award"
        emptyButton="{{ translate('Create Badge') }}"
        emptyButtonModal="#createBadgeModal"
    >
        <thead>
            <tr>
                <th class="no-sort no-export">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('Badge') }}</th>
                <th class="text-center">{{ translate('Type') }}</th>
                <th class="text-center">{{ translate('Created Date') }}</th>
                <th class="text-end no-sort">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($badges as $badge)
                <tr>
                    <td class="no-export">
                        <input type="checkbox" class="form-check-input row-checkbox" name="ids[]"
                            value="{{ $badge->id }}">
                    </td>
                    <td>
                       <div class="d-flex align-items-center gap-3">
                            <div class="image-fluid image-md rounded">
                                <img src="{{ $badge->image_url }}" alt="{{ $badge->name }}">
                            </div>
                            <div>
                                {{ $badge->name }}
                                <p class="text-muted small mb-0">
                                    {{ $badge->title ? $badge->title : translate('No title set') }}
                                </p>
                            </div>
                       </div>
                    </td>
                    <td class="text-center">
                        @if($badge->isCountryBadge())
                            <span class="badge bg-text-primary">
                                <i class="bi bi-flag me-1"></i>{{ translate('Country') }}
                            </span>
                        @elseif($badge->IsSellerLevelBadge())
                            <span class="badge bg-text-green">
                                <i class="bi bi-star me-1"></i>{{ translate('Seller Level') }}
                            </span>
                        @elseif($badge->isMembershipYearsBadge())
                            <span class="badge bg-text-purple">
                                <i class="bi bi-calendar me-1"></i>{{ translate('Membership') }}
                            </span>
                        @else
                            <span class="badge bg-text-dark">
                                <i class="bi bi-award me-1"></i>{{ translate('Default') }}
                            </span>
                        @endif
                    </td>
                    <td class="text-center">{{ dateFormat($badge->created_at) }}</td>
                    <td class="text-end">
                        <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                            <x-dropdown.item
                                href="javascript:void(0)"
                                icon="bi bi-pencil-square"
                                iconClass="text-primary me-2"
                                class="edit-badge-btn"
                                data-id="{{ $badge->id }}">
                                {{ translate('Edit Details') }}
                            </x-dropdown.item>
                            @if (!$badge->is_permanent)
                                <x-dropdown.item type="divider" />
                                <x-dropdown.item
                                    href="{{ route('admin.settings.badges.destroy', $badge->id) }}"
                                    icon="bi bi-trash"
                                    color="danger"
                                    class="action-confirm"
                                    data-method="DELETE"
                                    data-confirm="{{ translate('Are you sure you want to delete this badge? This action cannot be undone.') }}">
                                    {{ translate('Delete') }}
                                </x-dropdown.item>
                            @endif
                        </x-dropdown>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>

    {{-- Create Badge Modal --}}
    <x-modal id="createBadgeModal" :title="translate('Create New Badge')" :icon="'bi bi-award'">
        @include('admin.settings.badges.partials.create-modal')
    </x-modal>

    {{-- Edit Badge Modal --}}
    <x-modal id="editBadgeModal" :title="translate('Edit Badge')" :icon="'bi bi-award'">
        <x-loader id="modalLoader" centered />
        <div id="modalContent" class="d-none"></div>
    </x-modal>
@endsection

@push('top_scripts')
    <script>
        "use strict";
        config.translates.searchPlaceholder = "{{ translate('Search Badges') }}";
    </script>
@endpush

@push('scripts_libs')
    <script>
        const tableElement = document.getElementById('badgesTable');
        if (tableElement) {
            // Define bulk actions as dropdown menu items
            const bulkActions = [
                {
                    text: '<i class="bi bi-trash text-danger me-2"></i>{{ translate("Delete Selected") }}',
                    className: 'dropdown-item text-danger',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.settings.badges.bulk-delete') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected badges?') }}"
                        });
                    }
                }
            ];
            $(tableElement).data('bulk-actions', bulkActions);

            // Attach create badge button
            const customButtons = [
                {
                    text: '<i class="bi bi-plus-lg me-1"></i> {{ translate("Create Badge") }}',
                    className: 'btn btn-primary',
                    action: function(e, dt, node, config) {
                        $('#createBadgeModal').modal('show');
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

         // Initialize AJAX modal content loader for badge editing
        initAjaxModalContent({
            triggerSelector: '.edit-badge-btn',
            modalId: 'editBadgeModal',
            contentId: 'modalContent',
            urlBuilder: (id) => `{{ url('admin/settings/badges') }}/${id}/edit-modal`,
            errorMessage: '{{ translate("Failed to load badge details") }}',
            iconClass: 'bi-award'
        });

        // Initialize AJAX form handler for edit badge (supports dynamically loaded forms)
        initAjaxModalForm({
            formSelector: '#editBadgeForm',
            modalSelector: '#editBadgeModal',
            submitButtonSelector: '#editBadgeBtn',
            loadingText: '{{ translate("Updating...") }}',
            useDelegation: true
        });

        // Badge type change handler for create modal
        $('#badgeTypeSelect').on('change', function() {
            let type = $(this).val();

            // Hide all conditional fields and disable their inputs
            $('#countryField, #sellerLevelField, #membershipYearsField').addClass('d-none');
            $('#countryField select, #sellerLevelField select, #membershipYearsField input').prop('disabled', true).removeAttr('required');

            // Show and enable the selected type's field
            if (type === 'countries') {
                $('#countryField').removeClass('d-none');
                $('#countryField select').attr('required', true).prop('disabled', false);
            } else if (type === 'seller_levels') {
                $('#sellerLevelField').removeClass('d-none');
                $('#sellerLevelField select').attr('required', true).prop('disabled', false);
            } else if (type === 'membership_years') {
                $('#membershipYearsField').removeClass('d-none');
                $('#membershipYearsField input').attr('required', true).prop('disabled', false);
            }
        });

        // Initialize create badge modal form
        initAjaxModalForm({
            formSelector: '#createBadgeForm',
            modalSelector: '#createBadgeModal',
            submitButtonSelector: '#createBadgeBtn',
            loadingText: '{{ translate("Creating...") }}',
        });
    </script>
@endpush
















