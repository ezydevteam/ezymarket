<div class="profile-followers-content">
    <div class="row align-items-center justify-content-between g-3 mb-3 pb-2 border-bottom-dashed">
        <div class="col-auto">
            <h4 class="fw-bold text-gray-700 mb-0 h5">{{ translate('Followers') }}</h4>
        </div>
        <div class="col-auto" wire:poll.5s>
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fs-12">
                {{ translate(':count Followers', ['count' => numberFormat($user->fresh()->total_followers)]) }}
            </span>
        </div>
    </div>

    @if ($followers->count() > 0)
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4" id="followers-list">
            @foreach ($followers as $follower)
                @php $follower = $follower->follower; @endphp
                <div class="col">
                    <div class="card rounded-4 p-3 border h-100">
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ $follower->profile_link }}" class="user-avatar user-avatar-md rounded flex-shrink-0">
                                <img src="{{ $follower->avatar_url }}" alt="{{ $follower->username }}">
                            </a>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-1">
                                    <a href="{{ $follower->profile_link }}"
                                        class="text-dark fw-bold text-truncate hover-primary">
                                        {{ $follower->username }}
                                    </a>
                                    @if ($follower->id == authUser()?->id)
                                        <span class="border border-primary text-primary rounded-pill fw-light px-2 py-0 fs-10">
                                            {{ translate('You') }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mb-0 text-gray-600 fs-12 mt-1">
                                    {{ translate('Since :date', ['date' => dateFormat($follower->created_at, 'M Y')]) }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <livewire:follow :user="$follower" btnClass="btn-dark rounded-pill" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @themeInclude('partials.load-more', [
            'items' => $followers,
            'target' => '#followers-list',
        ])
    @else
        <div class="text-center py-5 bg-light rounded-4">
            <div class="opacity-25 mb-3">
                <i class="bi bi-people display-4"></i>
            </div>
            <h5 class="fw-bold">{{ translate('No followers yet') }}</h5>
            <p class="text-muted mb-0">{{ translate(':user doesn\'t have any followers yet.', ['user'=> $user->full_name]) }}</p>
        </div>
    @endif
</div>
