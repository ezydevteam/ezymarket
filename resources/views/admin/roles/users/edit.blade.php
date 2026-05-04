@extends(request()->ajax() ? 'admin.layouts.ajax' : 'admin.layouts.app')
@section('section', translate('Users'))
@section('title', translate('Account Details'))
@section('container', 'container-max-xxl')

@section('content')
    <div class="user-edit-wrapper">
        @include('admin.roles.users.includes.header')

        <div class="ajax-tabs">
            @include('admin.roles.users.includes.nav-tabs')

            <div class="ajax-tabs-content pt-4" id="ajax-tabs-content">
                @include($activePartial)
            </div>
        </div>
    </div>

    <x-modal id="sendMailModal" title="{{ translate('Send Email') }}" :icon="'bi-envelope'" :scrollable="true">
        <form action="{{ route('admin.roles.users.sendmail', $user->id) }}" method="POST" class="ajax-form">
            @csrf
            <div class="mb-4">
                <label class="form-label lh-1">{{ translate('Subject') }} <span class="ps-1 text-danger">*</span></label>
                <input type="text" name="subject" class="form-control" placeholder="{{ translate('Enter Subject') }}" required>
            </div>
            <div class="mb-0">
                <label class="form-label lh-1">{{ translate('Message') }} <span class="ps-1 text-danger">*</span></label>
                <textarea name="message" class="form-control" rows="10" placeholder="{{ translate('Enter Message') }}"></textarea>
            </div>
            <div class="d-flex align-items-center gap-2 pt-4 border-0">
                <button type="button" class="btn btn-md btn-cancel flex-fill" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="submit" class="btn btn-md btn-primary flex-fill">{{ translate('Send') }}</button>
            </div>
        </form>
    </x-modal>
@endsection

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/clipboard/clipboard.min.js') }}"></script>
@endpush

@push('footer_content')
@include('admin.partials.ckeditor')
@endpush
