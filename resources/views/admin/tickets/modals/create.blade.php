<x-modal :title="translate('Create Support Ticket')" icon="bi-plus-circle-dotted" id="createTicketContent"
    :content-only="true" id="createTicketContent">
    <form action="{{ route('admin.tickets.store') }}" method="POST" class="ajax-form" id="createTicketForm">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-bold">{{ translate('Select User') }}</label>
            <select name="user" class="form-select selectpicker" required
                data-placeholder="{{ translate('Select a user') }}" data-live-search="true" data-size="8">
                @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->full_name }} (@ {{ $user->username }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">{{ translate('Category') }}</label>
            <select name="category" class="form-select selectpicker" required data-live-search="true" data-size="8"
                data-placeholder="{{ translate('Select a category') }}">
                @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">{{ translate('Subject') }}</label>
            <input type="text" name="subject" class="form-control" placeholder="{{ translate('Enter ticket subject') }}"
                required>
        </div>
        <div class="mb-0">
            <label class="form-label fw-bold">{{ translate('Description') }}</label>
            <textarea name="description" class="form-control" rows="5"
                placeholder="{{ translate('Describe the issue or inquiry...') }}" required></textarea>
        </div>
    </form>
    <x-slot:footer>
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">{{ translate('Dismiss')
            }}</button>
        <button type="submit" form="createTicketForm" class="btn btn-primary flex-fill"><i
                class="bi bi-check2-circle me-2"></i>{{ translate('Create Ticket') }}</button>
    </x-slot>
</x-modal>
