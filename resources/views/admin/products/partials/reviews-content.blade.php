<div class="card border-0 shadow-sm rounded-4 p-0 transition-all hover-shadow">
    <div class="card-body rounded-4 p-0">
        <div class="table-responsive">
            <table class="table ezydev-table text-nowrap">
                <thead>
                    <tr>
                        <th class="text-wrap">{{ translate('Review') }}</th>
                        <th>{{ translate('Reviewer') }}</th>
                        <th class="text-center">{{ translate('Published Date') }}</th>
                        <th class="text-end">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>
                                <div class="text-wrap">{{ truncateText($review->body, 50) }}</div>
                                <span class="d-inline-block status-badge border bg-light text-gray-700 mt-2">
                                    {{ translate('Rating: ' . $review->stars . ' stars') }}
                                </span>
                            </td>
                            <td>
                                <x-user :user="$review->user" avatarSize="sm" />
                            </td>
                            <td class="text-center text-muted">
                                {{ dateFormat($review->created_at) }}
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                        data-bs-popper-config='{"strategy": "fixed"}'>
                                        <i class="bi bi-three-dots-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ $review->view_link }}" target="_blank" class="dropdown-item">
                                                <i class="bi bi-box-arrow-up-right me-2"></i>{{ translate('View on site') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button data-action="{{ route('admin.products.reviews.delete', [$product->id, $review->id]) }}"
                                                class="dropdown-item text-danger action-confirm"
                                                data-text="{{ translate('Are you sure want to delete this review? This action can not be undone.') }}"
                                                data-method="DELETE">
                                               <i class="bi bi-trash me-2"></i>{{ translate('Delete') }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-0">
                                <x-empty :message="translate('No reviews found for this product.')" size="lg" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if ($reviews->hasPages())
<div class="mt-3 ajax-pagination">
    {{ $reviews->appends(['tab' => 'reviews'])->links() }}
</div>
@endif
