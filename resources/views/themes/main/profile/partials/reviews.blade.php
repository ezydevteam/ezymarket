<div class="profile-reviews-content">
    <div class="row align-items-center justify-content-between g-3 mb-3 pb-2 border-bottom-dashed">
        <div class="col-auto">
            <h4 class="fw-bold text-gray-700 mb-0 h5">{{ translate('Reviews') }}</h4>
        </div>
        @if (@$settings->product->reviews_status && $user->total_reviews > 0)
            <div class="col-auto">
                <div class="bg-white border rounded-pill px-3 py-1">
                    @include('themes.main.partials.rating-stars', [
                        'args' => $user,
                        'counter_only' => true,
                    ])
                </div>
            </div>
        @endif
    </div>

    @if ($reviews->count() > 0)
        <div class="reviews-list">
            <div class="row row-cols-1 g-4" id="reviews-list">
                @foreach ($reviews as $review)
                    <div class="col">
                        @themeInclude('partials.product-review', [
                            'product' => $review->product,
                            'review' => $review,
                        ])
                    </div>
                @endforeach
            </div>
        </div>

        @themeInclude('partials.load-more', [
            'items' => $reviews,
            'target' => '#reviews-list',
        ])
    @else
        <div class="text-center py-5 bg-light rounded-4">
            <div class="opacity-25 mb-3">
                <i class="bi bi-star display-4"></i>
            </div>
            <h5 class="fw-bold">{{ translate('No reviews yet') }}</h5>
            <p class="text-muted mb-0">{{ translate(':user hasn\'t received any reviews yet.', ['user' => $user->full_name]) }}</p>
        </div>
    @endif
</div>
