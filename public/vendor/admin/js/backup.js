/* builder-manager.js
 * @dependency: jQuery, jQuery UI, Bootstrap 5, Toastr, Coloris
 * @Author: EzyDev Team
 * @Version: 2.0.0
 * Reusable JavaScript Builder Manager for Home, Header, and Footer Builders
 */

(function($) {
    "use strict";

    class BuilderManager {
        /**
         * @param {Object} options - Configuration options
         * @param {string} options.type - Builder type: 'home', 'header', or 'footer'
         * @param {string} options.containerId - Container ID selector
         * @param {string} options.availableBlocksId - Available blocks container ID
         * @param {string} options.canvasId - Canvas container ID
         * @param {Array} options.layoutData - Initial layout data
         * @param {Object} options.routes - Route URLs for AJAX calls
         * @param {string} options.csrfToken - CSRF token
         * @param {Object} options.translations - Translation strings
         * @param {boolean} options.allowDuplicates - Allow duplicate blocks in same column (default: false)
         * @param {boolean} options.enableSectionSettings - Enable section/row settings (default: true)
         * @param {boolean} options.fixedSections - Use fixed predefined sections (default: false)
         * @param {Array} options.sections - Fixed section definitions (when fixedSections is true)
         */
        constructor(options = {}) {
            this.config = {
                type: 'home',
                containerId: '#builder-container',
                availableBlocksId: '#available-blocks',
                canvasId: '#builder-canvas',
                itemClass: '.builder-item',
                allowDuplicates: false,
                enableSectionSettings: true,
                fixedSections: false,
                sections: [],
                translations: {},
                routes: {},
                layoutData: [],
                csrfToken: ''
            };

            this.config = $.extend({}, this.config, options);

            this.canvas = $(this.config.canvasId);
            this.layoutData = this.config.layoutData || [];
            this.currentSectionId = null;
            this.currentUniqueId = null;
            this.richTextEditorInstance = null;

            this.injectStyles(); // Inject custom styles for column controls
            this.translations = this.config.translations;

            this.init();
        }

        t(key) {
            return this.translations[key] || key;
        }

        generateUUID() {
            return Math.random().toString(36).substring(2, 10);
        }

        init() {
            this.renderBuilder();
            this.bindEvents();
            this.initDraggables();
        }

        // --- Drag & Drop ---

        initDraggables() {
            const self = this;
            const selector = this.config.availableBlocksId + ' .builder-item';

            // Destroy existing draggables first
            try { $(selector).draggable('destroy'); } catch(e) {}

            // Sidebar items
            $(selector).draggable({
                helper: function(e) {
                    const $el = $(this);
                    const title = $el.data('title') || $el.find('.fw-medium').text().trim();
                    const icon = $el.data('icon') || 'bi-columns-gap';

                    // Create a brand new element as helper
                    var $helper = $('<div></div>');
                    $helper.addClass('ui-draggable-helper');
                    $helper.html(
                        '<i class="' + icon + ' fs-4 text-muted mb-2"></i>' +
                        '<div class="small fw-medium text-dark">' + title + '</div>'
                    );
                    $helper.css({
                        'position': 'absolute',
                        'width': $el.outerWidth() + 'px',
                        'height': $el.outerHeight() + 'px',
                        'padding': '12px',
                        'background-color': '#ffffff',
                        'border': '1px solid #dee2e6',
                        'border-radius': '6px',
                        'box-shadow': '0 8px 16px rgba(0,0,0,0.2)',
                        'display': 'flex',
                        'flex-direction': 'column',
                        'align-items': 'center',
                        'justify-content': 'center',
                        'text-align': 'center',
                        'z-index': '10001',
                        'cursor': 'grabbing'
                    });

                    // Store source data
                    $helper.data('source-id', $el.data('id'));
                    $helper.data('source-title', $el.data('title'));

                    // Append to body immediately
                    $('body').append($helper);

                    return $helper;
                },
                revert: 'invalid',
                revertDuration: 200,
                zIndex: 10001,
                cursor: 'grabbing',
                start: function(e, ui) {
                    ui.helper.show();
                    $('body').addClass('builder-dragging');
                },
                stop: function(e, ui) {
                    $('body').removeClass('builder-dragging');
                }
            });
        }

        initSortables(selector) {
            const self = this;
            const $target = selector ? $(selector) : $('.builder-col');

            try { $target.sortable('destroy'); } catch(e) {}
            try { $target.droppable('destroy'); } catch(e) {}

            // Make columns droppable for sidebar items
            $target.droppable({
                accept: this.config.availableBlocksId + ' .builder-item',
                hoverClass: 'ui-droppable-hover',
                tolerance: 'pointer',
                drop: function(event, ui) {
                    const $container = $(this);
                    const $dragged = ui.draggable;
                    const $helper = ui.helper;

                    const id = $helper.data('source-id') || $dragged.data('id');
                    const title = $helper.data('source-title') || $dragged.data('title');
                    const icon = $dragged.data('icon') || 'bi-columns-gap';

                    // Duplicate check (unless allowDuplicates is true)
                    if (!self.config.allowDuplicates) {
                        let exists = false;
                        const idStr = String(id);
                        $container.find('.builder-item').each(function() {
                            if (String($(this).attr('data-id')) === idStr) {
                                exists = true;
                                return false;
                            }
                        });

                        if (exists) {
                            toastr.warning(self.t("This block is already added in this column."));
                            return;
                        }
                    }

                    const newItemData = {
                        id: id,
                        uniqueId: self.generateUUID(),
                        title: title,
                        icon: icon,
                        options: {}
                    };

                    // Use compact item HTML for fixed sections (header/footer)
                    if (self.config.fixedSections) {
                        $container.append(self.createFixedItemHtml(newItemData));
                        // Recalculate flex-grow for the row
                        self.updateColumnFlexGrow($container.closest('.builder-row'));
                    } else {
                        $container.append(self.createItemHtml(newItemData));
                    }

                    // Refresh sortable to register new item
                    $container.sortable('refresh');
                }
            });

            // Sortable for reordering within and between columns
            $target.sortable({
                connectWith: '.builder-col',
                items: '> .builder-item[data-unique-id]',
                cancel: '.dropdown, .dropdown *',
                placeholder: "builder-item-placeholder ui-corner-all",
                forcePlaceholderSize: true,
                forceHelperSize: true,
                tolerance: 'intersect',
                cursor: 'grabbing',
                dropOnEmpty: true,
                scrollSensitivity: 40,
                scrollSpeed: 20,
                revert: 100,
                start: function(e, ui) {
                    // Store original dimensions
                    ui.item.data('origWidth', ui.item.outerWidth());
                    ui.item.data('origHeight', ui.item.outerHeight());

                    // For fixed sections, use inline placeholder
                    if (self.config.fixedSections) {
                        ui.placeholder.removeClass('mb-2').addClass('d-inline-flex me-1');
                        ui.placeholder.css({
                            'height': ui.item.outerHeight() + 'px',
                            'width': ui.item.outerWidth() + 'px',
                            'min-width': ui.item.outerWidth() + 'px',
                            'vertical-align': 'middle'
                        });
                        // Add visual indicator for the dragged item
                        ui.item.addClass('opacity-50');
                    } else {
                        ui.placeholder.addClass('mb-2');
                        ui.placeholder.height(ui.item.outerHeight());
                        ui.placeholder.css('width', '100%');
                    }
                },
                change: function(e, ui) {
                    // Smooth placeholder transition
                    ui.placeholder.stop(true, true).hide().fadeIn(150);
                },
                over: function(e, ui) {
                    // For fixed sections, keep inline placeholder size
                    if (!self.config.fixedSections) {
                        const $col = $(this);
                        ui.placeholder.width($col.innerWidth() - 20);
                    }
                },
                helper: function(e, item) {
                    const title = item.data('title') || item.find('.fw-medium').text().trim();
                    const $helper = $('<div class="builder-item dragging-helper p-2 bg-white border rounded shadow d-flex align-items-center gap-2">' +
                        '<span class="small fw-medium text-truncate">' + title + '</span>' +
                        '<span class="ms-auto small text-muted"><i class="bi bi-three-dots-vertical"></i></span>' +
                    '</div>');
                    $helper.css({
                        'width': 'fit-content',
                        'max-width': '220px',
                        'z-index': '10001',
                        'pointer-events': 'none'
                    });
                    return $helper;
                },
                stop: function(e, ui) {
                    // Clean up any inline styles added by sortable for fixed sections
                    if (self.config.fixedSections) {
                        ui.item.removeClass('opacity-50');
                        ui.item.removeAttr('style').css('white-space', 'nowrap');
                        // Recalculate flex-grow for all columns in this row
                        self.updateColumnFlexGrow(ui.item.closest('.builder-row'));
                    }
                },
                update: function(e, ui) {
                    // Recalculate flex-grow when items are moved between columns
                    if (self.config.fixedSections) {
                        self.updateColumnFlexGrow(ui.item.closest('.builder-row'));
                    }
                },
                appendTo: 'body',
                zIndex: 10000
            });
        }

        // Recalculate flex-grow for columns based on User Toggles (Smart Flex)
        updateColumnFlexGrow(row) {
            const $row = row ? $(row) : $('.builder-row');

            $row.each(function() {
                // We base everything on the TOGGLES state
                const $toggles = $(this).find('.col-flex-grow-toggle');
                const totalCols = $toggles.length;

                // If no columns/toggles, nothing to do
                if (totalCols === 0) return;

                let activeFlexCols = 0;
                $toggles.each(function() {
                     if ($(this).is(':checked')) activeFlexCols++;
                });

                // Logic: If NO columns are set to grow, OR ALL columns are set to grow -> Fill Equal Space
                const forceFlexFill = (activeFlexCols === 0 || activeFlexCols === totalCols);

                $toggles.each(function() {
                     const isChecked = $(this).is(':checked');
                     let visualGrow = 0;

                     if (forceFlexFill) {
                         visualGrow = 1;
                     } else if (isChecked) {
                         visualGrow = 1;
                     }

                     // "Toggle OFF" columns should not auto-grow with content, they should stay compact (140px)
                     // while "Toggle ON" columns fill the rest.
                     const flexBasis = '0%';
                     const minWidth = '140px';

                     const $wrapper = $(this).closest('.column-wrapper');
                     // Apply visual style
                     $wrapper.css('flex', `${visualGrow} 1 ${flexBasis}`);
                     $wrapper.css('min-width', minWidth);
                });
            });
        }

        // --- Rendering ---

        renderBuilder() {
            this.canvas.empty();

            // Fixed sections mode (for header/footer builders)
            if (this.config.fixedSections && this.config.sections.length > 0) {
                this.renderFixedSections();
            } else {
                // Dynamic sections mode (for home builder)
                if(!Array.isArray(this.layoutData) || this.layoutData.length === 0) {
                    this.renderRow({ id: this.generateUUID(), columns: [{ id: this.generateUUID(), width: 12, blocks: [] }] });
                } else {
                    this.layoutData.forEach(row => this.renderRow(row));
                }

                // Render "Add New Section" button if not exists
                if ($('#add-new-row-btn').length === 0) {
                    this.canvas.after(`
                        <div class="text-center">
                            <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" id="add-new-row-btn">
                                <i class="bi bi-plus-circle me-2"></i>
                                ${this.t('Add New Section')}
                            </button>
                        </div>
                    `);
                }

                this.initRowSortables();
            }

            this.initSortables('.builder-col');
        }

        renderFixedSections() {
            const self = this;

            this.config.sections.forEach(sectionDef => {
                // Find existing data for this section
                const existingData = Array.isArray(this.layoutData)
                    ? this.layoutData.find(s => s.id === sectionDef.id)
                    : null;

                const sectionOptions = existingData?.options || {};
                const isEnabled = sectionOptions.enabled !== false;

                // Build columns from existing data or defaults
                let columns = [];
                if (existingData && existingData.columns) {
                    columns = existingData.columns;
                } else {
                    columns = sectionDef.defaultColumns.map(col => ({
                        id: this.generateUUID(),
                        width: col.width,
                        flexGrow: col.flexGrow || 0,
                        blocks: []
                    }));
                }

                this.renderFixedRow(sectionDef, columns, sectionOptions, isEnabled);
            });
        }

        renderFixedRow(sectionDef, columns, options, isEnabled) {
            const rowId = sectionDef.id;
            const optionsStr = encodeURIComponent(JSON.stringify({ ...options, enabled: isEnabled }));

            // Determine current layout type based on columns
            const colCount = columns.length;
            let currentLayout = colCount.toString();

            // Logic: Check if all flexGrows are 0 or all are 1
            const activeFlexCount = columns.filter(c => c.flexGrow && parseInt(c.flexGrow) === 1).length;
            const forceFlexFill = (activeFlexCount === 0 || activeFlexCount === colCount);

            let colsHtml = '';
            columns.forEach((col, index) => {
                let itemsHtml = '';
                const blocks = col.blocks || [];

                if(blocks.length > 0) {
                    blocks.forEach(item => {
                        const itemData = {
                            id: item.id,
                            uniqueId: item.uniqueId || this.generateUUID(),
                            title: item.title,
                            options: item.options || {},
                            status: item.status !== undefined ? item.status : 1
                        };

                        const sourceItem = $(`.builder-item[data-id="${item.id}"]`);
                        if(sourceItem.length) {
                            if(!itemData.title) itemData.title = sourceItem.data('title');
                            if(!itemData.icon) itemData.icon = sourceItem.data('icon');
                        }

                        if(itemData.options && itemData.options.title) itemData.title = itemData.options.title;

                        itemsHtml += this.createFixedItemHtml(itemData);
                    });
                }

                // Column settings
                const colId = col.id || this.generateUUID();
                const actualFlexGrow = col.flexGrow !== undefined ? parseInt(col.flexGrow) : 0;
                // If force fill is active, visual flexGrow is 1, but toggle state reflects actual data
                const visualFlexGrow = forceFlexFill ? 1 : actualFlexGrow;

                const align = col.align || 'start';

                let alignClass = 'justify-content-start';
                if (align === 'center') alignClass = 'justify-content-center';
                else if (align === 'end') alignClass = 'justify-content-end';

                // Column Controls (Floating)
                const alignIcon = align === 'center' ? 'bi-text-center' : (align === 'end' ? 'bi-text-right' : 'bi-text-left');

                const colControls = `
                    <div class="col-controls position-absolute start-50 translate-middle px-2 py-1 d-flex gap-2 align-items-center bg-white rounded-pill shadow-sm border" style="top:-16px; z-index: 10;">
                        <div class="form-check form-switch m-0 min-h-auto" title="${this.t('Expand column')}">
                             <input class="form-check-input col-flex-grow-toggle cursor-pointer" type="checkbox" ${actualFlexGrow ? 'checked' : ''} data-col-id="${colId}">
                        </div>
                        <div class="vr"></div>
                        <div class="dropdown">
                             <button class="btn btn-xs btn-link text-dark p-0" type="button" data-bs-toggle="dropdown" title="${this.t('Content Alignment')}">
                                <i class="bi ${alignIcon}"></i>
                             </button>
                             <ul class="dropdown-menu shadow-sm" style="min-width: 110px;">
                                <li><a class="dropdown-item col-align-item ${align === 'start' ? 'active' : ''}" href="#" data-align="start" data-col-id="${colId}"><i class="bi bi-text-left me-2"></i>${this.t('Start')}</a></li>
                                <li><a class="dropdown-item col-align-item ${align === 'center' ? 'active' : ''}" href="#" data-align="center" data-col-id="${colId}"><i class="bi bi-text-center me-2"></i>${this.t('Center')}</a></li>
                                <li><a class="dropdown-item col-align-item ${align === 'end' ? 'active' : ''}" href="#" data-align="end" data-col-id="${colId}"><i class="bi bi-text-right me-2"></i>${this.t('End')}</a></li>
                             </ul>
                        </div>
                    </div>
                `;

                const flexBasis = '0%';
                const minWidth = '140px';

                colsHtml += `
                    <div class="column-wrapper d-flex flex-column position-relative" style="flex: ${visualFlexGrow} 1 ${flexBasis}; min-width: ${minWidth};">
                        ${colControls}
                        <div class="builder-col flex-grow-1 d-flex flex-wrap align-items-center p-2 gap-2 ${alignClass} bg-light border border-dashed rounded"
                             data-col-id="${colId}"
                             data-width="${col.width}"
                             data-flex-grow="${actualFlexGrow}"
                             data-align="${align}">
                            ${itemsHtml}
                        </div>
                    </div>
                `;
            });


            const sectionSettingsBtn = this.config.enableSectionSettings ? `
                <button class="btn btn-sm btn-link text-dark p-0 edit-row-btn" title="${this.t('Section Options')}">
                    <i class="bi bi-sliders2-vertical"></i>
                </button>
            ` : '';

            // Column layout dropdown
            let colOptionsHtml = `
                    <li><a class="dropdown-item set-cols-btn ${currentLayout == '1' ? 'active' : ''}" href="#" data-cols="1"><i class="bi bi-square me-2"></i>1 ${this.t('Column')}</a></li>
                    <li><a class="dropdown-item set-cols-btn ${currentLayout == '2' ? 'active' : ''}" href="#" data-cols="2"><i class="bi bi-layout-split me-2"></i>2 ${this.t('Columns')}</a></li>
                    <li><a class="dropdown-item set-cols-btn ${currentLayout == '3' ? 'active' : ''}" href="#" data-cols="3"><i class="bi bi-layout-three-columns me-2"></i>3 ${this.t('Columns')}</a></li>
            `;

            if (this.config.type !== 'header') {
                colOptionsHtml += `<li><a class="dropdown-item set-cols-btn ${currentLayout == '4' ? 'active' : ''}" href="#" data-cols="4"><i class="bi bi-grid me-2"></i>4 ${this.t('Columns')}</a></li>`;
            }

            const colLayoutDropdown = `
                <div class="vr"></div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-link text-dark p-0 text-decoration-none" data-bs-toggle="dropdown" title="${this.t('Column Layout')}">
                        <i class="bi bi-layout-three-columns"></i>
                    </button>
                    <ul class="dropdown-menu">
                        ${colOptionsHtml}
                    </ul>
                </div>
            `;

            // Enable/disable toggle
            const enableToggle = sectionDef.canDisable ? `
                <div class="vr"></div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input section-enable-toggle" type="checkbox" id="enable_${rowId}" ${isEnabled ? 'checked' : ''} data-row-id="${rowId}">
                </div>
            ` : '';

            const disabledOverlay = !isEnabled ? `
                <div class="vr"></div>
                <div class="section-disabled-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.8); z-index: 5;">
                    <span class="badge bg-secondary fs-6">${this.t('Disabled')}</span>
                </div>
            ` : '';

            const rowHtml = `
                <div class="card builder-row mb-3 ${!isEnabled ? 'opacity-75' : ''}" data-row-id="${rowId}" data-options="${optionsStr}" data-fixed="true">
                    <div class="row-controls d-flex align-items-center gap-3">
                        ${sectionSettingsBtn}
                        ${colLayoutDropdown}
                        ${enableToggle}
                    </div>
                    <div class="card-header py-2 d-flex align-items-center">
                        <i class="${sectionDef.icon} me-2 text-primary"></i>
                        <strong class="small">${sectionDef.title}</strong>
                    </div>
                    <div class="card-body p-3 position-relative">
                        ${disabledOverlay}
                        <div class="d-flex g-3 gap-2 columns-container">
                            ${colsHtml}
                        </div>
                    </div>
                </div>
            `;

            this.canvas.append(rowHtml);
        }

        initRowSortables() {
            this.canvas.sortable({
                handle: '.sortable-row-handle',
                placeholder: "builder-item-placeholder ui-corner-all",
                forcePlaceholderSize: true,
                tolerance: 'pointer',
                axis: 'y',
                containment: 'parent',
                start: function(e, ui) {
                    ui.placeholder.height(ui.item.outerHeight());
                    ui.placeholder.addClass('mb-3');
                    ui.helper.width(ui.item.outerWidth());
                }
            });
        }

        injectStyles() {
            const css = `
                .col-controls {
                    opacity: 0;
                    pointer-events: none;
                    transition: all 0.2s ease-in-out;
                }
                .column-wrapper:hover .col-controls {
                    opacity: 1;
                    pointer-events: auto;
                }
                /* Active Drop Zone Highlight (Hover Only) */
                .builder-col.ui-droppable-hover {
                    border-color: #0d6efd !important;
                    background-color: rgba(13, 110, 253, 0.05) !important;
                }
            `;
            if (!$('#builder-extra-styles').length) {
                $('<style id="builder-extra-styles">').text(css).appendTo('head');
            }
        }

        renderRow(row) {
            const rowId = row.id || this.generateUUID();
            const cols = row.columns || [{ id: this.generateUUID(), width: 12, items: [] }];
            const rowOptions = row.options || {};
            const rowOptionsStr = encodeURIComponent(JSON.stringify(rowOptions));

            let colsHtml = '';
            cols.forEach(col => {
                let itemsHtml = '';
                const blocks = col.blocks || [];

                if(blocks.length > 0) {
                    blocks.forEach(item => {
                        const itemData = {
                            id: item.id,
                            uniqueId: item.uniqueId || this.generateUUID(),
                            title: item.title,
                            options: item.options || {},
                            status: item.status !== undefined ? item.status : 1
                        };

                        // Try to get title from sidebar elements
                        const sourceItem = $(`.builder-item[data-id="${item.id}"]`);
                        if(sourceItem.length) {
                            if(!itemData.title) itemData.title = sourceItem.data('title');
                            if(!itemData.icon) itemData.icon = sourceItem.data('icon');
                            if(item.status === undefined && sourceItem.data('status') !== undefined) itemData.status = sourceItem.data('status');
                        }

                        if(itemData.options && itemData.options.title) itemData.title = itemData.options.title;

                        itemsHtml += this.createItemHtml(itemData);
                    });
                }

                const colClass = `col-md-${col.width}`;
                colsHtml += `
                    <div class="${colClass}">
                        <div class="builder-col p-2 h-100" data-col-id="${col.id}" data-width="${col.width}">
                            ${itemsHtml}
                        </div>
                    </div>
                `;
            });

            const sectionSettingsBtn = this.config.enableSectionSettings ? `
                <button class="btn btn-sm btn-link text-dark p-0 edit-row-btn" title="${this.t('Section Options')}">
                    <i class="bi bi-sliders2-vertical"></i>
                </button>
                <div class="vr"></div>
            ` : '';

            const rowHtml = `
                <div class="card builder-row mb-3" data-row-id="${rowId}" data-options="${rowOptionsStr}">
                    <div class="row-controls d-flex align-items-center gap-3">
                        ${sectionSettingsBtn}
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-dark p-0 text-decoration-none" data-bs-toggle="dropdown" title="Column Layout">
                                <i class="bi bi-layout-three-columns"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item set-cols-btn" href="#" data-cols="1"><i class="bi bi-square me-2"></i>1 Column</a></li>
                                <li><a class="dropdown-item set-cols-btn" href="#" data-cols="2"><i class="bi bi-layout-split me-2"></i>2 Columns</a></li>
                                <li><a class="dropdown-item set-cols-btn" href="#" data-cols="3"><i class="bi bi-layout-three-columns me-2"></i>3 Columns</a></li>
                                <li><a class="dropdown-item set-cols-btn" href="#" data-cols="2-84"><i class="bi bi-layout-sidebar me-2"></i>Sidebar Right</a></li>
                                <li><a class="dropdown-item set-cols-btn" href="#" data-cols="2-48"><i class="bi bi-layout-sidebar-reverse me-2"></i>Sidebar Left</a></li>
                            </ul>
                        </div>
                        <div class="vr"></div>
                        <span class="btn btn-sm btn-link text-muted p-0 sortable-row-handle cursor-move" title="${this.t('Move Section')}"><i class="bi bi-arrows-move"></i></span>
                        <div class="vr"></div>
                        <button class="btn btn-sm btn-link text-danger p-0 delete-row-btn" title="${this.t('Delete Section')}"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            ${colsHtml}
                        </div>
                    </div>
                </div>
            `;

            this.canvas.append(rowHtml);
        }

        createItemHtml(data) {
            const id = data.id;
            const uniqueId = data.uniqueId;
            const title = data.title;
            const optionsStr = encodeURIComponent(JSON.stringify(data.options || {}));
            const status = data.status !== undefined ? data.status : 1;
            const textClass = status == 0 ? 'text-muted' : '';
            const statusBadge = status == 0 ? ` <small class="text-danger">(${this.t("Inactive")})</small>` : '';

            return `
                <div class="builder-item p-2 bg-white border rounded shadow-sm mb-2 d-flex align-items-center gap-2 cursor-grab"
                        data-id="${id}"
                        data-unique-id="${uniqueId}"
                        data-title="${title}"
                        data-options="${optionsStr}"
                        title="${data.description || ''}"
                        style="min-width: 0; width: 100%;">
                    <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 0;">
                        <div class="text-truncate small fw-medium ${textClass}" style="min-width: 0;">
                            ${title}
                            ${statusBadge}
                        </div>
                    </div>
                    <div class="dropdown flex-shrink-0">
                        <button class="btn btn-sm text-muted p-2" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item edit-block-btn" href="javascript:void(0)" data-unique-id="${uniqueId}" data-id="${id}"><i class="bi bi-pencil-square text-primary me-2"></i>${this.t('Edit Details')}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger remove-item-btn" href="#"><i class="bi bi-trash me-2"></i>${this.t('Remove')}</a></li>
                        </ul>
                    </div>
                </div>
            `;
        }

        // Compact horizontal item HTML for fixed sections (header/footer)
        createFixedItemHtml(data) {
            const id = data.id;
            const uniqueId = data.uniqueId;
            const title = data.title;
            const optionsStr = encodeURIComponent(JSON.stringify(data.options || {}));
            const status = data.status !== undefined ? data.status : 1;
            const textClass = status == 0 ? 'text-muted text-decoration-line-through' : '';

            return `
                <div class="builder-item px-2 py-1 bg-white border rounded shadow-sm d-inline-flex align-items-center gap-1"
                        data-id="${id}"
                        data-unique-id="${uniqueId}"
                        data-title="${title}"
                        data-options="${optionsStr}"
                        title="${data.description || ''}"
                        style="white-space: nowrap;">
                    <span class="cursor-grab small fw-medium ${textClass}">${title}</span>
                    <div class="dropdown">
                        <button class="btn btn-sm text-muted p-0 ms-1" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical small"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item edit-block-btn" href="javascript:void(0)" data-unique-id="${uniqueId}" data-id="${id}"><i class="bi bi-pencil-square text-primary me-2"></i>${this.t('Edit')}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger remove-item-btn" href="#"><i class="bi bi-trash me-2"></i>${this.t('Remove')}</a></li>
                        </ul>
                    </div>
                </div>
            `;
        }

        // --- Event Binding ---

        bindEvents() {
            const self = this;

            // --- Fixed Sections Operations ---

            // Section enable/disable toggle
            $(document).on('change', '.section-enable-toggle', function() {
                const $toggle = $(this);
                const rowId = $toggle.data('row-id');
                const isEnabled = $toggle.is(':checked');
                const $row = $(`.builder-row[data-row-id="${rowId}"]`);
                const $label = $toggle.siblings('label');

                // Update label
                $label.text(isEnabled ? self.t('Enabled') : self.t('Disabled'));

                // Update row appearance
                if (isEnabled) {
                    $row.removeClass('opacity-75');
                    $row.find('.section-disabled-overlay').remove();
                } else {
                    $row.addClass('opacity-75');
                    if ($row.find('.section-disabled-overlay').length === 0) {
                        $row.find('.card-body').prepend(`
                            <div class="section-disabled-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.8); z-index: 5;">
                                <span class="badge bg-secondary fs-6">${self.t('Disabled')}</span>
                            </div>
                        `);
                    }
                }

                // Update options data
                let options = {};
                try {
                    const rawOptions = $row.data('options');
                    if (typeof rawOptions === 'string' && rawOptions.startsWith('%7B')) {
                        options = JSON.parse(decodeURIComponent(rawOptions));
                    } else if (typeof rawOptions === 'object') {
                        options = rawOptions;
                    }
                } catch(e) {}
                options.enabled = isEnabled;
                $row.attr('data-options', encodeURIComponent(JSON.stringify(options)));
                $row.data('options', options);
            });

            // Column width change
            $(document).on('change', '.col-width-select', function() {
                const colId = $(this).data('col-id');
                const newWidth = $(this).val();
                const $col = $(`.builder-col[data-col-id="${colId}"]`);
                const $colWrapper = $col.parent();

                // Remove old col class and add new
                $colWrapper.removeClass(function(i, className) {
                    return (className.match(/(^|\s)col-md-\S+/g) || []).join(' ');
                });
                $colWrapper.addClass('col-md-' + newWidth);
                $col.attr('data-width', newWidth);
            });

            // Column flex-grow change
            $(document).on('change', '.col-flexgrow-select', function() {
                const colId = $(this).data('col-id');
                const flexGrow = $(this).val();
                const $col = $(`.builder-col[data-col-id="${colId}"]`);
                const $colWrapper = $col.parent();

                $colWrapper.css('flex-grow', flexGrow);
                $col.attr('data-flex-grow', flexGrow);
            });

            // Sticky Header: Toggle Flex Grow
            $(document).on('change', '.col-flex-grow-toggle', function() {
                const colId = $(this).data('col-id');
                const isChecked = $(this).is(':checked');
                const actualFlexGrow = isChecked ? 1 : 0; // Store the actual user preference

                const $col = $(`.builder-col[data-col-id="${colId}"]`);
                // Update data attribute for persistence
                $col.attr('data-flex-grow', actualFlexGrow);
                // Also update the DOM data property
                $col.data('flex-grow', actualFlexGrow);

                // Use the shared method to update visuals based on Smart Flex logic
                // Find the parent row container
                const $wrapper = $(this).closest('.column-wrapper');
                // The wrapper is inside the flex container (the row)
                const $row = $wrapper.parent();

                self.updateColumnFlexGrow($row);
            });

            // Sticky Header: Column Alignment
            $(document).on('click', '.col-align-item', function(e) {
                e.preventDefault();
                const align = $(this).data('align');
                const colId = $(this).data('col-id');

                const $col = $(`.builder-col[data-col-id="${colId}"]`);
                const $parent = $col.closest('.column-wrapper');

                // Update classes on builder-col
                $col.removeClass('justify-content-start justify-content-center justify-content-end');

                if (align === 'center') {
                    $col.addClass('justify-content-center');
                } else if (align === 'end') {
                    $col.addClass('justify-content-end');
                } else {
                    $col.addClass('justify-content-start');
                }

                // Update data attribute
                $col.attr('data-align', align);
                $col.data('align', align);

                // Update active state
                $parent.find('.col-align-item').removeClass('active');
                $(this).addClass('active');
            });

            // --- Rows Operations ---

            $(document).on('click', '#add-new-row-btn', function() {
                self.renderRow({ id: self.generateUUID(), columns: [{ id: self.generateUUID(), width: 12, items: [] }] });
                self.initSortables('.builder-col');
                self.initRowSortables();
                $('html, body').animate({ scrollTop: $(document).height() }, 1000);
            });

            $(document).on('click', '.delete-row-btn', function() {
                if(confirm(self.t("Delete this section?"))) {
                    $(this).closest('.builder-row').remove();
                }
            });

            $(document).on('click', '.set-cols-btn', function(e) {
                e.preventDefault();
                const cols = $(this).data('cols');
                const row = $(this).closest('.builder-row');
                self.updateRowLayout(row, cols);
            });

            // Remove item
            $(document).on('click', '.remove-item-btn', function(e) {
                e.preventDefault();
                if(confirm(self.t("Remove this block?"))) {
                    const $item = $(this).closest('.builder-item');
                    $item.remove();
                }
            });

            // --- Saving ---

            $('#save-builder-btn').on('click', function() {
                self.saveBuilder($(this));
            });

            // --- Row/Section Settings ---
            if (this.config.enableSectionSettings) {
                this.bindSectionSettings();
            }

            // --- Edit Block ---
            this.bindEditBlock();
        }

        bindSectionSettings() {
            const self = this;

            $(document).on('click', '.edit-row-btn', function() {
                const row = $(this).closest('.builder-row');
                const rowId = row.data('row-id');
                const rawOptions = row.data('options');
                let options = {};
                try {
                    if (typeof rawOptions === 'string' && rawOptions.startsWith('%7B')) {
                        options = JSON.parse(decodeURIComponent(rawOptions));
                    } else if (typeof rawOptions === 'object') {
                        options = rawOptions;
                    }
                } catch(e) {}

                // Get section title from row header
                const sectionTitle = row.find('.card-header strong').text().trim() || 'Section';

                self.currentSectionId = rowId;
                $('#currentSectionId').val(rowId);

                const offcanvasEl = document.getElementById('sectionSettingsOffcanvas') || document.getElementById('sectionSettingsOffcanva');
                if (!offcanvasEl) return;

                const bsOffcanvas = new bootstrap.Offcanvas(offcanvasEl);
                bsOffcanvas.show();

                const $content = $('#sectionSettingsContent').length ? $('#sectionSettingsContent') : $('#sectionSettingsConten');
                const $loader = $('#sectionSettingsLoader');
                if ($content.length) $content.addClass('d-none');
                if ($loader.length) $loader.removeClass('d-none');

                if (self.config.routes.sectionSettings) {
                    $.get(self.config.routes.sectionSettings, {
                        section: sectionTitle,
                        id: rowId
                    }, function(resp) {
                        $content.html(resp.content).removeClass('d-none');
                        if ($loader.length) $loader.addClass('d-none');
                        $('#currentSectionId').val(rowId);
                        // Update offcanvas title with response (preserve icon)
                        const offcanvasTitleEl = offcanvasEl.querySelector('.offcanvas-title');
                        if (offcanvasTitleEl && resp.title) {
                            const iconEl = offcanvasTitleEl.querySelector('i');
                            const iconHtml = iconEl ? iconEl.outerHTML : '';
                            offcanvasTitleEl.innerHTML = iconHtml + resp.title;
                        }
                        self.populateSectionSettings(options);
                        self.bindSectionSettingsUI();
                    }).fail(function() {
                        $content.html(`<div class="alert alert-danger">${self.t("Failed to load options")}</div>`).removeClass('d-none');
                        if ($loader.length) $loader.addClass('d-none');
                    });
                }
            });

            $(document).on('click', '#saveSectionSettingsBtn', function() {
                const rowId = $('#currentSectionId').val();
                if(!rowId) return;

                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${self.t("Applying...")}`);

                const rowElement = $(`.builder-row[data-row-id="${rowId}"]`);
                const formData = {};
                $('#sectionSettingsForm').serializeArray().forEach(item => {
                    formData[item.name] = item.value;
                });

                if ($('#bg_image_toggle').length && !$('#bg_image_toggle').is(':checked')) {
                    formData['bg_image'] = '';
                }

                const jsonStr = encodeURIComponent(JSON.stringify(formData));
                rowElement.attr('data-options', jsonStr);
                rowElement.data('options', formData);

                setTimeout(() => {
                    $btn.prop('disabled', false).html(originalHtml);
                    toastr.success(self.t("Section settings applied"));
                    const offcanvasEl = document.getElementById('sectionSettingsOffcanvas') || document.getElementById('sectionSettingsOffcanva');
                    const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if(offcanvas) offcanvas.hide();
                }, 500);
            });
        }

        populateSectionSettings(options) {
            const form = $('#sectionSettingsForm');

            // Initialize Coloris first so inputs get wrapped
            if (window.Coloris) {
                Coloris({
                    el: '.coloris',
                    parent: '#sectionSettingsOffcanvas .offcanvas-body',
                    rtl: document.dir === 'rtl',
                    selectInput: false
                });

                Coloris.setInstance('.coloris', {
                    theme: 'polaroid',
                    formatToggle: true,
                });
            }

            $.each(options, function(key, value) {
                if(key === 'container_width') {
                    form.find(`input[name="container_width"][value="${value}"]`).prop('checked', true);
                    $('#custom_width_wrapper').toggleClass('d-none', value !== 'custom');
                }  else if(key === 'box_shadow_toggle') {
                    const isChecked = value === 'on';
                    form.find('#box_shadow_toggle').prop('checked', isChecked).trigger('change');
                    form.find('#box_shadow_wrapper').toggleClass('d-none', !isChecked);
                } else {
                    const input = form.find('[name="'+key+'"]');
                    if(input.length) {
                        input.val(value);
                        // Trigger change for conditional logics and styling
                        input.trigger('change');

                        // Update coloris input color preview (clr-field wrapper)
                        if(input.hasClass('coloris') && value) {
                            const clrField = input.parent('.clr-field');
                            if(clrField.length) {
                                clrField.css('color', value);
                            }
                        }
                    }
                }
            });

            // --- Sync Linked Input Toggles ---
            const linkSets = [
                { id: 'margin', inputs: ['margin_top', 'margin_right', 'margin_bottom', 'margin_left'] },
                { id: 'padding', inputs: ['padding_top', 'padding_right', 'padding_bottom', 'padding_left'] },
                { id: 'border', inputs: ['border_top_width', 'border_right_width', 'border_bottom_width', 'border_left_width'] }
            ];

            linkSets.forEach(set => {
                const $inputs = set.inputs.map(name => form.find(`[name="${name}"]`));
                // Get values, treating empty strings as matching checks if all empty
                const values = $inputs.map($inp => $inp.val());

                // check if values are present (at least one)
                const hasValue = values.some(v => v !== '' && v !== null);

                if (hasValue) {
                    const firstVal = values[0];
                    const allSame = values.every(v => v === firstVal);
                    const $toggle = form.find(`#link_${set.id}_values`);

                    if($toggle.length) {
                        $toggle.prop('checked', allSame);
                        const $icon = $toggle.next('label').find('i');
                        if(allSame) {
                            $icon.removeClass('text-muted opacity-50');
                        } else {
                            $icon.addClass('text-muted opacity-50');
                        }
                    }
                }
            });
        }

        bindEditBlock() {
            const self = this;

            // Support multiple button class names for compatibility
            $(document).on('click', '.edit-block-btn, .edit-home-section-btn, .edit-item-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const uniqueId = $(this).data('unique-id');
                self.currentUniqueId = uniqueId;

                const $itemElement = $(`.builder-item[data-unique-id="${uniqueId}"]`);
                let instanceOptionsStr = $itemElement.attr('data-options');

                // Support multiple offcanvas IDs
                const offcanvasEl = document.getElementById('editBlockOffcanvas')
                    || document.getElementById('editHomeSectionOffcanvas')
                    || document.getElementById('editElementOffcanvas');
                if (!offcanvasEl) return;

                const bsOffcanvas = new bootstrap.Offcanvas(offcanvasEl);
                bsOffcanvas.show();

                const $content = $('#offcanvasContent').length ? $('#offcanvasContent') : $('#editBlockContent, #elementSettingsContent').first();
                const $loader = $('#offcanvasLoader').length ? $('#offcanvasLoader') : $('#editBlockLoader');
                if ($content.length) $content.addClass('d-none');
                if ($loader.length) $loader.removeClass('d-none');

                if (!self.config.routes.editBlock) {
                    $content.html(`<div class="alert alert-info">${self.t("Edit form not configured")}</div>`).removeClass('d-none');
                    if ($loader.length) $loader.addClass('d-none');
                    return;
                }

                let url = self.config.routes.editBlock.replace('BLOCK_ID', id);
                if (instanceOptionsStr && instanceOptionsStr !== '' && instanceOptionsStr !== '%7B%7D') {
                    url += (url.includes('?') ? '&' : '?') + 'options=' + instanceOptionsStr;
                }

                $.get(url, function(resp) {
                    $content.html(resp.content).removeClass('d-none');
                    if ($loader.length) $loader.addClass('d-none');
                    if(resp.title) {
                        const labelEl = offcanvasEl.querySelector('.offcanvas-title')
                            || document.getElementById('editHomeSectionOffcanvasLabel')
                            || document.getElementById('editElementOffcanvasLabel');
                        if (labelEl) labelEl.innerHTML = '<i class="bi bi-pencil-square me-2"></i>' + resp.title;
                    }
                    self.initEditBlockUI();
                }).fail(function() {
                    $content.html(`<div class="alert alert-danger">${self.t("Failed to load content")}</div>`).removeClass('d-none');
                    if ($loader.length) $loader.addClass('d-none');
                });
            });

            // Submit Edit Form - support multiple form IDs
            $(document).on('submit', '#editBlockForm, #editHomeBlockForm', function(e) {
                e.preventDefault();

                if (self.richTextEditorInstance) {
                    $('#richTextEditor').val(self.richTextEditorInstance.getData());
                }

                const $form = $(this);
                const $btn = $('#editBlockBtn, #editHomeBlockBtn, #saveElementSettingsBtn').filter(':visible').first();
                const originalText = $btn.html();

                $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${self.t("Saving...")}`);

                self.processFormWithUploads($form, function(finalData) {
                    const uniqueId = self.currentUniqueId;
                    if(uniqueId) {
                        const $item = $(`.builder-item[data-unique-id="${uniqueId}"]`);

                        // Handle checkbox for is_active which might be missing from FormData if unchecked
                        if ($('#is_active').length) {
                             finalData.is_active = $('#is_active').is(':checked') ? '1' : '0';
                        } else if ($('#block_status').length) {
                             finalData.is_active = $('#block_status').is(':checked') ? '1' : '0';
                             finalData.status = finalData.is_active;
                        }

                        // Ensure links is an array if present
                        if (finalData.links && typeof finalData.links === 'object') {
                            finalData.links = Object.values(finalData.links);
                        }

                        const jsonStr = encodeURIComponent(JSON.stringify(finalData));
                        $item.attr('data-options', jsonStr);
                        $item.data('options', finalData);

                        // Update status visual
                        const isActive = finalData.is_active === '1' || finalData.is_active === 1;
                        $item.attr('data-status', isActive ? 1 : 0);

                        // Find text container (supports both home builder .text-truncate and header builder .cursor-grab)
                        let $textContainer = $item.find('.text-truncate');
                        if ($textContainer.length === 0) {
                            $textContainer = $item.find('.cursor-grab');
                        }

                        const $statusBadge = $textContainer.find('.text-danger');

                        if(isActive) {
                             // Robustly remove classes from container and any children
                             $textContainer.removeClass('text-muted text-decoration-line-through');
                             $item.find('.text-decoration-line-through').removeClass('text-decoration-line-through');
                             $item.find('.text-muted').removeClass('text-muted');
                            // Re-select text container in case it changed
                            let $titleContainer = $item.find('.text-truncate');
                            if ($titleContainer.length === 0) {
                                $titleContainer = $item.find('.cursor-grab');
                            }

                            const $badge = $titleContainer.find('small');

                            // For fixed items (cursor-grab), the container IS the text node basically
                            // For home items (text-truncate), it might contain other things

                            // Simplest way is to set text content, then re-append badge if needed
                            // But .text() removes children (badge)

                            $titleContainer.contents().filter(function() {
                                return this.nodeType === 3; // Text nodes
                            }).replaceWith(finalData.title);

                            // If it was empty or weird structure, just set text
                             if ($titleContainer.text().trim() === '') {
                                 $titleContainer.text(finalData.title);
                                 if (!isActive && $titleContainer.hasClass('text-truncate')) {
                                     $titleContainer.append(` <small class="text-danger">(${self.t("Inactive")})</small>`);
                                 }
                             }
                        }
                        if(typeof finalData.description !== 'undefined') {
                            $item.attr('title', finalData.description);
                        }
                    }

                    const offcanvasEl = document.getElementById('editBlockOffcanvas')
                        || document.getElementById('editHomeSectionOffcanvas')
                        || document.getElementById('editElementOffcanvas');
                    const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if (bsOffcanvas) bsOffcanvas.hide();

                    toastr.success(self.t("Block settings saved"));
                    self.currentUniqueId = null;
                    $btn.prop('disabled', false).html(originalText);

                }, function(errMsg) {
                    toastr.error(errMsg || self.t("Failed to update"));
                    $btn.prop('disabled', false).html(originalText);
                });
            });

            // Clean up offcanvas
            ['editBlockOffcanvas', 'editHomeSectionOffcanvas', 'editElementOffcanvas'].forEach(id => {
                const el = document.getElementById(id);
                if(el){
                    el.addEventListener('hidden.bs.offcanvas', function () {
                        if (self.richTextEditorInstance) {
                            self.richTextEditorInstance.destroy()
                                .then(() => { self.richTextEditorInstance = null; })
                                .catch(error => { console.error(error); });
                        }
                    });
                }
            });

            // Reusable Linked Inputs Logic
            $(document).on('input', '.linked-input-group .linked-input', function() {
                const $group = $(this).closest('.linked-input-group');
                const $toggle = $group.find('.linked-toggle');

                if ($toggle.is(':checked')) {
                    const val = $(this).val();
                    $group.find('.linked-input').not(this).val(val);
                }
            });

            $(document).on('change', '.linked-input-group .linked-toggle', function() {
                const $group = $(this).closest('.linked-input-group');
                const $btnIcon = $(this).next('label').find('i');
                const isChecked = $(this).is(':checked');

                if (isChecked) {
                    $btnIcon.removeClass('bi-link-45deg text-muted opacity-50').addClass('bi-link text-primary');
                    // Sync values to the first input's value
                    const val = $group.find('.linked-input').first().val();
                    $group.find('.linked-input').val(val);
                } else {
                    $btnIcon.removeClass('bi-link text-primary').addClass('bi-link-45deg text-muted opacity-50');
                }
            });

            // Sticky Header: Hide offset/transition when Always sticky
            $(document).on('change', 'select[name="sticky_header_type"]', function() {
                const type = $(this).val();
                const $wrapper = $('#sticky_details_wrapper');

                if (type === 'always') {
                    $wrapper.addClass('d-none');
                } else if (type === 'none') {
                    $wrapper.addClass('d-none');
                } else {
                    $wrapper.removeClass('d-none');
                }
            });
        }

        // --- Helper Methods ---

        updateRowLayout(row, colsType) {
            const currentCols = row.find('.builder-col');
            const items = [];
            currentCols.each(function() { items.push($(this).children().detach()); });

            const isFixed = row.data('fixed') === true;
            const self = this;

            let newColsDef = [];
            if(colsType == '1') newColsDef = [12];
            else if(colsType == '2') newColsDef = [4, 8]; // 33% + 66%
            else if(colsType == '3') newColsDef = [3, 6, 3]; // 25% + 50% + 25%
            else if(colsType == '4') newColsDef = [3, 3, 3, 3]; // 4 equal columns
            else if(colsType == '2-84') newColsDef = [8, 4];
            else if(colsType == '2-48') newColsDef = [4, 8];

            // For fixed sections, add auto flex-grow for multi-column and horizontal display
            const flexClass = isFixed ? 'd-flex flex-wrap align-items-center gap-2' : '';

            let colsHtml = '';
            newColsDef.forEach((width, index) => {
                if (isFixed) {
                    const colId = this.generateUUID();
                    // Default values for new columns
                    const actualFlexGrow = 0;
                    // Since all new columns start at 0, "All Off" rule applies -> Force Fill
                    const visualFlexGrow = 1;

                    const align = 'start';
                    const alignClass = 'justify-content-start';
                    const alignIcon = 'bi-text-left';

                     const colControls = `
                        <div class="col-controls position-absolute start-50 translate-middle px-2 py-1 d-flex gap-2 align-items-center bg-white rounded-pill shadow-sm border" style="top: -16px; z-index: 10;">
                            <div class="form-check form-switch m-0 min-h-auto" title="${this.t('Expand column')}">
                                <input class="form-check-input col-flex-grow-toggle cursor-pointer" type="checkbox" ${actualFlexGrow ? 'checked' : ''} data-col-id="${colId}">
                            </div>
                            <div class="vr"></div>
                            <div class="dropdown">
                                <button class="btn btn-xs btn-link text-dark p-0" type="button" data-bs-toggle="dropdown" title="${this.t('Content Alignment')}">
                                    <i class="bi ${alignIcon}"></i>
                                </button>
                                <ul class="dropdown-menu shadow-sm" style="min-width: 110px;">
                                    <li><a class="dropdown-item col-align-item ${align === 'start' ? 'active' : ''}" href="#" data-align="start" data-col-id="${colId}"><i class="bi bi-text-left me-2"></i>${this.t('Start')}</a></li>
                                    <li><a class="dropdown-item col-align-item ${align === 'center' ? 'active' : ''}" href="#" data-align="center" data-col-id="${colId}"><i class="bi bi-text-center me-2"></i>${this.t('Center')}</a></li>
                                    <li><a class="dropdown-item col-align-item ${align === 'end' ? 'active' : ''}" href="#" data-align="end" data-col-id="${colId}"><i class="bi bi-text-right me-2"></i>${this.t('End')}</a></li>
                                </ul>
                            </div>
                        </div>
                    `;

                    const flexBasis = '0%';
                    const minWidth = '140px';

                    colsHtml += `
                        <div class="column-wrapper d-flex flex-column position-relative" style="flex: ${visualFlexGrow} 1 ${flexBasis}; min-width: ${minWidth};">
                             ${colControls}
                            <div class="builder-col p-2 flex-grow-1 d-flex flex-wrap align-items-center gap-2 ${flexClass} ${alignClass} bg-light border border-dashed rounded"
                                 data-col-id="${colId}"
                                 data-width="${width}"
                                 data-flex-grow="${actualFlexGrow}"
                                 data-align="${align}">
                            </div>
                        </div>`;
                } else {
                    colsHtml += `<div class="col-md-${width}"><div class="builder-col p-2 h-100" data-col-id="${this.generateUUID()}" data-width="${width}"></div></div>`;
                }
            });

            // Update container - use d-flex for fixed sections, row for others
            if (isFixed) {
                row.find('.card-body .columns-container, .card-body .row').replaceWith(`<div class="d-flex gap-2 columns-container">${colsHtml}</div>`);
            } else {
                row.find('.card-body .row').html(colsHtml);
            }

            const newCols = row.find('.builder-col');
            items.forEach((itemSet, index) => {
                if(newCols[index]) $(newCols[index]).append(itemSet);
                else $(newCols[newCols.length-1]).append(itemSet);
            });

            // Remove alignment dropdowns if any exist (legacy)
            row.find('.align-dropdown-group').remove();

            // Update active state in dropdown
            row.find('.set-cols-btn').removeClass('active');
            row.find(`.set-cols-btn[data-cols="${colsType}"]`).addClass('active');

            this.initSortables('.builder-col');
        }

        saveBuilder($btn) {
            const self = this;
            const originalText = $btn ? $btn.html() : '';
            if($btn) $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> ' + self.t("Saving..."));

            const newLayout = this.getLayoutData();

            $.ajax({
                url: self.config.routes.updateLayout,
                method: "POST",
                contentType: "application/json",
                headers: { 'X-CSRF-TOKEN': self.config.csrfToken },
                data: JSON.stringify({ layout: newLayout }),
                success: function(resp) {
                    toastr.success(resp.message || self.t("Layout saved successfully"));
                },
                error: function() {
                    toastr.error(self.t("Failed to update layout"));
                },
                complete: function() {
                    if($btn) $btn.prop('disabled', false).html(originalText);
                }
            });
        }

        getLayoutData() {
            const self = this;
            const newLayout = [];

            self.canvas.find('.builder-row').each(function() {
                let options = $(this).data('options');
                if (typeof options === 'string') {
                    try { options = JSON.parse(decodeURIComponent(options)); } catch(e) {}
                }

                const row = {
                    id: $(this).data('row-id'),
                    options: options,
                    columns: []
                };

                $(this).find('.builder-col').each(function() {
                    const col = {
                        id: $(this).data('col-id'),
                        width: parseInt($(this).data('width')) || 12,
                        flexGrow: parseInt($(this).data('flex-grow')) || 0,
                        align: $(this).data('align') || 'start',
                        blocks: []
                    };

                    $(this).find('.builder-item[data-unique-id]').each(function() {
                        let itemOptions = $(this).attr('data-options');
                        if(itemOptions && typeof itemOptions === 'string') {
                            try { itemOptions = JSON.parse(decodeURIComponent(itemOptions)); } catch(e) {}
                        }

                        // Extract status from options if possible
                        let status = 1;
                        if(itemOptions && typeof itemOptions === 'object' && itemOptions.is_active !== undefined) {
                             status = (itemOptions.is_active === '1' || itemOptions.is_active === 1) ? 1 : 0;
                        } else {
                            // If not in options, try data-status
                            const dataStatus = $(this).attr('data-status');
                            if(dataStatus !== undefined) status = parseInt(dataStatus);
                        }

                        col.blocks.push({
                            id: $(this).data('id'),
                            uniqueId: $(this).data('unique-id'),
                            title: $(this).data('title'),
                            status: status,
                            options: itemOptions || {}
                        });
                    });

                    row.columns.push(col);
                });

                newLayout.push(row);
            });

            return newLayout;
        }

        bindSectionSettingsUI() {
            const self = this;
            if($.fn.selectpicker) $('.selectpicker').selectpicker();

            $('input[name="container_width"]').on('change', function() {
                $('#custom_width_wrapper').toggleClass('d-none', $(this).val() !== 'custom');
            });

            $('#bg_image_toggle').on('change', function() {
                $('#bg_image_wrapper').toggleClass('d-none', !$(this).is(':checked'));
            });

            $('#row-bg-file').on('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('_token', self.config.csrfToken);

                    const $input = $(this);
                    const $display = $('#bg_image_display');

                    $display.val(self.t("Uploading..."));
                    $input.prop('disabled', true);

                    $.ajax({
                        url: self.config.routes.uploadImage,
                        method: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(resp) {
                            if(resp.success) {
                                $('#bg_image_hidden').val(resp.path);
                                $display.val(file.name);
                            } else {
                                $display.val('');
                                $('#bg_image_hidden').val('');
                                toastr.error(resp.message || self.t("Upload failed"));
                            }
                        },
                        error: function(err) {
                            $display.val('');
                            $('#bg_image_hidden').val('');
                            toastr.error(self.t("Upload failed"));
                        },
                        complete: function() {
                            $input.prop('disabled', false);
                            $input.val('');
                        }
                    });
                }
            });
        }

        initEditBlockUI() {
            const self = this;
            if($.fn.selectpicker) $('.selectpicker').selectpicker();

            // Trigger conditional toggles to set initial state
            $('[data-conditional-toggle]').trigger('change');

            // Init & Trigger Slide Toggles
            const handleSlideToggle = function($el, animate = true) {
                const target = $el.data('slide-toggle');
                const $target = $(target);
                const isChecked = $el.is(':checked');

                if (isChecked) {
                    $target.removeClass('d-none');
                    if(animate) $target.slideDown();
                    else $target.show();
                } else {
                    if(animate) $target.slideUp();
                    else $target.hide();
                }
            };

            $(document).off('change', '[data-slide-toggle]').on('change', '[data-slide-toggle]', function() {
                handleSlideToggle($(this), true);
            });
            $('[data-slide-toggle]').each(function() {
                handleSlideToggle($(this), false);
            });

            // Initialize Coloris for color pickers in edit block form
            if (window.Coloris) {
                const offcanvasBody = document.querySelector('#editBlockOffcanvas .offcanvas-body');
                Coloris({
                    el: '#editBlockForm .coloris, #editHomeBlockForm .coloris',
                    parent: offcanvasBody ? '#editBlockOffcanvas .offcanvas-body' : null,
                    rtl: document.dir === 'rtl',
                    focusInput: false,
                    selectInput: false
                });
                Coloris.setInstance('#editBlockForm .coloris, #editHomeBlockForm .coloris', {
                    theme: 'polaroid',
                    formatToggle: true,
                });

                // Update color preview for existing values
                setTimeout(function() {
                    $('#editBlockForm .coloris, #editHomeBlockForm .coloris').each(function() {
                        const value = $(this).val();
                        if (value) {
                            const clrField = $(this).parent('.clr-field');
                            if (clrField.length) {
                                clrField.css('color', value);
                            }
                        }
                    });
                }, 50);
            }

            if ($('#richTextEditor').length && window.ClassicEditor) {
                ClassicEditor.create(document.querySelector('#richTextEditor'))
                    .then(editor => { self.richTextEditorInstance = editor; })
                    .catch(error => { console.error(error); });
            }

            $(document).off('click', '.accordion-header button').on('click', '.accordion-header button', function(){
                const target = $(this).data('bs-target');
                $(target).collapse('toggle');
            });

            if($('#accordionContent').length) {
                setTimeout(function() {
                    try {
                        $('#accordionContent').sortable({
                            axis: 'y',
                            handle: '.sortable-handle',
                            placeholder: 'bg-primary-light border border-dashed rounded-3 mb-2',
                            cursor: 'grabbing',
                            opacity: 0.8,
                            forcePlaceholderSize: true,
                            start: function(e, ui) {
                                ui.placeholder.height(ui.item.outerHeight());
                                ui.placeholder.addClass('mb-2');
                            }
                        });
                    } catch(e) {}
                }, 200);

                let itemIndex = $('#accordionContent .accordion-item').length;
                $('.add-item-btn').on('click', function(){
                    const template = document.getElementById('item-template').innerHTML;
                    const newItem = template.replace(/INDEX/g, ++itemIndex);
                    $('#accordionContent').find('.empty-state').remove();
                    $('#accordionContent').append(newItem);
                    try { $('#accordionContent').sortable('refresh'); } catch(e) {}
                    if($.fn.selectpicker) $('.selectpicker').selectpicker();
                });

                $(document).on('click', '.remove-repeater-item', function(){
                    if(confirm(self.t("Remove this item?"))) {
                        $(this).closest('.accordion-item').remove();
                    }
                });
            }

            $(document).on('change', '.repeater-image-input', function(e) {
                const previewId = $(this).data('preview');
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) { $(previewId).attr('src', e.target.result); }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            $(document).on('keyup', '.item-title-input', function() {
                const val = $(this).val();
                $(this).closest('.accordion-item').find('.item-title').text(val || self.t("New Item"));
            });

             // Footer Links Repeater in Edit Block
             if ($('#links-wrapper').length) {

                $('#add-link-btn').off('click').on('click', function(){
                    const uniqueIndex = new Date().getTime();
                    const template = `
                        <div class="input-group mb-2 link-item">
                            <input type="text" name="links[${uniqueIndex}][label]" class="form-control" placeholder="${self.t('Label')}">
                            <input type="text" name="links[${uniqueIndex}][url]" class="form-control" placeholder="${self.t('URL')}">
                            <button type="button" class="btn bg-text-red remove-link-btn"><i class="bi bi-trash"></i></button>
                        </div>
                    `;
                    $('#links-wrapper').append(template);
                });

                $(document).off('click', '.remove-link-btn').on('click', '.remove-link-btn', function(){
                    $(this).closest('.link-item').remove();
                });
            }
        }

        processFormWithUploads($form, successCallback, errorCallback) {
            const self = this;
            const serializedData = {};

            const setValue = (obj, path, value) => {
                const parts = path.split('[').map(k => k.replace(']', ''));
                let current = obj;

                for(let i=0; i<parts.length - 1; i++) {
                    const key = parts[i];
                    if(key === '') continue; // Should not happen for first part

                    if(!current[key]) {
                        const nextKey = parts[i+1];
                        if(nextKey === '') {
                             current[key] = [];
                        } else {
                             current[key] = {};
                        }
                    }
                    current = current[key];
                }

                const lastKey = parts[parts.length - 1];
                if(lastKey === '') {
                    if(Array.isArray(current)) {
                        current.push(value);
                    } else {
                         // Fallback if structure mismatch
                         const index = Object.keys(current).length;
                         current[index] = value;
                    }
                } else {
                    current[lastKey] = value;
                }
            };

            const fileInputs = $form.find('input[type="file"]').toArray();
            const inputsWithFiles = fileInputs.filter(input => input.files.length > 0);
            const uploadedFiles = [];

            let chain = Promise.resolve();

            inputsWithFiles.forEach(input => {
                chain = chain.then(() => {
                    const file = input.files[0];
                    const name = $(input).attr('name');

                    return new Promise((resolve, reject) => {
                        const fd = new FormData();
                        fd.append('file', file);
                        fd.append('_token', self.config.csrfToken);

                        // Use a flag to identify builder uploads
                        fd.append('is_builder_upload', '1');

                        $.ajax({
                            url: self.config.routes.uploadImage,
                            method: 'POST',
                            data: fd,
                            contentType: false,
                            processData: false,
                            success: (res) => {
                                if(res.success) {
                                    uploadedFiles.push({ name: name, value: res.path });
                                    resolve();
                                } else {
                                    reject(res.message || "Upload failed");
                                }
                            },
                            error: (err) => {
                                console.error(err);
                                reject(self.t("Upload failed"));
                            }
                        });
                    });
                });
            });

            chain.then(() => {
                const formData = new FormData($form[0]);
                for(let [key, value] of formData.entries()) {
                    if(value instanceof File) continue;
                    setValue(serializedData, key, value);
                }
                uploadedFiles.forEach(f => {
                    setValue(serializedData, f.name, f.value);
                });

                const normalizeImages = (obj) => {
                    if (Array.isArray(obj)) {
                        obj.forEach(item => normalizeImages(item));
                    } else if (typeof obj === 'object' && obj !== null) {
                        if (obj.hasOwnProperty('old_image') && !obj.hasOwnProperty('image')) {
                            obj.image = obj.old_image;
                        }
                        delete obj.old_image;
                        Object.values(obj).forEach(val => normalizeImages(val));
                    }
                };

                if(serializedData.content && typeof serializedData.content === 'object' && !Array.isArray(serializedData.content)) {
                    serializedData.content = Object.values(serializedData.content);
                }
                // Also run on the whole object to be safe
                normalizeImages(serializedData);

                successCallback(serializedData);

            }).catch(error => {
                toastr.error(error);

                // Re-enable button
                const $btn = $('#editBlockBtn, #editHomeBlockBtn, #saveElementSettingsBtn').filter(':visible').first();
                $btn.prop('disabled', false).html($btn.data('original-text') || self.t("Save"));

                if(errorCallback) errorCallback(error);
            });
        }

    } // End Class

    // Export to window
    window.BuilderManager = BuilderManager;

})(jQuery);
