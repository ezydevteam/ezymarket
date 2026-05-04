<div class="card">
    {{-- Product Thumbnail --}}
    <div class="card-img-top position-relative">
        <img src="{{ $productUpdate->thumbnail_url }}"
            alt="{{ $productUpdate->name }}"
            class="w-100 rounded-top-3 object-fit-cover"
            style="max-height: 200px;">
    </div>

    <div class="card-body p-3">
        {{-- Product Name --}}
        <h6 class="card-title fw-semibold mb-3 text-truncate" title="{{ $productUpdate->name }}">
            {{ $productUpdate->name }}
        </h6>

        {{-- Product Info List --}}
        <ul class="list-group list-group-flush mb-3">
            <li class="d-flex align-items-center py-2 border-bottom">
                <span>
                    <i class="bi bi-hash me-2"></i>{{ translate('ID') }}
                </span>
                <span class="ms-auto">#{{ $productUpdate->id }}</span>
            </li>
            <li class="d-flex align-items-center py-2 border-bottom">
                <span>
                    <i class="bi bi-folder-plus me-2"></i>{{ translate('Category') }}
                </span>
                <span class="ms-auto">
                    <a href="{{ route('admin.products.categories.edit', $productUpdate->category->id) }}" target="_blank" class="text-decoration-none small">
                        {{ $productUpdate->category->name }}
                    </a>
                    @if ($productUpdate->subCategory)
                        <i class="bi bi-chevron-right small text-muted mx-1"></i>
                        <a href="{{ route('admin.products.categories.sub-categories.index', ['subCategory' => $productUpdate->subCategory->id]) }}" target="_blank" class="text-decoration-none small">
                            {{ $productUpdate->subCategory->name }}
                        </a>
                    @endif
                </span>
            </li>
            <li class="d-flex align-items-center py-2 border-bottom">
                <span>
                    <i class="bi bi-person me-2"></i>{{ translate('Seller') }}
                </span>
                <x-user :user="$productUpdate->seller"
                    class="ms-auto"
                    :showEmail="false"
                    :showAvatar="false"
                    fontWeight="normal" />
            </li>
            <li class="d-flex align-items-center py-2 border-bottom">
                <span>
                    <i class="bi bi-calendar me-2"></i>{{ translate('Submitted') }}
                </span>
                <span class="ms-auto text-muted">{{ dateFormat($productUpdate->created_at) }}</span>
            </li>
            @if($productUpdate->last_updated_at)
                <li class="d-flex align-items-center py-2 border-bottom">
                    <span>
                        <i class="bi bi-calendar-check me-2"></i>{{ translate('Last Updated') }}
                    </span>
                    <span class="ms-auto">{{ dateFormat($productUpdate->last_updated_at) }}</span>
                </li>
            @endif
        </ul>

        {{-- Action Buttons --}}
        <div class="d-flex align-items-center gap-2">
             <a href="{{ route('admin.products.updated.destroy', $productUpdate->id) }}"
                class="btn bg-text-red flex-fill action-confirm"
                data-confirm="{{ translate('Are you sure you want to delete this update request?') }}"
                data-method="DELETE">
                <i class="bi bi-trash me-1"></i>
                {{ translate('Delete') }}
            </a>
            @if ($productUpdate->main_file)
                @if ($productUpdate->isMainFileExternal())
                    <a href="{{ $productUpdate->main_file['path'] ?? '' }}" target="_blank"
                        class="btn btn-success flex-fill">
                        <i class="bi bi-download me-1"></i>
                        {{ translate('Download') }}
                    </a>
                @else
                    <a href="{{ route('admin.products.updated.download', $productUpdate->id) }}"
                        class="btn btn-success flex-fill">
                        <i class="bi bi-download me-1"></i>
                        {{ translate('Download') }}
                    </a>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Changed Properties Card --}}
@php
    $updatedProperties = $productUpdate->getUpdatedProperties();
@endphp

@if (count($updatedProperties) > 0)
    <div class="card mt-4">
        <div class="card-header">
            <i class="bi bi-pencil-square me-2"></i>{{ translate('Changes Made') }}
            <span class="badge bg-primary rounded-pill py-1 ms-2">{{ count($updatedProperties) }}</span>
        </div>
        <div class="card-body px-0 pt-3 pb-1">
            <ul class="list-group list-group-flush">
                @foreach ($updatedProperties as $property => $change)
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-medium small">{{ translate($change['label']) }}</span>
                            @php
                                $isFile = in_array($property, ['preview_image', 'preview_video', 'preview_audio', 'main_file', 'gallery']);
                                $isArray = is_array($change['new']);
                                $isBoolean = is_bool($change['new']);
                                $isLink = $property === 'demo_link';
                                $isLongText = !$isFile && !$isArray && !$isBoolean && !$isLink && is_string($change['new']) && strlen($change['new']) > 50;
                            @endphp

                            @if ($isFile)
                                <span class="badge bg-text-blue">{{ translate('File Updated') }}</span>
                            @elseif ($isArray)
                                <span class="badge bg-text-blue">{{ translate('Updated') }}</span>
                            @elseif ($isBoolean)
                                <span class="d-flex align-items-center gap-1">
                                    <span class="badge {{ $change['old'] ? 'bg-text-green' : 'bg-text-dark' }}">
                                        {{ $change['old'] ? translate('Yes') : translate('No') }}
                                    </span>
                                    <i class="bi bi-arrow-right text-muted small"></i>
                                    <span class="badge {{ $change['new'] ? 'bg-text-green' : 'bg-text-dark' }}">
                                        {{ $change['new'] ? translate('Yes') : translate('No') }}
                                    </span>
                                </span>
                            @elseif ($isLink)
                                <span class="badge bg-text-blue">{{ translate('Link Updated') }}</span>
                            @elseif ($isLongText)
                                <span class="badge bg-text-blue">{{ translate('Text Updated') }}</span>
                            @elseif (in_array($property, ['regular_price', 'extended_price']))
                                <span class="d-flex align-items-center gap-1">
                                    <span class="text-muted text-decoration-line-through small">{{ getAmount($change['old']) }}</span>
                                    <i class="bi bi-arrow-right text-muted small"></i>
                                    <span class="text-success fw-medium small">{{ getAmount($change['new']) }}</span>
                                </span>
                            @elseif ($property === 'category_id')
                                <span class="badge bg-text-blue">{{ translate('Category Changed') }}</span>
                            @elseif ($property === 'sub_category_id')
                                <span class="badge bg-text-blue">{{ translate('Sub-category Changed') }}</span>
                            @else
                                <span class="d-flex align-items-center gap-1 text-end" style="max-width: 60%;">
                                    <span class="text-muted text-decoration-line-through small text-truncate" title="{{ $change['old'] }}">
                                        {{ truncateText($change['old'] ?? '-', 20) }}
                                    </span>
                                    <i class="bi bi-arrow-right text-muted small"></i>
                                    <span class="text-success small text-truncate" title="{{ $change['new'] }}">
                                        {{ truncateText($change['new'], 20) }}
                                    </span>
                                </span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@else
    <div class="card mt-4">
        <div class="card-body">
            <x-empty message="No changes detected in this update request." icon="bi-pencil-square" />
        </div>
    </div>
@endif
