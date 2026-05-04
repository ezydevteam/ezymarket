<div class="dropdown">
    <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end border shadow-sm">
        <li>
            <button type="button" class="dropdown-item py-2" data-bs-toggle="modal"
                data-bs-target="#payoutDetailsModal" data-id="{{ $payout->id }}"
                data-action="{{ route('user.payout.show', $payout->id) }}">
                <i class="bi bi-clock-history me-2"></i>{{ translate('View Details') }}
            </button>
        </li>
        <li>
            <hr class="dropdown-divider">
        </li>
        @if ($payout->isPending())
        <li>
            <button type="button" class="dropdown-item py-2 action-confirm text-warning"
                data-action="{{ route('user.payout.recall', $payout->id) }}" data-method="POST"
                data-confirm="{{ translate('Are you sure you want to recall/cancel this payout request? The full amount will be reversed to your wallet.') }}">
                <i class="bi bi-arrow-counterclockwise me-2"></i>{{ translate('Recall Request') }}
            </button>
        </li>
        @else
        <li>
            <button type="button" class="dropdown-item py-2 action-confirm text-danger"
                data-action="{{ route('user.payout.destroy', $payout->id) }}"
                data-method="DELETE"
                data-confirm="{{ translate('Are you sure you want to delete this record? This action cannot be undone.') }}">
                <i class="bi bi-trash3 me-2"></i>{{ translate('Delete Record') }}
            </button>
        </li>
        @endif
    </ul>
</div>
