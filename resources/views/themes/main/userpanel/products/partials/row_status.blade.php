<div class="text-center">
    <span class="badge {{ $product->status->badgeClass() }} px-3 py-2 rounded-pill">
        <i class="bi {{ $product->status->icon() }} me-1 small"></i>
        {{ $product->status->label() }}
    </span>
</div>
