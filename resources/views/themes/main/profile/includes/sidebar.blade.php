@php
    $isOwner = authUser()?->id == $user->id;

    // Completion Items (14 Total)
    $completionItems = [
        'avatar'         => $user->avatar ?? null,
        'cover'          => $user->basic_info['cover'] ?? null,
        'heading'        => $user->basic_info['heading'] ?? null,
        'bio'            => $user->basic_info['bio'] ?? null,
        'birth_date'     => $user->basic_info['birth_date'] ?? null,
        'gender'         => $user->basic_info['gender'] ?? null,
        'hobby'          => $user->basic_info['hobby'] ?? null,
        'profession'     => $user->basic_info['profession'] ?? null,
        'company'        => $user->basic_info['company'] ?? null,
        'website'        => $user->basic_info['website'] ?? null,
        'business_email' => $user->basic_info['business_email'] ?? null,
        'timezone'       => $user->basic_info['timezone'] ?? null,
        'nationality'    => $user->basic_info['nationality'] ?? null,
        'language'       => $user->basic_info['language'] ?? null,
    ];

    $completedCount = collect($completionItems)->filter()->count();
    $completedAboutCount = collect($completionItems)->except(['avatar', 'cover', 'heading', 'bio'])->filter()->count();
    $percentage = round(($completedCount / 14) * 100);
    $radius = 30;
    $circumference = 2 * pi() * $radius;
    $offset = $circumference - ($percentage / 100) * $circumference;
    $progressColor = $percentage >= 80 ? 'var(--bs-success)' : ($percentage >= 50 ? 'var(--bs-warning)' : 'var(--bs-danger)');
@endphp

