@php
$data = (object)($data ?? []);
$style = $data->block_style ?? 'grid';
@endphp
@if (@$settings->actions->blog && $blogArticlesBlock && $blogArticles?->count() > 0)
<div id="{{ $data->uniqueId }}" class="blog-articles {{ $isFullWidth ? $data->containerClass : '' }}">
    @themeInclude('blocks.home.partials.block-title', [
    'data' => $data,
    'viewDefaultLink' => route('blog.index'),
    ])
    <div class="article-wrapper">
        <div
            class="row {{ $data->gridClass ?? 'row-cols-1 row-cols-md-2 row-cols-lg-3' }} g-4">
            @foreach ($blogArticles as $blogArticle)
            <div class="col" data-aos="fade-up" data-aos-duration="1000"
                data-aos-delay="{{ ($loop->index + 1) * 100 }}">

                <div class="blog-post {{ $data->postClass }}">
                    <div class="blog-post-header">
                        <a href="{{ $blogArticle->view_link }}" class="d-block h-100">
                            <img src="{{ $blogArticle->image_link }}" alt="{{ $blogArticle->title }}"
                                class="blog-post-img">
                        </a>
                        @if($style === 'split' && ($data->show_category ?? true))
                        <div class="position-absolute top-0 end-0 m-3">
                            <a href="{{ $blogArticle->category?->view_link ?? '#' }}"
                            class="badge bg-primary text-white rounded-pill px-3 py-2 shadow-sm">
                                {{ $blogArticle->category?->name ?? '' }}
                            </a>
                        </div>
                        @endif
                    </div>
                    <div class="blog-post-body d-flex flex-column align-items-{{ $data->alingment }} text-{{ $data->alingment }}">
                        <h5 class="blog-post-title mb-2">
                            <a href="{{ $blogArticle->view_link }}">
                                {{ truncateText($blogArticle->title, 50) }}
                            </a>
                        </h5>

                        <div class="d-flex align-items-center flex-wrap gap-1 text-muted small mb-2">
                            @if($data->author_name ?? true)
                            <div class="post-meta">
                                <i class="bi bi-person"></i>
                                <span class="ms-1">{{ $blogArticle->author?->full_name }}</span>
                            </div>
                            <div class="dot-seperator"></div>
                            @endif

                            @if($style !== 'split' && ($data->show_category ?? true))
                            <div class="post-meta">
                                <i class="bi bi-tag"></i>
                                <span class="ms-1">
                                    <a class="text-muted" href="{{ $blogArticle->category?->view_link ?? '#' }}">
                                        {{ $blogArticle->category?->name ?? '' }}</a>
                                </span>
                            </div>
                            <div class="dot-seperator"></div>
                            @endif

                            @if($data->post_date ?? true)
                            <div class="post-meta">
                                <i class="bi bi-clock-history"></i>
                                <span class="ms-1">{{ $blogArticle->created_at?->diffForHumans() }}</span>
                            </div>
                            @endif
                        </div>

                        <p class="blog-post-text mb-0">
                            {{ truncateText($blogArticle->short_description, 100) }}
                            @if($data->readmore_btn ?? true)
                            <a href="{{ $blogArticle->view_link }}" class="read-more-btn text-dark">
                                {{ translate('Read More') }} <i class="bi bi-arrow-right"></i>
                            </a>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
