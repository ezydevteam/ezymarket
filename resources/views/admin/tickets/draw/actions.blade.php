<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        <x-dropdown.item
            href="{{ route('admin.tickets.show', $ticket->id) }}"
            icon="bi bi-eye">
            {{ translate('View Ticket') }}
        </x-dropdown.item>

        @if($ticket->isOpened())
            <x-dropdown.item
                type="button"
                data-action="{{ route('admin.tickets.close', $ticket->id) }}"
                class="action-confirm"
                data-method="POST"
                data-confirm="{{ translate('Are you sure you want to close this ticket?') }}"
                icon="bi bi-x-circle">
                {{ translate('Close Ticket') }}
            </x-dropdown.item>
        @endif

        <x-dropdown.item type="divider" />

        <x-dropdown.item
            type="button"
            data-action="{{ route('admin.tickets.destroy', $ticket->id) }}"
            icon="bi bi-trash"
            class="text-danger action-confirm"
            data-method="DELETE"
            data-confirm="{{ translate('Are you sure to delete this record?') }}">
            {{ translate('Delete') }}
        </x-dropdown.item>
    </x-dropdown>
</div>
