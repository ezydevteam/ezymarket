@extends('themes.main.userpanel.layout')
@section('title', translate('My Drafts'))

@section('content')
    <div class="product-drafts-section">
        @if ($drafts->count() > 0)
            <div class="product-submission-card mb-4">
                <div class="card-v-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">{{ translate('Product Drafts') }}</h5>
                    <a href="{{ route('user.product.create') }}" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-plus-lg me-1"></i>{{ translate('New draft') }}
                    </a>
                </div>
                <div class="card-v-body p-0">
                    <div class="table-responsive">
                        <table class="table ezydev-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">{{ translate('Product Details') }}</th>
                                    <th class="text-center">{{ translate('Category') }}</th>
                                    <th class="text-center">{{ translate('Last Updated') }}</th>
                                    <th class="text-end pe-4">{{ translate('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($drafts as $draft)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="product-info-column d-flex align-items-center gap-3 py-2">
                                                <div class="product-thumbnail">
                                                    @if($draft->preview_image)
                                                        <img src="{{ $draft->preview_image_url }}" alt="{{ $draft->name }}"
                                                        class="image-fluid image-md">
                                                    @else
                                                        <div class="no-image-placeholder rounded d-flex align-items-center justify-content-center bg-light"
                                                            width="60" height="45">
                                                            <i class="bi bi-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="product-meta">
                                                    <h6 class="mb-1 text-truncate">{{ truncateText($draft->name, 50) }}</h6>
                                                    <a href="{{ route('user.product.create', ['draft' => $draft->id]) }}"
                                                        class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-11 fw-normal">
                                                        <i class="bi bi-pencil-square me-1"></i>{{ translate('Draft') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ ($draft->category) ? $draft->category->name : translate('No Category set yet') }}
                                        </td>
                                        <td class="text-center">
                                            <div class="timestamp">
                                                {{ $draft->updated_at->format('M d, Y') }}
                                                <div class="small text-gray-600">{{ $draft->updated_at->format('h:i A') }}</div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('user.product.create', ['draft' => $draft->id]) }}"
                                                    class="btn btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ translate('Resume Editing') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-success action-confirm"
                                                    data-action="{{ route('user.product.publish', $draft->id) }}"
                                                    data-text="{{ translate('Are you sure you want to publish this draft?') }}"
                                                    data-method="POST" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ translate('Publish') }}">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger action-confirm"
                                                    data-action="{{ route('user.product.draft.delete', $draft->id) }}"
                                                    data-text="{{ translate('Are you sure you want to delete this draft?') }}"
                                                    data-method="DELETE" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ translate('Delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="alert alert-info d-flex align-items-center border-0 shadow-sm">
                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                <div>
                    <h6 class="mb-1 fw-bold">{{ translate('Draft Management') }}</h6>
                    <p class="mb-0 small opacity-75">{!! translate('You can save up to <strong>:max</strong> drafts at a time.
                        Drafts are not visible to anyone else until published.', ['max' => @$settings->product->maximum_drafts]) !!}</p>
                </div>
            </div>
        @else
            @themeInclude('userpanel.partials.empty', [
                'title' => translate('No Drafts Found'),
                'description' => translate('You don\'t have any saved drafts. You can save your progress and finish later while creating products.'),
                'icon' => 'pencil-square',
                'btn_text' => translate('Start New Product'),
                'btn_url' => route('user.product.create'),
            ])
        @endif
    </div>
@endsection
