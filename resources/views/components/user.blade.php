@props([
    'user' => null,
    'class' => '',
    'showEmail' => true,
    'showAvatar' => true,
    'avatarSize' => 'md',
    'roleLabel' => null,
    'roleIcon' => 'bi-person',
    'linkRoute' => 'admin.roles.users.edit',
    'directRoute' => false,
    'emptyText' => null,
    'fontWeight' => 'medium',
    'linkTarget' => '_self',
    'customAttributes' => []
])

@if($user)
    <div class="user-box d-flex align-items-center gap-2 {{ $class }}">
        @if($showAvatar)
            <a class="image-fluid image-{{ $avatarSize }} rounded transition-all"
                href="{{ $directRoute ? route($linkRoute) :  route($linkRoute, $user->id) }}" target="{{ $linkTarget }}">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}">
            </a>
        @endif
        <span class="user-details">
            <a class="text-reset hover-primary fw-{{ $fontWeight }} transition-all"
                href="{{ $directRoute ? route($linkRoute) :  route($linkRoute, $user->id) }}" target="{{ $linkTarget }}">
                {{ $user->full_name }}
            </a>
            @isset($afterName)
                {{ $afterName }}
            @endisset
            @if($showEmail || !empty($roleLabel))
                <p class="text-muted small mb-0">
                    {{ $showEmail ? hideInDemo($user->email) : '' }}
                    @if(!empty($roleLabel))
                        <i class="bi {{ $roleIcon }} me-1"></i>{{ $user->role_label ?? $roleLabel }}
                    @endif
                </p>
            @endif
        </span>
    </div>
@else
    <span class="text-muted">{{ $emptyText ?? translate('User Deleted') }}</span>
@endif
