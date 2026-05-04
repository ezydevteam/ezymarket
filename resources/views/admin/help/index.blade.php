@extends('admin.layouts.app')
@section('section', translate('Help Center'))
@section('title', translate('Help Center Articles'))
@section('create', route('admin.help.articles.create'))
@section('content')
    <div class="card">
        <div class="card-header p-3 border-bottom-small">
            <form action="{{ request()->url() }}" method="GET">
                <div class="row g-3">
                    <div class="col-12 col-lg-8">
                        <input type="text" name="search" class="form-control" placeholder="{{ translate('Search...') }}"
                            value="{{ request()->input('search') ?? '' }}">
                    </div>
                    <div class="col-12 col-lg-2">
                        <select name="category" class="form-select selectpicker" title="{{ translate('Category') }}"
                            data-live-search="true">
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col">
                        <button class="btn btn-primary w-100"><i class="fa fa-search"></i></button>
                    </div>
                    <div class="col">
                        <a href="{{ url()->current() }}" class="btn btn-secondary w-100">{{ translate('Reset') }}</a>
                    </div>
                </div>
            </form>
        </div>
        <x-datatable :items="$articles" emptyMessage="{{ translate('No articles found') }}"
            emptyDescription="{{ translate('All help center articles will appear here') }}"
            emptyIcon="bi-file-text">
            <thead>
                <tr>
                    <th>{{ translate('ID') }}</th>
                    <th>{{ translate('Article') }}</th>
                    <th class="text-center">{{ translate('Categories') }}</th>
                    <th class="text-center">{{ translate('Views') }}</th>
                    <th class="text-center">{{ translate('Likes') }}</th>
                    <th class="text-center">{{ translate('Dislikes') }}</th>
                    <th class="text-center">{{ translate('Published date') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                            @foreach ($articles as $article)
                                <tr>
                                    <td>{{ $article->id }}</td>
                                    <td>
                                        <div class="vironeer-content-box">
                                            <div class="icon me-3">
                                                <i class="fa-regular fa-file-lines fa-3x text-muted"></i>
                                            </div>
                                            <div>
                                                <a class="text-reset"
                                                    href="{{ route('admin.help.articles.edit', $article->id) }}">{{ truncateText($article->title, 50) }}</a>
                                                <p class="text-muted mb-0">
                                                    {{ truncateText($article->description, 60) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.help.categories.edit', $article->category->id) }}">
                                            <span class="badge bg-primary">{{ $article->category->name }}</span>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-dark">{{ $article->total_views }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $article->likes }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">{{ $article->dislikes }}</span>
                                    </td>
                                    <td class="text-center">
                                        {{ dateFormat($article->created_at) }}</td>
                                    <td>
                                        <div class="text-end">
                                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                                <x-dropdown.item href="{{ $article->view_link }}"
                                                    icon="bi-eye" iconClass="text-primary" target="_blank">
                                                    {{ translate('View') }}
                                                </x-dropdown.item>
                                                <x-dropdown.item href="{{ route('admin.help.articles.edit', $article->id) }}"
                                                    icon="bi-pencil" iconClass="text-primary">
                                                    {{ translate('Edit') }}
                                                </x-dropdown.item>
                                                <x-dropdown.item type="divider" />
                                                <x-dropdown.item href="{{ route('admin.help.articles.destroy', $article->id) }}"
                                                    icon="bi-trash" color="danger"
                                                    data-method="DELETE"
                                                    data-confirm="{{ translate('Are you sure you want to delete this article?') }}">
                                                    {{ translate('Delete') }}
                                                </x-dropdown.item>
                                            </x-dropdown>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-datatable>
    </div>
    {{ $articles->links() }}
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.js') }}"></script>
    @endpush
@endsection


















