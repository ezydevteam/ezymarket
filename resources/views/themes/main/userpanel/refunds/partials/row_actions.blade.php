<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn btn-sm btn-icon">
        <x-dropdown.item
            :href="route('user.refund.show', $refund->id)"
            icon="bi-eye"
            class="py-2">
            {{ translate('View Details') }}
        </x-dropdown.item>

        @if ($refund->user_id == authUser()->id)
            @if ($refund->canCancel())
                <x-dropdown.item type="divider" />
                <x-dropdown.item
                    type="button"
                    color="warning"
                    icon="bi-slash-circle"
                    class="py-2 action-confirm"
                    data-action="{{ route('user.refund.cancel', $refund->id) }}"
                    data-method="POST"
                    data-text="{{ translate('Are you sure you want to cancel this refund request?') }}">
                    {{ translate('Cancel Request') }}
                </x-dropdown.item>
            @elseif ($refund->canDelete())
                <x-dropdown.item type="divider" />
                <x-dropdown.item
                    type="button"
                    color="danger"
                    icon="bi-trash"
                    class="py-2 action-confirm"
                    data-action="{{ route('user.refund.destroy', $refund->id) }}"
                    data-method="DELETE"
                    data-text="{{ translate('Are you sure you want to permanently delete this refund request?') }}">
                    {{ translate('Delete History') }}
                </x-dropdown.item>
            @endif
        @endif
    </x-dropdown>
</div>

