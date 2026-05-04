<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn btn-sm btn-icon">
        <x-dropdown.item
            :href="route('user.ticket.show', $ticket->id)"
            icon="bi-eye"
            class="py-2">
            {{ translate('View Details') }}
        </x-dropdown.item>

        @if ($ticket->canCancel())
            <x-dropdown.item type="divider" />
            <x-dropdown.item
                type="button"
                color="warning"
                icon="bi-slash-circle"
                class="py-2 action-confirm"
                data-action="{{ route('user.ticket.cancel', $ticket->id) }}"
                data-method="POST"
                data-text="{{ translate('Are you sure you want to cancel this ticket?') }}">
                {{ translate('Cancel Ticket') }}
            </x-dropdown.item>
        @elseif ($ticket->canDelete())
            <x-dropdown.item type="divider" />
            <x-dropdown.item
                type="button"
                color="danger"
                icon="bi-trash"
                class="py-2 action-confirm"
                data-action="{{ route('user.ticket.destroy', $ticket->id) }}"
                data-method="DELETE"
                data-text="{{ translate('Are you sure you want to delete this ticket history?') }}">
                {{ translate('Delete History') }}
            </x-dropdown.item>
        @endif
    </x-dropdown>
</div>

