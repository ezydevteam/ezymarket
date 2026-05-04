<div class="card border-0 shadow-sm rounded-4 p-0 transition-all hover-shadow">
    <div class="card-body rounded-4  p-0">
        <div class="table-responsive">
            <table class="table ezydev-table">
                <thead>
                    <tr>
                        <th class="text-wrap">{{ translate('Comment') }}</th>
                        <th>{{ translate('Commented by') }}</th>
                        <th class="text-center">{{ translate('Published Date') }}</th>
                        <th class="text-end">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comments as $comment)
                        <tr>
                            <td>
                                <div class="text-wrap">{{ truncateText($comment->replies->first()->body ?? '', 60) }}</div>
                            </td>
                            <td>
                                <x-user :user="$comment->user" avatarSize="sm" />
                            </td>
                            <td class="text-center text-muted">
                                {{ dateFormat($comment->created_at) }}
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                        data-bs-popper-config='{"strategy": "fixed"}'>
                                        <i class="bi bi-three-dots-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ $comment->view_link }}" target="_blank" class="dropdown-item">
                                                <i class="bi bi-box-arrow-up-right me-2"></i>{{ translate('View on site') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button data-action="{{ route('admin.products.comments.delete', [$product->id, $comment->id]) }}"
                                                class="dropdown-item text-danger action-confirm"
                                                data-text="{{ translate('Are you sure want to delete this comment? This action can not be undone.') }}"
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
                                <x-empty :message="translate('No comments found for this product.')" size="lg" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if ($comments->hasPages())
<div class="mt-3 ajax-pagination">
    {{ $comments->appends(['tab' => 'comments'])->links() }}
</div>
@endif
