@extends('admin.layouts.full')
@section('section', translate('Premium'))
@section('title', translate('Membership'))
@section('content')
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card counter-card">
                <div class="card-body">
                    <div class="card-info">
                       <div>
                            <h6 class="card-title">
                                {{ translate('Sessions') }}
                            </h6>
                            @php
                                $percent = $counters['total_members_percent'];
                                $isPositive = $percent >= 0;
                            @endphp
                            <div class="card-count">
                                {{ numberFormat($counters['total_members']) }}
                                <span class="count-percent {{ $isPositive ? 'count-positive' : 'count-negative' }}">
                                    ({{ $isPositive ? '+' : '-' }}{{ $percent }}%)
                                </span>
                            </div>
                       </div>
                       <div class="card-icon bg-text-primary">
                            <a href="{{ route('admin.premium.members.index') }}" class="text-reset">
                                <i class="bi bi-people"></i>
                            </a>
                        </div>
                    </div>
                    <label class="card-label">{{ translate('Total Members') }}</label>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card counter-card">
                <div class="card-body">
                    <div class="card-info">
                       <div>
                            <h6 class="card-title">
                                {{ translate('Active') }}
                            </h6>
                            @php
                                $percent = $counters['active_members_percent'];
                                $isPositive = $percent >= 0;
                            @endphp
                            <div class="card-count">
                                {{ numberFormat($counters['active_members']) }}
                                <span class="count-percent {{ $isPositive ? 'count-positive' : 'count-negative' }}">
                                    ({{ $isPositive ? '+' : '-' }}{{ $percent }}%)
                                </span>
                            </div>
                       </div>
                       <div class="card-icon bg-text-green">
                            <a href="{{ route('admin.premium.members.index', ['status' => '1']) }}" class="text-reset">
                                <i class="bi bi-check-circle"></i>
                            </a>
                        </div>
                    </div>
                    <label class="card-label">{{ translate('Last week analytics') }}</label>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card counter-card">
                <div class="card-body">
                    <div class="card-info">
                       <div>
                            <h6 class="card-title">
                                {{ translate('On Hold') }}
                            </h6>
                            @php
                                $percent = $counters['on_hold_members_percent'];
                                $isPositive = $percent >= 0;
                            @endphp
                            <div class="card-count">
                                {{ numberFormat($counters['on_hold_members']) }}
                                <span class="count-percent {{ $isPositive ? 'count-positive' : 'count-negative' }}">
                                    ({{ $isPositive ? '+' : '-' }}{{ $percent }}%)
                                </span>
                            </div>
                       </div>
                       <div class="card-icon bg-text-orange">
                            <a href="{{ route('admin.premium.members.index', ['status' => '3']) }}" class="text-reset">
                                <i class="bi bi-pause-circle"></i>
                            </a>
                        </div>
                    </div>
                    <label class="card-label">{{ translate('Last week analytics') }}</label>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
             <div class="card counter-card">
                <div class="card-body">
                    <div class="card-info">
                       <div>
                            <h6 class="card-title">
                                {{ translate('Expiring Soon') }}
                            </h6>
                            @php
                                $percent = $counters['expiring_soon_percent'];
                                $isPositive = $percent >= 0;
                            @endphp
                            <div class="card-count">
                                {{ numberFormat($counters['expiring_soon']) }}
                                <span class="count-percent {{ $isPositive ? 'count-positive' : 'count-negative' }}">
                                    ({{ $isPositive ? '+' : '-' }}{{ $percent }}%)
                                </span>
                            </div>
                       </div>
                       <div class="card-icon bg-text-red">
                            <a href="{{ route('admin.premium.members.index', ['status' => '2']) }}" class="text-reset">
                                <i class="bi bi-clock-history"></i>
                            </a>
                        </div>
                    </div>
                    <label class="card-label">{{ translate('Last week analytics') }}</label>
                </div>
            </div>
        </div>
    </div>
    <x-datatable
        id="premiumsTable"
        :items="$premiums"
        tableClass="datatable2"
        emptyMessage="{{ translate('No premium members found!') }}"
        emptyDescription="{{ translate('Premium memberships will appear here when users subscribe to plans') }}"
        emptyIcon="bi-star"
    >
        <thead>
            <tr>
                <th class="no-sort no-export">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('Member Details') }}</th>
                <th class="text-center">{{ translate('Plan') }}</th>
                <th class="text-center">{{ translate('Downloads') }}</th>
                <th class="text-center">{{ translate('Membership Date') }}</th>
                <th class="text-center">{{ translate('Expiry Date') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($premiums as $premium)
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $premium->id }}">
                    </td>
                    <td>
                        <div class="user-box d-flex align-items-center gap-3">
                            <a class="image-fluid image-md rounded transition-all"
                                href="{{ route('admin.roles.users.edit', $premium->user->id) }}">
                                <img src="{{ $premium->user->avatar_url }}" alt="{{ $premium->user->username }}">
                            </a>
                            <span>
                                <a class="text-reset hover-primary fw-medium transition-all"
                                    href="{{ route('admin.roles.users.edit', $premium->user->id) }}">{{ $premium->user->full_name }}</a>
                                <p class="text-muted small mb-0">
                                    {{ hideInDemo($premium->user->email) }}
                                </p>
                            </span>
                        </div>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.premium.plans.index', ['premium_plan' => $premium->plan->id]) }}"
                            class="text-dark">
                            {{ translate(':plan_name (:plan_interval)', [
                                'plan_name' => $premium->plan->name,
                                'plan_interval' => $premium->plan->interval_name,
                            ]) }}
                        </a>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-text-primary">{{ $premium->total_downloads }} / {{ $premium->plan->download_label }}</span>
                    </td>
                    <td class="text-center text-muted">{{ dateFormat($premium->created_at) }}</td>
                    <td class="text-center text-muted">{{ dateFormat($premium->expiry_at) }}</td>
                    <td class="text-center">
                        {!! $premium->status_badge !!}
                    </td>
                    <td class="text-end">
                        <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                            <x-dropdown.item
                                href="#"
                                data-bs-toggle="modal"
                                data-bs-target="#premiumDetailsModal-{{ $premium->id }}"
                                icon="bi bi-eye"
                                iconClass="text-primary me-1">
                                {{ translate('View Details') }}
                            </x-dropdown.item>
                            @if(authAdmin()->canManageSystem())
                            @if ($premium->isActive() || $premium->isAboutToExpire())
                                <x-dropdown.item
                                    href="{{ route('admin.premium.members.hold', $premium->id) }}"
                                    icon="bi bi-pause-circle"
                                    iconClass="text-orange me-1"
                                    class="action-confirm"
                                    data-method="POST"
                                    data-confirm="{{ translate('Are you sure you want to put this premium membership on hold?') }}">
                                    {{ translate('Put On Hold') }}
                                </x-dropdown.item>
                            @elseif ($premium->isOnHold())
                                <x-dropdown.item
                                    href="{{ route('admin.premium.members.unhold', $premium->id) }}"
                                    icon="bi bi-play-circle"
                                    iconClass="text-success me-1"
                                    class="action-confirm"
                                    data-method="POST"
                                    data-confirm="{{ translate('Are you sure you want to resume this premium membership?') }}">
                                    {{ translate('Resume Membership') }}
                                </x-dropdown.item>
                            @endif
                            <x-dropdown.item type="divider" />
                            <x-dropdown.item
                                href="{{ route('admin.premium.members.cancel', $premium->id) }}"
                                icon="bi bi-x-circle"
                                color="danger"
                                class="action-confirm"
                                data-method="POST"
                                data-confirm="{{ translate('Are you sure you want to cancel this premium membership? This action cannot be undone.') }}">
                                {{ translate('Cancel Membership') }}
                            </x-dropdown.item>
                            @endif
                        </x-dropdown>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>

    @foreach ($premiums as $premium)
        @include('admin.premium.members.details-modal', ['premium' => $premium])
    @endforeach

@endsection
@push('scripts_libs')
    <script>
        "use strict";
        config.translates.searchPlaceholder = "{{ translate('Search Premium Members') }}";

        const tableElement = document.getElementById('premiumsTable');
        if (tableElement) {
            const bulkActions = [
                {
                    text: '<i class="bi bi-pause-circle text-orange me-2"></i>{{ translate("Hold Selected") }}',
                    className: 'dropdown-item',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.premium.members.bulk-hold') }}",
                            confirmMessage: "{{ translate('Are you sure you want to put the selected premium memberships on hold?') }}"
                        });
                    }
                },
                {
                    text: '<i class="bi bi-play-circle text-success me-2"></i>{{ translate("Resume Selected") }}',
                    className: 'dropdown-item',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.premium.members.bulk-resume') }}",
                            confirmMessage: "{{ translate('Are you sure you want to resume the selected premium memberships?') }}"
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
                            url: "{{ route('admin.premium.members.bulk-delete') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected premium memberships?') }}"
                        });
                    }
                }
            ];
            @if(authAdmin()->canManageSystem())
                $(tableElement).data('bulk-actions', bulkActions);
            @endif
        }
    </script>
@endpush
