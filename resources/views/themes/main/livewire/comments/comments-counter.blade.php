<div class="product-comment-counter justify-content-between align-items-center gap-2 {{ $count > 0 ? 'd-flex mb-3' : 'd-none' }}">
    <h5 class="flex-grow-1 fw-medium text-gray-200 mb-0">
        {{ trans_choice(':count comment found.|:count comments found.', $count, ['count' => $count]) }}
    </h5>
    <div class="comment-sorting flex-shrink-0">
        <div class="input-group input-group-sm">
            <span class="input-group-text" title="{{ translate('Sort by') }}">
                <i class="bi bi-sort-down"></i>
            </span>
            <select id="commentSort" wire:model.live="sort" class="form-select form-select-sm">
                <option value="newest">{{ translate('Newest first') }}</option>
                <option value="oldest">{{ translate('Oldest first') }}</option>
            </select>
        </div>
    </div>
</div>
