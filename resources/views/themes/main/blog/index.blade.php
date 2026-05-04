@extends('themes.main.blog.layout')
@section('header_title', $title)
@section('title', $title)
@section('breadcrumbs', Breadcrumbs::render($breadcrumbData['alias'], ...$breadcrumbData['params']))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', $breadcrumbData['alias'], ...$breadcrumbData['params']))

@section('main')
    @php
        $blogSettings = themePageSettings();
        $viewStyle = $blogSettings->blog_index_view_style ?? 'grid';
    @endphp

    <x-advertisement alias="blog_page_top" @class('mb-4') />

    @if ($blogArticles->count() > 0)
        <div class="row {{ $viewStyle === 'grid' ? 'row-cols-1 row-cols-md-2 row-cols-xl-3' : 'row-cols-1' }} g-4">
            @foreach ($blogArticles as $blogArticle)
                <div class="col">
                    @themeInclude('blog.partials.blog-post', [
                        'blogArticle' => $blogArticle,
                        'viewStyle' => $viewStyle,
                        'showAuthor' => $blogSettings->blog_index_show_author ?? 1,
                        'showDate' => $blogSettings->blog_index_show_date ?? 1,
                        'showCategory' => $blogSettings->blog_index_show_category ?? 1,
                        'showReadMore' => $blogSettings->blog_index_show_read_more ?? 1,
                    ])
                </div>
            @endforeach
        </div>
        <div class="mt-4">
            {{ $blogArticles->links() }}
        </div>
    @else
        <div class="card-v border p-5 text-center">
            <span class="text-muted">{{ translate('No blog articles found') }}</span>
        </div>
    @endif

    <x-advertisement alias="blog_page_bottom" @class('mt-4') />
@endsection
