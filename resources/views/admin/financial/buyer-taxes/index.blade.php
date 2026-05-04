@extends('admin.layouts.full')
@section('section', translate('Financial'))
@section('title', translate('Taxes'))
@section('content')
<div class="card">
    <div class="card-body px-0 pt-0 pb-3">
        {{-- Nav Tabs --}}
        <ul class="nav nav-tabs ezydev-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $type == 'buyer' ? 'active' : '' }}" id="buyer-taxes-tab"
                    data-bs-toggle="tab" data-bs-target="#buyer-taxes-content" data-tab="buyer-taxes"
                    data-url="{{ route('admin.financial.buyer-taxes.index') }}" type="button" role="tab">
                    <i class="bi bi-bag-plus me-2"></i>
                    {{ translate('Buyer Taxes') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $type == 'seller' ? 'active' : '' }}" id="seller-taxes-tab"
                    data-bs-toggle="tab" data-bs-target="#seller-taxes-content" data-tab="seller-taxes"
                    data-url="{{ route('admin.financial.seller-taxes.index') }}" type="button" role="tab">
                    <i class="bi bi-bag-check me-2"></i>
                    {{ translate('Seller Taxes') }}
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade {{ $type == 'buyer' ? 'show active' : '' }}" id="buyer-taxes-content"
                role="tabpanel">
                <x-datatable id="buyerTaxesTable" :items="$buyerTaxes" tableClass="datatable2"
                    emptyMessage="{{ translate('No buyer taxes found!') }}"
                    emptyDescription="{{ translate('Create your first buyer tax to get started') }}"
                    emptyIcon="bi-bag-plus">
                    <thead>
                        <tr>
                            <th class="no-sort">
                                <input type="checkbox" class="form-check-input bulk-select-checkbox">
                            </th>
                            <th>{{ translate('Name') }}</th>
                            <th>{{ translate('Effective Countries') }}</th>
                            <th class="text-center">{{ translate('Tax Rate') }}</th>
                            <th class="text-center">{{ translate('Created Date') }}</th>
                            <th class="text-end no-sort">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($buyerTaxes as $tax)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input row-checkbox" name="ids[]"
                                    value="{{ $tax->id }}">
                            </td>
                            <td>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#editBuyerTaxModal-{{ $tax->id }}"
                                    class="text-dark">
                                    {{ $tax->name }}
                                </a>
                            </td>
                            <td>
                                @if (count($tax->countries) > 3)
                                {{ translate(':count Countries', ['count' => count($tax->countries)]) }}
                                @else
                                {{ implode(
                                ', ',
                                array_map(function ($country) {
                                return countries($country);
                                }, $tax->countries),
                                ) }}
                                @endif
                            </td>
                            <td class="text-center text-primary">{{ $tax->percentage }}%</td>
                            <td class="text-center text-muted">{{ dateFormat($tax->created_at) }}</td>
                            <td>
                                <div class="text-end">
                                    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                        <x-dropdown.item href="#" data-bs-toggle="modal"
                                            data-bs-target="#editBuyerTaxModal-{{ $tax->id }}"
                                            icon="bi bi-pencil-square" iconClass="text-primary me-2">
                                            {{ translate('Edit Details') }}
                                        </x-dropdown.item>
                                        <x-dropdown.item type="divider" />
                                        <x-dropdown.item
                                            href="{{ route('admin.financial.buyer-taxes.destroy', $tax->id) }}"
                                            icon="bi bi-trash" color="danger" class="action-confirm"
                                            data-method="DELETE"
                                            data-confirm="{{ translate('Are you sure to delete this tax? This action cannot be undone.') }}">
                                            {{ translate('Delete') }}
                                        </x-dropdown.item>
                                    </x-dropdown>
                                </div>
                            </td>
                        </tr>

                        {{-- Include Edit Modal for each tax --}}
                        @include('admin.financial.buyer-taxes.partials.edit-modal', ['tax' => $tax])
                        @endforeach
                    </tbody>
                </x-datatable>
            </div>

            <div class="tab-pane fade {{ $type == 'seller' ? 'show active' : '' }}" id="seller-taxes-content"
                role="tabpanel">
                @include('admin.financial.seller-taxes.view', ['sellerTaxes' => $sellerTaxes])
            </div>
        </div>
    </div>
</div>

