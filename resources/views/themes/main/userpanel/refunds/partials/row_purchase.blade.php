<div class="text-start">
    <div class="d-flex flex-column">
        <span class="text-dark fw-medium small mb-1"
            title="{{ $refund->subject }}">
            {{ truncateText($refund->subject, 30) }}
        </span>
        <code class="small text-muted" title="{{ translate('Refund ID') }}">#{{ $refund->id }}</code>
    </div>
</div>
