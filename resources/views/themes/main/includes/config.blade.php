<script>
    "use strict";
    window.config = {!! json_encode([
        'url' => url('/'),
        'lang' => getLocale(),
        'direction' => getDirection(),
        'colors' => $themeSettings->colors,
        'translates' => [
            'copied' => translate('Copied to clipboard'),
            'actionConfirm' => translate('Are you sure you want to perform this action?'),
            'noneSelectedText' => translate('Nothing selected'),
            'noneResultsText' => translate('No results match'),
            'countSelectedText' => translate('{0} of {1} selected'),
            'searchPlaceholder' => translate('Start typing to search...'),
            'sLengthMenu' => translate('_MENU_'),
            'info' => translate('Showing _START_ to _END_ of _TOTAL_ totals'),
            'infoEmpty' => translate('Showing 0 to 0 of 0 entries'),
            'infoFiltered' => translate('(filtered from _MAX_ totals)'),
            'zeroRecords' => translate('No matching records found!'),
            'selectAtLeastOne' => translate('Please select at least one item'),
            'bulkActions' => translate('Bulk Actions'),
            'deleteSelected' => translate('Delete Selected'),
            'deleteSelectedError' => translate('Some selected items could not be deleted. Please try again later.'),
            'confirmBulkDelete' => translate('Are you sure you want to delete the selected items?'),
            'paginate' => [
                'first' => '<i class="bi bi-chevron-bar-left"></i>',
                'previous' => '<i class="bi bi-chevron-left"></i>',
                'next' => '<i class="bi bi-chevron-right"></i>',
                'last' => '<i class="bi bi-chevron-bar-right"></i>',
            ],
        ],
    ]) !!};
</script>
