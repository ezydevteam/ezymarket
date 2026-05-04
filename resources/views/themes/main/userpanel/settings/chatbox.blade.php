@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('title', translate('Settings - Chatbox'))

@section('content')
    <div class="ajax-tabs">
        @themeInclude('userpanel.settings.includes.tabs-nav')
        <div class="ajax-tabs-content">
            <div class="card border-0 rounded-4 px-4 shadow-sm">
                @if (!$blockedUsers->isEmpty())
                    <div class="border-bottom-dashed py-2">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
                            <div>
                                <h5 class="mb-1 d-flex align-items-center">
                                    <span class="icon-circle icon-circle-sm bg-danger-subtle text-danger">
                                        <i class="bi bi-ban fs-5"></i>
                                    </span>
                                    {{ translate('Blocked Users') }}
                                </h5>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">
                                {{ $blockedUsers->count() }} {{ translate('Blocked') }}
                            </span>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="row g-4">
                            @foreach ($blockedUsers as $user)
                                <div class="col-12 col-md-6 col-lg-4" id="user-row-{{ $user['id'] }}">
                                    <div class="blocked-user-card p-3 border rounded-4 transition-all hover-shadow-sm h-100 position-relative">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="flex-shrink-0">
                                                <a href="{{ $user['profile_link'] }}"
                                                    class="d-block position-relative shadow-sm rounded-circle p-1 bg-white">
                                                    <img src="{{ $user['avatar'] }}"
                                                        class="user-avatar user-avatar-md"
                                                        alt="{{ $user['name'] }}">
                                                    <span class="position-absolute bottom-0 end-0 bg-danger border border-2 border-white rounded-circle p-1"
                                                        title="{{ translate('Blocked') }}"></span>
                                                </a>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <h6 class="mb-0 text-truncate fw-bold">
                                                    <a href="{{ $user['profile_link'] }}" class="text-dark text-decoration-none">
                                                        {{ $user['name'] }}
                                                    </a>
                                                </h6>
                                                <div class="d-flex align-items-center mt-1">
                                                    <span class="text-muted fs-13 d-flex align-items-center">
                                                        <i class="bi bi-clock-history me-1"></i>
                                                        {{ $user['blocked_at_human'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 pt-3 border-top">
                                            <button type="button"
                                                    class="btn btn-light btn-sm w-100 rounded-3 fw-medium d-flex align-items-center justify-content-center text-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#unblockModal{{ $user['id'] }}">
                                                <i class="bi bi-unlock-fill me-2"></i>{{ translate('Unblock User') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <div class="icon-circle icon-circle-xl bg-success-subtle text-success">
                                <i class="bi bi-shield-check display-4"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-2">{{ translate('Your Inbox is Secure') }}</h5>
                        <p class="text-gray-600 max-w-400 mx-auto">
                            {{ translate('You haven\'t blocked any users yet. Your messaging environment is currently open to everyone.') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Unblock Modals --}}
    @foreach ($blockedUsers as $user)
        <x-modal id="unblockModal{{ $user['id'] }}" :title="translate('Unblock User')" icon="bi-unlock-fill">
            <form action="{{ route('user.settings.chatbox.unblock-user') }}" class="ajax-form" id="unblockForm{{ $user['id'] }}"
                data-reload="true" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                <div class="text-center">
                    <div class="mb-4">
                        <div class="position-relative d-inline-block">
                            <img src="{{ $user['avatar'] }}" alt="{{ $user['name'] }}"
                                class="user-avatar user-avatar-md shadow-sm p-1 bg-white border">
                            <div class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                style="width: 24px; height: 24px; border: 2px solid #fff;">
                                <i class="bi bi-person-check-fill fs-12"></i>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-1">{{ translate('Unblock') }} {{ $user['name'] }}?</h5>
                    <p class="text-gray-600 fs-14 px-3 mb-0">
                        {{ translate('They will be able to contact you again and view your profile information.') }}
                    </p>
                </div>
            </form>

            <x-slot:footer>
                <button type="button" class="btn btn-outline-secondary flex-fill text-uppercase" data-bs-dismiss="modal">
                    {{ translate('Cancel') }}
                </button>
                <button type="submit" form="unblockForm{{ $user['id'] }}" class="btn btn-danger flex-fill text-uppercase">
                    {{ translate('Yes, Unblock') }}
                </button>
            </x-slot:footer>
        </x-modal>
    @endforeach
@endsection



