/**
 * WidgetManager - Admin Widget Management System
 *
 * Handles drag-drop widget placement, sorting, settings, and CRUD operations.
 *
 * @requires jQuery
 * @requires jQuery UI (Draggable, Sortable, Droppable)
 * @requires toastr
 */
class WidgetManager {
    /**
     * Create a new WidgetManager instance.
     * @param {Object} options - Configuration options
     */
    constructor(options = {}) {
        this.csrfToken = options.csrfToken || $('meta[name="csrf-token"]').attr('content');
        this.routes = options.routes || {};
        this.translations = Object.assign({
            loading: 'Loading...',
            saving: 'Saving...',
            confirmDelete: 'Are you sure you want to remove this widget?',
            dragHere: 'Drag widgets here',
            failedToAdd: 'Failed to add widget',
            failedToSave: 'Failed to save order',
            failedToLoad: 'Failed to load settings',
            failedToUpdate: 'Failed to save settings',
            failedToToggle: 'Failed to toggle widget',
            failedToDelete: 'Failed to delete widget'
        }, options.translations || {});

        this.selectors = Object.assign({
            availableWidgets: '#availableWidgets',
            widgetItem: '.widget-item-draggable',
            sortableAreas: '.sortable-widgets',
            widgetInstance: '.widget-instance',
            settingsModal: '#widgetSettingsCanvas',
            settingsForm: '#widgetSettingsForm',
            settingsBtn: '#widgetSettingsBtn',
            emptyZone: '.empty-zone',
            widgetTitle: '.widget-title'
        }, options.selectors || {});

        // Gallery files storage
        this.galleryFiles = [];

        this.init();
    }

    /**
     * Initialize the widget manager.
     */
    init() {
        this.initDraggable();
        this.initSortable();
        this.initWidgetActions();
        this.initQuickAdd();
        this.initSettingsModal();
    }

    /**
     * Initialize draggable widgets from the available widgets panel.
     */
    initDraggable() {
        const self = this;
        $(`${this.selectors.availableWidgets} ${this.selectors.widgetItem}`).draggable({
            helper: function() {
                const $el = $(this);
                const $clone = $el.clone();
                $clone.css('width', $el.outerWidth());
                return $clone;
            },
            appendTo: 'body',
            revert: 'invalid',
            zIndex: 9999,
            scroll: false
        });
    }

    /**
     * Initialize sortable widget areas.
     */
    initSortable() {
        const self = this;

        $(this.selectors.sortableAreas).sortable({
            connectWith: this.selectors.sortableAreas,
            placeholder: 'widget-placeholder',
            cancel: '.no-drag',
            items: this.selectors.widgetInstance,
            tolerance: 'pointer',
            helper: function(e, item) {
                item.width(item.width());
                return item;
            },
            update: function(e, ui) {
                if (!ui.sender) {
                    self.saveOrder();
                }
            }
        }).droppable({
            accept: this.selectors.widgetItem,
            hoverClass: 'drag-over',
            drop: function(e, ui) {
                const widgetId = ui.draggable.data('widget-id');
                const areaId = $(this).data('area-id');
                self.addWidget(widgetId, areaId, $(this));
            }
        });
    }

    /**
     * Initialize widget action buttons (settings, toggle, delete).
     */
    initWidgetActions() {
        const self = this;

        // Settings button
        $(document).off('click', '.btn-widget-settings').on('click', '.btn-widget-settings', function() {
            const $instance = $(this).closest(self.selectors.widgetInstance);
            const instanceId = $instance.data('instance-id');
            const widgetTitle = $instance.find(self.selectors.widgetTitle).text().trim();
            self.openSettings(instanceId, widgetTitle);
        });

        // Toggle button
        $(document).off('click', '.btn-widget-toggle').on('click', '.btn-widget-toggle', function() {
            const $instance = $(this).closest(self.selectors.widgetInstance);
            const instanceId = $instance.data('instance-id');
            self.toggleWidget(instanceId, $instance);
        });

        // Delete button
        $(document).off('click', '.btn-widget-delete').on('click', '.btn-widget-delete', function() {
            const $instance = $(this).closest(self.selectors.widgetInstance);
            const instanceId = $instance.data('instance-id');
            if (confirm(self.translations.confirmDelete)) {
                self.deleteWidget(instanceId, $instance);
            }
        });
    }

