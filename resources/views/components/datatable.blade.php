@props([
'id' => 'dataTable',
'items' => [],
'class' => '',
'bodyClass' => 'px-0 py-3',
'tableClass' => '',
'title' => null, // Optional title
'description' => null, // Optional description
'titleClass' => 'fw-semibold mb-3 fs-5',
'ajaxUrl' => null, // DataTables AJAX URL
'serverSide' => false, // Whether to use server-side processing
'columns' => null, // Columns configuration
'filters' => null, // Filters configuration
'export' => true, // Whether to show export buttons
'searchPlaceholder' => null, // Search placeholder
'paging' => true, // Whether to enable DataTables paging
'pageLength' => 10, // Default page length
'sortingRoute' => null, // Optional sorting route for drag and drop
'customButtons' => [], // Array of custom buttons
'bulkActions' => null, // Bulk actions configuration
'bulkDeleteBtn' => null, // Optional bulk action button
'confirm' => 'Are you sure?',
'emptyClass' => '',
'emptySize' => 'col-lg-8 mx-auto',
'emptyTitle' => null,
'emptyDesc' => 'It seems that the section is empty or your search didn\'t fetch any results',
'emptyIcon' => 'bi-search',
'emptyIconColor' => 'muted',
'emptyBtnText' => null, // Button text
'emptyBtnIcon' => 'bi-plus-lg', // Button icon
'emptyBtnClass' => 'btn btn-primary', // Button classes
'emptyBtnLink' => null, // Route/URL for link
'emptyBtnModal' => null, // Modal target (e.g., '#createModal')
'emptyBtnModalText' => null, // Modal target (e.g., '#createModal')
'emptyBtnModalAction' => null, // Modal target (e.g., '#createModal')
'emptyBtnModalClass' => 'btn btn-primary', // Modal target (e.g., '#createModal')
'order' => null, // Default ordering configuration
])

@php $hasItems = is_numeric($items) ? $items > 0 : count($items) > 0; @endphp
<div class="card datatable-card border-0 shadow-sm rounded-4 p-0 {{ $class }}">
    @if($hasItems)
    <div class="card-body {{ $bodyClass }}">
        <div class="table-responsive">
            <table {{ $attributes->merge(['class' => "table ezydev-table datatable $tableClass", 'id' => $id]) }}
                @if($ajaxUrl) data-ajax-url="{{ $ajaxUrl }}" @endif
                @isset($serverSide) data-server-side="{{ $serverSide ? 'true' : 'false' }}" @endisset
                @if($columns) data-columns='@json($columns)' @endif
                @if($filters) data-ajax-filter="true" data-filter-config='@json(["filters" => $filters, "autoApply" =>
                true])' @endif
                @if($export) data-export="true" @endif
                @isset($title) data-title="{{ $title }}" @endisset
                @isset($description) data-description="{{ $description }}" @endisset
                @isset($paging) data-paging="{{ $paging ? 'true' : 'false' }}" @endisset
                @isset($pageLength) data-page-length="{{ $pageLength }}" @endisset
                @if($searchPlaceholder) data-search-placeholder="{{ $searchPlaceholder }}" @endif
                @if($sortingRoute) data-sortable="{{ $sortingRoute }}" @endif
                @if(count($customButtons) > 0) data-custom-buttons='@json($customButtons)' @endif
                @if($bulkActions) data-bulk-actions='@json($bulkActions)' @endif
                @if($bulkDeleteBtn) data-bulk-delete-btn='@json($bulkDeleteBtn)' @endif
                @if($order) data-order='@json($order)' @endif>
                {{ $slot }}
            </table>
        </div>
    </div>
    @else
    <x-empty :class="$emptyClass" :size="$emptySize" :icon="$emptyIcon" :iconColor="$emptyIconColor"
        :title="$emptyTitle" :desc="$emptyDesc" :titleClass="$titleClass" :btnText="$emptyBtnText"
        :btnIcon="$emptyBtnIcon" :btnClass="$emptyBtnClass" :btnLink="$emptyBtnLink" :btnModal="$emptyBtnModal"
        :btnModalText="$emptyBtnModalText" :btnModalAction="$emptyBtnModalAction"
        :btnModalClass="$emptyBtnModalClass" />
    @endif
</div>
