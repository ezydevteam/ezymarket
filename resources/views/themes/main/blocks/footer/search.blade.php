@php
    $id = $data['uniqueId'];
    $placeholder = $data['placeholder'];
    $buttonText = $data['buttonText'];
    $buttonIcon = $data['buttonIcon'];
    $buttonStyle = $data['buttonStyle'];
    $showButton = $data['showButton'];
    $style = $data['style'];
    $size = $data['size'];
    $isStacked = $data['style'] === 'stacked';
@endphp

<div id="{{ $id }}" class="footer-search">
    <form action="{{ route('products.search') }}" method="GET">
        <div class="{{ $isStacked ? 'd-flex flex-column gap-2' : "input-group input-group-{$size}" }}">

            <input type="text"
                   name="q"
                   class="form-control {{ $isStacked ? "form-control-{$size}" : '' }}"
                   placeholder="{{ $placeholder }}">

            @if($showButton)
                <button type="submit" class="btn btn-{{ $buttonStyle }}" {{ $isStacked ? "btn-{$size}" : '' }}>
                     @if($buttonIcon)
                        <i class="bi {{ $buttonIcon }} {{ $buttonText ? 'me-2' : '' }}"></i>
                    @endif
                    {{ $buttonText }}
                </button>
            @endif

        </div>
    </form>
</div>
