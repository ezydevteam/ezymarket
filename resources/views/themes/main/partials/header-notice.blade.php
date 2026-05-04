<div class="announcement" style="background-color: {{ @$settings->announcement->background_color }};">
	<div class="container container-custom d-flex align-items-center position-relative">
		<div class="announcement-text">
			<span>{{ @$settings->announcement->body }}</span>
			@if (@$settings->announcement->button_title && @$settings->announcement->button_link)
				<a href="{{ @$settings->announcement->button_link }}"
				   class="btn btn-sm ms-2"
				   style="background-color: {{ @$settings->announcement->button_background_color }}; color: {{ @$settings->announcement->button_text_color }};">
					{{ @$settings->announcement->button_title }}
				</a>
			@endif
		</div>
		<button class="announcement-close" aria-label="close">
			<i class="bi bi-x"></i>
		</button>
	</div>
</div>
