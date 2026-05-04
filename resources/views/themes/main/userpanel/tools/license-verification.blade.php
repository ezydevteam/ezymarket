@extends('themes.main.userpanel.layout')
@section('title', translate('License Verification Tool'))
@section('breadcrumbs', Breadcrumbs::render('user.tool.license-verification'))
@section('back', route('user.product.index'))
@section('container', 'userpanel-container-sm')
@section('content')
    <div class="userpanel-modern-card card-v text-center mb-4 p-4">
        <h3 class="mb-2">
            <i class="bi bi-shield-check text-primary me-1"></i>
            {{ translate('Verify Your Sold product License') }}
        </h3>
        <p class="text-muted">
            {{ translate('You can use this tool to verify license codes after receiving them from your buyers.') }}
        </p>
        <form action="{{ route('user.tool.license-verification.verify') }}" class="mt-4 mb-3" method="POST">
            @csrf
            <div class="input-group">
				<input type="text" name="purchase_code"
					   class="form-control form-control-md"
					   placeholder="{{ translate('Enter purchase code') }}"
					   value="{{ old('purchase_code') }}"
					   required autofocus>
				<button class="btn btn-primary btn-modern btn-md">
					{{ translate('Verify') }}
				</button>
			</div>
        </form>
    </div>
    @php
        $purchase = session('purchase');
    @endphp
    <div class="card-v border p-4 mb-3">
        @if ($purchase)
            <ul class="list-group list-group-flush">
                <li class="list-group-item py-3 px-0">
                    <div class="row align-items-center g-3">
                        <div class="col">
                            <strong>{{ translate('Purchased By') }}</strong>
                        </div>
                        <div class="col-auto">
                            <a href="{{ $purchase->user->profile_link }}" target="_blank">{{ $purchase->user->full_name }}</a>
                        </div>
                    </div>
                </li>
                <li class="list-group-item py-3 px-0">
                    <div class="row align-items-center g-3">
                        <div class="col">
                            <strong>{{ translate('Purchase Code') }}</strong>
                        </div>
                        <div class="col-auto">
                            <span>{{ $purchase->code }}</span>
                        </div>
                    </div>
                </li>
                <li class="list-group-item py-3 px-0">
                    <div class="row align-items-center g-3">
                        <div class="col">
                            <strong>{{ translate('product') }}</strong>
                        </div>
                        <div class="col-auto">
                            @php
                                 $product= $purchase->product;
                            @endphp
                            @if ($product->isApproved())
                                <a href="{{ $product->view_link }}" target="_blank">
                                    <i class="fa-solid fa-up-right-from-square me-1"></i>
                                    {{ $product->name }}
                                </a>
                            @else
                                <span>{{ $product->name }}</span>
                            @endif
                        </div>
                    </div>
                </li>
                <li class="list-group-item py-3 px-0">
                    <div class="row align-items-center g-3">
                        <div class="col">
                            <strong>{{ translate('License Type') }}</strong>
                        </div>
                        <div class="col-auto">
                            @if ($purchase->isRegularLicense())
                                <div class="badge bg-gray rounded-2 fw-light px-3 py-2">
                                    {{ translate('Regular') }}
                                </div>
                            @else
                                <div class="badge bg-primary rounded-2 fw-light px-3 py-2">
                                    {{ translate('Extended') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
                <li class="list-group-item py-3 px-0">
                    <div class="row align-items-center g-3">
                        <div class="col">
                            <strong>{{ translate('Purchase Date') }}</strong>
                        </div>
                        <div class="col-auto">
                            {{ dateFormat($purchase->created_at) }}
                        </div>
                    </div>
                </li>
                <li class="list-group-item py-3 px-0">
                    <div class="row align-items-center g-3">
                        <div class="col">
                            <strong>{{ translate('Downloaded') }}</strong>
                        </div>
                        <div class="col-auto">
                            @if ($purchase->isDownloaded())
                                <div class="badge bg-blue rounded-2 fw-light px-3 py-2">
                                    {{ translate('Yes') }}
                                </div>
                            @else
                                <div class="badge bg-gray rounded-2 fw-light px-3 py-2">
                                    {{ translate('No') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
            </ul>
        @else
            <div class="p-4">
                <p class="text-center text-muted m-0">{{ translate('Not data found') }}</p>
            </div>
        @endif
    </div>
@endsection

