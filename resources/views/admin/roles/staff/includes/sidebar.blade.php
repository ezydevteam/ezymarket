<div class="col-lg-4">
    <div class="card user-details-card">
        <div class="card-body px-4 py-3">
            <a href="{{ route('admin.roles.staff.index') }}" class="btn btn-light btn-sm d-none d-lg-inline-block mb-1">
                <i class="bi bi-arrow-left"></i>
                {{ translate('Back') }}
            </a>
            <div class="d-flex flex-column align-items-center mb-4">
                <div class="image-fluid image-xl rounded">
                    <img src="{{ $staff->avatar_url }}" alt="{{ $staff->username }}">
                </div>
                <h5 class="h6 mt-2 mb-0">{{ $staff->full_name }}</h5>
                <p class="text-muted mb-2">&commat;{{ $staff->username }}</p>
                <span class="badge bg-{{ $staff->role === \App\Enums\Admin\AdminRole::MANAGER ? 'primary' : ($staff->role === \App\Enums\Admin\AdminRole::ACCOUNTANT ? 'info' : 'warning') }}">
                    {{ $staff->role->label() }}
                </span>
            </div>
            <div class="user-details">
                <div class="profile-details mt-3">
                    <h6 class="border-bottom py-2">{{ translate('Details') }}</h6>
                    <ul class="list-group">
                        <li class="list-group-item border-0 px-0 py-1">
                            <strong>{{ translate('ID:') }}</strong>
                            <span>#{{ $staff->id }}</span>
                        </li>
                        <li class="list-group-item border-0 px-0 py-1">
                            <strong>{{ translate('Role:') }}</strong>
                            <span>{{ $staff->role_label }}</span>
                        </li>
                        <li class="list-group-item border-0 px-0 py-1">
                            <strong>{{ translate('Status:') }}</strong>
                            <span>{{ $staff->status ? translate('Active') : translate('Inactive') }}</span>
                        </li>
                        <li class="list-group-item border-0 px-0 py-1">
                            <strong>{{ translate('Email:') }}</strong>
                            <span>{{ hideInDemo($staff->email) }}</span>
                        </li>
                        <li class="list-group-item border-0 px-0 py-1">
                            <strong>{{ translate('Joined:') }}</strong>
                            <span>{{ dateFormat($staff->created_at) }}</span>
                        </li>
                        @if ($staff->role === \App\Enums\Admin\AdminRole::REVIEWER)
                        <li class="list-group-item border-0 px-0 py-1">
                            <strong>{{ translate('Assigned Categories:') }}</strong>
                            <span>{{ $staff->categories->count() }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
                <div class="text-center mt-3">
                    <form action="{{ route('admin.roles.staff.destroy', $staff->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn bg-text-red btn-sm action-confirm"
                            data-confirm="{{ translate('Are you sure you want to delete this staff member? This action cannot be undone.') }}">
                            {{ translate('Delete') }}
                        </button>
                    </form>
                    <button class="btn bg-text-secondary btn-sm ms-3" data-bs-toggle="modal" data-bs-target="#sendMailStaffModal">
                        {{ translate('Send Mail') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<x-modal id="sendMailStaffModal"
        size="lg"
        :title="translate('Send Email')"
        icon="bi bi-envelope-paper">

    <form id="sendMailStaffForm" action="{{ route('admin.roles.staff.sendmail', $staff->id) }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">{{ translate('Subject') }} <span class="text-danger">*</span></label>
                <input type="text" name="subject" class="form-control" placeholder="{{ translate('About message') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">{{ translate('Reply To') }} <span class="text-danger">*</span></label>
                <input type="email" name="reply_to" class="form-control"
                    placeholder="{{ translate('Reply to email address') }}"
                    value="{{ authAdmin()->email }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">{{ translate('Message') }} <span class="text-danger">*</span></label>
                <textarea name="message" class="form-control" rows="10" required></textarea>
            </div>
             <div class="col-12">
                <label class="form-label">{{ translate('Attachments') }}</label>
                <div class="attachments">
                    <div class="input-group mb-2">
                        <input type="file" name="attachments[]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip">
                        <button type="button" class="btn btn-dark" id="addAttachment">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
                <small class="text-muted">{{ translate('Maximum file size: 10MB per file. Supported formats: PDF, DOC, DOCX, JPG, PNG, ZIP') }}</small>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <div class="d-flex align-items-center gap-3 w-100">
            <button type="button" class="btn btn-md btn-cancel w-50" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}
            </button>
            <button type="submit" form="sendMailStaffForm" class="btn btn-md btn-primary w-50"
                data-confirm="{{ translate('Are you sure you want to send this email?') }}">
                <i class="bi bi-send me-2"></i>{{ translate('Send Email') }}
            </button>
        </div>
    </x-slot>
</x-modal>
