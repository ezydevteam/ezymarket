<x-modal :title="translate('Refund Request Details')" icon="bi-arrow-return-left" :scrollable="true" :content-only="true"
    id="refundDetailsContent">

    <x-archived-alert :model="$refund"
        :restoreRoute="route('admin.records.refunds.restore', $refund->id)"
        :deleteRoute="route('admin.records.refunds.destroy', $refund->id)" />

    {{-- Header Section --}}
    <div class="py-3 border-bottom bg-light-subtle rounded-top">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="text-muted text-uppercase fw-semibold mb-1 d-block fs-12 letter-spacing-1">
                    {{ translate('Refund Request') }}
                </span>
                <h4 class="fw-bold mb-0 text-dark">#{{ $refund->id }}</h4>
            </div>
            <div class="text-end">
                <span class="badge {{ $refund->status->badgeClass() }} py-2 px-3 fs-12">
                    <i class="bi {{ $refund->status->icon() }} me-1"></i>{{ $refund->status->label() }}
                </span>
            </div>
        </div>

        {{-- Financial & Timeline Summary --}}
        <div class="row g-3">
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Requested Date') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                        {{ dateFormat($refund->created_at) }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Purchase ID') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-hash me-1 text-primary"></i>
                        #{{ $refund->purchase_id }}
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="px-3 py-3 border rounded bg-primary-subtle shadow-sm border-primary-subtle">
                    <div class="row align-items-center">
                        <div class="col-12">
                            <small class="text-muted d-block mb-1">{{ translate('Refund Amount') }}</small>
                            <div class="h3 fw-bold text-primary mb-0">{{ getAmount((float) ($refund->purchase?->sale?->price ?? 0)) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-3">
        {{-- Subject --}}
        <div class="mb-4">
            <h6 class="fw-bold text-uppercase fs-12 text-muted mb-2 letter-spacing-1">
                {{ translate('Subject') }}
            </h6>
            <div class="p-3 border rounded-3 bg-white fw-medium text-dark">
                {{ $refund->subject }}
            </div>
        </div>

        {{-- Parties Information --}}
        <div class="row g-4 mb-4">
            <div class="col-6">
                <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
                    <i class="bi bi-person me-1"></i>{{ translate('Buyer') }}
                </h6>
                <div class="p-2 border rounded-3 bg-white">
                    <x-user :user="$refund->user" avatarSize="sm" />
                </div>
            </div>
            <div class="col-6">
                <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
                    <i class="bi bi-shop me-1"></i>{{ translate('Seller') }}
                </h6>
                <div class="p-2 border rounded-3 bg-white">
                    <x-user :user="$refund->seller" avatarSize="sm" />
                </div>
            </div>
        </div>

        {{-- Product Info --}}
        <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
            <i class="bi bi-box-seam me-1"></i>{{ translate('Product Details') }}
        </h6>
        @php $product = $refund->purchase?->product; @endphp
        <div class="bg-light p-3 rounded-3 border mb-4">
            <div class="d-flex gap-3">
                <div class="bg-white rounded p-2 border shadow-xs">
                    <img src="{{ $product?->thumbnail_url }}" alt="{{ $product?->name }}"
                        class="image-fluid image-md rounded">
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold text-dark fs-15 mb-1">{{ $product?->name }}</div>
                    <div class="d-flex gap-3 small text-muted">
                        <span><i class="bi bi-tag me-1"></i>{{ $product?->category?->name }}</span>
                        <span><i class="bi bi-upc-scan me-1"></i>ID: #{{ $product?->id }}</span>
                    </div>
                </div>
                <div class="text-end">
                    <a href="{{ route('admin.products.show', $product?->id) }}"
                       class="btn btn-sm btn-outline-primary mt-2" target="_blank">
                        <i class="bi bi-eye me-1"></i>{{ translate('View') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Replies / Conversations --}}
        @if($refund->replies->isNotEmpty())
            <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
                <i class="bi bi-chat-dots me-1"></i>{{ translate('Conversation History') }}
            </h6>
            <div class="conversation-trail">
                @foreach($refund->replies as $reply)
                    <div class="p-3 border rounded-3 mb-2 bg-white shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $reply->user?->avatar_url }}" class="image-fluid image-sm rounded" alt="{{ $reply->user?->username }}">
                                <span class="fw-bold fs-13">{{ $reply->user?->full_name }}</span>
                                @if($reply->user_id == $refund->seller_id)
                                    <span class="badge bg-info-subtle text-info fs-10 text-uppercase">{{ translate('Seller') }}</span>
                                @elseif($reply->user_id == $refund->user_id)
                                    <span class="badge bg-primary-subtle text-primary fs-10 text-uppercase">{{ translate('Buyer') }}</span>
                                @endif
                            </div>
                            <small class="text-muted fs-11">{{ timeAgo($reply->created_at) }}</small>
                        </div>
                        <div class="text-dark fs-13 ps-5">
                            {!! sanitizeHtml($reply->message) !!}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
            {{ translate('Dismiss') }}
        </button>
        @if($refund->isPending())
            <a href="{{ route('admin.records.purchases.index', ['id' => $refund->purchase_id]) }}"
                class="btn btn-primary flex-fill" target="_blank">
                <i class="bi bi-eye me-2"></i>{{ translate('View Purchase') }}
            </a>
        @endif
    </x-slot>
</x-modal>
