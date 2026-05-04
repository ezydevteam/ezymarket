<div class="row g-4 mb-4">
    {{-- Total Transactions --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-counter-card
            :title="translate('Total Transactions')"
            :count="numberFormat($counters['total_transactions'])"
            :percent="$counters['total_transactions_percent']"
            icon="bi-receipt"
            color="primary"
            :link="route('admin.financial.transactions.index')"
        />
    </div>

    {{-- Paid Transactions --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-counter-card
            :title="translate('Paid Transactions')"
            :count="numberFormat($counters['paid_transactions'])"
            :percent="$counters['paid_transactions_percent']"
            icon="bi-check-circle"
            color="success"
            :link="route('admin.financial.transactions.index', ['status' => 'paid'])"
        />
    </div>

    {{-- Pending Transactions --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-counter-card
            :title="translate('Pending Transactions')"
            :count="numberFormat($counters['pending_transactions'])"
            :percent="$counters['pending_transactions_percent']"
            icon="bi-hourglass-split"
            color="warning"
            :link="route('admin.financial.transactions.index', ['status' => 'pending'])"
        />
    </div>

    {{-- Cancelled Transactions --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-counter-card
            :title="translate('Cancelled Transactions')"
            :count="numberFormat($counters['cancelled_transactions'])"
            :percent="$counters['cancelled_transactions_percent']"
            icon="bi-x-circle"
            color="danger"
            :link="route('admin.financial.transactions.index', ['status' => 'cancelled'])"
        />
    </div>
</div>
