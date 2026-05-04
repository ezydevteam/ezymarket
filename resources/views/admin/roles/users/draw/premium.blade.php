<span class="status-badge {{ $user->isPremiumMember() ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary' }}">
    {{ $user->isPremiumMember() ? translate('Yes') : translate('No') }}
</span>
