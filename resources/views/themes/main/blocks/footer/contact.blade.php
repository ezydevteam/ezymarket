@php
    $id = $data['uniqueId'];
    $address = $data['address'];
    $phone = $data['phone'];
    $email = $data['email'];
    $moreInfo = $data['moreInfo'];
@endphp

<div id="{{ $id }}" class="footer-contact">
    <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
        @if($address)
            <li class="d-flex gap-2 align-items-start">
                <i class="bi bi-geo-alt opacity-75 mt-1"></i>
                <span class="opacity-75">{!! nl2br(e($address)) !!}</span>
            </li>
        @endif

        @if($phone)
            <li class="d-flex gap-2 align-items-center">
                <i class="bi bi-telephone opacity-75"></i>
                <a href="tel:{{ $phone }}" class="text-reset opacity-75 link-hover">{{ $phone }}</a>
            </li>
        @endif

        @if($email)
            <li class="d-flex gap-2 align-items-center">
                <i class="bi bi-envelope opacity-75"></i>
                <a href="mailto:{{ $email }}" class="text-reset opacity-75 link-hover">{{ $email }}</a>
            </li>
        @endif

        @if($moreInfo)
             <li class="d-flex gap-2 align-items-center">
                <i class="bi bi-info-circle opacity-75"></i>
                <div class="opacity-75">{!! nl2br(e($moreInfo)) !!}</div>
            </li>
        @endif
    </ul>
</div>
