<x-modal :content-only="true" :title="translate('Create Support Ticket')" icon="bi-headset">
    <form action="{{ route('user.ticket.store') }}" method="POST" class="ajax-form" id="createTicketForm" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">
            <!-- Subject -->
            <div class="col-12">
                <label class="form-label fw-semibold text-gray-700 small mb-2">{{ translate('Subject') }} <span class="text-danger">*</span></label>
                <input type="text" name="subject" class="form-control"
                    placeholder="{{ translate('How can we help you?') }}" required>
            </div>

            <!-- Category -->
            <div class="col-12">
                <label class="form-label fw-semibold text-gray-700 small mb-2">{{ translate('Category') }} <span class="text-danger">*</span></label>
                <select name="category" class="form-select selectpicker" data-live-search="true" required>
                    <option value="" selected disabled>{{ translate('Choose a category...') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Description -->
            <div class="col-12">
                <label class="form-label fw-semibold text-gray-700 small mb-2">{{ translate('Description') }} <span class="text-danger">*</span></label>
                <textarea name="description" rows="5" class="form-control"
                    placeholder="{{ translate('Please describe your issue in detail...') }}" required></textarea>
            </div>

            <!-- Attachments -->
            <div class="col-12">
                <label class="form-label fw-semibold text-gray-700 small mb-2">{{ translate('Attachments') }}</label>
                <input type="file" name="attachments[]" class="form-control" multiple>
                <div class="form-text small text-muted mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    {{ translate('Allowed types: :types. Max size: :sizeMB', ['types' => @settings('ticket')->file_types, 'size' => @settings('ticket')->max_file_size]) }}
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <button type="button" class="btn btn-outline-dark flex-fill text-uppercase" data-bs-dismiss="modal">
                {{ translate('Cancel') }}
            </button>
            <button type="submit" form="createTicketForm" class="btn btn-primary flex-fill text-uppercase">
                <i class="bi bi-send me-2"></i>{{ translate('Submit Ticket') }}
            </button>
        </x-slot>
    </form>
</x-modal>
