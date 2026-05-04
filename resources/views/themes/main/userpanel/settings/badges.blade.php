@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('title', translate('Settings - Badges'))

@section('content')
    <div class="ajax-tabs">
        @themeInclude('userpanel.settings.includes.tabs-nav')
        <div class="ajax-tabs-content">
            <div class="card-v px-4 py-3 shadow-sm rounded-4">
                <div class="card-v-header border-0 p-0 mb-4">
                    <div class="d-flex align-items-center justify-content-between border-bottom-dashed pb-2">
                        <div>
                            <h5 class="mb-0 fw-bold">
                                <span class="icon-circle icon-circle-sm bg-primary-subtle text-primary me-2">
                                    <i class="bi bi-stars"></i>
                                </span>
                                {{ translate('My Badges') }}
                            </h5>
                        </div>
                    </div>
                </div>
                @if ($userBadges->count() > 0)
                    <ul class="sortable-list custom-list-group list-group" data-sortable="{{ route('user.settings.badges.sortable') }}">
                        @foreach ($userBadges as $userBadge)
                            <li class="list-group-item border p-3" data-id="{{ $userBadge->id }}">
                                <div class="row g-3 align-items-center">
                                    <div class="col-auto">
                                        <span class="sortable-list-handle text-muted hover-primary">
                                            <i class="bi bi-arrows-move fs-5"></i>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-auto">
                                                <img src="{{ $userBadge->badge->image_url }}"
                                                    alt="{{ $userBadge->badge->name }}"
                                                    title="{{ $userBadge->badge->full_title }}"
                                                    class="image-fluid image-sm">
                                            </div>
                                            <div class="col-auto">
                                                {{ $userBadge->badge->name }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="userpanel-card-empty pd">
                        <div class="mb-3">
                            <i class="bi bi-stars text-primary fs-1"></i>
                        </div>
                        <h4>{{ translate('You do not have any badges') }}</h4>
                        <p class="text-gray-600 mb-0">
                            {{ translate('When you have badges, they will appear here and you can sort them as you want.') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles_libs')
<link href="{{ asset('vendor/libs/jquery/jquery-ui.min.css') }}" />
@endpush
@push('scripts_libs')
<script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>
@endpush
