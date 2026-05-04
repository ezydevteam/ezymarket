@extends('admin.layouts.form')
@section('section', translate('Settings'))
@section('title', translate('Mail Server Settings'))
@section('description', translate('Configure your Mail Server settings to enable email sending through your mail
server.'))
@section('container', 'container-max-lg')
@section('content')
<form id="ezydev-form" action="{{ route('admin.mail.settings.update') }}" method="POST">
    @csrf

    {{-- Mail Server Status --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon bg-text-green">
                            <i class="bi bi-power"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">{{ translate('Mail Server Status') }}</h6>
                            <small class="text-muted">{{ translate('Enable Mail Server to send emails') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-md-end">
                    <x-switch name="mail[status]" id="mailStatus" :showLabel="false" onLabel="Enabled"
                        offLabel="Disabled" :checked="@$settings->mail->status ?? false" />
                </div>
            </div>
        </div>
    </div>

    {{-- General Configuration --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                <i class="bi bi-gear-fill text-primary fs-5"></i>
                <h5 class="mb-0 fw-semibold">{{ translate('General Configuration') }}</h5>
            </div>

            <div class="mb-4">
                <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                    <i class="bi bi-truck me-1"></i>{{ translate('Mail Driver') }}
                </label>
                <select name="mail[driver]" class="form-select form-select-lg">
                    <option value="smtp" @selected(@$settings->mail->driver == 'smtp')>{{ translate('SMTP') }}</option>
                    <option value="sendmail" @selected(@$settings->mail->driver == 'sendmail')>{{ translate('SENDMAIL')
                        }}</option>
                </select>
                <small class="text-muted mt-1 d-block">
                    {{ translate('Select the mail driver to use for sending emails') }}
                </small>
            </div>
        </div>
    </div>

    {{-- Server Configuration --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                <i class="bi bi-server text-success fs-5"></i>
                <h5 class="mb-0 fw-semibold">{{ translate('Server Configuration') }}</h5>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                        <i class="bi bi-hdd-network me-1"></i>{{ translate('Mail Host') }}
                    </label>
                    <input type="text" name="mail[host]" class="remove-spaces form-control form-control-lg"
                        value="{{ hideInDemo(@$settings->mail->host) }}" placeholder="smtp.example.com">
                    <small class="text-muted mt-1 d-block">
                        {{ translate('SMTP server hostname or IP address') }}
                    </small>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                        <i class="bi bi-plug me-1"></i>{{ translate('Mail Port') }}
                    </label>
                    <input type="text" name="mail[port]" class="remove-spaces form-control form-control-lg"
                        value="{{ hideInDemo(@$settings->mail->port) }}" placeholder="587">
                    <small class="text-muted mt-1 d-block">
                        {{ translate('SMTP server port (587, 465, or 25)') }}
                    </small>
                </div>

                <div class="col-12">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                        <i class="bi bi-shield-lock me-1"></i>{{ translate('Mail Encryption') }}
                    </label>
                    <select name="mail[encryption]" class="form-select form-select-lg">
                        <option value="tls" @selected(@$settings->mail->encryption == 'tls')>{{ translate('TLS -
                            Transport Layer Security') }}</option>
                        <option value="ssl" @selected(@$settings->mail->encryption == 'ssl')>{{ translate('SSL - Secure
                            Sockets Layer') }}</option>
                    </select>
                    <div class="alert alert-info border-0 mt-2 mb-0">
                        <small>
                            <strong>{{ translate('TLS (Port 587):') }}</strong> {{ translate('Modern, recommended')
                            }}<br>
                            <strong>{{ translate('SSL (Port 465):') }}</strong> {{ translate('Legacy, still supported')
                            }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Authentication --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                <i class="bi bi-shield-check text-purple fs-5"></i>
                <h5 class="mb-0 fw-semibold">{{ translate('Authentication') }}</h5>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                        <i class="bi bi-person-fill me-1"></i>{{ translate('Mail Username') }}
                    </label>
                    <input type="text" name="mail[username]" class="form-control form-control-lg remove-spaces"
                        value="{{ hideInDemo(@$settings->mail->username) }}" placeholder="user@example.com">
                    <small class="text-muted mt-1 d-block">
                        {{ translate('Your Mail Server account username or email address') }}
                    </small>
                </div>

                <div class="col-12">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                        <i class="bi bi-lock-fill me-1"></i>{{ translate('Mail Password') }}
                    </label>
                    <input type="password" name="mail[password]" class="form-control form-control-lg"
                        value="{{ hideInDemo(@$settings->mail->password) }}" placeholder="••••••••••••">
                    <small class="text-muted mt-1 d-block">
                        {{ translate('Your password is encrypted and stored securely') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Sender Information --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                <i class="bi bi-envelope-fill text-info fs-5"></i>
                <h5 class="mb-0 fw-semibold">{{ translate('Sender Information') }}</h5>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                        <i class="bi bi-at me-1"></i>{{ translate('From Email') }}
                    </label>
                    <input type="text" name="mail[from_email]" class="remove-spaces form-control form-control-lg"
                        value="{{ hideInDemo(@$settings->mail->from_email) }}" placeholder="noreply@example.com">
                    <small class="text-muted mt-1 d-block">
                        {{ translate('The email address that will appear as the sender') }}
                    </small>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                        <i class="bi bi-pencil-square me-1"></i>{{ translate('From Name') }}
                    </label>
                    <input type="text" name="mail[from_name]" class="form-control form-control-lg"
                        value="{{ hideInDemo(@$settings->mail->from_name) }}" placeholder="Your Company Name">
                    <small class="text-muted mt-1 d-block">
                        {{ translate('The display name that will appear as the sender') }}
                    </small>
                </div>

                <div class="col-12">
                    <div class="alert alert-success border-0 mb-0">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-eye-fill me-2"></i>
                            <div>
                                <strong>{{ translate('Email Preview:') }}</strong><br>
                                <span id="preview-from-name">{{ @$settings->mail->from_name ?: 'Your Name' }}</span>
                                &lt;<span id="preview-from-email">{{ @$settings->mail->from_email ?:
                                    'noreply@example.com' }}</span>&gt;
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Testing --}}
    @if (@$settings->mail->status)
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                <i class="bi bi-send-check-fill text-danger fs-5"></i>
                <h5 class="mb-0 fw-semibold">{{ translate('Mail Server Testing') }}</h5>
            </div>

            <div class="alert alert-info border-0 mb-4">
                {{ translate('Send a test email to verify your Mail Server configuration') }}
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-8">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                        <i class="bi bi-envelope-at me-1"></i>{{ translate('Test Email Address') }}
                    </label>
                    <input type="email" id="test-email" class="form-control form-control-lg"
                        placeholder="john@example.com" value="{{ authAdmin()->email }}">
                </div>
                <div class="col-lg-4 d-flex align-items-end">
                    <button type="button" id="send-test-email" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-send me-2"></i>{{ translate('Send Test') }}
                    </button>
                </div>
            </div>

            <div class="alert alert-warning border-0 mb-0">
                <h6 class="mb-2">
                    <i class="bi bi-lightbulb me-2"></i>{{ translate('Testing Tips') }}
                </h6>
                <ul class="mb-0 small">
                    <li>{{ translate('Make sure all Mail Server settings are saved before testing') }}</li>
                    <li>{{ translate('Check your spam/junk folder if you don\'t receive the test email') }}</li>
                    <li>{{ translate('Verify that your firewall allows outbound connections to the Mail Server port') }}
                    </li>
                    <li>{{ translate('Some providers require app-specific passwords instead of regular passwords') }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
    </div>
    @endif

</form>
@endsection

@push('scripts')
<script>
    // Send test email via AJAX
    document.getElementById('send-test-email')?.addEventListener('click', function () {
        const emailInput = document.getElementById('test-email');
        const email = emailInput.value.trim();

        if (!email) {
            toastr.error('{{ translate('Please enter an email address') }}');
            emailInput.focus();
            return;
        }

        // Disable button during request
        const button = this;
        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>{{ translate('Sending...') }}';

    // Send AJAX request
    fetch('{{ route('admin.mail.settings.test') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ email: email })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message || '{{ translate('Sent successfully') }}');
            } else {
                toastr.error(data.message || '{{ translate('Sending failed') }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('{{ translate('An error occurred while sending the test email') }}');
            })
            .finally(() => {
        // Re-enable button
        button.disabled = false;
        button.innerHTML = originalHtml;
    });
        });

    // Live preview for sender information
    document.querySelector('input[name="mail[from_name]"]')?.addEventListener('input', function () {
        document.getElementById('preview-from-name').textContent = this.value || '{{ translate('Your Name') }}';
    });

    document.querySelector('input[name="mail[from_email]"]')?.addEventListener('input', function () {
        document.getElementById('preview-from-email').textContent = this.value || 'noreply@example.com';
    });
</script>
@endpush
