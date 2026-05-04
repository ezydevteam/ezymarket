{{-- Blog Posts Widget (Recent or Popular) --}}
@php
    $cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
    $titleStyle = $widgetSettings['title_style'] ?? 'default';
    $titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3 pb-2' );
    $contentPadding = ($cardStyle === 'none') ? 'p-0' : (in_array($titleStyle, ['style_1', 'style_2']) ? 'px-3 py-2' : 'p-0');
@endphp

<div class="widget-blog-posts {{ $titlePadding }}">
    @include('widgets.partials.widget-title', [
        'title' => $widgetTitle ?? '',
        'widgetSettings' => $widgetSettings ?? []
    ])
    <div class="widget-content {{ $contentPadding }}">
        @if($articles->isNotEmpty())
            <div class="list-group list-group-flush border-0">
                @foreach($articles as $index => $article)
                    <a href="{{ $article->view_link }}"
                        class="list-group-item list-group-item-action d-flex align-items-center hover-primary border-0 px-0">
                        @if(($widgetSettings['post_type'] ?? 'recent') === 'popular')
                            <span class="badge bg-primary rounded-pill fs-10 me-2 px-2">{{ $index + 1 }}</span>
                        @endif

                        @if($widgetSettings['show_image'] ?? true)
                            <div class="flex-shrink-0 me-3 image-fluid rounded">
                                <img src="{{ $article->image_link }}" alt="{{ $article->title }}">
                            </div>
                        @endif

                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="mb-0 text-truncate fs-15 fw-medium">{{ $article->title }}</h6>
                            @if($widgetSettings['show_meta'] ?? true)
                                <div class="text-muted d-flex align-items-center mt-1 fs-12">
                                    @if(($widgetSettings['post_type'] ?? 'recent') === 'popular')
                                        <i class="bi bi-eye me-1"></i>
                                        <span>{{ translate(':view views', ['view' => numberFormat($article->views ?? 0)]) }}</span>
                                    @else
                                        <i class="bi bi-clock-history me-1"></i>
                                        <span>{{ $article->created_at->format('M d, Y') }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="py-3 px-3 text-center">
                <p class="text-muted mb-0 small">{{ translate('No articles found') }}</p>
            </div>
        @endif
    </div>
</div>
