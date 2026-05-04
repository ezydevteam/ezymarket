<span class="status-badge {{ $user->isActive() ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
    {{ $user->isActive() ? translate('Active') : translate('Suspended') }}
</span>
