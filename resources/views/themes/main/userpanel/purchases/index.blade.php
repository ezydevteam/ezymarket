@extends('themes.main.userpanel.layout')
@section('title', translate('My Purchases'))
@section('container', 'userpanel-container-xl')

@section('content')
    @if ($hasRecords)
        @section('header_title', translate('My Purchases'))
        @section('description', translate('Manage your digital products and licenses.'))

        <x-datatable id="purchasesTable" :ajax-url="route('user.purchase.index')" :columns="$columns" :filters="$filters"
            :server-side="true" data-export="true" :bulk-actions="[]" search-placeholder="{{ translate('Search purchases...') }}">
            <thead>
                <tr>
                    <th>{{ translate('Product') }}</th>
                    <th class="text-center">{{ translate('License') }}</th>
                    <th class="text-center">{{ translate('Support') }}</th>
                    <th class="text-center">{{ translate('Date') }}</th>
                    <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                {{-- DataTables will load data via AJAX --}}
            </tbody>
        </x-datatable>
    @else
        @themeInclude('userpanel.partials.empty', [
            'title' => 'No Purchases Yet!',
            'description' => 'Your digital products will appear here once you make your first purchase on our marketplace.',
            'icon' => 'cart-x',
            'btn_text' => 'Explore Marketplace',
        ])
    @endif

    {{-- Empty Modal Shells for AJAX Loading --}}
    <x-modal id="purchaseCodeModal" :header="false" />
    <x-modal id="supportModal" :header="false" />

    @if (@settings('actions')->refunds)
        <x-modal id="createRefundModal" :header="false" />
    @endif
@endsection
