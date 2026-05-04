@extends('installer::layouts.app')
@section('title', translate_text('Requirements'))
@section('content')
    <div class="codebay-steps-body">
        <div class="codebay-form-info-text mb-4">
            {{ translate_text('The following PHP extensions are required to run EasyMarket. Please ensure that these extensions are enabled on your server before proceeding with the installation.') }}
        </div>
        @foreach ($extensions as $extension)
            <div class="codebay-steps-req">
                <p class="mb-0">{{ $extension['name'] }}</p>
                @if (check_extension_availability($extension['name']))
                    <div class="text-success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                @else
                    <div class="codebay-steps-req-fail">
                        <i class="bi bi-x"></i>
                    </div>
                @endif
            </div>
        @endforeach
        <div class="mt-3">
            @if (!$error)
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ translate_text('All extensions are enabled. You can continue to the next step.') }}
                </div>
                <form action="{{ route('installer.requirements') }}" method="POST" class="text-end">
                    @csrf
                    <button class="btn btn-primary btn-md">{{ translate_text('Continue') }}<i
                            class="bi bi-arrow-right ms-2"></i></button>
                </form>
            @else
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    {{ translate_text('Some extensions are required. Please enable them before you can continue.') }}
                </div>
                <div class="text-end">
                    <button class="btn btn-primary btn-md" disabled>{{ translate_text('Continue') }}<i
                            class="bi bi-arrow-right ms-2"></i></button>
                </div>
            @endif
        </div>
    </div>
@endsection


















