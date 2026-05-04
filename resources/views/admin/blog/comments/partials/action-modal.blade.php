{{-- View Comment Modal --}}
<x-modal
    id="viewComment-{{ $comment->id }}"
    :title="translate('View Comment')"
    :icon="'bi bi-chat-dots'"
>
    <textarea class="form-control" rows="5" readonly>{{ $comment->body }}</textarea>

    <x-slot:footer>
        <a href="{{ route('admin.blog.comments.destroy', $comment->id) }}"
            class="btn bg-text-red flex-fill action-confirm"
            data-method="DELETE"
            data-confirm="{{ translate('Are you sure want to delete this comment? This action can not be undone.') }}">
            <i class="bi bi-trash me-2"></i>
            {{ translate('Delete') }}
        </a>
        @if ($comment->isHold())
        <form action="{{ route('admin.blog.comments.unhold', $comment->id) }}"
            method="POST"
            class="d-inline flex-fill"
            data-ajax-confirm="true">
            @csrf
            <button type="submit"
                class="btn btn-success w-100 action-confirm"
                data-confirm="{{ translate('Are you sure want to unhold this comment?') }}">
                <i class="bi bi-play-circle me-2"></i>
                {{ translate('Unhold') }}
            </button>
        </form>
        @elseif ($comment->isPublished())
        <form action="{{ route('admin.blog.comments.update', $comment->id) }}"
            method="POST"
            class="d-inline flex-fill"
            data-ajax-confirm="true">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="hold">
            <button type="submit"
                class="btn btn-warning w-100 action-confirm"
                data-confirm="{{ translate('Are you sure want to hold this comment?') }}">
                <i class="bi bi-pause-circle me-2"></i>
                {{ translate('Hold') }}
            </button>
        </form>
        @endif
        @if ($comment->isPending())
        <form action="{{ route('admin.blog.comments.update', $comment->id) }}"
            method="POST"
            class="d-inline flex-fill"
            data-ajax-confirm="true">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="published">
            <button type="submit"
                class="btn btn-success w-100 action-confirm"
                data-confirm="{{ translate('Are you sure want to publish this comment?') }}">
                <i class="bi bi-check-circle me-2"></i>
                {{ translate('Publish') }}
            </button>
        </form>
        @endif
    </x-slot:footer>
</x-modal>
