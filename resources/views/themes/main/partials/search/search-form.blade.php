<div class="{{ $container_class ?? 'live-search-component' }} position-relative">
	<form id="{{ $id ?? 'mainSearchForm' }}" action="{{ route('products.search') }}" method="GET">
		<div class="{{ $wrapper_class ?? 'search-input' }}">
			<input type="text" class="live-search-input {{ $input_class ?? '' }}"
				name="query" placeholder="{{ $placeholder ?? translate('Search for...') }}"
				value="{{ request('query') }}" autocomplete="off" required />

			<button type="button" class="clear-search-button d-none"
				aria-label="{{ translate('Clear search') }}">
				<i class="bi bi-x"></i>
			</button>

			<button type="submit" class="live-search-button {{ $btn_class ?? 'btn btn-primary' }}"
				aria-label="{{ translate('Search') }}">
				@if ($btn_icon ?? true)
					<i class="bi bi-search {{ !empty($btn_text) ? 'me-1' : '' }}"></i>
				@endif
				@if ($btn_text ?? false)
					<span>{{ translate($btn_text) }}</span>
				@endif
			</button>
		</div>
	</form>

	<div class="live-search-results list-group position-absolute bg-white border rounded shadow-sm d-none">
	    {{-- Results will be loaded here via AJAX --}}
	</div>

	@if ($show_backdrop ?? false)
		<div class="search-backdrop d-none"></div>
	@endif
</div>
