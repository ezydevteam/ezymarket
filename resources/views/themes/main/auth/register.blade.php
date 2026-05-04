@php $isAjax = request()->ajax(); @endphp
@extends($isAjax ? 'themes.main.layouts.ajax' : 'themes.main.auth.layout')
@section('title', translate('Sign Up'))

@section('content')
<div class="r{{ $isAjax ? 'register-modal' : 'egister-page  my-5' }}">
    <div class="card border-0 rounded-4 shadow-lg">
        <div class="card-body p-4">
            @themeInclude('auth.partials.register-form', ['isAjax' => $isAjax])
        </div>
    </div>
</div>
@endsection
