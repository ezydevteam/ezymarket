<div class="socials {{ $socials_class ?? 'gap-3' }}">
    <div class="product-share">
    <div class="product-share-truncate-btn">
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($link) }}" target="_blank" class="social-btn social-facebook">
            <i class="bi bi-facebook"></i>
    </a>
    @if ($show_label ?? true)
    <span class="text-xsmall text-truncate mt-2">{{ translate('Facebook') }}</span>
    @endif
    </div>
    </div>

    <div class="product-share">
         <div class="product-share-truncate-btn">
        <a href="https://twitter.com/intent/tweet?url={{ urlencode($link) }}" target="_blank" class="social-btn social-x">
            <i class="bi bi-twitter-x"></i>
        </a>
        @if ($show_label ?? true)
        <span class="text-xsmall text-truncate mt-2">{{ translate('X') }}</span>
        @endif
    </div>
    </div>

    <div class="product-share">
         <div class="product-share-truncate-btn">
        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode($link) }}" target="_blank"
        class="social-btn social-linkedin">
        <i class="bi bi-linkedin"></i>
    </a>
    @if ($show_label ?? true)
    <span class="text-xsmall text-truncate mt-2">{{ translate('Linkedin') }}</span>
    @endif
    </div>
    </div>

    <div class="product-share">
          <div class="product-share-truncate-btn">
       <a href="http://pinterest.com/pin/create/button/?url={{ urlencode($link) }}" target="_blank"
        class="social-btn social-pinterest">
        <i class="bi bi-pinterest"></i>
    </a>
    @if ($show_label ?? true)
    <span class="text-xsmall text-truncate mt-2">{{ translate('Pinterest') }}</span>
    @endif
    </div>
    </div>

    <div class="product-share">
           <div class="product-share-truncate-btn">
          <a href="https://wa.me/?text={{ urlencode($link) }}" target="_blank" class="social-btn social-whatsapp">
        <i class="bi bi-whatsapp"></i>
    </a>
    @if ($show_label ?? true)
    <span class="text-xsmall text-truncate mt-2">{{ translate('Whatsapp') }}</span>
    @endif
    </div>
    </div>

    <div class="product-share">
          <div class="product-share-truncate-btn">
        <button class="social-btn social-copy-link" data-link="{{ $link }}" title="Copy Link">
            <i class="bi bi-link-45deg"></i>
        </button>
        @if ($show_label ?? true)
        <span class="text-xsmall text-truncate mt-2">{{ translate('Copy Link') }}</span>
        @endif
    </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('click', function(event) {
        const copyButton = event.target.closest('.social-copy-link');

        if (copyButton) {
            const linkToCopy = copyButton.getAttribute('data-link');
            const textSpan = copyButton.nextElementSibling;

            const provideFeedback = (success, buttonElement) => {
                const originalIconElement = buttonElement.querySelector('i');

                // Update title
                buttonElement.setAttribute('title', success ? 'Link Copied!' : 'Failed to Copy');

                // Update icon
                if (originalIconElement) {
                    originalIconElement.className = success ? 'bi bi-check2' : 'bi bi-x-lg';
                }

                // Update text
                if (textSpan) {
                    textSpan.textContent = success ? 'Link Copied' : 'Failed!';
                }

                // Update background color classes
                if (success) {
                    buttonElement.classList.add('copied-success');
                    buttonElement.classList.remove('copied-failed');
                } else {
                    buttonElement.classList.add('copied-failed');
                    buttonElement.classList.remove('copied-success');
                }

            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(linkToCopy)
                    .then(() => {
                        provideFeedback(true, copyButton);
                    })
                    .catch(err => {
                        fallbackCopyTextToClipboard(linkToCopy, copyButton);
                    });
            } else {
                fallbackCopyTextToClipboard(linkToCopy, copyButton);
            }

            function fallbackCopyTextToClipboard(text, buttonElement) {
                let textArea = document.createElement("textarea");
                textArea.value = text;

                textArea.style.position = "fixed";
                textArea.style.top = "0";
                textArea.style.left = "0";
                textArea.style.width = "2em";
                textArea.style.height = "2em";
                textArea.style.padding = "0";
                textArea.style.border = "none";
                textArea.style.outline = "none";
                textArea.style.boxShadow = "none";
                textArea.style.background = "transparent";

                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        provideFeedback(true, buttonElement);
                    } else {
                        provideFeedback(false, buttonElement);
                        alert('Could not copy the link. Please copy manually: ' + text);
                    }
                } catch (err) {
                    provideFeedback(false, buttonElement);
                    alert('Could not copy the link. Please copy manually: ' + text);
                } finally {
                    document.body.removeChild(textArea);
                }
            }
        }
    });
});
</script>
