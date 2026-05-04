<div class="comments-section">
    @if($comments->count() < 1)
    <div class="card-v card-bg text-center p-4">
        <i class="bi bi-chat fs-2 text-muted d-block mb-3"></i>
        <p class="mt-2">{{ translate('No comments yet.') }}</p>
        @if (!authUser())
            <p class="mt-2 mb-0">{!! translate(':sign_in to comment', [
                'sign_in' => '<a class="comment-needs-login-modal" href="' . route('login') . '">' . translate('Sign In') . '</a>',
            ]) !!}</p>
        @endif
    </div>
    @endif
    @if ($comments->count() > 0)
    <div class="product-comments">
        <div class="row row-cols-1 {{ $comments->count() > 1 ? 'g-2' : '' }}">
            @foreach ($comments as $comment)
                <livewire:comments.comment-replies :comment="$comment" wire:key="{{ hash_encode($comment->id) }}" />
            @endforeach
            @if (!authUser())
            <p class="text-center mt-3 mb-0">{!! translate(':sign_in to comment or reply.', [
                'sign_in' => '<a class="comment-needs-login-modal" href="' . route('login') . '">' . translate('Sign In') . '</a>',
            ]) !!}</p>
            @endif
        </div>
    </div>
    <div class="d-flex justify-content-end">
        {{ $comments->links() }}
    </div>
    @endif
    @if (authUser())
    <div class="comment-box mt-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="fw-medium"><i class="bi bi-chat-dots me-2"></i>{{ translate('Add a comment') }}</h5>
            <div class="form-check form-check-sm mb-0">
                <input class="form-check-input" type="checkbox" id="notifyRepliesCheckbox" wire:model="notifyReplies">
                <label class="form-check-label text-muted hover-primary-underline small cursor-pointer" for="notifyRepliesCheckbox">
                    {{ translate('Notify me when someone replies to my comment') }}
                </label>
            </div>
        </div>
        <div class="card-v bg-light-subtle border p-3">
            <div class="leave-comment">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <img src="{{ authUser()->avatar_url }}" alt="{{ authUser()->username }}" width="45" height="45"
                            class="rounded-circle border object-fit-cover">
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <form wire:submit.prevent="storeComment">
                            <textarea class="form-control form-control-md w-100 mb-3" wire:model="comment"
                                placeholder="{{ translate('Write your comment...') }}" rows="2" required></textarea>
                            <div class="d-flex justify-content-between align-items-center">
                                <a data-bs-toggle="collapse" href="#formatReminderComments" role="button" aria-expanded="false"
                                    aria-controls="formatReminderComments" class="text-muted small hover-primary-underline">
                                    {{ translate('Find out how to format your comment...') }}
                                </a>
                                <button class="btn btn-primary btn-sm px-4" type="submit">{{ translate('Publish') }}</button>
                            </div>
                            <div class="collapse mt-3" id="formatReminderComments">
                                <div class="card card-body bg-light text-muted p-3 border mb-2 small">
                                    <p class="mb-2 fw-medium text-dark">
                                        {{ translate("Here’s a quick refresher on adding HTML enhancements to your comment.") }}
                                    </p>
                                    <ul class="list-unstyled mb-0 lh-lg">
                                        <li><code>&lt;strong&gt;&lt;/strong&gt;</code> {{ translate('to make things bold') }}</li>
                                        <li><code>&lt;em&gt;&lt;/em&gt;</code> {{ translate('to emphasize') }}</li>
                                        <li><code>&lt;ul&gt;&lt;li&gt;</code> {{ translate('or') }} <code>&lt;ol&gt;&lt;li&gt;</code> {{ translate('to make lists') }}</li>
                                        <li><code>&lt;h3&gt;</code> {{ translate('or') }} <code>&lt;h4&gt;</code> {{ translate('to make headings') }}</li>
                                        <li><code>&lt;pre&gt;&lt;/pre&gt;</code> {{ translate('for code blocks') }}</li>
                                        <li><code>&lt;code&gt;&lt;/code&gt;</code> {{ translate('for a few words of code') }}</li>
                                        <li><code>&lt;a&gt;&lt;/a&gt;</code> {{ translate('for links') }}</li>
                                        <li><code>&lt;img&gt;</code> {{ translate("to paste in an image") }}</li>
                                        <li><code>&lt;blockquote&gt;&lt;/blockquote&gt;</code> {{ translate('to quote somebody') }}</li>
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
    </div>
    @endif
</div>
