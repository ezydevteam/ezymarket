<x-modal
    id="createTicketModal"
    size="lg"
    :title="translate('Open New Ticket')"
    :icon="'bi bi-ticket-detailed'"
>
    @if (!@settings('ticket')->status)
        <div class="alert alert-danger mb-0">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>{{ translate('Ticket System Disabled') }}</strong>
            <p class="mb-0">{{ translate('The ticket system is currently disabled. Please enable it in the') }}
                <a href="{{ route('admin.tickets.settings.index') }}" class="alert-link">{{ translate('ticket settings') }}</a>
                {{ translate('to create new tickets.') }}
            </p>
        </div>
    @else
        <form id="createTicketForm" action="{{ route('admin.tickets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">{{ translate('Subject') }} <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ translate('User') }} <span class="text-danger">*</span></label>
                    <select name="user" class="form-select selectpicker" data-live-search="true" title="{{ translate('Choose') }}" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user') == $user->id || request('user') == $user->id)>
                                {{ $user->full_name }} ({{ hideInDemo($user->email) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ translate('Category') }} <span class="text-danger">*</span></label>
                    <select name="category" class="form-select selectpicker" data-live-search="true" title="{{ translate('Choose') }}" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ translate('Description') }} <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="6" required>{{ old('description') }}</textarea>
                </div>
                <div class="col-12">
                    <div class="attachments">
                        <div class="attachment-box-1">
                            <label class="form-label">
                                <i class="bi bi-paperclip me-2"></i>{{ translate('Attachments') }}
                                <span class="text-muted small">({{ translate('Optional') }})</span>
                            </label>
                            <div class="input-group">
                                <input type="file" name="attachments[]" class="form-control">
                                <button id="addAttachment" class="btn bg-text-secondary" type="button">
                                    <i class="bi bi-plus-lg me-1"></i>{{ translate('Add More') }}
                                </button>
                            </div>
                            <small class="form-text text-muted mt-1">
                                {{ translate('Maximum file size: :size MB', ['size' => @settings('ticket')->max_file_size ?? 10]) }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer mt-3">
                <button type="button" class="btn btn-md btn-cancel" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}
                </button>
                <button type="submit" form="createTicketForm" class="btn btn-md btn-primary ms-3" id="createTicketBtn">
                    <i class="bi bi-send me-2"></i>{{ translate('Send Ticket') }}
                </button>
            </div>
        </form>
    @endif
</x-modal>
