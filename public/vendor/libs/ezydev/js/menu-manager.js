/**
 * Menu Manager Module
 * Handles all menu management functionality including:
 * - Menu item selection and bulk operations
 * - Adding items (pages, categories, custom links)
 * - Search/filter functionality
 * - Import menus from other locations
 * - Location switching
 */
const MenuManager = (function($) {
    'use strict';

    // Configuration - will be set on init
    let config = {
        bulkAddRoute: '',
        currentLocation: '',
        csrfToken: '',
        translations: {
            selectAtLeastOne: 'Please select at least one item',
            adding: 'Adding...',
            somethingWrong: 'Something went wrong',
            enterLinkText: 'Please enter link text',
            updating: 'Updating...',
            deleting: 'Deleting...',
            importing: 'Importing...',
            failedToLoadMenus: 'Failed to load menus'
        }
    };

    // Selectors
    const selectors = {
        // Container
        menuItemsContainer: '#menuItemsContainer',

        // Checkboxes
        selectAllMenus: '#selectAllMenus',
        rowCheckbox: '.row-checkbox',
        selectAllCheckbox: '.select-all-checkbox',

        // Bulk actions
        bulkDeleteBtn: '#menuBulkDeleteBtn',
        addSelectedBtn: '.add-selected-btn',

        // Custom link
        customLinkName: '#customLinkName',
        customLinkSlug: '#customLinkSlug',
        addCustomLinkBtn: '#addCustomLinkBtn',

        // Location
        locationSelect: '#menuLocationSelect',
        locationBtn: '#menuLocationBtn',

        // Search
        searchToggle: '#menuSearchToggle',
        searchBox: '.menu-search-box',
        searchInput: '#menuSearchInput',
        searchClose: '#menuSearchClose',
        searchNoResult: '#menuSearchNoResult',
        menuItem: '.dd-item',

        // Import
        importFromLocation: '#importFromLocation',
        importFromLocationHidden: '#importFromLocationHidden',
        importMenuListContainer: '#importMenuListContainer',
        importMenuList: '#importMenuList',
        importMenuLoading: '#importMenuLoading',
        importSelectAll: '#importSelectAll',
        importMenuItem: '.import-menu-item',
        importMenuBtn: '#importMenuBtn',
        importMenuModal: '#importMenuModal',
        importMenuForm: '#importMenuForm'
    };

    /**
     * Helper: AJAX POST with CSRF token
     */
    function ajaxPost(url, data) {
        return $.ajax({
            url: url,
            type: 'POST',
            data: { _token: config.csrfToken, ...data }
        });
    }

    /**
     * Update bulk delete button visibility
     */
    function updateBulkDeleteBtn() {
        const $checked = $(selectors.rowCheckbox + ':checked');
        $(selectors.bulkDeleteBtn).toggleClass('d-none', $checked.length === 0);
    }

    /**
     * Update import button state
     */
    function updateImportBtn() {
        const count = $(selectors.importMenuItem + ':checked').length;
        $(selectors.importMenuBtn).prop('disabled', count === 0);
    }

    /**
     * Initialize edit menu modals
     */
    function initMenuModals() {
        $('[id^="editMenuModal-"]').each(function() {
            const id = this.id.replace('editMenuModal-', '');

            initAjaxModalForm({
                modalSelector: '#editMenuModal-' + id,
                formSelector: '#editMenuForm-' + id,
                submitButtonSelector: '#editMenuBtn-' + id,
                loadingText: config.translations.updating
            });

            initAjaxModalForm({
                modalSelector: '#editMenuModal-' + id,
                formSelector: '#editMenuDeleteForm-' + id,
                submitButtonSelector: '#editMenuDeleteBtn-' + id,
                loadingText: config.translations.deleting
            });
        });

        // Reset select all checkbox after content refresh
        $(selectors.selectAllMenus).prop({ checked: false, indeterminate: false });
        updateBulkDeleteBtn();
    }

    /**
     * Refresh menu container content
     */
    function refreshMenuContent(html) {
        $(selectors.menuItemsContainer).html(html);
        window.initNestable();
        initMenuModals();

        // Reinitialize selectpicker for new content
        $(selectors.menuItemsContainer).find('.selectpicker').selectpicker('destroy').selectpicker();
    }

    // ==========================================
    // Event Handlers
    // ==========================================

    /**
     * Handle select all menus checkbox
     */
    function onSelectAllMenusChange() {
        const isChecked = this.checked;
        $(selectors.rowCheckbox).prop('checked', isChecked).each(function() {
            $(this).closest('.dd-handle').css('background', isChecked ? 'rgb(from var(--primary-color) r g b / 0.05)' : '');
        });
        updateBulkDeleteBtn();
    }

    /**
     * Handle individual row checkbox change
     */
    function onRowCheckboxChange() {
        $(this).closest('.dd-handle').css('background', this.checked ? 'rgb(from var(--primary-color) r g b / 0.05)' : '');

        const $all = $(selectors.rowCheckbox);
        const $checked = $all.filter(':checked');

        $(selectors.selectAllMenus).prop({
            checked: $all.length === $checked.length && $all.length > 0,
            indeterminate: $checked.length > 0 && $checked.length < $all.length
        });

        updateBulkDeleteBtn();
    }

    /**
     * Handle bulk delete button click
     */
    function onBulkDeleteClick() {
        window.bulkAction({
            url: $(this).data('url'),
            confirmMessage: config.translations.bulkDeleteConfirm,
            reloadOnSuccess: true
        });
    }

    /**
     * Handle select all checkbox in sidebar
     */
    function onSelectAllCheckboxChange() {
        $('.' + $(this).data('target')).prop('checked', this.checked);
    }

    /**
     * Handle add selected items button click
     */
    function onAddSelectedClick() {
        const $btn = $(this);
        const type = $btn.data('type');
        const $checked = $('.' + type + '-item:checked');

        if (!$checked.length) {
            toastr.warning(config.translations.selectAtLeastOne);
            return;
        }

        const items = $checked.map(function() {
            return {
                name: $(this).data('name'),
                slug: $(this).data('slug'),
                type: $(this).data('type')
            };
        }).get();

        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>' + config.translations.adding);

        ajaxPost(config.bulkAddRoute, { location: config.currentLocation, items: items })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    if (response.data?.html) {
                        refreshMenuContent(response.data.html);
                    }
                    $checked.prop('checked', false);

                    const selectAllId = type === 'page' ? '#selectAllPages' :
                                        type === 'category' ? '#selectAllCategories' : '#selectAllSubCategories';
                    $(selectAllId).prop({ checked: false, indeterminate: false });
                }
            })
            .fail(function(xhr) {
                const msg = xhr.responseJSON?.message || config.translations.somethingWrong;
                toastr.error(msg);
            })
            .always(function() {
                $btn.prop('disabled', false).html(originalHtml);
            });
    }

    /**
     * Handle add custom link button click
     */
    function onAddCustomLinkClick() {
        const $btn = $(this);
        const $name = $(selectors.customLinkName);
        const $slug = $(selectors.customLinkSlug);
        const name = $name.val().trim();

        if (!name) {
            toastr.warning(config.translations.enterLinkText);
            return;
        }

        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>' + config.translations.adding);

        ajaxPost(config.bulkAddRoute, {
            location: config.currentLocation,
            items: [{ name: name, slug: $slug.val().trim(), type: 'custom' }]
        })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    if (response.data?.html) {
                        refreshMenuContent(response.data.html);
                    }
                    $name.val('');
                    $slug.val('');
                }
            })
            .fail(function(xhr) {
                const msg = xhr.responseJSON?.message || config.translations.somethingWrong;
                toastr.error(msg);
            })
            .always(function() {
                $btn.prop('disabled', false).html(originalHtml);
            });
    }

    /**
     * Handle location select change
     */
    function onLocationSelectChange() {
        $(selectors.locationBtn).removeClass('d-none');
    }

    /**
     * Handle location button click
     */
    function onLocationBtnClick() {
        const $btn = $(this);
        const $select = $(selectors.locationSelect);

        $select.prop('disabled', true);
        $btn.prop('disabled', true).html('<span role="status" class="spinner-border spinner-border-sm"></span>');

        window.location.href = $select.val();
    }

    /**
     * Handle search toggle click
     */
    function onSearchToggleClick() {
        $(this).addClass('d-none');
        $(selectors.searchBox).removeClass('d-none');
        $(selectors.searchInput).focus();
    }

    /**
     * Handle search close click
     */
    function onSearchCloseClick() {
        $(selectors.searchInput).val('');
        $(selectors.searchBox).addClass('d-none');
        $(selectors.searchToggle).removeClass('d-none');

        // Show all menu items and hide no result
        $(selectors.menuItem).show();
        $(selectors.searchNoResult).addClass('d-none');
    }

    /**
     * Handle search input
     */
    function onSearchInput() {
        const query = $(this).val().toLowerCase().trim();
        const $noResult = $(selectors.searchNoResult);

        if (!query) {
            $(selectors.menuItem).show();
            $noResult.addClass('d-none');
            return;
        }

        let matchCount = 0;

        $(selectors.menuItem).each(function() {
            const $item = $(this);
            const text = $item.find('.dd-handle').first().text().toLowerCase();
            const match = text.includes(query);

            $item.toggle(match);

            // Also show parent items if child matches
            if (match) {
                matchCount++;
                $item.parents('.dd-item').show();
            }
        });

        // Show/hide no result message
        $noResult.toggleClass('d-none', matchCount > 0);
    }

    /**
     * Handle search input keyup (Escape to close)
     */
    function onSearchKeyup(e) {
        if (e.key === 'Escape') {
            $(selectors.searchClose).click();
        }
    }

    /**
     * Handle import location select change
     */
    function onImportLocationChange() {
        const location = $(this).val();
        const $container = $(selectors.importMenuListContainer);
        const $list = $(selectors.importMenuList);
        const $loading = $(selectors.importMenuLoading);
        const $btn = $(selectors.importMenuBtn);

        // Update hidden field
        $(selectors.importFromLocationHidden).val(location);

        if (!location) {
            $container.addClass('d-none');
            $btn.prop('disabled', true);
            return;
        }

        $loading.removeClass('d-none');
        $container.addClass('d-none');

        $.get($(this).data('url'), { location: location })
            .done(function(response) {
                if (response.success && response.data?.html) {
                    $list.html(response.data.html);
                    $container.removeClass('d-none');
                    $(selectors.importSelectAll).prop('checked', false);
                    $btn.prop('disabled', true);
                }
            })
            .fail(function() {
                toastr.error(config.translations.failedToLoadMenus);
            })
            .always(function() {
                $loading.addClass('d-none');
            });
    }

    /**
     * Handle import select all change
     */
    function onImportSelectAllChange() {
        $(selectors.importMenuItem).prop('checked', this.checked);
        updateImportBtn();
    }

    /**
     * Handle import menu item checkbox change
     */
    function onImportMenuItemChange() {
        const $all = $(selectors.importMenuItem);
        const $checked = $all.filter(':checked');

        $(selectors.importSelectAll).prop({
            checked: $all.length === $checked.length && $all.length > 0,
            indeterminate: $checked.length > 0 && $checked.length < $all.length
        });

        updateImportBtn();
    }

    /**
     * Handle import modal hidden
     */
    function onImportModalHidden() {
        $(selectors.importFromLocation).val('');
        $(selectors.importFromLocationHidden).val('');
        $(selectors.importMenuListContainer).addClass('d-none');
        $(selectors.importMenuList).html('');
        $(selectors.importSelectAll).prop('checked', false);
        $(selectors.importMenuBtn).prop('disabled', true);
    }

    /**
     * Bind all event handlers
     */
    function bindEvents() {
        const $doc = $(document);

        // Menu selection
        $doc.on('change', selectors.selectAllMenus, onSelectAllMenusChange);
        $doc.on('change', selectors.rowCheckbox, onRowCheckboxChange);
        $doc.on('click', selectors.bulkDeleteBtn, onBulkDeleteClick);

        // Sidebar - Add items
        $doc.on('change', selectors.selectAllCheckbox, onSelectAllCheckboxChange);
        $doc.on('click', selectors.addSelectedBtn, onAddSelectedClick);
        $doc.on('click', selectors.addCustomLinkBtn, onAddCustomLinkClick);

        // Location
        $doc.on('change', selectors.locationSelect, onLocationSelectChange);
        $doc.on('click', selectors.locationBtn, onLocationBtnClick);

        // Search
        $doc.on('click', selectors.searchToggle, onSearchToggleClick);
        $doc.on('click', selectors.searchClose, onSearchCloseClick);
        $doc.on('input', selectors.searchInput, onSearchInput);
        $doc.on('keyup', selectors.searchInput, onSearchKeyup);

        // Import
        $doc.on('change', selectors.importFromLocation, onImportLocationChange);
        $doc.on('change', selectors.importSelectAll, onImportSelectAllChange);
        $doc.on('change', selectors.importMenuItem, onImportMenuItemChange);
        $doc.on('hidden.bs.modal', selectors.importMenuModal, onImportModalHidden);
    }

    /**
     * Initialize import form
     */
    function initImportForm() {
        initAjaxModalForm({
            modalSelector: selectors.importMenuModal,
            formSelector: selectors.importMenuForm,
            submitButtonSelector: selectors.importMenuBtn,
            loadingText: config.translations.importing,
            reloadOnSuccess: false,
            onSuccess: function(response) {
                if (response.data?.html) {
                    refreshMenuContent(response.data.html);
                }
            }
        });
    }

    /**
     * Initialize the Menu Builder
     * @param {Object} options - Configuration options
     */
    function init(options) {
        // Merge options with defaults
        config = $.extend(true, config, options);

        // Bind all events
        bindEvents();

        // Initialize import form
        initImportForm();

        // Initialize edit modals
        initMenuModals();
    }

    // Public API
    return {
        init: init,
        initMenuModals: initMenuModals,
        refreshMenuContent: refreshMenuContent
    };

})(jQuery);
