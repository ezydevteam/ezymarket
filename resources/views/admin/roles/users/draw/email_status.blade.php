<span class="status-badge {{ $user->isEmailVerified() ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
    {{ $user->isEmailVerified() ? translate('Verified') : translate('Unverified') }}
</span>
