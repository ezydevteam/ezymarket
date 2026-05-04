@php
    $id = $data['uniqueId'];
    $heading = $data['heading'];
    $subHeading = $data['subHeading'];
    $headingAlign = $data['headingAlign'];
    $style = $data['style'];
    $placeholder = $data['placeholder'];
    $buttonText = $data['buttonText'];
    $buttonIcon = $data['buttonIcon'];
    $buttonDisplay = $data['buttonDisplay'];
    $buttonStyle = $data['buttonStyle'];
    $showName = $data['showName'];
    $namePlaceholder = $data['namePlaceholder'];
@endphp

<div id="{{ $id }}" class="footer-newsletter">

    @if(($heading || $subHeading) && $style !== 'footer')
        <div class="mb-3 text-{{ $headingAlign }}">
            @if($heading)<h5 class="fw-bold mb-1">{{ $heading }}</h5>@endif
            @if($subHeading)<p class=" opacity-75 mb-0">{{ $subHeading }}</p>@endif
        </div>
    @endif

    <livewire:newsletter-form
        :heading="$heading"
        :subHeading="$subHeading"
        :style="$style"
        :placeholder="$placeholder"
        :showName="$showName"
        :namePlaceholder="$namePlaceholder"
        :buttonText="$buttonText"
        :buttonDisplay="$buttonDisplay"
        :buttonIcon="$buttonIcon"
        :buttonStyle="$buttonStyle"
    />
</div>
