@extends('admin.layouts.full')
@section('section', translate('Financial'))
@section('title', translate('Payout Methods'))
@section('container', 'container-max-xxl')
@section('content')
    <x-datatable
        id="payoutMethodsTable"
        :items="$payoutMethods"
        tableClass="datatable2"
        emptyMessage="{{ translate('No payout methods found!') }}"
        emptyDescription="{{ translate('Create your first payout method to get started') }}"
        emptyIcon="bi-cash-coin"
        emptyButton="{{ translate('New Method') }}"
        emptyButtonModal="createPayoutMethodModal"
    >
        <thead>
            <tr>
                <th class="no-sort no-export">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th class="no-sort no-export">
                    <i class="bi bi-sort-down fs-6"></i>
                </th>
                <th>{{ translate('Payout Method') }}</th>
                <th class="text-center">{{ translate('Min Withdraw') }}</th>
                <th class="text-center">{{ translate('Max Withdraw') }}</th>
                <th class="text-center">{{ translate('Processing Fee') }}</th>
                <th class="text-center">{{ translate('Monthly Limit') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="sortable-list">
            @foreach ($payoutMethods as $payoutMethod)
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="{{ $payoutMethod->id }}">
                    </td>
                    <td>
                        <span class="sortable-list-handle text-muted">
                            <i class="bi bi-arrows-move"></i>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0">
                                    <span role="button" data-bs-toggle="modal" data-bs-target="#editPayoutMethodModal-{{ $payoutMethod->id }}">
                                        {{ $payoutMethod->name }}
                                    </span>
                                </h6>
                                @if($payoutMethod->instructions)
                                    <small class="text-muted">{{ truncateText($payoutMethod->instructions, 50, '...', true) }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        {{ getAmount($payoutMethod->min_amount) }}
                    </td>
                    <td class="text-center">
                        {!! ($payoutMethod->max_amount > 0)
                            ? getAmount($payoutMethod->max_amount)
                            : '<span class="text-muted">' . translate('No limit') . '</span>' !!}
                    </td>
                    <td class="text-center">
                        {!! $payoutMethod->getProcessingFee() ?? '<span class="text-muted">' . translate('N/A') . '</span>' !!}
                    </td>
                    <td class="text-center">
                        @if($payoutMethod->monthly_limits > 0)
                            <span class="text-primary">
                                {{ $payoutMethod->monthly_limits }} {{ translate('per month') }}
                            </span>
                        @else
                            <span class="text-orange">
                                {{ translate('Unlimited') }}
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span role="button" data-bs-toggle="modal" data-bs-target="#editPayoutMethodModal-{{ $payoutMethod->id }}">
                            {!! $payoutMethod->status_badge !!}</span>
                    </td>
                    <td>
                        <div class="text-end">
                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                <x-dropdown.item
                                    href="#"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editPayoutMethodModal-{{ $payoutMethod->id }}"
                                    icon="bi bi-pencil-square"
                                    iconClass="text-primary me-2">
                                    {{ translate('Edit Details') }}
                                </x-dropdown.item>
                                <x-dropdown.item type="divider" />
                                <x-dropdown.item
                                    href="{{ route('admin.financial.payout-methods.destroy', $payoutMethod->id) }}"
                                    icon="bi bi-trash"
                                    color="danger"
                                    class="action-confirm"
                                    data-method="DELETE"
                                    data-confirm="{{ translate('Are you sure you want to delete this payout method? This action cannot be undone.') }}">
                                    {{ translate('Delete') }}
                                </x-dropdown.item>
                            </x-dropdown>
                        </div>
                    </td>
                </tr>
                @include('admin.financial.payout-methods.partials.edit-modal', ['payoutMethod' => $payoutMethod])
            @endforeach
        </tbody>
    </x-datatable>

    @include('admin.financial.payout-methods.partials.create-modal')
@endsection

@push('top_scripts')
    <script>
        "use strict";
        const sortingRoute = "{{ route('admin.financial.payout-methods.sortable') }}";
        config.translates.searchPlaceholder = "{{ translate('Search Payout Methods') }}";
    </script>
@endpush
@push('styles_libs')
    <link href="{{ asset('vendor/libs/jquery/jquery-ui.min.css') }}" />
@endpush
@push('scripts_libs')
    <script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>
    <script>
        "use strict";
        (() => {
            const tableElement = document.getElementById('payoutMethodsTable');
            if (tableElement) {
                const bulkActions = [
                    {
                        text: '<i class="bi bi-check-circle text-success me-2"></i>{{ translate("Active Selected") }}',
                        className: 'dropdown-item',
                        action: function(e, dt, node, config) {
                            bulkAction({
                                url: "{{ route('admin.financial.payout-methods.bulk-active') }}",
                                confirmMessage: "{{ translate('Are you sure you want to activate the selected payout methods?') }}"
                            });
                        }
                    },
                    {
                        text: '<i class="bi bi-x-circle text-danger me-2"></i>{{ translate("Inactive Selected") }}',
                        className: 'dropdown-item',
                        action: function(e, dt, node, config) {
                            bulkAction({
                                url: "{{ route('admin.financial.payout-methods.bulk-inactive') }}",
                                confirmMessage: "{{ translate('Are you sure you want to deactivate the selected payout methods?') }}"
                            });
                        }
                    },
                    {
                        className: 'dropdown-item border-top my-1 p-0',
                    },
                    {
                        text: '<i class="bi bi-trash text-danger me-2"></i>{{ translate("Delete Selected") }}',
                        className: 'dropdown-item text-danger',
                        action: function(e, dt, node, config) {
                            bulkAction({
                                url: "{{ route('admin.financial.payout-methods.bulk-delete') }}",
                                method: 'DELETE',
                                confirmMessage: "{{ translate('Are you sure you want to delete the selected payout methods?') }}"
                            });
                        }
                    }
                ];

                $(tableElement).data('bulk-actions', bulkActions);

                const customButtons = [
                    {
                        text: '<i class="bi bi-plus-lg"></i> {{ translate("New Method") }}',
                        className: 'btn btn-primary',
                        action: function(e, dt, node, config) {
                            $('#createPayoutMethodModal').modal('show');
                        }
                    }
                ];

                $(tableElement).data('custom-buttons', customButtons);
            }
        })();
    </script>
@endpush
@push('scripts')
    <script>
        "use strict";
        $(document).ready(function() {
            // Ajax Handle Create Payout Method
            initAjaxModalForm({
                formSelector: '#createPayoutMethodForm',
                modalSelector: '#createPayoutMethodModal',
                submitButtonSelector: '#createPayoutMethodBtn',
                lodingText: '{{ translate("Creating...") }}'
            });

            // Ajax Handle Edit Payout Method
            @foreach ($payoutMethods as $payoutMethod)
                initAjaxModalForm({
                    formSelector: '#editPayoutMethodForm-{{ $payoutMethod->id }}',
                    modalSelector: '#editPayoutMethodModal-{{ $payoutMethod->id }}',
                    submitButtonSelector: '#editPayoutMethodBtn-{{ $payoutMethod->id }}',
                    lodingText: '{{ translate("Updating...") }}'
                });
            @endforeach
        });

        // Unified Fees Type Handler for Create & Edit Modals
        function handleFeesTypeChange(selectElement) {
            const targetPrefix = selectElement.dataset.targetPrefix;
            const targetHint = selectElement.dataset.targetHint;
            const targetWrapper = selectElement.dataset.targetWrapper;

            const prefix = document.getElementById(targetPrefix);
            const hint = document.getElementById(targetHint);
            const wrapper = document.getElementById(targetWrapper);

            if (!prefix || !hint || !wrapper) return;

            const value = selectElement.value;

            if (value === 'percentage') {
                prefix.textContent = '%';
                hint.textContent = '{{ translate("Enter percentage (e.g., 2.5 for 2.5%)") }}';
                wrapper.style.display = '';
            } else if (value === 'fixed') {
                prefix.textContent = '{{ defaultCurrency()->symbol }}';
                hint.textContent = '{{ translate("Enter fixed amount (e.g., 10 for $10)") }}';
                wrapper.style.display = '';
            } else {
                wrapper.style.display = 'none';
            }
        }

        // Initialize all fee type selects
        document.querySelectorAll('.fees-type-select').forEach(function(select) {
            // Initialize on page load
            handleFeesTypeChange(select);

            // Add change event listener
            select.addEventListener('change', function() {
                handleFeesTypeChange(this);
            });
        });
    </script>
@endpush















