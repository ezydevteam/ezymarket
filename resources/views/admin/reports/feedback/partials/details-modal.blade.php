<x-modal
    id="feedbackDetailsModal-{{ $feedback->id }}"
    :title="translate('Feedback Details')"
    :icon="'bi-star-half'"
    size="md"
    :scrollable="true"
>
    <ul class="list-group list-group-flush">
        <li class="list-group-item px-0 pb-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-hash me-2"></i>{{ translate('Feedback ID') }}</strong>
                <div class="text-end">
                    <span>#{{ $feedback->id }}</span>
                </div>
            </div>
        </li>
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-person me-2"></i>{{ translate('Submitted By') }}</strong>
                <div class="text-end">
                    <x-user
                        :user="$feedback->user"
                        :showAvatar="false"
                        :showEmail="false"
                        fontWeight="normal"
                    />
                </div>
            </div>
        </li>
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-folder me-2"></i>{{ translate('Field') }}</strong>
                <div>{!! $feedback->field_badge !!}</div>
            </div>
        </li>
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-info-circle me-2"></i>{{ translate('Status') }}</strong>
                <div>{!! $feedback->status_badge !!}</div>
            </div>
        </li>
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-calendar-event me-2"></i>{{ translate('Submitted Date') }}</strong>
                <span class="text-muted">{{ dateFormat($feedback->created_at) }}</span>
            </div>
        </li>
        @if($feedback->isReviewed() && $feedback->reviewed_at)
            <li class="list-group-item px-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <strong class="text-muted"><i class="bi bi-calendar-check me-2"></i>{{ translate('Reviewed Date') }}</strong>
                    <span class="text-muted">{{ dateFormat($feedback->reviewed_at) }}</span>
                </div>
            </li>
        @endif
        @if($feedback->isResolved() && $feedback->resolved_at)
            <li class="list-group-item px-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <strong class="text-muted"><i class="bi bi-calendar-check me-2"></i>{{ translate('Resolved Date') }}</strong>
                    <span class="text-muted">{{ dateFormat($feedback->resolved_at) }}</span>
                </div>
            </li>
        @endif
        <li class="list-group-item px-0 py-3">
            <strong class="text-muted text-start d-block mb-2"><i class="bi bi-card-text me-2"></i>{{ translate('Feedback Details') }}</strong>
            <textarea class="form-control text-dark" rows="4" readonly>{{ $feedback->description }}</textarea>
        </li>
        @if($feedback->hasScreenshots())
            <li class="list-group-item px-0 py-3">
                <strong class="text-muted text-start d-block mb-2"><i class="bi bi-paperclip me-2"></i>{{ translate('Attachments') }}</strong>
                <div class="row g-2">
                    @foreach($feedback->screenshot_links as $screenshot)
                        <div class="col-3">
                            <a href="{{ $screenshot }}" class="ratio ratio-16x9" target="_blank">
                                <img src="{{ $screenshot }}" alt="Screenshot" class="img-fluid rounded object-fit-cover">
                            </a>
                        </div>
                    @endforeach
                </div>
            </li>
        @endif
        @if($feedback->admin_notes)
            <li class="list-group-item px-0 pt-3">
                <strong class="text-muted text-start d-block mb-2"><i class="bi bi-sticky me-2"></i>{{ translate('Admin Notes') }}</strong>
                <textarea class="form-control text-dark" rows="3" readonly>{{ $feedback->admin_notes }}</textarea>
            </li>
        @endif
    </ul>

    <x-slot name="footer">
        <a class="btn bg-text-red action-confirm me-2"
            href="{{ route('admin.reports.feedback.destroy', $feedback->id) }}"
            data-method="DELETE"
            data-confirm="{{ translate('Are you sure you want to delete this feedback?') }}">
            <i class="bi bi-trash me-1"></i>{{ translate('Delete') }}
        </a>
        <div class="text-start flex-fill">
            <form id="updateFeedbackStatusForm-{{ $feedback->id }}"
                action="{{ route('admin.reports.feedback.update-status', $feedback->id) }}"
                method="POST"
                data-ajax-confirm="true"
            >
                @csrf
                @method('PUT')
                <div class="d-flex align-items-center gap-3">
                    <select
                        name="status"
                        id="feedbackStatus-{{ $feedback->id }}"
                        class="form-select"
                        data-conditional-toggle="#feedbackAdminNotes-{{ $feedback->id }}"
                        data-conditional-value="pending"
                        data-conditional-logic="not-equal"
                    >
                        @foreach($feedback->getStatusOptions() as $key => $label)
                            <option value="{{ $key }}" {{ $feedback->status->value === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                        id="updateFeedbackStatusBtn-{{ $feedback->id }}"
                        class="btn btn-primary action-confirm flex-shrink-0"
                        data-confirm="{{ translate('Are you sure you want to update the status?') }}"
                        title="{{ translate('Update status') }}"
                        @disabled($feedback->isResolved())
                        >
                        <i class="bi bi-check-circle me-2"></i>{{ translate('Update') }}
                    </button>
                </div>
                @if(!$feedback->admin_notes)
                    <div class="mt-3 {{ $feedback->isPending() ? 'd-none' : '' }}" id="feedbackAdminNotes-{{ $feedback->id }}">
                        <label class="form-label">{{ translate('Admin Notes') }}</label>
                        <textarea name="admin_notes" class="form-control" rows="3"
                            placeholder="{{ translate('Write a note for future identification...') }}">{{ old('admin_notes', $feedback->admin_notes) }}</textarea>
                    </div>
                @endif
            </form>
        </div>
    </x-slot>
</x-modal>
