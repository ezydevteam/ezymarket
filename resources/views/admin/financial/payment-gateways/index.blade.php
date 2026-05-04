@extends('admin.layouts.full')
@section('section', translate('Financial'))
@section('title', translate('Payment Gateways'))
@section('container', 'container-max-xxl')
@section('content')
    <x-datatable
        id="paymentGatewaysTable"
        :items="$paymentGateways"
        tableClass="datatable2"
        emptyMessage="{{ translate('No payment gateways found!') }}"
        emptyDescription="{{ translate('No payment gateways available') }}"
        emptyIcon="bi-credit-card"
    >
        <thead>
            <tr>
                <th class="no-sort">
                    <i class="bi bi-sort-down fs-6"></i>
                </th>
                <th>{{ translate('Gateway Name') }}</th>
                <th class="text-center">{{ translate('Processing Fee') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-end no-sort">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="sortable-list">
            @foreach ($paymentGateways as $paymentGateway)
                <tr data-id="{{ $paymentGateway->id }}">
                    <td>
                        <span class="sortable-list-handle text-muted">
                            <i class="bi bi-arrows-move"></i>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="image-container">
                                <img src="{{ $paymentGateway->logo_link }}"
                                    alt="{{ $paymentGateway->alias }}"
                                    height="35px" width="90px"
                                    class="rounded-3 object-fit-contain">
                            </div>
                            <div>
                                <h6 class="mb-0">
                                    <span role="button" data-bs-toggle="modal" data-bs-target="#editPaymentGatewayModal-{{ $paymentGateway->id }}">
                                        {{ $paymentGateway->name }}
                                        @if ($paymentGateway->mode)
                                            <small class="text-info">
                                                {{ ($paymentGateway->isSandboxMode()) ? translate('Sandbox') : translate('Live') }}
                                            </small>
                                        @endif
                                    </span>
                                </h6>
                                @if ($paymentGateway->instructions)
                                    <small class="text-muted">
                                        {{ truncateText($paymentGateway->instructions, 50) }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-center text-primary">
                        {!! $paymentGateway->fees ? $paymentGateway->fees . '%' : '<span class="text-muted">' . translate('N/A') . '</span>' !!}
                    </td>
                    <td class="text-center">
                        @php
                            $statusBool = $paymentGateway->isActive();
                        @endphp
                        <span class="badge bg-text-{{ $statusBool ? 'green' : 'red' }}">
                            <i class="bi bi-{{ $statusBool ? 'check' : 'x' }}-circle me-1"></i>{{ $statusBool ? translate('Active') : translate('Inactive') }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn-icon text-muted" data-bs-toggle="modal" data-bs-target="#editPaymentGatewayModal-{{ $paymentGateway->id }}" title="{{ translate('Configure') }}">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </td>
                </tr>
                @include('admin.financial.payment-gateways.partials.edit-modal', ['paymentGateway' => $paymentGateway])
            @endforeach
        </tbody>
    </x-datatable>
@endsection

@push('top_scripts')
    <script>
        const sortingRoute = "{{ route('admin.financial.payment-gateways.sortable') }}";
        config.translates.searchPlaceholder = "{{ translate('Search Payment Gateways') }}";
    </script>
@endpush

@push('styles_libs')
    <link href="{{ asset('vendor/libs/jquery/jquery-ui.min.css') }}" />
@endpush

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>
@endpush

@push('scripts')
    <script>
        "use strict";
        $(document).ready(function() {
            // Ajax Handle Edit Payment Gateway
            @foreach ($paymentGateways as $paymentGateway)
                initAjaxModalForm({
                    formSelector: '#editPaymentGatewayForm-{{ $paymentGateway->id }}',
                    modalSelector: '#editPaymentGatewayModal-{{ $paymentGateway->id }}',
                    submitButtonSelector: '#editPaymentGatewayBtn-{{ $paymentGateway->id }}',
                    loadingText: '{{ translate("Updating...") }}'
                });
            @endforeach
        });
    </script>
@endpush
