{{-- Tax Guidelines --}}
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="bi bi-info-circle text-info me-2"></i>
            {{ translate('Tax Guidelines') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6 class="text-primary">
                    <i class="bi bi-bag-plus me-2"></i>{{ translate('Buyer Taxes') }}
                </h6>
                <ul class="small mb-0">
                    <li class="mb-2">
                        {{ translate('Buyer taxes are applied to purchases made by customers on the platform.') }}
                    </li>
                    <li class="mb-2">
                        {{ translate('These taxes are added to the final price that buyers pay at checkout.') }}
                    </li>
                    <li class="mb-2">
                        {{ translate('You can configure different tax rates for different countries.') }}
                    </li>
                    <li class="mb-2">
                        {{ translate('If no country is specified, the tax applies globally.') }}
                    </li>
                </ul>
            </div>
            <div class="col-md-6 mb-3">
                <h6 class="text-primary">
                    <i class="bi bi-bag-check me-2"></i>{{ translate('Seller Taxes') }}
                </h6>
                <ul class="small mb-0">
                    <li class="mb-2">
                        {{ translate('Seller taxes are deducted from sellers\' earnings on the platform.') }}
                    </li>
                    <li class="mb-2">
                        {{ translate('These taxes are automatically calculated when sellers receive payments.') }}
                    </li>
                    <li class="mb-2">
                        {{ translate('Country-specific tax rates help comply with local tax regulations.') }}
                    </li>
                    <li class="mb-2">
                        {{ translate('Sellers will see the net amount after tax deductions.') }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@include('admin.financial.buyer-taxes.partials.create-modal')
@include('admin.financial.seller-taxes.partials.create-modal')
@endsection

@push('scripts_libs')
<script>
    "use strict";
    config.translates.searchPlaceholder = "{{ translate('Search Taxes') }}";
    (() => {
        // Setup custom buttons for buyer taxes table
        const buyerTableElement = document.getElementById('buyerTaxesTable');
        if (buyerTableElement) {
            const bulkActions = [
                {
                    text: '<i class="bi bi-trash text-danger me-2"></i>{{ translate("Delete Selected") }}',
                    className: 'dropdown-item text-danger',
                    action: function (e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.financial.buyer-taxes.bulk-delete') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected buyer taxes?') }}"
                        });
                    }
                }
            ];
            $(buyerTableElement).data('bulk-actions', bulkActions);

            const buyerCustomButtons = [{
                text: '<i class="bi bi-plus-lg me-1"></i> {{ translate("Create Buyer Tax") }}',
                className: 'btn btn-primary',
                action: function (e, dt, node, config) {
                    $('#createBuyerTaxModal').modal('show');
                }
            }];
            $(buyerTableElement).data('custom-buttons', buyerCustomButtons);
        }

        // Setup custom buttons for seller taxes table
        const sellerTableElement = document.getElementById('sellerTaxesTable');
        if (sellerTableElement) {
            const bulkActions = [
                {
                    text: '<i class="bi bi-trash text-danger me-2"></i>{{ translate("Delete Selected") }}',
                    className: 'dropdown-item text-danger',
                    action: function (e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.financial.seller-taxes.bulk-delete') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected seller taxes?') }}"
                        });
                    }
                }
            ];
            $(sellerTableElement).data('bulk-actions', bulkActions);

            const sellerCustomButtons = [{
                text: '<i class="bi bi-plus-lg me-1"></i> {{ translate("Create Seller Tax") }}',
                className: 'btn btn-primary',
                action: function (e, dt, node, config) {
                    $('#createSellerTaxModal').modal('show');
                }
            }];
            $(sellerTableElement).data('custom-buttons', sellerCustomButtons);
        }
    })();
</script>
@endpush

@push('scripts')
<script>
    "use strict";
    $(document).ready(function () {

        initAjaxModalForm({
            modalSelector: '#createBuyerTaxModal',
            formSelector: '#createBuyerTaxForm',
            submitButtonSelector: '#createBuyerTaxBtn',
            loadingText: '{{ translate("Creating...") }}'
        });
        initAjaxModalForm({
            modalSelector: '#createSellerTaxModal',
            formSelector: '#createSellerTaxForm',
            submitButtonSelector: '#createSellerTaxBtn',
            loadingText: '{{ translate("Creating...") }}'
        });

        // Initialize AJAX forms for edit modals
        @foreach($buyerTaxes as $tax)
        initAjaxModalForm({
            modalSelector: '#editBuyerTaxModal-{{ $tax->id }}',
            formSelector: '#editBuyerTaxForm-{{ $tax->id }}',
            submitButtonSelector: '#editBuyerTaxBtn-{{ $tax->id }}',
            loadingText: '{{ translate("Updating...") }}'
        });
        @endforeach

        @foreach($sellerTaxes as $tax)
        initAjaxModalForm({
            modalSelector: '#editSellerTaxModal-{{ $tax->id }}',
            formSelector: '#editSellerTaxForm-{{ $tax->id }}',
            submitButtonSelector: '#editSellerTaxBtn-{{ $tax->id }}',
            loadingText: '{{ translate("Updating...") }}'
        });
        @endforeach

        // Initialize tab manager
        initTabManager({
            storageKey: 'financialTaxesTab',
            tabSelector: 'button[data-bs-toggle="tab"]',
            tabSuffix: ''
        });

        // Initialize edit forms for seller taxes
        @foreach($sellerTaxes as $tax)
        initAjaxModalForm({
            modalSelector: '#editSellerTaxModal-{{ $tax->id }}',
            formSelector: '#editSellerTaxForm-{{ $tax->id }}',
            submitButtonSelector: '#editSellerTaxBtn-{{ $tax->id }}',
            loadingText: '{{ translate("Updating...") }}'
        });
        @endforeach
    });
</script>
@endpush
