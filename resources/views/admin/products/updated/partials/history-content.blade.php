@forelse ($productHistories as $productHistory)
@php
$seller = $productHistory?->seller;
$admin = $productHistory?->admin;
@endphp
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
            <div class="d-flex align-items-center gap-3">
                @if ($admin)
                @if ($admin?->id === superAdmin()?->id)
                <x-user :user="$admin" :showEmail="false" :roleLabel="translate('Admin')" roleIcon="bi-person-lock"
                    linkRoute="admin.account.index" :directRoute="true" />
                @else
                <x-user :user="$admin" :showEmail="false" :roleLabel="$admin->role_label" roleIcon="bi-person-check"
                    linkRoute="admin.roles.staff.edit" />
                @endif
                @elseif ($seller)
                <x-user :user="$seller" :showEmail="false" :roleLabel="translate('Seller')" />
                @endif
            </div>
            <small class="text-muted">
                <i class="bi bi-clock me-1"></i>{{ dateFormat($productHistory?->created_at) }}
            </small>
        </div>

        <div>
            {!! $productHistory?->badge !!}
        </div>

        @if ($productHistory?->body)
        <div class="mt-3 text-gray-700">
            {!! sanitizeRichText($productHistory?->body) !!}
        </div>
        @endif
    </div>
</div>
@empty
<x-empty :message="translate('No history found for this product.')" :size="'lg'" :icon="'bi-clock-history'" />
@endforelse

@if ($productHistories?->hasPages())
<div class="mt-4 ajax-pagination">
    {{ $productHistories?->appends(['tab' => 'history'])->links() }}
</div>
@endif
