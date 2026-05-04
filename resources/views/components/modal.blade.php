@if(!$contentOnly)
<div {{ $attributes->merge(['class' => 'modal fade', 'id' => $id, 'tabindex' => '-1', 'aria-labelledby' => $id . 'Label', 'aria-hidden' => 'true']) }}
    @if($static) data-bs-backdrop="static" data-bs-keyboard="false" @endif
    @if($autoOpen) data-auto-open="true" @endif>
    <div class="modal-dialog {{ $dialogClass }}">
@endif
        <div class="modal-content {{ !$contentOnly ? 'content-only' : '' }} {{ $contentClass }} col-lg-6">
            @if($header)
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $id }}Label">
                        @if($icon)
                            <i class="bi {{ $icon }} me-2"></i>
                        @endif
                        {!! $title !!}
                    </h5>
                    @if($closeButton)
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    @endif
                </div>
            @endif
            <div class="modal-body {{ $bodyClass }} {{ $scrollable ? 'mh-70vh overflow-y-auto' : '' }}">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="modal-footer {{ $footerClass }}">
                    {{ $footer }}
                </div>
            @endisset
        </div>
@if(!$contentOnly)
    </div>
</div>
@endif
