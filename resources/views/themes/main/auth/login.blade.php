@php $isAjax = request()->ajax(); @endphp
@extends($isAjax ? 'themes.main.layouts.ajax' : 'themes.main.auth.layout')
@section('title', translate('Sign In'))

@section('content')
<div class="{{ $isAjax ? 'login-modal' : 'login-page my-5' }}">
    <div class="card border-0 rounded-4 shadow-lg">
        <div class="card-body p-4">
            @themeInclude('auth.partials.login-form', ['isAjax' => $isAjax])
        </div>
    </div>
</div>
@endsection
