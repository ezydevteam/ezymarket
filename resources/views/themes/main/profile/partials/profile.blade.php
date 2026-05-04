<div class="profile-content-header mb-3">
    @if (isset($user->basic_info['heading']))
        <h4 class="fw-bold text-dark mb-0">{{ $user->basic_info['heading'] }}</h4>
    @else
        <h4 class="fw-bold text-dark mb-1">
            {{ translate('About :username', ['username' => $user->full_name]) }}
        </h4>
        <p class="text-muted fs-14 mb-0">
            {{ translate('A look into the story and professional background of :name.', ['name' => $user->username]) }}
        </p>
    @endif
</div>

<div class="profile-about-content">
    <div class="lh-base">
        @if (isset($user->basic_info['bio']))
            {!! sanitizeRichText($user->basic_info['bio']) !!}
        @else
            <div class="text-center py-5">
                <i class="bi bi-card-text fs-1 mb-3 text-gray-300 d-block"></i>
                <p class="text-muted italic mb-0">
                    {{ translate(':user hasn\'t added a biography yet.', ['user'=> $user->full_name]) }}
                </p>
            </div>
        @endif
    </div>
</div>
