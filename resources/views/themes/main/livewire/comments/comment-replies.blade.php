<div class="product-cooment-section p-2">
    <div class="card-v border p-0 {{ (authUser()?->id == $comment->user->id) ? 'border-primary' : '' }}">
        <div class="product-comment p-0">
            @foreach ($commentReplies as $commentReply)
            @php
            $user = $commentReply->user;
            $commentUserBadge = $user->hasVerifiedBadge();
            @endphp
            <div class="comment-item px-3 pt-3 {{ $loop->first ? 'bg-light rounded-3' : 'border-top' }}">
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0">
                        <a href="{{ $user->profile_link }}" class="user-avatar user-avatar-sm rounded">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}">
                        </a>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <a href="{{ $user->profile_link }}" class="text-reset hover-primary fw-medium">
                                    {{ $user->username }}
                                    @if ($commentUserBadge)
                                    <img src="{{ $commentUserBadge->image_url }}" alt="{{ $commentUserBadge->name }}"
                                        title="{{ translate('Verified') }}" width="12" height="12">
                                    @endif
                                </a>

                                @if ($user->id == $product->seller->id)
                                <span class="badge bg-secondary bg-opacity-75 rounded-1 fw-normal text-uppercase fs-8">
                                    {{ translate('Seller') }}
                                </span>
                                @elseif ($user->hasPurchasedproduct($product->id))
                                <span class="badge bg-dark bg-opacity-75 rounded-1 fw-normal text-uppercase fs-8">
                                    {{ translate('Purchased') }}
                                </span>
                                @endif

                                @if ((authUser()?->id == $product->seller->id) || (authUser()?->id == $user->id))
                                @php $purchase = $user->getPurchaseRecord($product->id); @endphp
                                @if ($purchase && $purchase->support_expiry_at)
                                @if ($purchase->isSupportExpired())
                                <span class="badge bg-danger bg-opacity-75 rounded-1 fw-normal text-uppercase fs-8">
                                    {{ translate('Support Expired') }}
                                </span>
                                @endif
                                @endif
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-3 text-muted small">
                                <a href="{{ route('products.comment', [$product->slug, $product->id, $comment->id]) }}"
                                    class="text-reset hover-underline small">
                                    {{ $commentReply->created_at->diffforhumans() }}
                                </a>
                                @if (authUser() && !$commentReply->hasReported())
                                <a href="javascript:void(0)"
                                    wire:click.prevent="$dispatch('reportProductComment', { id: {{ $commentReply->id }} })"
                                    class="text-muted hover-warning" title="{{ translate('Report this comment') }}">
                                    <i class="bi bi-flag-fill"></i>
                                </a>
                                @endif
                            </div>
                        </div>

                        <div
                            class="fw-light text-gray-200 comment-body {{ $commentReply->hasReported() ? 'pb-3' : '' }}">
                            @if ($commentReply->hasReported())
                            <em class="text-muted">{{ translate('This comment is under review.') }}</em>
                            @else
                            {!! sanitizeHtml($commentReply->body, true) !!}
                            @endif
                        </div>

                        @if ($loop->last)
                        @if ((authUser()?->id == $product->seller->id) || (authUser()?->id == $comment->user->id))
                        <div class="pb-3">
                            <a class="link text-primary small fw-medium" data-bs-toggle="collapse"
                                data-bs-target="#reply{{ hash_encode($commentReply->id) }}" style="cursor: pointer;">
                                <i class="bi bi-reply-all-fill me-1"></i>
                                {{ translate('Reply') }}
                            </a>
                            <div wire:ignore.self class="collapse mt-3" id="reply{{ hash_encode($commentReply->id) }}">
                                <div class="d-flex align-items-start gap-3 border-top pt-3">
                                    <div class="flex-shrink-0">
                                        <img src="{{ authUser()->avatar_url }}" alt="{{ authUser()->username }}"
                                            class="user-avatar user-avatar-sm rounded">
                                    </div>
                                    <div class="flex-grow-1">
                                        <form wire:submit.prevent="storeReply">
                                            <textarea class="form-control form-control-md w-100 mb-3" wire:model="reply"
                                                placeholder="{{ translate('Your reply') }}" rows="2"
                                                required></textarea>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <a data-bs-toggle="collapse"
                                                    href="#formatReminderReply{{ hash_encode($commentReply->id) }}"
                                                    role="button" aria-expanded="false"
                                                    aria-controls="formatReminderReply{{ hash_encode($commentReply->id) }}"
                                                    class="text-muted small hover-primary-underline">
                                                    {{ translate('Find out how to format your reply...') }}
                                                </a>
                                                <button class="btn btn-primary btn-sm px-4">{{ translate('Reply')
                                                    }}</button>
                                            </div>
                                            <div class="collapse mt-3"
                                                id="formatReminderReply{{ hash_encode($commentReply->id) }}">
                                                <div class="card card-body bg-light text-muted p-3 border mb-2 small">
                                                    <p class="mb-2 fw-medium text-dark">
                                                        {{ translate("Here’s a quick refresher on adding HTML
                                                        enhancements to your reply.") }}
                                                    </p>
                                                    <ul class="list-unstyled mb-0  lh-lg">
                                                        <li><code>&lt;strong&gt;&lt;/strong&gt;</code> {{ translate('to
                                                            make things bold') }}</li>
                                                        <li><code>&lt;em&gt;&lt;/em&gt;</code> {{ translate('to
                                                            emphasize') }}</li>
                                                        <li><code>&lt;ul&gt;&lt;li&gt;</code> {{ translate('or') }}
                                                            <code>&lt;ol&gt;&lt;li&gt;</code> {{ translate('to make
                                                            lists') }}</li>
                                                        <li><code>&lt;h3&gt;</code> {{ translate('or') }}
                                                            <code>&lt;h4&gt;</code> {{ translate('to make headings') }}
                                                        </li>
                                                        <li><code>&lt;pre&gt;&lt;/pre&gt;</code> {{ translate('for code
                                                            blocks') }}</li>
                                                        <li><code>&lt;code&gt;&lt;/code&gt;</code> {{ translate('for a
                                                            few words of code') }}</li>
                                                        <li><code>&lt;a&gt;&lt;/a&gt;</code> {{ translate('for links')
                                                            }}</li>
                                                        <li><code>&lt;img&gt;</code> {{ translate("to paste in an
                                                            image") }}</li>
                                                        <li><code>&lt;blockquote&gt;&lt;/blockquote&gt;</code> {{
                                                            translate('to quote somebody') }}</li>
                                                        <li><code style="font-family: inherit;">:grin:</code> 😁</li>
                                                        <li><code style="font-family: inherit;">:shocked:</code> 😲</li>
                                                        <li><code style="font-family: inherit;">:cry:</code> 😢</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif

                        @if ($loop->first && !$allRepliesLoaded && $totalCommentReplies)
                        <div class="mt-3">
                            <button class="btn btn-link p-0 text-gray-700 fs-12 text-decoration-none hover-primary"
                                wire:click="loadAllReplies">
                                {{ translate($totalCommentReplies > 1 ? ':count more replies' : ':count more reply',
                                ['count' => $totalCommentReplies]) }}
                                <i class="bi bi-chevron-down fs-8 ms-1"></i>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
