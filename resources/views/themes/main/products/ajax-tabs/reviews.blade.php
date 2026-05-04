<div class="product-reviews-ajax-tab"
    data-ajax-url="{{ route('products.ajax_content', ['slug' => $product->slug, 'id' => $product->id, 'tab' => 'reviews']) }}">
    @if ($product->total_reviews > 0)
    <div class="review-breakdown mb-4">
        <div class="row g-4 align-items-stretch">
            <div class="col-md-5 col-lg-4">
                <div class="card-v d-flex flex-column justify-content-center align-items-center border p-4 h-100">
                    <div class="d-flex flex-wrap justify-content-center text-center gap-3">
                        <h5 class="display-3 fw-bolder mb-0 text-dark">{{ number_format($product->avg_reviews, 1) }}
                        </h5>
                        <div class="d-flex flex-column justify-content-center align-items-center">
                            <p class="mb-2">{{ translate('Average Rating') }}</p>
                            @include('themes.main.partials.rating-stars', [
                            'args' => $product,
                            'type' => 'full',
                            'rating_number' => false,
                            'ratings_classes' => 'justify-content-center h4 mb-0'
                            ])
                        </div>
                    </div>
                    <p class="text-gray-700 mb-0">
                        {{ translate('Based on :count reviews', ['count' => numberFormat($product->total_reviews)]) }}
                    </p>
                </div>
            </div>
            <div class="col-md-7 col-lg-8">
                <div class="card-v border p-4 h-100 d-flex flex-column justify-content-center">
                    @for ($i = 5; $i >= 1; $i--)
                    @php
                    $count = $starBreakdown[$i] ?? 0;
                    $starPercentage = $product->total_reviews > 0 ? ($count / $product->total_reviews) * 100 : 0;
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="text-nowrap" style="width: 50px;">
                            <span class="fw-medium">{{ $i }}</span> <i
                                class="bi bi-star-fill text-warning small ms-1"></i>
                        </div>
                        <div class="progress flex-grow-1 bg-light" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar bg-warning rounded-5" role="progressbar"
                                style="width: {{ $starPercentage }}%;" aria-valuenow="{{ $starPercentage }}"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="text-muted text-end small" style="width: 50px;">
                            {{ $starPercentage }}%
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row row-cols-auto align-items-center justify-content-between g-3 mb-4">
        <div class="col">
            <h5 class="text-gray-200 mb-0">{{ translate(':count reviews found.', ['count' => $product->total_reviews])
                }}</h5>
        </div>
        <div class="col d-flex gap-2 align-items-center">
            @if ($product->total_reviews > 0)
            <div class="review-sort">
                <div class="input-group input-group-sm">
                    <span class="input-group-text" title="{{ translate('Sort by') }}">
                        <i class="bi bi-sort-down"></i>
                    </span>
                    <select class="form-select form-select-sm" id="reviewSortBy">
                        <option value="newest" {{ request('review_sort_by')=='newest' ? 'selected' : '' }}>
                            {{ translate('Newest') }}</option>
                        <option value="highest_rating" {{ request('review_sort_by')=='highest_rating' ? 'selected' : ''
                            }}>
                            {{ translate('Highest Rating') }}</option>
                        <option value="lowest_rating" {{ request('review_sort_by')=='lowest_rating' ? 'selected' : ''
                            }}>
                            {{ translate('Lowest Rating') }}</option>
                    </select>
                </div>
            </div>
            @endif
            @if (authUser() && authUser()->hasPurchasedProduct($product->id) &&
            !authUser()->hasReviewedProduct($product->id))
            <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse"
                data-bs-target="#reviewsForm">
                <i class="bi bi-pencil-square me-1"></i>{{ translate('Write a review') }}
            </button>
            @endif
        </div>
    </div>

    @if (authUser() && authUser()->hasPurchasedProduct($product->id) && !authUser()->hasReviewedProduct($product->id))
    <div class="collapse mb-4" id="reviewsForm">
        <div class="card card-body bg-light-subtle border p-4">
            <form action="{{ route('products.reviews.store', [$product->slug, $product->id]) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-medium">{{ translate('Your Rating') }}</label>
                    <div class="row row-cols-auto g-2 ratings ratings-lg ratings-selective">
                        @for ($i = 1; $i <= 5; $i++) <div class="col rating" data-rating="{{ $i }}">
                            <input type="radio" name="review_stars" value="{{ $i }}" id="star{{ $i }}" class="d-none" {{
                                $i==5 ? 'checked' : '' }}>
                            <label for="star{{ $i }}" style="cursor: pointer;"><i
                                    class="bi bi-star-fill fs-4"></i></label>
                    </div>
                    @endfor
                </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">{{ translate('Subject') }}</label>
            <select class="form-select" name="subject" required>
                <option value="" disabled selected>{{ translate('Select Subject') }}</option>
                <option value="Customer Support">{{ translate('Customer Support') }}</option>
                <option value="Feature Availability">{{ translate('Feature Availability') }}</option>
                <option value="Design Quality">{{ translate('Design Quality') }}</option>
                <option value="Code Quality">{{ translate('Code Quality') }}</option>
                <option value="Flexibility">{{ translate('Flexibility') }}</option>
                <option value="Documentation Quality">{{ translate('Documentation Quality') }}</option>
                <option value="Future Updates">{{ translate('Future Updates') }}</option>
                <option value="Performance">{{ translate('Performance') }}</option>
                <option value="Pricing">{{ translate('Pricing') }}</option>
                <option value="Quality">{{ translate('Quality') }}</option>
                <option value="Reliability">{{ translate('Reliability') }}</option>
                <option value="Support">{{ translate('Support') }}</option>
                <option value="Usability">{{ translate('Usability') }}</option>
                <option value="Value for Money">{{ translate('Value for Money') }}</option>
                <option value="Overall">{{ translate('Overall') }}</option>
                <option value="Other">{{ translate('Other') }}</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="form-label fw-medium">{{ translate('Your Review') }}</label>
            <textarea class="form-control" name="review" rows="4"
                placeholder="{{ translate('What did you like or dislike?') }}" required></textarea>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-primary px-4">{{ translate('Submit') }}</button>
        </div>
        </form>
    </div>
</div>
@endif

<div class="product-reviews">
    @if ($reviews->count() > 0)
    <div class="row row-cols-1 g-3">
        @foreach ($reviews as $review)
        @themeInclude('partials.product-review', [
        'product' => $product,
        'review' => $review,
        ])
        @endforeach
    </div>
    @else
    <div class="card-v border text-center py-5">
        <i class="bi bi-star text-muted fs-1 mb-3 opacity-25"></i>
        <h6 class="text-muted">{{ translate('No reviews found.') }}</h6>
    </div>
    @endif
</div>

@if ($reviews->hasPages())
<div class="d-flex justify-content-end mt-2">
    {{ $reviews->links() }}
</div>
@endif
</div>
