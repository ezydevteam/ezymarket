@extends('admin.layouts.app')
@section('section', translate('System'))
@section('title', translate('System Information'))
@section('container', 'container-max-lg')
@section('content')
    {{-- Application Info --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
                <div class="card-icon bg-text-primary me-3">
                    <i class="bi bi-app-indicator"></i>
                </div>
                <h5 class="mb-0 fw-semibold">{{ translate('Application') }}</h5>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ translate('Name') }}</span>
                        <span class="fw-medium">{{ str_replace('_', ' ', $system->application->name) }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ translate('Version') }}</span>
                        <span class="badge bg-primary">v{{ $system->application->version }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ translate('Laravel Version') }}</span>
                        <span class="badge bg-danger">v{{ $system->application->laravel }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ translate('Timezone') }}</span>
                        <span class="fw-medium">{{ $system->application->timezone }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Server Details --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
                <div class="card-icon bg-text-green me-3">
                    <i class="bi bi-server"></i>
                </div>
                <h5 class="mb-0 fw-semibold">{{ translate('Server Details') }}</h5>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ translate('Software') }}</span>
                        <span class="fw-medium text-end">{{ $system->server->SERVER_SOFTWARE }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ translate('PHP Version') }}</span>
                        <span class="badge bg-info">v{{ $system->server->php }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ translate('IP Address') }}</span>
                        <span class="fw-medium">{{ $system->server->SERVER_ADDR }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ translate('Protocol') }}</span>
                        <span class="fw-medium">{{ $system->server->SERVER_PROTOCOL }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ translate('HTTP Host') }}</span>
                        <span class="fw-medium">{{ $system->server->HTTP_HOST }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ translate('Port') }}</span>
                        <span class="fw-medium">{{ $system->server->SERVER_PORT }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- System Requirements --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
                <div class="card-icon bg-text-purple me-3">
                    <i class="bi bi-list-check"></i>
                </div>
                <h5 class="mb-0 fw-semibold">{{ translate('System Requirements') }}</h5>
            </div>

            {{-- PHP Version --}}
            <div class="mb-4">
                <h6 class="text-muted small text-uppercase mb-3">{{ translate('PHP Version') }}</h6>
                <div class="d-flex justify-content-between align-items-center p-3 rounded bg-light">
                    <div>
                        <span class="fw-medium">PHP 8.2 or higher</span>
                        <small class="d-block text-muted">{{ translate('Current version') }}: {{ PHP_VERSION }}</small>
                    </div>
                    @if(version_compare(PHP_VERSION, '8.2.0', '>='))
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>{{ translate('Met') }}</span>
                    @else
                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>{{ translate('Required') }}</span>
                    @endif
                </div>
            </div>

            {{-- PHP Extensions --}}
            <div class="mb-4">
                <h6 class="text-muted small text-uppercase mb-3">{{ translate('Required PHP Extensions') }}</h6>
                <div class="row g-2">
                    @php
                        $extensions = ['bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'json', 'mbstring', 'openssl', 'pcre', 'PDO', 'pdo_mysql', 'tokenizer', 'xml', 'zip', 'gd', 'intl'];
                    @endphp
                    @foreach($extensions as $extension)
                        <div class="col-md-6 col-lg-4">
                            <div class="d-flex justify-content-between align-items-center p-2 rounded border">
                                <span class="small">{{ $extension }}</span>
                                @if(extension_loaded($extension))
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Directory Permissions --}}
            <div>
                <h6 class="text-muted small text-uppercase mb-3">{{ translate('Directory Permissions') }}</h6>
                <div class="row g-2">
                    @php
                        $directories = [
                            'storage/framework/' => '0775',
                            'storage/logs/' => '0775',
                            'bootstrap/cache/' => '0775',
                            'public/' => '0775',
                        ];
                    @endphp
                    @foreach($directories as $dir => $permission)
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center p-2 rounded border">
                                <div>
                                    <span class="small fw-medium d-block">{{ $dir }}</span>
                                    <span class="text-muted" style="font-size: 0.7rem;">{{ translate('Required') }}: {{ $permission }}</span>
                                </div>
                                @if(is_writable(base_path($dir)))
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- System Cache --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
                <div class="card-icon bg-text-red me-3">
                    <i class="bi bi-trash3"></i>
                </div>
                <h5 class="mb-0 fw-semibold">{{ translate('System Cache') }}</h5>
            </div>
            <div class="alert alert-warning border-0 mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ translate('Clearing system cache will remove all temporary files and cached data. This may temporarily affect performance until caches are rebuilt.') }}
            </div>
            <div class="row g-2 mb-4">
                <div class="col-md-6">
                    <div class="p-3 rounded border bg-light">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <span class="small">{{ translate('Compiled views will be cleared') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded border bg-light">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <span class="small">{{ translate('Application cache will be cleared') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded border bg-light">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <span class="small">{{ translate('Route cache will be cleared') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded border bg-light">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <span class="small">{{ translate('Configuration cache will be cleared') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded border bg-light">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <span class="small">{{ translate('All other caches will be cleared') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded border bg-light">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <span class="small">{{ translate('Error logs will be cleared') }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.system.info.cache') }}" class="btn bg-text-red btn-lg w-100 action-confirm">
                <i class="bi bi-trash3 me-2"></i>
                {{ translate('Clear System Cache') }}
            </a>
        </div>
    </div>
@endsection
