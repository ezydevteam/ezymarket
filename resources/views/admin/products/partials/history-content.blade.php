@forelse ($productHistories as $productHistory)
@php
$seller = $productHistory?->seller;
$admin = $productHistory?->admin;
@endphp
<div class="card border-0 shadow-sm rounded-4 mb-4 {{ $productHistories->onLastPage() && $loop->first ? 'border-primary' : '' }}">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
            <div class="d-flex align-items-center gap-3">
                @if ($admin)
                @if ($admin->id === superAdmin()?->id)
                <x-user :user="$admin" :showEmail="false" :roleLabel="translate('Admin')" roleIcon="bi-person-lock"
                    linkRoute="admin.account.index" :directRoute="true" />
                @else
                <x-user :user="$admin" :showEmail="false" :roleLabel="translate('Manager')" roleIcon="bi-person-check"
                    linkRoute="admin.roles.staff.edit" />
                @endif
                @elseif ($seller)
                <x-user :user="$seller" :showEmail="false" :roleLabel="translate('Seller')" />
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted">
                    <i class="bi bi-clock-history me-2"></i>{{ dateFormat($productHistory?->created_at) }}
                </small>
                <div class="dropdown">
                    <button class="btn text-muted border-0 p-1" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button data-action="{{ route('admin.products.history.delete', [$productHistory?->product?->id, $productHistory?->id]) }}"
                                class="dropdown-item text-danger action-confirm"
                                data-confirm="{{ translate('Are you sure you want to delete this history entry? This can not be undone.') }}"
                                data-method="DELETE">
                                <i class="bi bi-trash me-2"></i>{{ translate('Delete') }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div>
            {!! $productHistory?->badge !!}
        </div>

        @if ($productHistory?->body)
        <div class="mt-3 text-gray-700">
            {!! sanitizeRichText($productHistory->body) !!}
        </div>
        @endif
    </div>
</div>
@empty
<x-empty desc="No history found for this product." :icon="'bi-clock-history'" />
@endforelse

@if ($productHistories->hasPages())
<div class="mt-3 ajax-pagination">
    {{ $productHistories->appends(['tab' => 'history'])->links() }}
</div>
@endif