    /**
     * Initialize quick-add buttons from the available widgets dropdown.
     */
    initQuickAdd() {
        const self = this;
        $(document).off('click', '.btn-quick-add').on('click', '.btn-quick-add', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const widgetId = $btn.data('widget-id');
            const areaId = $btn.data('area-id');
            const $container = $(`.widget-area-dropzone[data-area-id="${areaId}"]`);

            if ($container.length) {
                self.addWidget(widgetId, areaId, $container);

                // Optional: scroll to the target area
                $('html, body').animate({
                    scrollTop: $container.closest('.card').offset().top - 20
                }, 500);
            }
        });
    }

    /**
     * Initialize the settings modal form submission.
     */
    initSettingsModal() {
        const self = this;

        $(this.selectors.settingsBtn).on('click', function() {
            self.saveSettings();
        });
    }

    /**
     * Add a widget to an area.
     * @param {number} widgetId - Widget type ID
     * @param {string} areaId - Target area ID
     * @param {jQuery} $container - Container element
     */
    addWidget(widgetId, areaId, $container) {
        const self = this;

        $.ajax({
            url: this.routes.store,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({
                widget_id: widgetId,
                area: areaId
            }),
            success: function(response) {
                if (response.success && response.data.html) {
                    $container.find(self.selectors.emptyZone).remove();
                    $container.append(response.data.html);
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || self.translations.failedToAdd);
            }
        });
    }

    /**
     * Save widget order after drag-drop.
     */
    saveOrder() {
        const self = this;
        const items = [];

        $(this.selectors.sortableAreas).each(function() {
            const areaId = $(this).data('area-id');
            $(this).find(self.selectors.widgetInstance).each(function(index) {
                items.push({
                    id: $(this).data('instance-id'),
                    order: index,
                    area: areaId
                });
            });
        });

        if (items.length === 0) return;

        $.ajax({
            url: this.routes.sortable,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ items: items }),
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || self.translations.failedToSave);
            }
        });
    }

    /**
     * Open the settings modal for a widget instance.
     * @param {number} instanceId - Widget instance ID
     */
    openSettings(instanceId, widgetTitle) {
        const self = this;
        const $modal = $(this.selectors.settingsModal);
        const url = this.routes.instance.replace(':id', instanceId);

        // Update modal title dynamically
        if (widgetTitle) {
            $modal.find('.offcanvas-title').html(`<i class="bi bi-gear me-2"></i>${widgetTitle}`);
        }

        // Show loading state
        $modal.find('.offcanvas-body').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">${this.translations.loading}</span>
                </div>
            </div>
        `);
        $modal.data('instance-id', instanceId);
        bootstrap.Offcanvas.getOrCreateInstance($modal[0]).show();

        // Reset gallery files when opening new settings
        self.galleryFiles = [];

        // Load settings form
        $.get(url, function(response) {
            if (response.success) {
                $modal.find('.offcanvas-body').html(response.html);
                self.initImagePicker($modal);
                self.initGalleryUploader($modal);

                // Initialize selectpicker
                if ($.fn.selectpicker) {
                    $modal.find('.selectpicker').selectpicker();
                }

                // Initialize Coloris
                if (window.Coloris) {
                    Coloris({
                        el: '.coloris',
                        parent: '#widgetSettingsCanvas .offcanvas-body',
                        rtl: document.dir === 'rtl',
                        selectInput: false
                    });

                    Coloris.setInstance('.coloris', {
                        theme: 'polaroid',
                        formatToggle: true,
                    });
                }
            }
        }).fail(function(xhr) {
            bootstrap.Offcanvas.getInstance($modal[0])?.hide();
            toastr.error(xhr.responseJSON?.message || self.translations.failedToLoad);
        });
    }

    /**
     * Save widget settings from the modal form.
     * Uses FormData to support file uploads.
     */
    saveSettings() {
        const self = this;
        const $btn = $(this.selectors.settingsBtn);
        const $form = $(this.selectors.settingsForm);
        const $modal = $(this.selectors.settingsModal);
        const instanceId = $modal.data('instance-id');
        const originalText = $btn.html();

        if (!$form.length || !instanceId) return;

        $btn.prop('disabled', true).html(`<div class="spinner-border spinner-border-sm me-2"></div> ${this.translations.saving}`);

        // Use FormData for file upload support
        const formData = new FormData($form[0]);

        // Remove empty gallery_images and add stored files
        formData.delete('gallery_images[]');
        this.galleryFiles.forEach(function(file) {
            if (file !== null) {
                formData.append('gallery_images[]', file);
            }
        });

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $btn.prop('disabled', false).html(originalText);
                if (response.success) {
                    toastr.success(response.message);
                    bootstrap.Offcanvas.getInstance($modal[0])?.hide();
                    // Update title
                    const inst = response.data?.instance;
                    if (inst) {
                        const title = inst.title || inst.widget?.title || '';
                        $(`${self.selectors.widgetInstance}[data-instance-id="${instanceId}"] ${self.selectors.widgetTitle}`).text(title);
                    }
                } else {
                    toastr.error(response.message || self.translations.failedToUpdate);
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalText);
                toastr.error(xhr.responseJSON?.message || self.translations.failedToUpdate);
            }
        });
    }

    /**
     * Toggle widget active status.
     * @param {number} instanceId - Widget instance ID
     * @param {jQuery} $element - Widget element
     */
    toggleWidget(instanceId, $element) {
        const self = this;

        $.ajax({
            url: this.routes.toggle.replace(':id', instanceId),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: function(response) {
                if (response.success) {
                    const isActive = response.data.is_active;
                    $element.find('.widget-handle').toggleClass('opacity-50', !isActive);
                    $element.find('.btn-widget-toggle i')
                        .toggleClass('bi-eye', isActive)
                        .toggleClass('bi-eye-slash', !isActive);
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || self.translations.failedToToggle);
            }
        });
    }

    /**
     * Delete a widget instance.
     * @param {number} instanceId - Widget instance ID
     * @param {jQuery} $element - Widget element
     */
    deleteWidget(instanceId, $element) {
        const self = this;

        $.ajax({
            url: this.routes.destroy.replace(':id', instanceId),
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: function(response) {
                if (response.success) {
                    $element.fadeOut(300, function() {
                        $(this).remove();
                        // Show empty zone if no widgets left
                        $(self.selectors.sortableAreas).each(function() {
                            if ($(this).find(self.selectors.widgetInstance).length === 0) {
                                $(this).html(`
                                    <div class="empty-zone text-center text-muted py-4 pe-none">
                                        <i class="bi bi-plus-circle d-block mb-1"></i>
                                        <small class="d-block">${self.translations.dragHere}</small>
                                    </div>
                                `);
                            }
                        });
                    });
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || self.translations.failedToDelete);
            }
        });
    }

    /**
     * Initialize gallery uploader for multi-image widgets.
     * @param {jQuery} $container - Container element (usually the modal)
     */
    initGalleryUploader($container) {
        const self = this;
        const $input = $container.find('.gallery-images-input');

        if (!$input.length) return;

        const maxImages = 8;
        const inputId = $input.attr('id');
        const instanceId = inputId ? inputId.replace('galleryImagesInput_', '') : '';
        const $previewsRow = $container.find('#galleryPreviewsRow_' + instanceId);
        const $removedContainer = $container.find('#removedImagesContainer_' + instanceId);

        if (!$previewsRow.length) return;

        // Get current image count
        const getCurrentImageCount = function() {
            return $previewsRow.find('.gallery-item:not(.removing)').length;
        };

        // Handle file input change
        $input.off('change').on('change', function(e) {
            const files = Array.from(e.target.files);
            const currentCount = getCurrentImageCount();
            const availableSlots = maxImages - currentCount;

            if (availableSlots <= 0) {
                alert('Maximum ' + maxImages + ' images reached. Remove some images first.');
                $input.val('');
                return;
            }

            if (files.length > availableSlots) {
                alert('Maximum ' + maxImages + ' images allowed. You can add ' + availableSlots + ' more image(s).');
            }

            // Process files (limit to available slots)
            const filesToProcess = files.slice(0, availableSlots);

            filesToProcess.forEach(function(file) {
                const fileIndex = self.galleryFiles.length;
                self.galleryFiles.push(file);

                const reader = new FileReader();
                reader.onload = function(e) {
                    const $col = $('<div>', {
                        class: 'col-4 col-md-3 gallery-item new-preview',
                        'data-file-index': fileIndex
                    });
                    $col.html(
                        '<div class="position-relative border rounded overflow-hidden" style="aspect-ratio: 1/1;">' +
                            '<img src="' + e.target.result + '" class="w-100 h-100 object-fit-cover" alt="New Image">' +
                            '<button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-new-image" title="Remove">' +
                                '<i class="bi bi-x"></i>' +
                            '</button>' +
                            '<span class="badge bg-primary position-absolute bottom-0 start-0 m-1">New</span>' +
                        '</div>'
                    );
                    $previewsRow.append($col);

                    // Handle remove for new images
                    $col.find('.remove-new-image').on('click', function() {
                        const idx = parseInt($col.attr('data-file-index'));
                        self.galleryFiles[idx] = null;
                        $col.remove();
                    });
                };
                reader.readAsDataURL(file);
            });

            // Clear input so same files can be selected again
            $input.val('');
        });

        // Handle remove existing images
        $previewsRow.find('.remove-gallery-image').off('click').on('click', function() {
            const $item = $(this).closest('.gallery-item');
            const $hiddenInput = $item.find('input[name="existing_images[]"]');

            if ($hiddenInput.length) {
                const imagePath = $hiddenInput.val();
                $removedContainer.append(
                    $('<input>', {
                        type: 'hidden',
                        name: 'removed_images[]',
                        value: imagePath
                    })
                );
            }

            $item.addClass('removing');
            setTimeout(function() { $item.remove(); }, 300);
        });
    }

    /**
     * Initialize image preview functionality within a container.
     * Shows a preview when a file is selected.
     * @param {jQuery} $container - Container element (usually the modal)
     */
    initImagePicker($container) {
        // Add change listener for file inputs to show preview
        $container.find('input[type="file"][accept="image/*"]').off('change').on('change', function() {
            const $input = $(this);
            const $wrapper = $input.closest('.input-group').parent();
            let $previewContainer = $wrapper.find('.image-preview-container');

            if (this.files && this.files[0]) {
                const file = this.files[0];
                const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
                const extension = file.name.split('.').pop().toLowerCase();

                if (validExtensions.includes(extension)) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Check if we already have a preview container
                        if (!$previewContainer.length) {
                            // Create new preview container if no existing image preview
                            const $existingPreview = $wrapper.find('.mt-2');
                            if ($existingPreview.length) {
                                // Update existing preview
                                $existingPreview.find('img').attr('src', e.target.result);
                            } else {
                                // Create new preview
                                $previewContainer = $(`
                                    <div class="mt-2 image-preview-container">
                                        <img src="${e.target.result}" class="img-thumbnail" style="max-height: 100px;">
                                        <small class="text-muted d-block mt-1">New image selected</small>
                                    </div>
                                `);
                                $wrapper.append($previewContainer);
                            }
                        } else {
                            $previewContainer.find('img').attr('src', e.target.result);
                        }
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    }
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WidgetManager;
}
