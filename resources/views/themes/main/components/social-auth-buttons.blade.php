@if ($socialAuths->count() > 0)
    <div class="login-with">
        <div class="login-with-divider">
            <span>{{ translate('or continue with') }}</span>
        </div>
        <div class="row row-cols-1 row-cols-sm-{{ $socialAuths->count() > 1 ? 2 : 1 }} g-3 justify-content-center">
            @foreach ($socialAuths as $socialAuth)
                <div class="col">
                    <a href="{{ route('oauth.login', $socialAuth->alias) }}"
                        class="btn btn-soft rounded-4
                        @if($socialAuth->isModern()) btn-modern
                        @elseif($socialAuth->isMinimalistic()) btn-minimalistic
                        @else btn-logo-only
                        @endif w-100 text-center">
                        <img src="{{ $socialAuth->logo_url }}" class="object-fit-contain me-1" width="30" height="30" alt="{{ $socialAuth->name }}">
                        @if(!$socialAuth->isLogoOnly())
                            {{ translate($socialAuth->name) }}
                        @endif
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif
