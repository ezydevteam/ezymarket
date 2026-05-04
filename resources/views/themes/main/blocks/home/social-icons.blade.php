@php $data = (object)($data ?? []); @endphp

<div id="{{ $data->uniqueId }}" class="home-socials {{ $isFullWidth ? $data->containerClass : '' }}">
    @themeInclude('blocks.home.partials.block-title', ['data' => $data])
    <div class="d-flex flex-wrap gap-3 w-100 justify-content-{{ $data->blockAlignment ?? 'center' }} social-icons-wrapper">
        @foreach($data->socialIcons ?? [] as $icon)
        @if(!empty($icon['iconClass']))
        <a href="{{ $icon['link'] }}" target="_blank" class="social-icon-link {{ $icon['brandClass'] ?? '' }}"
            title="{{ $icon['name'] }}">
            <i class="{{ $icon['iconClass'] }} fs-4"></i>
            @if(!empty($data->showName))
            <span class="social-icon-name">{{ $icon['name'] }}</span>
            @endif
        </a>
        @endif
        @endforeach
    </div>
</div>
