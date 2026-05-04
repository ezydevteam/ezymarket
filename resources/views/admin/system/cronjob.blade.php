@extends('admin.layouts.app')
@section('section', translate('System'))
@section('title', translate('Cron Job'))
@section('container', 'container-max-lg')
@section('content')
    <div class="card">
        <div class="card-header">
            <span>{{ translate('Command') }}</span>
        </div>
        <div class="card-body p-4">
            <div class="mb-3">
                @if (@$cronjobSettings->last_execution)
                    <div class="mb-2">
                        <i class="fw-light">
                            {{ str('Last Execution: {datetime}')->replace('{datetime}', dateFormat(@$cronjobSettings->last_execution)) }}
                        </i>
                    </div>
                @endif
                <div class="input-group">
                    <input id="cronInput" type="text" class="form-control form-control-md"
                        value="wget -q -O /dev/null {{ !empty($cronjobSettings->key) ? route('cronjob', ['key' => $cronjobSettings->key]) : route('cronjob') }}"
                        readonly>
                    <button class="btn btn-primary btn-copy" type="button" data-clipboard-target="#cronInput">
                        <i class="bi bi-copy me-2"></i>
                        {{ translate('Copy') }}
                    </button>
                </div>
                <div class="input-text mt-2">
                    {{ translate('The cron job command must be set to run every minute') }} ( <code>* * * * *</code> ).
                </div>
            </div>
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-auto">
                    <form action="{{ route('admin.system.cronjob.generate-key') }}" method="POST" data-ajax-confirm="true">
                        @csrf
                        <button class="btn btn-outline-primary btn-md w-100 action-confirm" data-confirm="{{ translate('Are you sure want to generate a new cron job key?') }}">
                            <i class="bi bi-arrow-repeat me-2"></i>
                            {{ translate('Generate Key') }}</button>
                    </form>
                </div>
                <div class="col-12 col-lg-auto">
                    <form action="{{ route('admin.system.cronjob.remove-key') }}" method="POST" data-ajax-confirm="true">
                        @csrf
                        <button class="btn bg-text-red btn-md w-100 action-confirm" data-confirm="{{ translate('Are you sure want to remove the cron job key?') }}" @disabled(empty($cronjobSettings->key))>
                            <i class="bi bi-trash me-2"></i>
                            {{ translate('Remove Key') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/clipboard/clipboard.min.js') }}"></script>
@endpush

















