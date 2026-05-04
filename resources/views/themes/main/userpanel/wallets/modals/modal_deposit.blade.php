<x-modal :content-only="true" :title="translate('Deposit Your Account')" icon="bi-plus-circle">
    @if (!@settings('deposit')->status)
        <div class="text-center py-4">
            <div class="mb-4">
                <i class="bi bi-x-circle-fill text-danger fs-1"></i>
            </div>
            <h5 class="mb-3 fw-bold">{{ translate('Deposits Currently Unavailable') }}</h5>
            <p class="text-gray-700 mb-4">
                {{ translate('Deposit functionality is temporarily disabled by the administrator.') }}
            </p>
            <p class="text-gray-600">
                {{ translate('Please contact support for more information or try again later.') }}
            </p>
        </div>
    @else
        <form action="{{ route('user.wallet.deposit') }}" class="ajax-form" id="depositFrom" method="POST">
            @csrf
            <div class="mb-4">
                @themeInclude('userpanel.partials.input-price', [
                    'name' => 'amount',
                    'min' => @$settings->deposit->minimum,
                    'required' => true,
                ])
                @if(@$settings->deposit->minimum > 0)
                    <small class="text-gray-700 d-block mt-2">
                        {{ translate('Minimum deposit amount') }}: <strong>{{ getAmount(@$settings->deposit->minimum) }}</strong>
                    </small>
                @endif
            </div>
        </form>
    @endif
    <x-slot:footer>
        @if (!@settings('deposit')->status)
            <button type="button" class="btn btn-outline-secondary flex-fill text-uppercase" data-bs-dismiss="modal">
                {{ translate('Close') }}
            </button>
        @else
            <button type="submit" form="depositFrom" class="btn btn-primary btn-modern btn-md flex-fill text-uppercase">
                {{ translate('Continue to Checkout') }}
            </button>
        @endif
    </x-slot:footer>
</x-modal>
