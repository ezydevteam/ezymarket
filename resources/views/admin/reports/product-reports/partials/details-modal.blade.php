<x-modal
    id="reportDetailsModal-{{ $report->id }}"
    :title="translate('Product Report Details')"
    size="md"
    :scrollable="true"
>
    <ul class="list-group list-group-flush">
        <li class="list-group-item px-0 pb-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-hash me-2"></i>{{ translate('Report ID') }}</strong>
                <div class="text-end">
                    <span>#{{ $report->id }}</span>
                </div>
            </div>
        </li>
         <li class="list-group-item px-0 pb-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-box-seam me-2"></i>{{ translate('Product ID') }}</strong>
                <div class="text-end">
                    <span>#{{ $report->product?->id }}</span>
                </div>
            </div>
        </li>
        <li class="list-group-item px-0 pb-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-bag me-2"></i>{{ translate('Product') }}</strong>
                <div class="text-end">
                    <x-product
                        :product="$report->product"
                        :showImage="false"
                        :showCategory="false"
                    />
                </div>
            </div>
        </li>
         <li class="list-group-item px-0 pb-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-toggle-on me-2"></i>{{ translate('Product Status') }}</strong>
                <div class="text-end">
                    {!! $report->product?->status_badge !!}
                </div>
            </div>
        </li>
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-shop me-2"></i>{{ translate('Seller') }}</strong>
                <div class="text-end">
                    <x-user
                        :user="$report->product?->seller"
                        :showAvatar="false"
                        :showEmail="false"
                        fontWeight="normal"
                    />
                </div>
            </div>
        </li>
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-person-exclamation me-2"></i>{{ translate('Reported By') }}</strong>
                <div class="text-end">
                    <x-user
                        :user="$report->user"
                        :showAvatar="false"
                        :showEmail="false"
                        fontWeight="normal"
                    />
                </div>
            </div>
        </li>
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-question-circle me-2"></i>{{ translate('Reason') }}</strong>
                <div>{!! $report->reason_badge !!}</div>
            </div>
        </li>
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-flag me-2"></i>{{ translate('Total Reports') }}</strong>
                <span class="text-danger">{{ numberFormat($report->product->reportCounter()) }}</span>
            </div>
        </li>
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-calendar-event me-2"></i>{{ translate('Reported Date') }}</strong>
                <span class="text-muted">{{ dateFormat($report->created_at) }}</span>
            </div>
        </li>
        @if($report->hasBeenReviewed())
            <li class="list-group-item px-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <strong class="text-muted"><i class="bi bi-person-check me-2"></i>{{ translate('Reviewed By') }}</strong>
                    <div class="text-end">
                        @if($report->reviewed_by_id === superAdmin()->id)
                            <span class="text-primary">{{ translate('Admin') }}</span>
                        @else
                            <x-user
                                :user="$report->reviewedBy"
                                :showAvatar="false"
                                :showEmail="false"
                                fontWeight="normal"
                            />
                        @endif
                    </div>
                </div>
            </li>
            <li class="list-group-item px-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <strong class="text-muted"><i class="bi bi-calendar-check me-2"></i>{{ translate('Reviewed Date') }}</strong>
                    <span class="text-muted">{{ dateFormat($report->reviewed_at) }}</span>
                </div>
            </li>
        @endif
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-info-circle me-2"></i>{{ translate('Report Status') }}</strong>
                <div>{!! $report->status_badge !!}</div>
            </div>
        </li>
        @if($report->description)
            <li class="list-group-item px-0 py-3">
                <strong class="text-muted d-block mb-2"><i class="bi bi-card-text me-2"></i>{{ translate('Report Details') }}</strong>
                <textarea class="form-control text-dark" rows="3" readonly>{{ $report->description }}</textarea>
            </li>
        @endif
        @if($report->hasScreenshots())
            <li class="list-group-item px-0 py-3">
                <strong class="text-muted d-block mb-2"><i class="bi bi-paperclip me-2"></i>{{ translate('Attachments') }}</strong>
                <div class="row g-2">
                    @foreach($report->screenshot_links as $screenshot)
                        <div class="col-4">
                            <a href="{{ $screenshot }}" class="ratio ratio-16x9" target="_blank">
                                <img src="{{ $screenshot }}" alt="Screenshot" class="img-fluid rounded object-fit-cover">
                            </a>
                        </div>
                    @endforeach
                </div>
            </li>
        @endif
        @if($report->admin_notes)
            <li class="list-group-item px-0 py-3">
                <strong class="text-muted d-block mb-2"><i class="bi bi-sticky me-2"></i>{{ translate('Admin Notes') }}</strong>
                <textarea class="form-control text-dark" rows="3" readonly>{{ $report->admin_notes }}</textarea>
            </li>
        @endif
    </ul>

    <x-slot name="footer">
        <div class="text-start flex-fill">
            <form id="updateStatusForm-{{ $report->id }}"
                action="{{ route('admin.reports.product-reports.update-status', $report->id) }}"
                method="POST"
                data-ajax-confirm="true"
            >
                @csrf
                @method('PUT')
                <div class="d-flex align-items-center gap-3">
                    <select
                        name="status"
                        id="reportStatus-{{ $report->id }}"
                        class="form-select report-status-select"
                        data-conditional-toggle="#productReportsAdminNotes-{{ $report->id }}"
                        data-conditional-value="pending"
                        data-conditional-logic="not-equal"
                    >
                        @foreach($report->getStatusOptions() as $key => $label)
                            <option value="{{ $key }}" {{ $report->status->value === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" id="updateStatusBtn-{{ $report->id }}" class="btn btn-primary action-confirm flex-shrink-0" data-confirm="{{ translate('Are you sure you want to update the status?') }}" title="{{ translate('Update status') }}" @disabled($report->canBeActioned() ? false : true)>
                        <i class="bi bi-check-circle me-2"></i>{{ translate('Update') }}
                    </button>
                </div>
                @if(!$report->admin_notes && $report->canBeActioned())
                    <div class="mt-3 {{ $report->status->value === 'pending' ? 'd-none' : '' }}" id="productReportsAdminNotes-{{ $report->id }}">
                        <label class="form-label">{{ translate('Admin Notes') }}</label>
                        <textarea name="admin_notes" class="form-control" rows="3"
                            placeholder="{{ translate('Write a note for future identification...') }}">{{ old('admin_notes', $report->admin_notes) }}</textarea>
                    </div>
                @endif
            </form>
        </div>
        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle flex-fill ms-2" data-bs-toggle="dropdown">
                {{ translate('More') }}
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#reportHistoryModal-{{ $report->id }}">
                        <i class="bi bi-clock-history text-info me-2"></i>
                        {{ translate('View History') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item action-confirm"
                        href="{{ route('admin.reports.product-reports.destroy', $report->id) }}"
                        data-method="DELETE"
                        data-confirm="{{ translate('Are you sure you want to delete this report?') }}">
                        <i class="bi bi-x-circle text-danger me-2"></i>
                        {{ translate('Delete Report') }}
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                @if($report->product->isRestricted())
                    <li>
                        <a class="dropdown-item action-confirm text-success"
                            href="#"
                            data-action="{{ route('admin.reports.product-reports.unrestrict', $report->product) }}"
                            data-method="POST"
                            data-confirm="{{ translate('Are you sure you want to unrestrict this product?') }}">
                            <i class="bi bi-shield-check me-2"></i>
                            {{ translate('Unrestrict Product') }}
                        </a>
                    </li>
                @else
                    <li>
                        <a class="dropdown-item action-confirm text-orange"
                            href="#"
                            data-action="{{ route('admin.reports.product-reports.restrict', $report->product) }}"
                            data-method="POST"
                            data-confirm="{{ translate('Are you sure you want to restrict this product?') }}">
                            <i class="bi bi-shield-exclamation me-2"></i>
                            {{ translate('Restrict Product') }}
                        </a>
                    </li>
                @endif
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item action-confirm text-danger"
                        href="#"
                        data-action="{{ route('admin.reports.product-reports.delete-product', $report->product) }}"
                        data-method="DELETE"
                        data-confirm="{{ translate('Are you sure you want to delete this product? This action cannot be undone.') }}">
                        <i class="bi bi-trash me-2"></i>
                        {{ translate('Delete Product') }}
                    </a>
                </li>
            </ul>
        </div>
    </x-slot>
</x-modal>
