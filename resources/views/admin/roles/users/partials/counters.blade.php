<div class="row g-4 mb-4">
    {{-- Active Users --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-counter-card
            :title="translate('Active Users')"
            :count="numberFormat($counters['active_users'])"
            :percent="$counters['active_users_percent']"
            icon="bi-person-add"
            color="success"
        />
    </div>

    {{-- Email Verified --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-counter-card
            :title="translate('Email Verified')"
            :count="numberFormat($counters['email_verified'])"
            :percent="$counters['email_verified_percent']"
            icon="bi-person-check"
            color="orange"
        />
    </div>

    {{-- Premium or Total Sellers --}}
    <div class="col-12 col-sm-6 col-lg-3">
        @if (isPremiumAvailable())
            <x-counter-card
                :title="translate('Premium Users')"
                :count="numberFormat($counters['premium_users'])"
                :percent="$counters['premium_users_percent']"
                icon="bi-person-hearts"
                color="danger"
            />
        @else
            <x-counter-card
                :title="translate('Total Sellers')"
                :count="numberFormat($counters['total_sellers'])"
                :percent="$counters['total_sellers_percent']"
                icon="bi-person-hearts"
                color="danger"
            />
        @endif
    </div>

    {{-- Total Users --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-counter-card
            :title="translate('Total Users')"
            :count="numberFormat($counters['total_users'])"
            :percent="$counters['total_users_percent']"
            icon="bi-people"
            color="primary"
        />
    </div>
</div>
