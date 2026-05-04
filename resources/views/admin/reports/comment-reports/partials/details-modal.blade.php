<x-modal
    id="commentReportDetailsModal-{{ $report->id }}"
    :title="translate('Comment Report Details')"
    icon="bi-chat-text"
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
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-chat-dots me-2"></i>{{ translate('Comment ID') }}</strong>
                <div class="text-end">
                    <a href="{{ route('products.comment', [
                        $report->commentReply->comment->product->slug,
                        $report->commentReply->comment->product->id,
                        $report->commentReply->comment->id,
                    ]) }}" target="_blank" class="text-primary">
                        #{{ $report->commentReply->comment->id }}
                        <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                    </a>
                </div>
            </div>
        </li>
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-bag me-2"></i>{{ translate('Product') }}</strong>
                <div class="text-end">
                    <x-product
                        :product="$report->commentReply->comment->product"
                        :showImage="false"
                        :showCategory="false"
                        fontWeight="normal"
                    />
                </div>
            </div>
        </li>
        <li class="list-group-item px-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-muted"><i class="bi bi-person me-2"></i>{{ translate('Commentator') }}</strong>
                <div class="text-end">
                    <x-user
                        :user="$report->commentReply->user"
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
                <strong class="text-muted"><i class="bi bi-calendar-event me-2"></i>{{ translate('Reported Date') }}</strong>
                <span class="text-muted">{{ dateFormat($report->created_at) }}</span>
            </div>
        </li>
        @if($report->description)
            <li class="list-group-item px-0 py-3">
                <strong class="text-muted d-block mb-2"><i class="bi bi-card-text me-2"></i>{{ translate('Report Details') }}</strong>
                <textarea class="form-control text-dark" rows="3" readonly>{{ $report->description }}</textarea>
            </li>
        @endif
        <li class="list-group-item px-0 py-3">
            <strong class="text-muted d-block mb-2"><i class="bi bi-chat-quote me-2"></i>{{ translate('Reported Comment') }}</strong>
            <div class="bg-light p-3 border rounded-2 small">
                {!! sanitizeHtml($report->commentReply->body, true) !!}
            </div>
        </li>
    </ul>

    <x-slot name="footer">
        <a class="btn bg-text-red action-confirm flex-fill"
            href="{{ route('admin.reports.comment-reports.delete', $report->id) }}"
            data-method="DELETE"
            data-confirm="{{ translate('Are you sure you want to delete this comment? This action cannot be undone.') }}">
            <i class="bi bi-trash me-2"></i>{{ translate('Delete Comment') }}
        </a>
        <a class="btn btn-primary action-confirm flex-fill ms-2"
            href="{{ route('admin.reports.comment-reports.keep', $report->id) }}"
            data-method="POST"
            data-confirm="{{ translate('Are you sure you want to keep this comment? The report will be dismissed.') }}">
            <i class="bi bi-check-circle me-2"></i>{{ translate('Keep Comment') }}
        </a>
    </x-slot>
</x-modal>
