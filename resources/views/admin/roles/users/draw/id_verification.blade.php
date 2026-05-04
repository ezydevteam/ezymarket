<span class="status-badge {{ $user->isIdVerified() ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-dark' }}">
    {{ $user->isIdVerified() ? translate('Verified') : translate('Unverified') }}
</span>
