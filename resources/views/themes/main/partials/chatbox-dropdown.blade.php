<div class="chat-dropdown" id="chatDropdown">
    <div class="chat-dropdown-header">
		<p class="fw-500 mb-0">{{ translate('Messages') }}</p>
	</div>
	<div id="recent-messages"></div>
	<div class="chat-dropdown-footer">
		<a href="{{ route('chatbox.index') }}">
			{{ translate('View all messages') }}
		</a>
	</div>       
</div>
