@extends('installer::layouts.app')
@section('title', translate_text('Permissions'))
@section('content')
    <div class="codebay-steps-body">
        <div class="codebay-form-info-text mb-4">
            {{ translate_text('The installer needs to verify that certain files and directories have the correct permissions to ensure a smooth installation process. Please review the list below to ensure that all required permissions are set correctly.') }}
        </div>
        @foreach ($permissions as $permission)
            <div class="codebay-steps-req">
                <p class="mb-0"><i class="bi bi-folder2-open me-2"></i>{{ str_replace(base_path() . '/', '', $permission['path']) }}
                </p>
                @if (validate_file_permission($permission))
                    <div class="text-success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                @else
                    <div class="vironeer-steps-req-fail">
                        <i class="bi bi-x"></i>
                    </div>
                @endif
            </div>
        @endforeach
        <div class="mt-3">
            @if (!$error)
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ translate_text('All permissions are enabled. You can continue to the next step.') }}
                </div>
                <form action="{{ route('installer.permissions') }}" method="POST" class="text-end">
                    @csrf
                    <button class="btn btn-primary btn-md">{{ translate_text('Continue') }}<i
                            class="bi bi-arrow-right ms-2"></i></button>
                </form>
            @else
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    {{ translate_text('Some permissions are missing. Please give 0775 permission to all files above.') }}
                </div>
                <div class="text-end">
                    <button class="btn btn-primary btn-md" disabled>{{ translate_text('Continue') }}<i
                            class="bi bi-arrow-right ms-2"></i></button>
                </div>
            @endif
        </div>
    </div>
@endsection


















