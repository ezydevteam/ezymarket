<div class="profile-following-content">
    <div class="row align-items-center justify-content-between g-3 mb-3 pb-2 border-bottom-dashed">
        <div class="col-auto">
            <h4 class="fw-bold text-gray-700 mb-0 h5">{{ translate('Following') }}</h4>
        </div>
        <div class="col-auto">
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fs-12">
                {{ translate('Following :count Users', ['count' => numberFormat($user->total_following)]) }}
            </span>
        </div>
    </div>

    @if ($followings->count() > 0)
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4" id="following-list">
            @foreach ($followings as $following)
                @php $following = $following->following; @endphp
                <div class="col">
                    <div class="card rounded-4 p-3 border h-100">
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ $following->profile_link }}" class="user-avatar user-avatar-md rounded flex-shrink-0">
                                <img src="{{ $following->avatar_url }}" alt="{{ $following->username }}">
                            </a>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-1">
                                    <a href="{{ $following->profile_link }}"
                                        class="text-dark fw-bold text-truncate hover-primary">
                                        {{ $following->username }}
                                    </a>
                                    @if ($following->id == authUser()?->id)
                                        <span class="border border-primary text-primary rounded-pill fw-light px-2 py-0 fs-10">
                                            {{ translate('You') }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mb-0 text-gray-600 fs-12 mt-1">
                                    {{ translate('Since :date', ['date' => dateFormat($following->current_follower?->created_at ?? now(), 'M Y')]) }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <livewire:follow :user="$following" btnClass="btn-dark rounded-pill" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @themeInclude('partials.load-more', [
            'items' => $followings,
            'target' => '#following-list',
        ])
    @else
        <div class="text-center py-5 bg-light rounded-4">
            <div class="opacity-25 mb-3">
                <i class="bi bi-person-plus display-4"></i>
            </div>
            <h5 class="fw-bold">{{ translate('Not following anyone') }}</h5>
            <p class="text-muted mb-0">{{ translate(':user isn\'t following anyone yet.', ['user'=> $user->full_name]) }}</p>
        </div>
    @endif
</div>
