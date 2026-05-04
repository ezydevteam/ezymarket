@php
    $uniqueId = $data['uniqueId'];
    $heading = $data['heading'];
    $paymentImage = $data['paymentImage'];
@endphp

<div id="{{ $uniqueId }}" class="footer-payment-icons">
    @if(!empty($heading))
        <div class="text-start opacity-75 mb-1">{{ $heading }}</div>
    @endif

    @if(!empty($paymentImage))
        <div class="payment-img-wrapper">
            <img src="{{ asset($paymentImage) }}"
                 alt="{{ $heading }}"
                 class="img-fluid rounded-2">
        </div>
    @endif
</div>

