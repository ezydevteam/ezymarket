@extends('admin.layouts.full')
@section('section', translate('Financial'))
@section('title', translate('Currencies'))
@section('container', 'container-max-xxl')
@section('content')
    <x-datatable
        id="currenciesTable"
        :items="$currencies"
        tableClass="datatable2"
        emptyMessage="{{ translate('No currencies found!') }}"
        emptyDescription="{{ translate('Start by adding your first currency') }}"
        emptyIcon="bi-currency-exchange"
        emptyButton="{{ translate('Add New Currency') }}"
        emptyButtonModal="createCurrencyModal"
    >
        <thead>
            <tr>
                <th>
                    <i class="bi bi-sort-down fs-6" title="{{ translate('Drag to reorder') }}"></i>
                </th>
                <th>{{ translate('Currency Icon') }}</th>
                <th>{{ translate('Location') }}</th>
                <th class="text-center">{{ translate('Code') }}</th>
                <th class="text-center">{{ translate('Symbol') }}</th>
                <th class="text-center">{{ translate('Exchange Rate') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-end">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="sortable-list">
            @forelse ($currencies as $currency)
                <tr data-id="{{ $currency->id }}">
                    <td>
                        <span class="sortable-list-handle cursor-move">
                            <i class="bi bi-arrows-move text-muted"></i>
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="image-fluid image-md rounded"
                            role="button"
                            data-bs-toggle="modal"
                            data-bs-target="#editCurrencyModal-{{ $currency->id }}">
                            <img src="{{ $currency->icon_link }}" alt="{{ $currency->code }}">
                        </div>
                    </td>
                        <td>
                        {{ $currency->country }}
                    </td>
                    <td class="text-center">
                        {{ $currency->code }}
                    </td>
                    <td class="text-center">
                        {{ $currency->symbol }}
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark">
                            {{ numberFormat($currency->rate, 4) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if ($currency->isDefault())
                            <span class="badge bg-text-primary">
                                <i class="bi bi-star me-1"></i>{{ translate('Default') }}
                            </span>
                        @else
                            <span class="badge bg-text-green">
                                    <i class="bi bi-check-circle me-1"></i>{{ translate('Active') }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="text-end">
                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                <x-dropdown.item
                                    href="#"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editCurrencyModal-{{ $currency->id }}"
                                    icon="bi bi-pencil-square"
                                    iconClass="text-primary me-2">
                                    {{ translate('Edit Details') }}
                                </x-dropdown.item>
                                @if (!$currency->isDefault())
                                    <x-dropdown.item
                                        href="{{ route('admin.financial.currencies.default', $currency->id) }}"
                                        icon="bi bi-star"
                                        iconClass="text-orange me-2"
                                        class="action-confirm"
                                        data-method="POST"
                                        data-confirm="{{ translate('Are you sure want to make :currency as default currency?', ['currency' => $currency->code]) }}">
                                        {{ translate('Set as Default') }}
                                    </x-dropdown.item>
                                    <x-dropdown.item type="divider" />
                                    <x-dropdown.item
                                        href="{{ route('admin.financial.currencies.destroy', $currency->id) }}"
                                        icon="bi bi-trash"
                                        color="danger"
                                        class="action-confirm"
                                        data-method="DELETE"
                                        data-confirm="{{ translate('Are you sure want to delete this currency? This action can not be undone.') }}">
                                        {{ translate('Delete') }}
                                    </x-dropdown.item>
                                @endif
                            </x-dropdown>
                        </div>
                    </td>
                </tr>

                {{-- Include edit modal for each currency --}}
                @include('admin.financial.currencies.partials.edit-modal', ['currency' => $currency])
            @endforeach
        </tbody>
    </x-datatable>

    {{-- Include create modal --}}
    @include('admin.financial.currencies.partials.create-modal')
@endsection

@push('styles_libs')
    <link href="{{ asset('vendor/libs/jquery/jquery-ui.min.css') }}" />
@endpush
@push('top_scripts')
    <script>
        const sortingRoute = "{{ route('admin.financial.currencies.sortable') }}";
        config.translates.searchPlaceholder = "{{ translate('Search Currencies') }}";
    </script>
@endpush

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>
    <script>
        // Setup custom buttons for currencies table
        (function() {
            const tableElement = document.getElementById('currenciesTable');
            if (tableElement) {
                const customButtons = [{
                    text: '<i class="bi bi-plus-lg me-1"></i> {{ translate("Add Currency") }}',
                    className: 'btn btn-primary',
                    action: function(e, dt, node, config) {
                        $('#createCurrencyModal').modal('show');
                    }
                }];
                $(tableElement).data('custom-buttons', customButtons);
            }
        })();
    </script>
@endpush

@push('scripts')
    <script>
        "use strict";
        $(document).ready(function() {

            // Initialize create modal form
            initAjaxModalForm({
                modalSelector: '#createCurrencyModal',
                formSelector: '#createCurrencyForm',
                submitButtonSelector: '#createCurrencyBtn',
                loadingText: "{{ translate('Creating...') }}"
            });

            // Initialize edit modal forms for all currencies
            @foreach($currencies as $currency)
                initAjaxModalForm({
                    modalSelector: '#editCurrencyModal-{{ $currency->id }}',
                    formSelector: '#editCurrencyForm-{{ $currency->id }}',
                    submitButtonSelector: '#editCurrencyBtn-{{ $currency->id }}',
                    loadingText: "{{ translate('Updating...') }}"
                });
            @endforeach
        });
    </script>
@endpush
















