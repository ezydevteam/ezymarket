@php
$stars = $rating ?? $args->avg_reviews ?? $args->stars ?? $star ?? 0;
$percentage = ($stars / 5) * 100;
$percentage = max(0, min(100, $percentage));
$totalReviews = $args->total_reviews ?? 0;
$type = $type ?? 'default';
$rating_number = $rating_number ?? true;
$star_size = $star_size ?? 'fs-5';
@endphp
<div class="ratings {{ $ratings_classes ?? '' }} {{ $type === 'full' ? 'ratings-full' : '' }}">
    <div class="rating-display d-flex align-items-center">
        @if ($type === 'full')
        <div class="position-relative d-inline-block text-nowrap vertical-align-middle lh-1 {{ $star_size }}">
            <div class="d-flex text-warning opacity-75 {{ $star_gap ?? 'gap-2' }}">
                <i class="bi bi-star"></i>
                <i class="bi bi-star"></i>
                <i class="bi bi-star"></i>
                <i class="bi bi-star"></i>
                <i class="bi bi-star"></i>
            </div>
            <div class="position-absolute top-0 start-0 overflow-hidden h-100" style="width: {{ $percentage }}%;">
                <div class="d-flex text-warning max-content {{ $star_gap ?? 'gap-2' }}">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                </div>
            </div>
        </div>
        @else
        <span class="fillable-star-container d-inline-block">
            <i class="bi bi-star-fill fillable-star-bg"></i>

            <div class="fillable-star-fill-wrapper" style="width: {{ $percentage }}%;">
                <i class="bi bi-star-fill fillable-star-fg"></i>
            </div>
        </span>
        @endif

        @if($rating_number)
        <span class="rating-number lh-0">
            {{ number_format($stars, 1) }}
        </span>
        @endif

        @if( ($counter_only ?? false) || !empty($label_only) || !empty($counter_label) || !empty($custom_label))
        <span class="text-gray-700 fs-12">
            @if(!empty($custom_label))
            {{-- Fully custom format: e.g. ':count reviews' --}}
            {{ translate($custom_label, ['count' => number_format($totalReviews)]) }}

            @elseif(!empty($counter_label))
            {{-- Count + label: e.g. "(5 reviews)" or "(1 review)" --}}
            ({{ number_format($totalReviews) }} {{ translate($totalReviews > 1 ? Str::plural($counter_label) :
            $counter_label) }})

            @elseif(!empty($label_only))
            {{-- Standalone label without count: e.g. "rating" --}}
            {{ translate($totalReviews > 1 ? Str::plural($label_only) : $label_only) }}

            @elseif($counter_only ?? false)
            {{-- Plain count: e.g. "(5)" --}}
            ({{ number_format($totalReviews) }})
            @endif
        </span>
        @endif

    </div>
</div>
