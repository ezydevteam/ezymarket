@extends('installer::layouts.app')
@section('title', translate_text('Verify License'))
@section('content')
    <div class="codebay-steps-body">
        <p class="codebay-form-info-text">
            {{ translate_text('To ensure the security and authenticity of our products, we verify each purchase through a license validation system. Your purchase code serves as your unique license key.') }}
        </p>
        <div class="mb-4">
            <form action="{{ route('installer.license') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ translate_text('Purchase Code') }} <span class="required">*</span></label>
                    <div class="input-group">
                        <input type="text" name="purchase_code" class="form-control form-control-md"
                            placeholder="{{ translate_text('Enter your purchase code') }}" autocomplete="off" autofocus required>
                        <button class="btn btn-primary" type="submit">
                            {{ translate_text('Verify') }}
                            <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="form-links">
            <h6 class="mb-3">
                {{ translate_text('Need help? Check these resources about licensing:') }}
            </h6>
            <ul>
                <li class="mb-1">
                    <a target="_blank"
                        href="https://codecanyon.net/licenses/standard">{{ translate_text('Understanding License Terms') }}</a>
                </li>
                <li class="mb-0">
                    <a target="__blank"
                        href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-">{{ translate_text('How to Find Your Purchase Code') }}</a>
                </li>
            </ul>
        </div>
    </div>
@endsection


















