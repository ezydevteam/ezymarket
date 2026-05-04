@extends('installer::layouts.app')
@section('title', translate_text('Import'))
@section('content')
@push('scripts')
<script>
document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Show progress bar
    document.getElementById('importBtn').classList.add('d-none');
    document.getElementById('importProgress').classList.remove('d-none');

    // Submit form via AJAX
    fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'Import failed. Please try manual import.');
            document.getElementById('importBtn').classList.remove('d-none');
            document.getElementById('importProgress').classList.add('d-none');
            document.getElementById('manual-tab').click();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Import failed. Please try manual import.');
        document.getElementById('importBtn').classList.remove('d-none');
        document.getElementById('importProgress').classList.add('d-none');
        document.getElementById('manual-tab').click();
    });
});
</script>
@endpush
    <div class="codebay-steps-body">
        <p class="codebay-form-info-text">
            {{ translate_text('Choose how you would like to set up your database. We recommend trying the automatic import first, as it\'s faster and easier. If you experience any issues, you can switch to the manual method at any time.') }}
        </p>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-product" role="presentation">
                <button class="nav-link active text-success" id="auto-tab" data-bs-toggle="tab" data-bs-target="#auto"
                    type="button" role="tab" aria-controls="auto" aria-selected="true"><i
                        class="bi bi-magic me-2"></i>{{ translate_text('One-Click Import') }}</button>
            </li>
            <li class="nav-product" role="presentation">
                <button class="nav-link text-secondary" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual"
                    type="button" role="tab" aria-controls="manual" aria-selected="false"><i
                        class="bi bi-tools me-2"></i>{{ translate_text('Manual Import') }}</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane show active" id="auto" role="tabpanel" aria-labelledby="auto-tab">
                <div class="card-body border-top-0 py-4">
                    <h3 class="text-muted mb-3">
                        {{ translate_text('Automatic Database Setup') }}
                    </h3>
                    <p class="text-muted mb-4">
                        {{ translate_text('Our system will automatically create all necessary tables and populate them with initial data. This process usually takes less than a minute.') }}
                    </p>
                    <div class="mb-4">
                        <form action="{{ route('installer.database.import.process') }}" method="POST" id="importForm">
                            @csrf
                            <button type="submit" class="btn btn-success btn-md" id="importBtn">
                                <i class="bi bi-rocket-takeoff me-2"></i>{{ translate_text('Start Installation') }}
                            </button>
                        </form>
                    </div>
                    <div id="importProgress" class="d-none">
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%"></div>
                        </div>
                        <p class="text-muted text-center">
                            <i class="bi bi-database-check me-2"></i>{{ translate_text('Setting up your database... This may take a moment.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="manual" role="tabpanel" aria-labelledby="manual-tab">
                <div class="card-body border-top-0 p-4">
                    <h3 class="text-muted mb-3">
                        {{ translate_text('Manual Database Installation') }}
                    </h3>
                    <p class="text-muted mb-4">
                        {{ translate_text('If you prefer to set up the database yourself or if automatic installation is not available, follow these steps:') }}
                    </p>
                    <div class="mb-0">
                        <div class="step-1">
                            <h5 class="text-muted mb-3">
                                <i class="bi bi-1-circle-fill me-2"></i>{{ translate_text('Get the Database File') }}
                            </h5>
                            <form action="{{ route('installer.database.import.download') }}" method="POST">
                                @csrf
                                <button class="btn btn-primary btn-md">
                                    <i class="bi bi-download me-2"></i>{{ translate_text('Download Database SQL') }}
                                </button>
                            </form>
                        </div>
                        <hr>
                        <div class="step-2">
                            <h5 class="text-muted mb-3">
                                <i class="bi bi-2-circle-fill me-2"></i>{{ translate_text('Import Using phpMyAdmin') }}
                            </h5>
                            <img src="{{ asset('vendor/installer/img/database-steps.png') }}" class="img-fluid rounded shadow-sm mb-3" width="500" alt="Database Import Steps">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-play-circle me-2"></i>
                                <span>{{ translate_text('Need help? Watch step-by-step video tutorial:') }}</span>
                                <br>
                                <a href="https://www.youtube.com/watch?v=jW5lrS6EUPM" class="alert-link" target="_blank">
                                    <i class="bi bi-youtube me-1"></i> Database Import Tutorial
                                </a>
                            </div>
                        </div>
                        <hr>
                        <div class="step-3">
                            <h5 class="text-muted mb-3">
                                <i class="bi bi-3-circle-fill me-2"></i>{{ translate_text('Complete Installation') }}
                            </h5>
                            <form action="{{ route('installer.database.import.skip') }}" method="POST">
                                @csrf
                                <button class="btn btn-success btn-md">
                                    <i class="bi bi-check-circle me-2"></i>{{ translate_text('Continue to Next Step') }}
                                </button>
                            </form>
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ translate_text('Please ensure you have successfully imported the database before continuing.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


















