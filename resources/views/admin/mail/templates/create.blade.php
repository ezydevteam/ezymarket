@extends('admin.layouts.form')
@section('section', translate('Mail Templates'))
@section('title', translate('Create Template'))
@section('back', route('admin.mail.templates.index'))
@section('container', 'container-max-lg')
@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form id="ezydev-form" action="{{ route('admin.mail.templates.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-lg-6">
                            <label class="form-label">{{ translate('Alias') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="alias" class="form-control" placeholder="e.g., welcome_email"
                                pattern="[a-z0-9_]+" title="Only lowercase letters, numbers, and underscores"
                                value="{{ old('alias') }}" required>
                            <small class="text-muted">{{ translate('Use lowercase letters, numbers, and underscores
                                only') }}</small>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">{{ translate('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Welcome Email"
                                value="{{ old('name') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ translate('Subject') }} <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control"
                            placeholder="e.g., Welcome to &#123;&#123;website_name&#125;&#125;"
                            value="{{ old('subject') }}" required>
                        <small class="text-muted">{{ translate('You can use shortcodes like') }}
                            &#123;&#123;username&#125;&#125;, &#123;&#123;website_name&#125;&#125;, {{ translate('etc.')
                            }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ translate('Content') }} <span class="text-danger">*</span></label>
                        <textarea name="content" class="ckeditor">{{ old('content') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ translate('Shortcodes') }} ({{ translate('Optional') }})</label>
                        <input type="text" name="shortcodes" class="form-control"
                            placeholder='e.g., username, email, website_name (comma separated)'
                            value="{{ old('shortcodes') }}">
                        <small class="text-muted">{{ translate('Enter shortcode names separated by commas. If left
                            empty, shortcodes will be auto-detected from your template content.') }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ translate('Status') }}</label>
                        <input type="checkbox" name="is_active" data-toggle="toggle" checked>
                    </div>

                    <button type="submit" class="btn btn-primary mail-template-submit-btn">
                        <i class="fas fa-save me-2"></i>{{ translate('Create Template') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="fas fa-info-circle me-2"></i>{{ translate('Common Shortcodes') }}
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>{{ translate('Available shortcodes:') }}</strong></p>
                <ul class="list-unstyled">
                    <li><code>&#123;&#123;username&#125;&#125;</code> - User's username</li>
                    <li><code>&#123;&#123;user_email&#125;&#125;</code> - User's email</li>
                    <li><code>&#123;&#123;website_name&#125;&#125;</code> - Website name</li>
                    <li><code>&#123;&#123;website_url&#125;&#125;</code> - Website URL</li>
                    <li><code>&#123;&#123;current_date&#125;&#125;</code> - Current date</li>
                </ul>
                <hr>
                <p class="text-muted small mb-0">
                    {{ translate('Wrap variables in double curly braces. They will be replaced with actual values when
                    the email is sent.') }}
                </p>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.ckeditor')

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('ezydev-form');
        const submitBtn = document.querySelector('.mail-template-submit-btn');

        form.addEventListener('submit', function (e) {
            // Get CKEditor instance
            if (typeof CKEDITOR !== 'undefined') {
                for (instance in CKEDITOR.instances) {
                    CKEDITOR.instances[instance].updateElement();
                }

                // Check if body is empty
                const bodyContent = CKEDITOR.instances.body.getData();
                if (!bodyContent || bodyContent.trim() === '') {
                    e.preventDefault();
                    alert('{{ translate("Body field is required") }}');
                    return false;
                }
            }

            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ translate("Creating...") }}';
        });
    });
</script>
@endpush
@endsection
