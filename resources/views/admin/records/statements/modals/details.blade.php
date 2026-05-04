<x-modal :title="translate('Statement Details')" icon="bi-receipt" :scrollable="true" :content-only="true"
    id="statementDetailsContent">

    {{-- Header Section --}}
    <div class="py-3 border-bottom bg-light-subtle rounded-top">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="text-muted text-uppercase fw-semibold mb-1 d-block fs-12 letter-spacing-1">
                    {{ translate('Financial Statement') }}
                </span>
                <h4 class="fw-bold mb-0 text-dark">#{{ $statement->id }}</h4>
            </div>
            <div class="text-end">
                <span class="badge {{ $statement->type->badge() }} py-2 px-3 fs-12">
                    <i class="bi {{ $statement->type->icon() }} me-1"></i>{{ $statement->type->label() }}
                </span>
            </div>
        </div>

        {{-- Date & User Summary --}}
        <div class="row g-3">
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Transaction Date') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                        {{ dateFormat($statement->created_at) }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Account Holder') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-person me-1 text-primary"></i>
                        {{ $statement->user->full_name }}
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="px-3 py-3 border rounded bg-{{ $statement->type->color() }}-subtle shadow-sm border-{{ $statement->type->color() }}-subtle">
                    <div class="row align-items-center">
                        <div class="col-12">
                            <small class="text-muted d-block mb-1">{{ translate('Final Amount') }}</small>
                            <div class="h3 fw-bold text-{{ $statement->type->color() }} mb-0">
                                {{ $statement->type->sign() }}{{ getAmount((float) $statement->total) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-3">
        {{-- Title / Description --}}
        <div class="mb-4">
            <h6 class="fw-bold text-uppercase fs-12 text-muted mb-2 letter-spacing-1">
                {{ translate('Transaction Title') }}
            </h6>
            <div class="p-3 border rounded-3 bg-white fw-medium text-dark">
                {{ $statement->title }}
            </div>
        </div>

        {{-- Financial Breakdown --}}
        <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
            <i class="bi bi-list-check me-1"></i>{{ translate('Financial Breakdown') }}
        </h6>
        <div class="bg-light p-3 rounded-3 border mb-4">
            <div class="space-y-2">
                <div class="d-flex justify-content-between text-muted fs-14">
                    <span>{{ translate('Subtotal Amount') }}</span>
                    <span class="text-dark fw-medium">{{ getAmount((float) $statement->amount) }}</span>
                </div>
                
                @if($statement->buyer_fee > 0)
                <div class="d-flex justify-content-between text-muted fs-14">
                    <span>{{ translate('Buyer Fee') }}</span>
                    <span class="text-danger">- {{ getAmount((float) $statement->buyer_fee) }}</span>
                </div>
                @endif

                @if($statement->seller_fee > 0)
                <div class="d-flex justify-content-between text-muted fs-14">
                    <span>{{ translate('Seller Fee') }}</span>
                    <span class="text-danger">- {{ getAmount((float) $statement->seller_fee) }}</span>
                </div>
                @endif

                @if(isset($statement->tax) && $statement->tax > 0)
                <div class="d-flex justify-content-between text-muted fs-14">
                    <span>{{ translate('Tax') }}</span>
                    <span class="text-danger">- {{ getAmount((float) $statement->tax) }}</span>
                </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <span class="fw-bold text-dark fs-16">{{ translate('Total Settlement') }}</span>
                    <span class="fw-bold text-{{ $statement->type->color() }} fs-4">
                        {{ $statement->type->sign() }}{{ getAmount((float) $statement->total) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- User Info --}}
        <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
            <i class="bi bi-person me-1"></i>{{ translate('User Details') }}
        </h6>
        <div class="p-3 border rounded-3 bg-white">
            <x-user :user="$statement->user" avatarSize="md" />
            <hr class="my-3 opacity-10">
            <div class="row g-2">
                <div class="col-6 small">
                    <span class="text-muted d-block">{{ translate('Username') }}</span>
                    <span class="fw-medium text-dark">{{ $statement->user->username }}</span>
                </div>
                <div class="col-6 small">
                    <span class="text-muted d-block">{{ translate('Email Address') }}</span>
                    <span class="fw-medium text-dark">{{ $statement->user->email }}</span>
                </div>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
            {{ translate('Dismiss') }}
        </button>
        <a href="{{ route('admin.roles.users.index', ['id' => $statement->user_id]) }}"
            class="btn btn-primary flex-fill" target="_blank">
            <i class="bi bi-person-gear me-2"></i>{{ translate('Manage User') }}
        </a>
    </x-slot>
</x-modal>
