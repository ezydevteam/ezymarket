<div class="row g-3">
    {{-- Column 1: User Information --}}
    <div class="col-12 col-md-5">
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between gap-3">
                <div>
                    <i class="bi bi-mortarboard me-2"></i>{{ translate('User Information') }}
                </div>
                <div>
                    <a href="{{ route('admin.roles.users.edit', $idVerification->user->id) }}" target="_blank"
                        class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-box-arrow-up-right me-2"></i>{{ translate('View Details') }}
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush rounded-3">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <span>
                            <i class="bi bi-person me-2"></i>{{ translate('Name') }}
                        </span>
                        <a href="{{ route('admin.roles.users.edit', $idVerification->user->id) }}" target="_blank"
                        class="text-reset hover-primary">{{ $idVerification->user->full_name }}</a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <span>
                            <i class="bi bi-envelope me-2"></i>{{ translate('Email Address') }}
                        </span>
                        <span>{{ hideInDemo($idVerification->user->email) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <span>
                            <i class="bi bi-calendar-check me-2"></i>{{ translate('Submitted Date') }}
                        </span>
                        <span>{{ dateFormat($idVerification->created_at) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <span>
                            <i class="bi bi-card-heading me-2"></i>{{ translate('Document Type') }}
                        </span>
                        <span>{{ $idVerification->document_type->label() }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <span>
                            <i class="bi bi-file-binary me-2"></i>{{ translate(':document Number', ['document' => $idVerification->document_type->label()]) }}
                        </span>
                        <span>{{ $idVerification->document_number }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <span>
                            <i class="bi bi-battery-charging me-2"></i>{{ translate('Current Status') }}
                        </span>
                        <span class="badge {{ $idVerification->status_badge_class }}">
                            <i class="{{ $idVerification->status_icon }} me-1"></i>
                            {{ $idVerification->status_name }}
                        </span>
                    </li>
                    @if ($idVerification->isRejected())
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <span><i class="bi bi-x-circle me-2"></i>{{ translate('Current Rejection Reason') }}</span>
                            <span>{{ $idVerification->rejection_reason }}</span>
                        </li>
                    @endif
                    @if ($idVerification->getUserRejectionsCount() > 0)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <span><i class="bi bi-exclamation-circle me-2"></i>{{ translate('Total Rejections') }}</span>
                            <span>{{ $idVerification->getUserRejectionsCount() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <span><i class="bi bi-clock-history me-2"></i>{{ translate('Last Rejection Date') }}</span>
                            <span>{{ dateFormat($idVerification->getLastRejectionDate()) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <span><i class="bi bi-file-text me-2"></i>{{ translate('Last Rejection Reason') }}</span>
                            <span>{{ $idVerification->getLastRejectionReason() }}</span>
                        </li>
                    @endif

                </ul>
            </div>
        </div>
        @if ($idVerification->isPending())
            <div class="card">
                <div class="card-header"><i class="bi bi-lightning me-2"></i>{{ translate('Take Action') }}</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <button class="verification-reject-btn btn bg-text-red btn-lg w-100" data-slide-toggle="#verificationRejectForm">
                                <i class="bi bi-x-circle me-1"></i>
                                <span>{{ translate('Reject') }}</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button data-action="{{ route('admin.id-verification.approve', $idVerification->id) }}"
                                class="btn btn-success btn-lg w-100 approve-form action-confirm"
                                data-method="POST"
                                data-confirm="{{ translate('Are you sure you want to approve this verification request?') }}">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ translate('Approve') }}
                            </button>
                        </div>
                        <div id="verificationRejectForm" class="w-100 d-none">
                            <form action="{{ route('admin.id-verification.reject', $idVerification->id) }}"
                                method="POST"
                                class="reject-form"
                                data-ajax-confirm="true">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">
                                        {{ translate('Rejection Reason') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="rejection_reason" class="form-control" rows="6" required></textarea>
                                </div>
                                <div class="text-end">
                                    <button class="btn btn-danger btn-lg px-5 action-confirm" data-confirm="{{ translate('Are you sure you want to reject this verification request?') }}">
                                        <span>{{ translate('Submit') }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Column 2: Documents & Actions --}}
    <div class="col-12 col-md-7">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-person-vcard me-2"></i>{{ translate('Documents') }}</div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach ($idVerification->documents as $key => $document)
                        @if ($document)
                            <div class="col-6">
                                <div class="border p-3 rounded-3 bg-light">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        {{ translate(ucfirst(str_replace('_', ' ', $key))) }}
                                    </h6>
                                    <div class="mb-3">
                                        <a href="{{ route('admin.id-verification.document', [$idVerification->id, $key]) }}"
                                            target="_blank">
                                            <img src="{{ route('admin.id-verification.document', [$idVerification->id, $key]) }}"
                                                alt="{{ $document }}" class="rounded-3" width="100%" height="200px" style="object-fit: cover;">
                                        </a>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <a href="{{ route('admin.id-verification.document', [$idVerification->id, $key]) }}"
                                                target="_blank" class="btn btn-outline-secondary btn-sm w-100"><i
                                                    class="bi bi-box-arrow-up-right me-2"></i>{{ translate('View') }}</a>
                                        </div>
                                        <div class="col-6">
                                            <form action="{{ route('admin.id-verification.download', [$idVerification->id, $key]) }}"
                                                method="POST">
                                                @csrf
                                                <button class="btn btn-primary btn-sm w-100"><i
                                                        class="bi bi-download me-2"></i>{{ translate('Download') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>


