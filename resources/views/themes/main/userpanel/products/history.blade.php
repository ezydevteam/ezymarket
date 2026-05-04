@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('section', translate('My Products'))
@section('title', $product->name)

@section('content')
    <div class="ajax-tabs">
        @themeInclude('userpanel.products.includes.tabs-nav')
        <div class="ajax-tabs-content">
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="row g-3">
                        @forelse ($productHistories as $productHistory)
                            <div class="col-12">
                                <div class="card-v border-0 shadow-sm rounded-4 p-3">
                                    <div class="card-v-body">
                                        <div class="conversation">
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between gap-2">
                                                    <div>
                                                        @if ($productHistory->seller)
                                                            @php $seller = $productHistory->seller; @endphp
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="user-avatar user-avatar-sm rounded">
                                                                    <img src="{{ $seller->avatar_url }}" alt="{{ $seller->username }}">
                                                                </div>
                                                                <div>
                                                                    <a href="{{ $seller->profile_link }}" target="_blank"
                                                                        class="text-gray-800 fw-semibold hover-primary">
                                                                        {{ $seller->username }}
                                                                    </a>
                                                                    <small class="text-muted d-block fw-normal fs-12">{{ $seller->email }}</small>
                                                                </div>
                                                            </div>
                                                        @elseif ($productHistory->admin)
                                                            @php $admin = $productHistory->admin; @endphp
                                                            <div>
                                                                <img src="{{ $admin->avatar_url }}" alt="{{ $admin->username }}"
                                                                    class="user-avatar user-avatar-sm rounded">
                                                                <span class="text-gray-800 fw-semibold ms-2">{{ $admin->username }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <time class="text-muted fs-12">
                                                            <i class="bi bi-clock-history me-1"></i>
                                                            {{ dateFormat($productHistory->created_at) }}
                                                        </time>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="conversation-content">
                                                <div class="mb-2">
                                                    {!! $productHistory->badge !!}
                                                </div>
                                                @if ($productHistory->body)
                                                    <div class="text-gray-600 mt-3">{!! sanitizeHtml($productHistory->body, true) !!}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card-v">
                                    <p class="text-center mb-0">{{ translate('No history found') }}</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    {{ $productHistories->links() }}
                </div>
                <div class="col-lg-4">
                    @themeInclude('userpanel.products.includes.sidebar')
                </div>
            </div>
        </div>
    </div>
@endsection

