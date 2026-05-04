@extends('admin.layouts.form')
@section('section', translate('Settings'))
@section('title', translate('Support Ticket Settings'))
@section('description', translate('Configure file attachment options and upload limits for support tickets.'))
@section('container', 'container-max-lg')
@section('content')
<form id="ezydev-form" action="{{ route('admin.tickets.settings.update') }}" method="POST">
    @csrf
    <div class="row g-3">
        {{-- Ticket System Status --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light border-bottom-small">
                    <i class="fa fa-toggle-on me-2"></i>{{ translate('Ticket System Status') }}
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-0">{{ translate('Support Tickets Status') }}</h5>
                            <div class="form-text mt-2">{{ translate('Enable or disable the entire support ticket system
                                for users') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="ezydev-switch-wrapper-xl">
                                <input type="hidden" name="ticket[status]" value="0">
                                <input id="ticket-status" class="ezydev-switch-input codebay-toggle-switch"
                                    type="checkbox" name="ticket[status]" value="1" {{ @$settings->ticket->status ?
                                'checked' : '' }} data-toggle-target="#ticket-system-settings">
                                <label class="ezydev-switch-label" for="ticket-status">
                                    <span class="ezydev-switch-slider">
                                        <span class="ezydev-switch-button">
                                            <span class="ezydev-switch-on">{{ translate('Enabled') }}</span>
                                            <span class="ezydev-switch-off">{{ translate('Disabled') }}</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="ticket-system-settings" class="{{ !@$settings->ticket->status ? 'd-none' : '' }}">
            {{-- Upload Limits --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light border-bottom-small">
                        <i class="fas fa-cloud-upload-alt me-2"></i>{{ translate('File & Upload Limits') }}
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            {{-- Allowed File Types --}}
                            <div class="col-12">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="codebay-feature-icon mb-3">
                                            <i class="fa fa-file-archive"></i>
                                        </div>
                                        <h6 class="codebay-feature-title mb-2">
                                            <i class="fa fa-list me-1"></i>{{ translate('Allowed File Types') }}
                                        </h6>
                                        <small class="text-muted codebay-feature-description d-block mb-3">
                                            {{ translate('Specify which file extensions users can upload with their
                                            support tickets') }}
                                        </small>
                                        <input type="text" name="ticket[file_types]"
                                            class="form-control form-control-lg tags-input"
                                            placeholder="{{ translate('Enter the file extension') }}"
                                            value="{{ @$settings->ticket->file_types }}" required>
                                        <div class="form-text mt-2">
                                            <i class="fa fa-info-circle me-1"></i>
                                            {{ translate('Press Enter after each file extension. Example: pdf, jpg, png,
                                            doc') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Max Upload Files --}}
                            <div class="col-lg-6">
                                <div class="card border h-100">
                                    <div class="card-body">
                                        <div class="codebay-feature-icon mb-3">
                                            <i class="fa fa-copy"></i>
                                        </div>
                                        <h6 class="codebay-feature-title mb-2">
                                            {{ translate('Max Upload Files') }}
                                        </h6>
                                        <small class="text-muted codebay-feature-description d-block mb-3">
                                            {{ translate('Maximum number of files that can be uploaded per ticket') }}
                                        </small>
                                        <input type="number" name="ticket[max_files]"
                                            class="form-control form-control-lg" placeholder="5"
                                            value="{{ @$settings->ticket->max_files }}" min="1" max="100" required>
                                        <div class="form-text mt-2">
                                            <i class="fa fa-info-circle me-1"></i>
                                            {{ translate('Range: 1 to 100 files') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Max Size Per File --}}
                            <div class="col-lg-6">
                                <div class="card border h-100">
                                    <div class="card-body">
                                        <div class="codebay-feature-icon mb-3">
                                            <i class="fa fa-weight"></i>
                                        </div>
                                        <h6 class="codebay-feature-title mb-2">
                                            {{ translate('Max Size Per File') }}
                                        </h6>
                                        <small class="text-muted codebay-feature-description d-block mb-3">
                                            {{ translate('Maximum file size allowed for each attachment') }}
                                        </small>
                                        <div class="input-group input-group-lg">
                                            <input type="number" name="ticket[max_file_size]"
                                                class="form-control form-control-lg" placeholder="10"
                                                value="{{ @$settings->ticket->max_file_size }}" min="1" required>
                                            <span class="input-group-text px-4">
                                                <strong>{{ translate('MB') }}</strong>
                                            </span>
                                        </div>
                                        <div class="form-text mt-2">
                                            <i class="fa fa-info-circle me-1"></i>
                                            {{ translate('Minimum: 1 MB') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Total Upload Calculation --}}
                            <div class="col-12">
                                <div class="card bg-light border-success">
                                    <div class="card-body">
                                        <h6 class="text-success mb-3">
                                            <i class="fa fa-calculator me-2"></i>{{ translate('Total Upload Capacity')
                                            }}
                                        </h6>
                                        <div class="alert alert-success mb-0">
                                            <div class="row text-center">
                                                <div class="col-md-4">
                                                    <div class="mb-2">
                                                        <i class="fa fa-file fa-2x text-success"></i>
                                                    </div>
                                                    <strong>{{ translate('Max Files:') }}</strong>
                                                    <div class="h4 mb-0">
                                                        <span id="calc-max-files">{{ @$settings->ticket->max_files ?? 0
                                                            }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-2">
                                                        <i class="fa fa-weight-hanging fa-2x text-success"></i>
                                                    </div>
                                                    <strong>{{ translate('Size Per File:') }}</strong>
                                                    <div class="h4 mb-0">
                                                        <span id="calc-file-size">{{ @$settings->ticket->max_file_size
                                                            ?? 0 }}</span> MB
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-2">
                                                        <i class="fa fa-database fa-2x text-success"></i>
                                                    </div>
                                                    <strong>{{ translate('Total Capacity:') }}</strong>
                                                    <div class="h4 mb-0">
                                                        <span id="calc-total-size">{{ (@$settings->ticket->max_files ??
                                                            0) * (@$settings->ticket->max_file_size ?? 0) }}</span> MB
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Important Notice --}}
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <h6 class="text-warning mb-3">
                                            <i class="fa fa-exclamation-circle me-2"></i>{{ translate('Important
                                            Notice') }}
                                        </h6>
                                        <ul class="mb-0">
                                            <li>{{ translate('These limits also depend on your PHP configuration') }}
                                            </li>
                                            <li>{{ translate('Check your php.ini settings: upload_max_filesize and
                                                post_max_size') }}</li>
                                            <li>{{ translate('The actual upload limit will be the smaller of your
                                                settings and PHP limits') }}</li>
                                            <li>{{ translate('Consider your server storage capacity when setting these
                                                values') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('styles_libs')
<link rel="stylesheet" href="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.css') }}">
@endpush

@push('scripts_libs')
<script src="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.min.js') }}"></script>
@endpush

@push('scripts')
<script>
    // Real-time calculation update
    document.querySelectorAll('input[name="ticket[max_files]"], input[name="ticket[max_file_size]"]').forEach(input => {
        input.addEventListener('input', function () {
            const maxFiles = parseInt(document.querySelector('input[name="ticket[max_files]"]').value) || 0;
            const maxSize = parseInt(document.querySelector('input[name="ticket[max_file_size]"]').value) || 0;
            const totalSize = maxFiles * maxSize;

            document.getElementById('calc-max-files').textContent = maxFiles;
            document.getElementById('calc-file-size').textContent = maxSize;
            document.getElementById('calc-total-size').textContent = totalSize;
        });
    });
</script>
@endpush
@endsection
