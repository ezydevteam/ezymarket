@extends('themes.main.userpanel.layout')
@section('title', translate('Transactions'))
@section('container', 'userpanel-container-xl')

@section('content')
    @if ($hasRecords)
        @section('header_title', translate('Transactions'))
        @section('description', translate('Keep track of all your financial activities and payments.'))

        <x-datatable id="transactionsTable" :ajax-url="route('user.transaction.index')" :server-side="true" :bulk-actions="[]"
            data-export="true" :columns="$columns" :filters="$filters" search-placeholder="{{ translate('Search transactions...') }}">
            <thead>
                <tr>
                    <th>{{ translate('Transaction') }}</th>
                    <th class="text-center">{{ translate('SubTotal') }}</th>
                    <th class="text-center">{{ translate('Tax') }}</th>
                    <th class="text-center">{{ translate('Fees') }}</th>
                    <th class="text-center">{{ translate('Total') }}</th>
                    <th class="text-center">{{ translate('Type') }}</th>
                    <th class="text-center">{{ translate('Status') }}</th>
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
            'title' => translate('No Transactions Yet!'),
            'description' => translate('Your transactions will appear here once you make your first transaction on our
                marketplace.'),
            'icon' => 'receipt',
            'btn_text' => translate('Explore Marketplace'),
            'btn_link' => route('home'),
        ])
    @endif

    <x-modal id="transactionDetailsModal" :header="false" />
@endsection