<div class="profile-sidebar">
    {{-- Completion Progress --}}
    @if ($isOwner)
        <div class="sidebar-block border-0 shadow-sm rounded-4 p-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="progress-circle-wrapper">
                    <svg class="progress-circle" width="80" height="80">
                        <circle class="bg" cx="40" cy="40" r="{{ $radius }}"></circle>
                        <circle class="meter" cx="40" cy="40" r="{{ $radius }}"
                                style="stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $offset }}; stroke: {{ $progressColor }}; ">
                        </circle>
                    </svg>
                    <div class="progress-percentage">{{ $percentage }}%</div>
                </div>
                <div>
                    @if($percentage < 100)
                        <h6 class="fw-bold mb-0 text-dark fs-15">{{ translate('Profile Completion') }}</h6>
                        <span class="text-gray-600 fs-12">{{ translate(':count of 14 completed', ['count' => $completedCount]) }}</span>
                        <a href="{{ route('user.settings.profile') }}" rel="noopener noreferrer"
                            class="btn btn-outline-primary rounded-pill fs-12 px-3 py-0 mt-2">{{ translate('Complete Now') }}</a>
                    @else
                        <h6 class="fw-bold mb-0 text-dark fs-15">{{ translate('Congratulations!') }}</h6>
                        <span class="text-gray-600 fs-12">{{ translate('Your profile is 100% completed') }}</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Basic Information List --}}
    @if ($completedAboutCount > 0)
    <div class="sidebar-block border-0 shadow-sm rounded-4 p-3 mb-4">
        <h5 class="profile-sidebar-title">{{ translate('About') }}</h5>
        <div class="profile-info-list">
            @if(isset($user->basic_info['profession']))
            <div class="profile-info-item">
                <div class="profile-info-icon"><i class="bi bi-briefcase"></i></div>
                <div>
                    <div class="text-gray-600 fs-11">{{ translate('Profession') }}</div>
                    <div class="fw-medium text-dark fs-14">{{ $user->basic_info['profession'] }}</div>
                </div>
            </div>
            @endif

            @if(isset($user->basic_info['company']))
            <div class="profile-info-item">
                <div class="profile-info-icon"><i class="bi bi-building"></i></div>
                <div>
                    <div class="text-gray-600 fs-11">{{ translate('Working at') }}</div>
                    <div class="fw-medium text-dark fs-14">{{ $user->basic_info['company'] }}</div>
                </div>
            </div>
            @endif

            @if(isset($user->basic_info['nationality']))
            <div class="profile-info-item">
                <div class="profile-info-icon"><i class="bi bi-globe"></i></div>
                <div>
                    <div class="text-gray-600 fs-11">{{ translate('Nationality') }}</div>
                    <div class="fw-medium text-dark fs-14">
                        {{ \App\Classes\Nationality::get($user->basic_info['nationality']) ?? $user->basic_info['nationality'] }}
                    </div>
                </div>
            </div>
            @endif

            @if(isset($user->basic_info['language']))
            <div class="profile-info-item">
                <div class="profile-info-icon"><i class="bi bi-translate"></i></div>
                <div>
                    <div class="text-gray-600 fs-11">{{ translate('Language') }}</div>
                    <div class="fw-medium text-dark fs-14">
                        {{ \App\Classes\Localization::get($user->basic_info['language']) ?? $user->basic_info['language'] }}
                    </div>
                </div>
            </div>
            @endif

            @if(isset($user->basic_info['hobby']))
            <div class="profile-info-item">
                <div class="profile-info-icon"><i class="bi bi-heart"></i></div>
                <div>
                    <div class="text-gray-600 fs-11">{{ translate('Hobby') }}</div>
                    <div class="fw-medium text-dark fs-14">{{ $user->basic_info['hobby'] }}</div>
                </div>
            </div>
            @endif

            @if(isset($user->basic_info['website']))
            <div class="profile-info-item">
                <div class="profile-info-icon"><i class="bi bi-link-45deg"></i></div>
                <div>
                    <div class="text-gray-600 fs-11">{{ translate('Website') }}</div>
                    <div class="fw-medium text-dark fs-14">
                        <a href="{{ $user->basic_info['website'] }}" target="_blank" class="text-decoration-none text-dark hover-primary">
                            {{ parse_url($user->basic_info['website'], PHP_URL_HOST) ?: $user->basic_info['website'] }}
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($user->basic_info['business_email']))
            <div class="profile-info-item">
                <div class="profile-info-icon bg-light-primary text-primary"><i class="bi bi-envelope"></i></div>
                <div>
                    <div class="text-gray-600 fs-11">{{ translate('Business Email') }}</div>
                    <div class="fw-medium text-dark fs-14">{{ $user->basic_info['business_email'] }}</div>
                </div>
            </div>
            @endif

            @if(isset($user->basic_info['birth_date']))
            <div class="profile-info-item">
                <div class="profile-info-icon"><i class="bi bi-calendar-event"></i></div>
                <div>
                    <div class="text-gray-600 fs-11">{{ translate('Birth Date') }}</div>
                    <div class="fw-medium text-dark fs-14">{{ $user->basic_info['birth_date'] }}</div>
                </div>
            </div>
            @endif

            @if(isset($user->basic_info['gender']))
            <div class="profile-info-item">
                <div class="profile-info-icon"><i class="bi bi-person"></i></div>
                <div>
                    <div class="text-gray-600 fs-11">{{ translate('Gender') }}</div>
                    <div class="fw-medium text-dark fs-14">
                        {{ translate(str($user->basic_info['gender'])->replace('_', ' ')->title()) }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    @php $userBadges = $user->badges; @endphp
    @if ($userBadges->count() > 0)
        <div class="sidebar-block border-0 shadow-sm rounded-4 p-3 mb-4">
            <h5 class="profile-sidebar-title">{{ translate('Badges') }}</h5>
            <div class="row row-cols-auto g-2">
                @foreach ($userBadges as $userBadge)
                    @if ($userBadge->badge->alias !== \App\Enums\BadgeAlias::VERIFIED_ACCOUNT)
                    <div class="col">
                        <div class="seller-badge">
                            <img src="{{ $userBadge->badge->image_url }}" alt="{{ $userBadge->badge->name }}"
                            data-bs-toggle="tooltip" data-bs-title="{{ $userBadge->badge->full_title }}">
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
