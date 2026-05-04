{{-- Blog Categories Widget --}}
@php
    $cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
    $titleStyle = $widgetSettings['title_style'] ?? 'default';
    $titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3 pb-0' );
    $contentPadding = ($cardStyle === 'none') ? 'p-0' : (in_array($titleStyle, ['style_1', 'style_2']) ? 'p-3' : 'p-0');
@endphp
<div class="widget-blog-categories {{ $titlePadding }}">
    @include('widgets.partials.widget-title', [
        'title' => $widgetTitle ?? '',
        'widgetSettings' => $widgetSettings ?? []
    ])
    <div class="widget-content {{ $contentPadding }}">
        @if($categories->isNotEmpty())
            <ul class="list-group">
                @foreach($categories as $category)
                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                        <a href="{{ $category->view_link }}"
                            style="color: {{ $widgetSettings['category_color'] ?? '#000000' }};">
                            {{ $category->name }}
                        </a>
                        @if($widgetSettings['show_count'] ?? true)
                            <span class="badge rounded-pill"
                            style="background-color: {{ $widgetSettings['category_color'] ?? '#000000' }};">
                                {{ $category->articles_count }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <div class="p-3 pt-0">
                <p class="text-muted mb-0">{{ translate('No categories found') }}</p>
            </div>
        @endif
    </div>
</div>
