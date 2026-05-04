<x-modal id="reportProductModal" :title="'<span class=\'report-product-modal-title\'>'
        . ' <i class=\'bi bi-flag text-danger me-2\'></i>'
        . translate('Reporting: ')
        . '<span class=\'small ms-1\'>'
        . truncateText($product->name, 20)
        . '</span>'
        . '</span>'">
    @themeInclude('products.partials.report-form')
</x-modal>

<x-modal id="productShareModal" title="Love it ?">
    <div class="product-share-titles text-center text-capitalize mb-3">
        <h4>{{ translate('Share This product Now') }}</h4>
        <p class="mb-0">
            {{ translate('Spread the word about this product!') }}
        </p>
    </div>
    <div class="product-share-modal-section d-flex align-items-center justify-content-center py-4 px-2">
        @themeInclude('partials.share-buttons', [
        'socials_classes' => 'product-share-wrapper justify-content-center',
        'link' => $product->view_link,
        ])
    </div>
</x-modal>

<!-- Rating Modal -->
<x-modal id="productReviewModal"
    :title="'<i class=\'bi bi-star-fill text-warning me-2\'></i>' . translate('Rate & Review')">
    <div class="modal-body-content">
        <form action="{{ route('products.reviews.store', [$product->slug, $product->id]) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label small fw-bold text-uppercase text-gray-200 mb-3">{{ translate('Your Rating')
                    }}</label>
                <div class="d-flex gap-3 justify-content-center py-2 bg-light rounded-3">
                    @for ($i = 1; $i <= 5; $i++) <input type="radio" name="review_stars" value="{{ $i }}"
                        id="modalStar{{ $i }}" class="d-none">
                        <label for="modalStar{{ $i }}" class="cursor-pointer">
                            <i class="bi bi-star-fill fs-2 text-gray-700 modal-star-icon transition-all"
                                data-value="{{ $i }}"></i>
                        </label>
                        @endfor
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-uppercase text-gray-200">{{ translate('Subject') }}</label>
                <select class="form-select border-2" name="subject" required>
                    <option value="" disabled selected>{{ translate('Why are you giving this rating?') }}</option>
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
                <label class="form-label small fw-bold text-uppercase text-gray-200">{{ translate('Your Review')
                    }}</label>
                <textarea class="form-control border-2" name="review" rows="4"
                    placeholder="{{ translate('What did you like or dislike?') }}" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-modern w-100 rounded-pill fw-semibold py-2">
                {{ translate('Submit Review') }}
            </button>
        </form>
    </div>
</x-modal>
