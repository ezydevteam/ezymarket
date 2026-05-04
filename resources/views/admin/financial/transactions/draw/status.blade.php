<span role="button" data-bs-toggle="modal" data-bs-target="#detailsModal"
    data-action="{{ route('admin.financial.transactions.details.modal', $trx->id) }}">
    {!! $trx->status_badge !!}
</span>
