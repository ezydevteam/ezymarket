<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-lg-8 border-lg-end">
                <div class="p-4">
                    <div class="d-flex align-items-center gap-4">
                        <div class="position-relative">
                            <img src="{{ $staff->avatar_url }}" alt="{{ $staff->username }}"
                                class="image-fluid image-xl rounded border border-3 border-white shadow-sm">
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">{{ $staff->full_name }}</h4>
                            <p class="text-muted mb-2">
                                {{ '@' . $staff->username }} <span class="mx-1 text-gray-300">|</span>
                                <i class="bi bi-shield-check me-1"></i>{{ $staff->role->label() }}
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-{{ $staff->role->color() }}-subtle text-{{ $staff->role->color() }} border border-{{ $staff->role->color() }}-subtle rounded-pill px-3">
                                    {{ $staff->role->label() }}
                                </span>
                                @if($staff->status)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">
                                        <i class="bi bi-check-circle me-1"></i>{{ translate('Active') }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">
                                        <i class="bi bi-dash-circle me-1"></i>{{ translate('Inactive') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <div class="row g-3">
                            <div class="col-sm-auto me-4">
                                <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Email Address') }}</p>
                                <p class="mb-0 fw-medium">{{ hideInDemo($staff->email) }}</p>
                            </div>
                            <div class="col-sm-auto me-4">
                                <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Joined') }}</p>
                                <p class="mb-0 fw-medium">{{ dateFormat($staff->created_at, 'M d, Y') }}</p>
                            </div>
                            <div class="col-sm-auto">
                                <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Last Login') }}</p>
                                <p class="mb-0 fw-medium text-primary">
                                    {{ $staff->last_login_at ? timeAgo($staff->last_login_at) : translate('Never') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="d-flex flex-column justify-content-between p-4 h-100">
                    <div class="row g-4 mb-3 text-center">
                        @if ($staff->role === \App\Enums\Admin\AdminRole::REVIEWER)
                        <div class="col-6">
                            <div class="stats-item">
                                <h3 class="fw-bold mb-0">{{ numberFormat($staff->categories->count()) }}</h3>
                                <p class="text-muted small mb-0">{{ translate('Categories') }}</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stats-item">
                                <h3 class="fw-bold mb-0 text-success">{{ numberFormat($staff->reviewed_products_count ?? 0) }}</h3>
                                <p class="text-muted small mb-0">{{ translate('Products Reviewed') }}</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bg-primary-subtle border-0 rounded-4 p-2">
                                <p class="small mb-0">
                                    {{ translate('Reviewers can only manage products within their assigned categories. Select one or
                                    more categories that this reviewer will be responsible for.') }}
                                </p>
                            </div>
                        </div>
                        @else
                        <div class="col-12">
                            <div class="py-3">
                                <i class="bi bi-shield-lock text-muted fs-1 mb-2"></i>
                                <p class="text-gray-700 mb-0">{{ $staff->role->description() }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-auto">
                        @php
                            $is_active = $staff->status;
                            $toggleText = $is_active ? 'Suspend' : 'Activate';
                            $icon = $is_active ? 'slash-circle' : 'check-circle';
                            $color = $is_active ? 'danger' : 'success';
                        @endphp

                        <button class="btn bg-{{ $color }}-subtle text-{{ $color }} w-100 fw-bold action-confirm"
                            data-action="{{ route('admin.roles.staff.status.update', $staff->id) }}"
                            data-method="POST"
                            data-text="{{ translate('Are you sure you want to ' . $toggleText . ' this staff member?') }}">
                            <i class="bi bi-{{ $icon }} me-2"></i>{{ translate($toggleText) }}
                        </button>

                        <button type="button" class="btn bg-primary-subtle text-primary fw-bold px-3"
                            data-bs-target="#sendMailStaffModal"
                            data-bs-toggle="modal" title="{{ translate('Send email') }}">
                            <i class="bi bi-envelope"></i>
                        </button>

                        <div class="dropdown">
                            <button class="btn bg-secondary-subtle text-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li>
                                    <a href="{{ route('admin.roles.staff.login', $staff->id) }}" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i>{{ translate('Login as Staff') }}
                                    </a>
                                </li>
                                <li> <hr class="dropdown-divider"> </li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger action-confirm"
                                        data-action="{{ route('admin.roles.staff.destroy', $staff->id) }}"
                                        data-method="DELETE"
                                        data-text="{{ translate('Are you sure you want to delete this staff member? This action cannot be undone.') }}">
                                        <i class="bi bi-trash me-2"></i>{{ translate('Delete Staff') }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
