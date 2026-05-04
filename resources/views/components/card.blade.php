{{-- Card Component --}}
@props([
    'title' => null,
    'subtitle' => null,
    'titleTag' => 'h5', // h1, h2, h3, h4, h5, h6
    'titleClass' => 'mb-0', // Custom title classes
    'subtitleClass' => 'text-muted small mb-0 mt-1', // Custom subtitle classes
    'bodyClass' => '',
    'headerClass' => '',
    'footerClass' => '',
    'headerBorder' => true, // Add border-bottom to header
    'noPadding' => false, // Remove body padding
    'fullHeight' => false, // Add h-100 class
])

<div {{ $attributes->merge(['class' => 'card' . ($fullHeight ? ' h-100' : '')]) }}>
    @if(isset($header) || $title || isset($actions))
        <div class="card-header {{ $headerClass }} @if(!$headerBorder) border-0 @endif">
            @if(isset($header))
                {{ $header }}
            @else
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @if($title)
                            @php
                                $tag = $titleTag;
                                $titleHtml = "<{$tag} class=\"card-title {$titleClass}\">{$title}</{$tag}>";
                            @endphp
                            {!! $titleHtml !!}
                        @endif
                        @if($subtitle)
                            <p class="card-subtitle {{ $subtitleClass }}">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @isset($actions)
                        <div class="card-actions">
                            {{ $actions }}
                        </div>
                    @endisset
                </div>
            @endif
        </div>
    @endif

    <div class="card-body {{ $bodyClass }} @if($noPadding) p-0 @endif">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="card-footer {{ $footerClass }}">
            {{ $footer }}
        </div>
    @endisset
</div>

{{--
Usage Examples:

1. Basic card:
<x-card title="User Profile">
    <p>Card content here</p>
</x-card>

2. Card with actions:
<x-card title="Analytics" subtitle="Last 30 days">
    <x-slot:actions>
        <button class="btn btn-sm btn-primary">Export</button>
    </x-slot:actions>
    <p>Chart content</p>
</x-card>

3. Card with custom header and footer:
<x-card>
    <x-slot:header>
        <div class="custom-header">Custom Header</div>
    </x-slot:header>
    <p>Body content</p>
    <x-slot:footer>
        <button class="btn btn-primary">Save</button>
    </x-slot:footer>
</x-card>

4. No padding (for tables/lists):
<x-card title="Users" noPadding>
    <table class="table mb-0">...</table>
</x-card>

5. Full height and custom styling:
<x-card title="Dashboard" fullHeight titleTag="h6" :headerBorder="false">
    <p>Content</p>
</x-card>
--}}
