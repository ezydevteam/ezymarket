@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('Seller Levels'))
@section('content')
    <x-datatable
        id="sellerLevelsTable"
        :items="$sellerLevels"
        emptyMessage="{{ translate('No seller levels created yet') }}"
        emptyDescription="{{ translate('Create your first level to reward successful sellers with lower commission fees') }}"
        emptyIcon="bi-trophy"
        emptyButton="{{ translate('Create Level') }}"
        emptyButtonModal="#createSellerLevelModal"
    >
        <thead>
            <tr>
                <th class="no-sort">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('Level Details') }}</th>
                <th class="text-center">{{ translate('Min. Earnings') }}</th>
                <th class="text-center">{{ translate('Commission Fee') }}</th>
                <th class="text-center">{{ translate('Created Date') }}</th>
                <th class="text-end no-sort">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sellerLevels as $sellerLevel)
                <tr>
                    <td class="no-export">
                        <input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="{{ $sellerLevel->id }}">
                    </td>
                    <td>
                       <div class="d-flex align-items-center gap-3">
                            <div class="image-fluid image-sm rounded">
                                @if ($sellerLevel->icon)
                                    <img src="{{ $sellerLevel->icon_url }}" alt="{{ $sellerLevel->name }}">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-muted rounded-circle">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <span role="button" class="text-reset fw-semibold edit-seller-level-btn" data-id="{{ $sellerLevel->id }}">
                                    {{ $sellerLevel->name }}
                                </span>
                                @if ($sellerLevel->isDefault())
                                    <span class="badge bg-primary ms-2">{{ translate('Default') }}</span>
                                @endif
                            </div>
                       </div>
                    </td>
                    <td class="text-center">
                        {{ getAmount($sellerLevel->min_earnings ?? 0) }}
                    </td>
                    <td class="text-center">
                       {{ $sellerLevel->fees }}%
                    </td>
                    <td class="text-center text-muted">{{ dateFormat($sellerLevel->created_at) }}</td>
                    <td class="text-end">
                        <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                            <x-dropdown.item
                                type="button"
                                icon="bi bi-pencil-square"
                                iconClass="text-primary me-2"
                                class="edit-seller-level-btn"
                                data-id="{{ $sellerLevel->id }}">
                                {{ translate('Edit Details') }}
                            </x-dropdown.item>
                            @if (!$sellerLevel->isDefault())
                                <x-dropdown.item type="divider" />
                                <x-dropdown.item
                                    href="{{ route('admin.settings.seller-levels.destroy', $sellerLevel->id) }}"
                                    icon="bi bi-trash"
                                    color="danger"
                                    class="action-confirm"
                                    data-method="DELETE"
                                    data-confirm="{{ translate('Are you sure you want to delete this seller level? This action cannot be undone.') }}">
                                    {{ translate('Delete') }}
                                </x-dropdown.item>
                            @endif
                        </x-dropdown>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>

    {{-- Create Level Modal --}}
   @include('admin.settings.seller-levels.partials.create-modal')

    {{-- Edit Level Modal --}}
    <x-modal id="editSellerLevelModal" :title="translate('Edit Seller Level')" :icon="'bi bi-trophy'" :scrollable="true">
        <x-loader id="modalLoader" centered />
        <div id="modalContent" class="d-none"></div>

         <x-slot:footer>
            <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}
            </button>
            <button type="submit" id="editSellerLevelBtn" form="editSellerLevelForm" class="btn btn-primary flex-fill">
                <i class="bi bi-check-circle me-2"></i> {{ translate('Save Changes') }}
            </button>
        </x-slot>
    </x-modal>

    {{-- Seller Levels Info Section --}}
    <div class="card mt-4 border-info shadow-none">
        <div class="card-header bg-info bg-opacity-10 border-info border-opacity-25">
            <h6 class="mb-0 text-info">
                <i class="bi bi-info-circle me-2"></i>{{ translate('Understanding Seller Levels') }}
            </h6>
        </div>
        <div class="card-body bg-info bg-opacity-10">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="d-flex align-items-start">
                        <div class="fs-3 text-info me-3">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">{{ translate('Progression System') }}</h6>
                            <p class="text-muted small mb-0">
                                {{ translate('Sellers automatically advance to higher levels when they reach the minimum earnings requirement.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-start">
                        <div class="fs-3 text-info me-3">
                            <i class="bi bi-percent"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">{{ translate('Commission Fees') }}</h6>
                            <p class="text-muted small mb-0">
                                {{ translate('Each level has its own commission rate. Reward successful sellers with lower fees.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-start">
                        <div class="fs-3 text-info me-3">
                            <i class="bi bi-house"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">{{ translate('Default Level') }}</h6>
                            <p class="text-muted small mb-0">
                                {{ translate('The default level is assigned to new sellers. It cannot be deleted or have earnings modified.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-start">
                        <div class="fs-3 text-info me-3">
                            <i class="bi bi-award"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">{{ translate('Level Badges') }}</h6>
                            <p class="text-muted small mb-0">
                                {{ translate('Levels can have associated badges that appear on seller profiles to showcase their achievement.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('top_scripts')
    <script>
        "use strict";
        config.translates.searchPlaceholder = "{{ translate('Search Seller Levels') }}";
    </script>
@endpush

@push('scripts_libs')
    <script>
        const tableElement = document.getElementById('sellerLevelsTable');
        if (tableElement) {
             const bulkActions = [
                {
                    text: '<i class="bi bi-trash text-danger me-2"></i>{{ translate("Delete Selected") }}',
                    className: 'dropdown-item text-danger',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.settings.seller-levels.bulk-delete') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected levels?') }}"
                        });
                    }
                }
            ];
            $(tableElement).data('bulk-actions', bulkActions);

            const customButtons = [
                {
                    text: '<i class="bi bi-plus-lg me-1"></i> {{ translate("Create Level") }}',
                    className: 'btn btn-primary',
                    action: function(e, dt, node, config) {
                        $('#createSellerLevelModal').modal('show');
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
            triggerSelector: '.edit-seller-level-btn',
            modalId: 'editSellerLevelModal',
            contentId: 'modalContent',
            urlBuilder: (id) => `{{ url('admin/settings/seller-levels') }}/${id}/edit-modal`,
            errorMessage: '{{ translate("Failed to load level details") }}',
            iconClass: 'bi-trophy'
        });

        // Initialize AJAX form handler for edit (supports dynamically loaded forms)
        initAjaxModalForm({
            formSelector: '#editSellerLevelForm',
            modalSelector: '#editSellerLevelModal',
            submitButtonSelector: '#editSellerLevelBtn',
            loadingText: '{{ translate("Saving...") }}',
            useDelegation: true
        });

        // Initialize create modal form
        initAjaxModalForm({
            formSelector: '#createSellerLevelForm',
            modalSelector: '#createSellerLevelModal',
            submitButtonSelector: '#createSellerLevelBtn',
            loadingText: '{{ translate("Creating...") }}',
        });
    </script>
@endpush


















