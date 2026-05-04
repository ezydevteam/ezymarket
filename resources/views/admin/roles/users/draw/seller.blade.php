<span class="status-badge {{ $user->isSeller() ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}">
    {{ $user->isSeller() ? translate('Yes') : translate('No') }}
</span>
