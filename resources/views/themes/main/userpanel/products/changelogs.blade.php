@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('section', translate('Changelogs'))
@section('title', $product->name)
@section('content')
    <div class="product-dashboard">
        @themeInclude('userpanel.products.includes.tabs-nav')
        <div class="ajax-tabs-content">
            <div class="row g-3">
                @if (!$product->isRestricted())
                    <div class="col-lg-8">
                        @if ($product->isApproved())
                            <div class="d-flex flex-column gap-4">
                                <!-- Add New Log Form -->
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="icon-circle icon-circle-md bg-primary-light text-primary me-3">
                                                <i class="bi bi-plus-lg fs-5"></i>
                                            </div>
                                            <h5 class="mb-0 fw-bold">{{ translate('Add New Log') }}</h5>
                                        </div>
                                        <form action="{{ route('user.product.changelogs.store', $product->id) }}" class="ajax-form" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label text-gray-700 fw-medium fs-14">{{ translate('Version Number') }}</label>
                                                <input type="text" name="version" class="form-control form-control-md rounded-3"
                                                    value="{{ old('version') }}"
                                                    placeholder="{{ translate('e.g. 1.0.1') }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-gray-700 fw-medium fs-14">{{ translate('Release Notes') }}</label>
                                                <textarea name="log" class="form-control rounded-3" rows="5"
                                                    placeholder="{{ translate('What changed in this version?') }}" required>{{ old('log') }}</textarea>
                                            </div>
                                            <div class="text-end">
                                                <button type="submit" class="btn btn-primary btn-md btn-modern px-5 rounded-3">
                                                    {{ translate('Submit Log') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Changelog List -->
                                <div class="changelog-list d-flex flex-column gap-3">
                                    @forelse ($changelogs as $changelog)
                                        <div class="card border-0 shadow-sm rounded-4 transition-all hover-shadow">
                                            <div class="card-body p-4">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-primary-light text-primary rounded-pill px-3 py-2 me-3 fs-13 fw-bold">
                                                            v{{ $changelog->version }}
                                                        </span>
                                                        <div class="text-gray-600 fs-13">
                                                            <i class="bi bi-calendar3 me-1"></i>
                                                            {{ dateFormat($changelog->created_at) }}
                                                        </div>
                                                    </div>
                                                    <button class="btn btn-transparent text-danger action-confirm"
                                                        data-action="{{ route('user.product.changelogs.delete', [$product->id, $changelog->id]) }}"
                                                        data-method="DELETE" data-text="{{ translate('Are you sure want to delete this changelog?') }}"
                                                        title="{{ translate('Delete') }}">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>
                                                <div class="changelog-text fs-14 bg-light p-3 rounded-3">
                                                    <code class="text-gray-800" style="white-space: pre-wrap;">{{ $changelog->log }}</code>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-5 bg-light rounded-4 border border-dashed">
                                            <i class="bi bi-journal-text fs-1 text-muted opacity-25"></i>
                                            <p class="text-muted mt-2 mb-0">{{ translate('No changelogs recorded yet.') }}</p>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="mt-2">
                                    {{ $changelogs->links() }}
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info border-0 rounded-4 p-4 shadow-sm">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle icon-circle-md bg-info-subtle text-info me-3">
                                        <i class="bi bi-info-circle fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold">{{ translate('Option Restricted') }}</h6>
                                        <p class="mb-0 text-gray-700 fs-14">{{ translate('This feature is only available for products that have been approved.') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-4">
                        @themeInclude('userpanel.products.includes.sidebar')
                    </div>
                @else
                    @themeInclude('userpanel.partials.restricted')
                @endif
            </div>
        </div>
    </div>
@endsection

