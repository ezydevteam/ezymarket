<x-modal :content-only="true" :title="translate('Payout Information')" :icon="'bi-info-circle'" :body-class="'p-0'" :scrollable="true">
    <div class="px-4 py-4 bg-light-subtle border-bottom border-light shadow-sm">
        @themeInclude('userpanel.partials.restored-notice', ['model' => $payout, 'type' => 'payout'])

        <div class="row align-items-center g-3">
            <div class="col">
                <span class="text-muted small d-block mb-2 text-uppercase ls-1 fw-bold">{{ translate('Current Status') }}</span>
                {!! $payout->status_badge !!}
            </div>
            <div class="col-auto text-end">
                <div class="text-muted small d-block mb-1 text-uppercase ls-1 fw-bold">{{ translate('Initiated On') }}</div>
                <div class="fw-bold text-dark">{{ dateFormat($payout->created_at) }}</div>
                <div class="fs-12 text-muted mt-1"><i class="bi bi-clock me-1"></i>{{ $payout->created_at->diffForHumans() }}</div>
            </div>
        </div>
    </div>

    <div class="p-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-4 border">
                    <div>
                        <span class="text-muted small d-block mb-1 text-uppercase ls-1 fw-bold">{{ translate('Payout ID') }}</span>
                        <span class="fw-black text-dark fs-5">#{{ $payout->id }}</span>
                    </div>
                    <div class="icon-circle icon-circle-sm bg-white text-primary shadow-sm border">
                        <i class="bi bi-hash fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="p-3 border-start border-4 border-light rounded-2">
                    <span class="text-muted small d-block mb-1 text-uppercase ls-1 fw-bold">{{ translate('Requested Amount') }}</span>
                    <div class="fw-bold fs-5 text-dark">{{ getAmount($payout->amount) }}</div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="p-3 border-start border-4 border-light rounded-2 text-sm-end">
                    <span class="text-muted small d-block mb-1 text-uppercase ls-1 fw-bold">{{ translate('Processing Fees') }}</span>
                    <div class="fw-bold fs-5 text-danger">{{ $payout->fees_label }}</div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 bg-primary-subtle rounded-4 p-4 text-center">
                    <span class="text-primary small d-block mb-1 text-uppercase ls-1 fw-black">{{ $payout->amount_info->label }}</span>
                    <h2 class="fw-bold text-primary mb-0 display-6">{{ $payout->amount_info->amount }}</h2>
                </div>
            </div>

            <div class="col-12">
                <div class="p-3 bg-light rounded-4 border border-dashed">
                    <div class="row g-3">
                        <div class="col-md-6 border-end-md">
                            <span class="text-muted small d-block mb-1 text-uppercase ls-1 fw-bold">{{ translate('Method') }}</span>
                            <div class="fw-bold text-dark d-flex align-items-center">
                                <i class="bi bi-bank2 me-2 text-primary"></i>
                                {{ $payout->method ?: translate('N/A') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block mb-1 text-uppercase ls-1 fw-bold">{{ translate('Account / Wallet') }}</span>
                            <div class="fw-medium text-dark font-monospace text-break">
                                {{ hideInDemo($payout->account) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($payout->admin_note)
        <div class="mx-4 mb-4 p-3 bg-warning-subtle border border-warning-subtle rounded-4">
            <span class="small d-block mb-2 fw-black text-uppercase ls-1 text-warning-emphasis">
                <i class="bi bi-chat-left-dots-fill me-2"></i>{{ translate('Administrator Note') }}
            </span>
            <p class="mb-0 small text-dark-emphasis fst-italic lh-base">{{ $payout->admin_note }}</p>
        </div>
    @endif

    <x-slot:footer>
        <button type="button" class="btn btn-outline-secondary w-100 text-uppercase" data-bs-dismiss="modal">
            {{ translate('Close') }}
        </button>
    </x-slot:footer>
</x-modal>
