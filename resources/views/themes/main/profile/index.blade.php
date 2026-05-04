@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.profile.layout')
@section('title', $user->full_name)

@section('content')
    @php $tab = $activeTab ?? 'profile'; @endphp

    @if($tab === 'profile')
        @themeInclude('profile.partials.profile')
    @elseif($tab === 'store')
        @themeInclude('profile.partials.store')
    @elseif($tab === 'followers')
        @themeInclude('profile.partials.followers')
    @elseif($tab === 'following')
        @themeInclude('profile.partials.following')
    @elseif($tab === 'reviews')
        @themeInclude('profile.partials.reviews')
    @endif
@endsection
