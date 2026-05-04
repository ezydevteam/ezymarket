@extends('admin.layouts.app')
@section('section', translate('Records'))
@section('title', translate('Referral Earnings'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.records.referral-earnings.partials.counters')

    <x-datatable
        id="referralEarningsTable"
        :items="$referralCount"
        :server-side="true"
        :ajax-url="route('admin.records.referral-earnings.index', request()->query())"
        :columns="$columns"
        :filters="$filters"
        :export="true"
        :title="translate('Referral Earnings')"
        :description="translate('Referral earnings will appear here when referred users make purchases')"
        :search-placeholder="translate('Search Referral Earnings...')"
        empty-title="No referral earnings found!"
        empty-desc="Referral earnings will appear here when referred users make purchases"
        empty-icon="bi-people"
        :bulk-delete-btn="[
            'text' => translate('Delete Selected'),
            'url' => route('admin.records.referral-earnings.bulk-delete'), 
            'confirm' => translate('Are you sure you want to delete the selected records?'),
        ]"
    />

    @if(request()->filled('id'))
        <x-modal
            id="earningDetailsModal-{{ request()->query('id') }}"
            :header="false"
            autoOpen="true"
            :data-action="route('admin.records.referral-earnings.details.modal', request()->query('id'))"
        />
    @else
        <x-modal id="earningDetailsModal" :header="false" />
    @endif
@endsection
