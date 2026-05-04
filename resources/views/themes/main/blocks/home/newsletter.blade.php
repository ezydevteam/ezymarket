@php $data = (object)($data ?? []); @endphp

<div id="{{ $data->uniqueId }}" class="home-newsletter {{ $isFullWidth ? $data->containerClass : '' }}">
    <div class="newsletter-section d-flex flex-column align-items-{{ $data->blockAlignment }} text-{{ $data->blockAlignment }}">
        @if(!$data->nlHideTopIcon)
        <div class="newsletter-icon display-4 mb-4"><i class="bi bi-envelope-paper"></i></div>
        @endif
        <h3 class="newsletter-title fw-bold mb-2">{{ $data->newsletterTitle }}</h3>
        <p class="newsletter-subtitle mb-4">{{ $data->newsletterSubtitle }}</p>

        {{-- Utilizing existing Livewire component --}}
        <div class="newsletter-wrapper">
            <livewire:newsletter-form :style="$data->nlStyle ?? 'generic'"
                :placeholder="$data->nlPlaceholder ?? null" :buttonText="$data->nlButtonText ?? null"
                :buttonDisplay="$data->nlButtonDisplay ?? 'text_only'"
                :buttonIcon="$data->nlButtonIcon ?? ''"
                :buttonStyle="$data->nlButtonStyle ?? 'primary'" :showName="$data->nlShowName ?? false" />
        </div>
    </div>
</div>
