<x-modal
    id="conversationModal-{{ $refund->id }}"
    :title="translate('Refund Conversation')"
    icon="bi-chat-dots"
    size="lg"
    scrollable="true"
>
    <div class="row g-3">
        @forelse ($refund->replies as $reply)
            @php
                $user = $reply->user;
                $seller = $refund->seller;
            @endphp
            <div class="col-12">
                <div class="conversation">
                    <div class="card border">
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <div class="row row-cols-auto justify-content-between align-items-center g-3">
                                    <div class="col">
                                        <a href="{{ route('admin.roles.users.edit', $user->id) }}"
                                            class="conversation-user text-dark d-flex align-items-center gap-2 text-decoration-none">
                                            <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}" class="rounded-circle" width="40" height="40">
                                            <div>
                                                <h6 class="mb-0 hover-primary">{{ $user->full_name }}</h6>
                                                <small class="text-muted">&commat;{{ $user->username }}
                                                    <span class="badge {{ $user->id === $seller->id ? 'bg-text-primary' : 'bg-text-green' }} px-2 py-1 mb-0 ms-1">
                                                        {{ $user->id === $seller->id ? translate('Seller') : translate('Buyer') }}
                                                    </span>
                                                </small>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col">
                                        <time class="text-muted small"><i class="bi bi-clock me-1"></i>{{ dateFormat($reply->created_at) }}</time>
                                    </div>
                                </div>
                            </div>
                            <div class="conversation-content">
                                {!! sanitizeHtml($reply->message, true) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-chat-square-dots display-4 text-muted mb-3"></i>
                    <p class="text-muted mb-0">{{ translate('No conversation messages found.') }}</p>
                </div>
            </div>
        @endforelse
    </div>

    <x-slot name="footer">
        <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal">{{ translate('Close') }}</button>
    </x-slot>
</x-modal>
