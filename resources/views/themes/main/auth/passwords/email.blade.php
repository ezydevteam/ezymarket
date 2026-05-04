@php $isAjax = request()->ajax(); @endphp
@extends($isAjax ? 'themes.main.layouts.ajax' : 'themes.main.auth.layout')
@section('title', translate('Reset Password'))

@section('content')
<div class="card border-0 rounded-4 shadow-lg">
    <div class="card-body p-4">
        @themeInclude('auth.partials.forgot-password-form', ['isAjax' => $isAjax])
    </div>
</div>
@endsection
