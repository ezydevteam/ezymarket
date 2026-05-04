<div class="offcanvas offcanvas-{{ $placement }}" tabindex="-1" id="{{ $id }}" aria-labelledby="{{ $id }}Label"
    @if($scroll) data-bs-scroll="true" @endif
    @if($backdrop === false) data-bs-backdrop="false" @endif
    @if($focus === false) data-bs-focus="false" @endif
    {{ $attributes }}
>
    @if($header)
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="{{ $id }}Label">
                @if($icon)
                    <i class="bi {{ $icon }} me-2"></i>
                @endif
                {!! $title !!}
            </h5>
            @if($closeButton)
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            @endif
        </div>
    @endif
    <div class="offcanvas-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="offcanvas-footer p-3 border-top bg-light">
            {{ $footer }}
        </div>
    @endisset
</div>
