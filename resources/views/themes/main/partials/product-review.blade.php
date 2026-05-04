@php
$reviewUser = $review->user;
$reviewUserBadge = $reviewUser->hasVerifiedBadge();
@endphp
<div
    class="product-review border rounded-3 p-0 bg-white overflow-hidden {{ $reviewUser->id == authUser()?->id ? 'border-primary' : '' }}">
    {{-- Review Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between p-3 border-bottom bg-light">
        <div class="d-flex align-items-center gap-2">
            @themeInclude('partials.rating-stars', [
            'args' => $review,
            'type' => 'full',
            'rating_number' => false
            ])
            <span class="text-muted">
                {{ translate('for') }} <span class="fw-semibold text-dark">{{ $review->subject }}</span>
            </span>
        </div>
        <div class="text-muted">
            {{ translate('by') }}
            <a href="{{ $reviewUser->profile_link }}" class="text-primary fw-medium">
                {{ $reviewUser->username }}
                @if ($reviewUserBadge)
                <img src="{{ $reviewUserBadge->image_url }}" alt="{{ $reviewUserBadge->name }}"
                    title="{{ translate('Verified') }}" width="12" height="12">
                @endif
            </a>
            <a href="{{ route('products.review', [$product->slug, $product->id, 'review_id' => $review->id]) }}"
                class="text-reset hover-underline small ms-1">{{ $review->created_at->diffForHumans() }}</a>
        </div>
    </div>

    {{-- Review Body --}}
    <div class="p-4 pb-2">
        @if ($review->body)
        <div class="text-dark-subtle fw-light">
            {!! sanitizeHtml($review->body, true) !!}
        </div>
        @endif

        @if (request()->routeIs('profile.reviews'))
        <p class="mb-0 mt-3 pt-2 border-top small">
            @if ($product->trashed())
            <span class="text-muted">{{ $product->name }}</span>
            @else
            <a href="{{ $product->view_link }}" class="text-muted hover-primary" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i>{{ $product->name }}
            </a>
            @endif
        </p>
        @endif
    </div>

    {{-- Seller Response / Reply --}}
    @if ($review->reply)
    @php
    $reply = $review->reply;
    $replyUser = $reply->user;
    @endphp
    <div class="bg-light-subtle border-top p-3 pb-2">
        <div class="d-flex gap-3">
            <div class="flex-shrink-0">
                <a href="{{ $replyUser->profile_link }}" class="user-avatar rounded" title="{{ $replyUser->username }}">
                    <img src="{{ $replyUser->avatar_url }}" alt="{{ $replyUser->username }}">
                </a>
            </div>
            <div class="flex-grow-1">
                <h6 class="fw-semibold text-dark mb-1">{{ translate('Seller Response') }}</h6>
                <div class="text-gray-700">
                    {!! sanitizeHtml($reply->body, true) !!}
                </div>
            </div>
        </div>
    </div>
    @elseif(authUser() && $review->body && $product->seller->id == authUser()->id)
    <div class="px-3 pb-3">
        <div class="border-top pt-3">
            <form action="{{ route('products.reviews.reply', [$product->slug, $product->id, $review->id]) }}"
                method="POST">
                @csrf
                <textarea class="form-control form-control-sm mb-3" name="reply"
                    placeholder="{{ translate('Your response...') }}" rows="2" required></textarea>
                <div class="text-end">
                    <button class="btn btn-primary btn-sm">{{ translate('Post') }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
