@php
    $purchase = $refund->purchase;
    $product = $purchase->product;
@endphp

<x-modal
    id="sendEmailModal-{{ $refund->id }}"
    :title="translate('Send Mail to Seller')"
    icon="bi-envelope"
    size="lg"
    scrollable="true"
>
    <form id="sendEmailForm-{{ $refund->id }}" class="send-mail-form" action="{{ route('admin.records.refunds.send-email', $refund->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-medium">{{ translate('Email') }}</label>
            <input type="text" class="form-control" value="{{ $refund->seller->username }} ({{ $refund->seller->email }})" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label fw-medium">{{ translate('Subject') }}</label>
            <input type="text" name="subject" class="form-control" value="{{ translate('Refund Request Notification') }} - #{{ $refund->id }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-medium">{{ translate('Message') }}</label>
            <textarea name="message" class="form-control" rows="12" required>
{{ translate('Dear') }} {{ $refund->seller->full_name }},

{{ translate('This is regarding the refund request for the following purchase:') }}

{{ translate('Refund ID') }}: #{{ $refund->id }}
{{ translate('Product Name') }}: {{ $product->name }}
{{ translate('Product ID') }}: #{{ $product->id }}
{{ translate('Buyer Name') }}: {{ $refund->user->full_name }}
{{ translate('Buyer Username') }}: {{ $refund->user->username }}
{{ translate('Buyer ID') }}: #{{ $refund->user->id }}
{{ translate('Refund Date') }}: {{ dateFormat($refund->created_at) }}
{{ translate('Purchase Date') }}: {{ dateFormat($purchase->created_at) }}
{{ translate('Current Status') }}: {{ $refund->status_name }}

{{ translate('Please review this refund request at your earliest convenience.') }}

{{ translate('Best regards') }},
{{ @$settings->general->site_name }}
            </textarea>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
            <i class="bi bi-x me-1"></i>
            {{ translate('Cancel') }}
        </button>
        <button type="submit" id="sendEmailBtn-{{ $refund->id }}" form="sendEmailForm-{{ $refund->id }}" class="btn btn-primary flex-fill ms-2">
            <i class="bi bi-send me-1"></i>
            {{ translate('Send Mail') }}
        </button>
    </x-slot>
</x-modal>
