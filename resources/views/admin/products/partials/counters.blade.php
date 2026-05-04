<div class="row g-4 mb-4">
    {{-- Approved Products --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-counter-card
            :title="translate('Approved Products')"
            :count="numberFormat($counters['approved'])"
            :percent="$counters['approved_percent']"
            icon="bi-check-circle"
            color="success"
        />
    </div>

    {{-- Pending Review --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-counter-card
            :title="translate('Pending Review')"
            :count="numberFormat($counters['pending'])"
            :percent="$counters['pending_percent']"
            icon="bi-hourglass-split"
            color="orange"
        />
    </div>

    {{-- Need Revision --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-counter-card
            :title="translate('Need Revision')"
            :count="numberFormat($counters['needs_revision'])"
            :percent="$counters['needs_revision_percent']"
            icon="bi-exclamation-circle"
            color="info"
        />
    </div>

    {{-- Rejected --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-counter-card
            :title="translate('Rejected')"
            :count="numberFormat($counters['rejected'])"
            :percent="$counters['rejected_percent']"
            icon="bi-x-circle"
            color="danger"
        />
    </div>
</div>
