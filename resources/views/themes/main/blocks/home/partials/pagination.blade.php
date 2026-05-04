@if(($data->pagination_style ?? 'load_more') == 'numeric' && $products instanceof
\Illuminate\Pagination\LengthAwarePaginator && $products->hasPages())
{{-- Numeric Pagination --}}
@php
$paginationQuery = collect(request()->query())->filter(function ($value, $key) {
return !(str_ends_with($key, '_page') && (int)$value <= 1); })->all();
@endphp
<div class="mt-4 d-flex justify-content-center w-100">
    {{ $products->appends($paginationQuery)->links() }}
</div>
@elseif(($data->pagination_style ?? 'load_more') == 'load_more' && $products instanceof
\Illuminate\Pagination\LengthAwarePaginator && $products->hasMorePages())
{{-- Load More Button --}}
<div class="text-center mt-4 w-100">
    <button class="btn btn-{{ $data->pagi_btn_style ?? 'outline-primary' }} btn-modern rounded-pill load-more-btn"
        data-url="{{ $products->nextPageUrl() }}"
        data-target="#{{ $data->uniqueId ?? 'newArrivalProducts' }} .row, #{{ $data->uniqueId ?? 'newArrivalProducts' }} .swiper-wrapper">
        <span class="load-more-text">{{ translate('Load More') }}</span>
        <x-loader class="load-more-loader d-none" :style="'spinner'" size="sm" :spinner_text="true" />
        @if(!empty($data->pagi_btn_icon))
        <i class="bi {{ $data->pagi_btn_icon }} ms-1 load-more-icon"></i>
        @endif
    </button>
</div>
@elseif((($data->pagination_style ?? 'load_more') == 'view_more'))
{{-- View More Button --}}
<div class="text-center mt-4 w-100">
    <a href="{{ route('products.index') }}"
        class="btn btn-{{ $data->pagi_btn_style ?? 'outline-primary' }} btn-modern rounded-pill">
        {{ translate('View More') }}
        @if(!empty($data->pagi_btn_icon))
        <i class="bi {{ $data->pagi_btn_icon }} ms-1"></i>
        @endif
    </a>
</div>
@endif 
