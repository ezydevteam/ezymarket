<x-modal
    id="premiumDetailsModal-{{ $premium->id }}"
    title="{{ translate('Premium Membership Details') }}"
    size="md"
    icon="bi-award"
    scrollable="true"
>
    <div class="list-group list-group-flush">
        <div class="list-group-item px-0 pb-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <strong><i class="bi bi-hash me-2 text-muted"></i>{{ translate('Membership ID') }}</strong>
                </div>
                <div class="col-auto">
                    #{{ $premium->id }}
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <strong><i class="bi bi-person me-2 text-muted"></i>{{ translate('User') }}</strong>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.roles.users.edit', $premium->user->id) }}" class="text-dark" target="_blank">
                        <img src="{{ $premium->user->avatar_url }}" alt="{{ $premium->user->username }}" class="rounded-circle me-1" width="20" height="20">
                        {{ $premium->user->full_name }}
                    </a>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <strong><i class="bi bi-box me-2 text-muted"></i>{{ translate('Plan') }}</strong>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.premium.plans.index', ['premium_plan' => $premium->plan->id]) }}" class="text-dark" target="_blank">
                        {{ $premium->plan->name }}
                        <small class="text-muted">({{ $premium->plan->interval_name }})</small>
                    </a>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <strong><i class="bi bi-box me-2 text-muted"></i>{{ translate('Plan Price') }}</strong>
                </div>
                <div class="col-auto">
                    {{ $premium->plan->price_label }}
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <strong><i class="bi bi-download me-2 text-muted"></i>{{ translate('Downloads') }}</strong>
                </div>
                <div class="col-auto">
                    @if ($premium->plan->hasUnlimitedDownloads())
                        <span class="badge bg-success"><i class="bi bi-infinity me-1"></i>{{ translate('Unlimited') }}</span>
                    @else
                        <span class="badge bg-text-primary">{{ $premium->total_downloads }} /
                            {{ $premium->plan->download_label }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <strong><i class="bi bi-calendar-check me-2 text-muted"></i>{{ translate('Membership Date') }}</strong>
                </div>
                <div class="col-auto">
                    {{ dateFormat($premium->created_at) }}
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <strong><i class="bi bi-calendar-x me-2 text-muted"></i>{{ translate('Expiry Date') }}</strong>
                </div>
                <div class="col-auto">
                    {{ dateFormat($premium->expiry_at) }}
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 pt-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <strong><i class="bi bi-info-circle me-2 text-muted"></i>{{ translate('Status') }}</strong>
                </div>
                <div class="col-auto">
                    {!! $premium->status_badge !!}
                </div>
            </div>
        </div>
    </div>

    @if(authAdmin()->canManageSystem())
    <x-slot name="footer">
        <div class="d-flex align-items-center gap-3">
            <form id="cancelForm-{{ $premium->id }}" action="{{ route('admin.premium.members.cancel', $premium->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn bg-text-red hover-opacity action-confirm " data-confirm="{{ translate('Are you sure you want to cancel this premium membership? This action cannot be undone.') }}">
                    <i class="bi bi-x-circle me-1"></i>
                    {{ translate('Cancel Membership') }}
                </button>
            </form>
            @if ($premium->isActive() || $premium->isAboutToExpire())
            <form id="holdForm-{{ $premium->id }}" action="{{ route('admin.premium.members.hold', $premium->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn bg-orange action-confirm" data-confirm="{{ translate('Are you sure you want to put this premium membership on hold?') }}">
                    <i class="bi bi-pause-circle me-1"></i>
                    {{ translate('Put On Hold') }}
                </button>
            </form>
            @elseif ($premium->isOnHold())
            <form id="unholdForm-{{ $premium->id }}" action="{{ route('admin.premium.members.unhold', $premium->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success action-confirm" data-confirm="{{ translate('Are you sure you want to resume this premium membership?') }}">
                    <i class="bi bi-play-circle me-1"></i>
                    {{ translate('Resume Membership') }}
                </button>
            </form>
            @endif
        </div>
    </x-slot>
    @endif
</x-modal>
