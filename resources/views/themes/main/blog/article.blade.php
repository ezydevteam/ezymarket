@extends('themes.main.blog.layout')
@section('title', $blogArticle->title)
@section('breadcrumbs', Breadcrumbs::render('blog_article', $blogArticle))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', 'blog_article', $blogArticle))
@section('og_image', $blogArticle->image_link)
@section('description', $blogArticle->short_description ?? '')

@section('main')
@php
$blogSettings = themePageSettings();
$viewStyle = $blogSettings->blog_article_view_style ?? 'style-1';
$showAuthor = $blogSettings->blog_article_show_author ?? 1;
$showDate = $blogSettings->blog_article_show_date ?? 1;
$showCategory = $blogSettings->blog_article_show_category ?? 1;
$showComments = $blogSettings->blog_article_show_comments ?? 1;
$showShare = $blogSettings->blog_article_show_share ?? 1;
@endphp

<div class="blog-article-page {{ $viewStyle }}">
    {{-- Dynamic Header Based on Style --}}
    @if ($viewStyle === 'style-3') {{-- Immersive --}}
    <div class="blog-immersive-header mb-4">
        <div class="position-relative">
            <img src="{{ $blogArticle->image_link }}" alt="{{ $blogArticle->title }}"
                class="img-fluid rounded-4 shadow-sm w-100 object-fit-cover">
            <div class="container overflow-visible">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card position-relative border-0 shadow-lg rounded-4 p-4 p-md-4 mt-n5 mx-md-4 z-2">
                            <div class="text-center">
                                @if($showCategory)
                                <a class="badge bg-primary text-white text-decoration-none shadow-sm px-3 py-2 rounded-pill mb-3"
                                    href="{{ $blogArticle->category->view_link }}">
                                    {{ $blogArticle->category->name }}
                                </a>
                                @endif
                                <h1 class="h2 fw-bold mb-3">{{ $blogArticle->title }}</h1>
                                <div class="d-flex justify-content-center flex-wrap gap-3 text-gray-700 small">
                                    @if($showAuthor)
                                    <span><i class="bi bi-person me-1"></i>
                                        {{ $blogArticle->author?->username ?? translate('Admin') }}
                                    </span>
                                    @endif
                                    @if($showDate)
                                    <span><i class="bi bi-calendar me-1"></i>
                                        {{ $blogArticle->created_at->format('M d, Y') }}
                                    </span>
                                    @endif
                                    @if($showComments)
                                    <span><i class="bi bi-chat-quote me-1"></i>
                                        {{ $blogArticleComments->count() }} {{ translate('Comments') }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @elseif ($viewStyle === 'style-2') {{-- Modern --}}
    <div class="blog-modern-header mb-3">
        <div class="row align-items-center g-4">
            <div class="col-md-6 order-md-2">
                <img src="{{ $blogArticle->image_link }}" alt="{{ $blogArticle->title }}"
                    class="img-fluid rounded-3 shadow-sm w-100">
            </div>
            <div class="col-md-6 order-md-1">
                @if ($showCategory)
                <span class="text-primary fw-bold text-uppercase small mb-2 d-block">
                    {{ $blogArticle->category->name }}
                </span>
                @endif
                <h1 class="h2 fw-bold mb-3">{{ $blogArticle->title }}</h1>
                <div class="text-gray-700 small mb-0 d-flex gap-3">
                    @if($showAuthor)
                    <span><i class="bi bi-person me-1"></i>
                        {{ $blogArticle->author?->username ?? translate('Admin') }}
                    </span>
                    @endif
                    @if($showDate)
                    <span><i class="bi bi-calendar me-1"></i>
                        {{ $blogArticle->created_at->format('M d, Y') }}
                    </span>
                    @endif
                </div>
                @if ($showShare)
                <div class="mt-3">
                    @themeInclude('partials.share-buttons', [
                    'link' => $blogArticle->view_link,
                    'socials_class' => 'no-truncate-share gap-1'
                    ])
                </div>
                @endif
            </div>
        </div>
    </div>
    @else {{-- Minimalist (Classic) --}}
    <div class="mb-3">
        @if ($showCategory)
        <div class="mb-2">
            <a class="badge bg-light text-primary border" href="{{ $blogArticle->category->view_link }}">
                {{ $blogArticle->category->name }}
            </a>
        </div>
        @endif
        <h1 class="h2 fw-bold mb-3">{{ $blogArticle->title }}</h1>
        <div class="d-flex flex-wrap gap-3 text-gray-700 small mb-4">
            @if($showAuthor)
            <span><i class="bi bi-person me-1"></i>
                {{ $blogArticle->author?->username ?? translate('Admin') }}
            </span>
            @endif
            @if($showDate)
            <span><i class="bi bi-calendar me-1"></i>
                {{ $blogArticle->created_at->format('M d, Y') }}
            </span>
            @endif
            @if($showComments)
            <span><i class="bi bi-chat-quote me-1"></i>
                {{ $blogArticleComments->count() }} {{ translate('Comments') }}
            </span>
            @endif
        </div>
        <img src="{{ $blogArticle->image_link }}" alt="{{ $blogArticle->title }}"
            class="img-fluid rounded-3 mb-4 w-100">
    </div>
    @endif

    <div class="blog-post-body">
        <x-advertisement alias="blog_article_page_top" @class('mb-4') />

        <div class="blog-post-content">
            {!! $blogArticle->body !!}
        </div>

        <x-advertisement alias="blog_article_page_bottom" @class('mb-4') />

        @if ($showShare && $viewStyle !== 'style-2')
        <div class="mt-4 pt-4 border-top">
            <h6 class="mb-3 small text-uppercase fw-bold">
                {{ translate('Share this article') }}
            </h6>
            @themeInclude('partials.share-buttons', [
            'link' => $blogArticle->view_link,
            'socials_class' => 'no-truncate-share gap-1'
            ])
        </div>
        @endif

        @if ($showComments)
        <div class="comments mt-5">
            <h5 class="fw-bold mb-4 border-bottom pb-3">
                <i class="bi bi-chat-quote me-2"></i>{{ translate('Comments') }}
                <span class="bg-light px-2 py-1 rounded small border ms-1">
                    {{ $blogArticleComments->count() }}
                </span>
            </h5>
            @forelse ($blogArticleComments as $blogArticleComment)
            @php $user = $blogArticleComment->user; @endphp
            <div class="card border bg-light-subtle p-4 mb-3 rounded-3">
                <div class="d-flex gap-3">
                    <a href="{{ $user->profile_link }}" class="flex-shrink-0 user-avatar rounded">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}">
                    </a>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <a href="{{ $user->profile_link }}" class="text-dark fw-semibold hover-primary-underline">
                                {{ $user->username }}
                            </a>
                            <time class="small text-muted">
                                <i class="bi bi-clock-history me-1 fs-12"></i>
                                {{ dateFormat($blogArticleComment->created_at) }}
                            </time>
                        </div>
                        <p class="mb-0 mt-2">{!! sanitizeHtml($blogArticleComment->body, true) !!}</p>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-muted small">
                {{ translate('No comments yet. Be the first to comment!') }}
            </p>
            @endforelse

            <div class="card border border-dashed p-4 mt-4 rounded-3 bg-light">
                @if (authUser())
                <h5 class="h5 fw-bold mb-4">{{ translate('Leave a comment') }}</h5>
                <form action="{{ route('blog.comment', $blogArticle->slug) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <textarea class="form-control " name="comment" rows="4"
                            placeholder="{{ translate('Your comment...') }}" required>{{ old('comment') }}</textarea>
                    </div>
                    <x-captcha />
                    <button class="btn btn-primary px-4 mt-3 rounded-pill">{{ translate('Publish Comment') }}</button>
                </form>
                @else
                <div class="text-center py-2">
                    <a href="{{route('login')}}" class="needs-login-modal fw-bold text-primary text-decoration-none">
                        {{ translate('Login') }}
                    </a>
                    <span class="text-muted ms-1">{{ translate('to leave comments') }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@push('schema')
{!! schema($__env, 'article', ['article' => $blogArticle]) !!}
@endpush
@endsection
