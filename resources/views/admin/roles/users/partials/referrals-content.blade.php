@if ($user->referral_link)
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3 small uppercase ls-1 text-muted">{{ translate('Unique Referral Link') }}</h6>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-link-45deg"></i></span>
            <input id="refLink" type="text" class="form-control border-start-0 ps-2 fw-medium h-auto"
                value="{{ $user->referral_link }}" readonly>
            <button type="button" class="btn btn-primary px-4 btn-copy fw-bold" id="copy-ref-btn"
                data-clipboard-target="#refLink">
                <i class="bi bi-files me-2"></i>
                {{ translate('Copy') }}
            </button>
        </div>
        <p class="form-text mt-2 mb-0 small">{{ translate('The user can share this link to earn commission from
            referrals.') }}</p>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="p-4">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                    <i class="bi bi-person-lines-fill fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"> {{ translate('Referred Users') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('List of users who joined using this user\'s link') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table ezydev-table">
                <thead>
                    <tr>
                        <th>{{ translate('User') }}</th>
                        <th class="text-center">{{ translate('Total Earnings') }}</th>
                        <th class="text-center">{{ translate('Joined Date') }}</th>
                        <th width="60" class="text-end">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($referrals as $referral)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <x-user :user="$referral->user" avatar-size="sm" />
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-success">{{ getAmount($referral->earnings) }}</span>
                        </td>
                        <td class="text-center">
                            <div>{{ dateFormat($referral->created_at, 'M d, Y') }}</div>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn-icon" data-bs-toggle="dropdown"
                                    data-bs-popper-config='{"strategy": "fixed"}'>
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li>
                                        <a class="dropdown-item py-2"
                                            href="{{ route('admin.roles.users.edit', $referral->user->id) }}"
                                            target="_blank">
                                            <i class="bi bi-eye me-2 text-primary"></i>{{ translate('View Details') }}
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item py-2 text-danger action-confirm"
                                            data-text="{{ translate('Are you sure you want to remove this referral connection?') }}"
                                            data-action="{{ route('admin.roles.users.referrals.delete', [$user->id, $referral->id]) }}"
                                            data-method="DELETE">
                                            <i class="bi bi-trash me-2"></i>{{ translate('Remove') }}
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-people mb-2 d-block fs-1"></i>
                                {{ translate('No referrals found for this user.') }}
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($referrals->hasPages())
        <div class="mt-4 d-flex justify-content-center ajax-pagination">
            {{ $referrals->links() }}
        </div>
        @endif
    </div>
</div>
