/**
 * EzyDev Utility Library
 * @version v1.0.0
 * @author EzyDev Team
 * @description High-performance shared utilities for EzyMarket
 * @dependencies jQuery, Bootstrap 5, DataTables ....
 */
(function($) {
    "use strict";

    window.EzyDev = {
        // ==========================================
        // PURE UTILITIES
        // ==========================================
        setCookie: function(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + date.toUTCString();
            document.cookie = `${name}=${value};${expires};path=/`;
        },

        getCookie: function(name) {
            const nameEQ = name + "=";
            const decodedCookie = decodeURIComponent(document.cookie);
            const cookieArray = decodedCookie.split(';');
            for (let cookie of cookieArray) {
                while (cookie.charAt(0) === ' ') {
                    cookie = cookie.substring(1);
                }
                if (cookie.indexOf(nameEQ) === 0) {
                    return cookie.substring(nameEQ.length);
                }
            }
            return "";
        },

        hexToRgb: function(hex) {
            return {
                r: parseInt(hex.slice(1, 3), 16),
                g: parseInt(hex.slice(3, 5), 16),
                b: parseInt(hex.slice(5, 7), 16)
            };
        },

        rgbToHex: function(r, g, b) {
            const toHex = (val) => val.toString(16).padStart(2, '0').toUpperCase();
            return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
        },

        applyOpacityToHex: function(hexColor, opacity) {
            const { r, g, b } = window.EzyDev.hexToRgb(hexColor);
            const backgroundRgb = { r: 255, g: 255, b: 255 };
            const newR = Math.round((1 - opacity) * backgroundRgb.r + opacity * r);
            const newG = Math.round((1 - opacity) * backgroundRgb.g + opacity * g);
            const newB = Math.round((1 - opacity) * backgroundRgb.b + opacity * b);
            return window.EzyDev.rgbToHex(newR, newG, newB);
        },

        addParameterToUrl: function(url, param, value) {
            const urlObj = new URL(url);
            const params = new URLSearchParams([
                ...Array.from(urlObj.searchParams.entries()),
                ...Object.entries({ [param]: value })
            ]);
            return new URL(`${urlObj.origin}${urlObj.pathname}?${params}`);
        },

        removeParameterFromUrl: function(url, param, paramValue, multiple = false) {
            const urlObj = new URL(url);
            const params = new URLSearchParams(urlObj.search);

            if (multiple) {
                const multipleParams = params.getAll(param).filter(p => p !== paramValue);
                params.delete(param);
                multipleParams.forEach(value => params.append(param, value));
            } else {
                params.delete(param);
            }

            return new URL(`${urlObj.origin}${urlObj.pathname}?${params}`);
        },

        generateSecurePassword: function(length) {
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            let password = "";
            for (let i = 0; i < length; i++) {
                const randomIndex = Math.floor(Math.random() * charset.length);
                password += charset.charAt(randomIndex);
            }
            return password;
        },

        initializePasswordGenerators: function (container = document) {
            const $container = $(container);

            // 1. Automatic generation for inputs with data-auto-generate="true"
            $container.find('.generate-password-input[data-auto-generate="true"]').each(function() {
                const $input = $(this);
                if (!$input.val()) {
                    $input.val(window.EzyDev.generateSecurePassword(typeof PASSWORD_LENGTH !== 'undefined' ? PASSWORD_LENGTH : 16));
                }
            });

            // 2. Global event delegation for the generate button
            if (container === document) {
                $(document).off('click', '.generate-password-btn').on('click', '.generate-password-btn', function(e) {
                    e.preventDefault();
                    const $btn = $(this);
                    const $input = $btn.closest('.input-group').find('.generate-password-input');
                    if ($input.length) {
                        $input.val(window.EzyDev.generateSecurePassword(typeof PASSWORD_LENGTH !== 'undefined' ? PASSWORD_LENGTH : 16));
                    }
                });
            }
        },

        previewImageFile: function(inputElement, imageElement) {
            if (inputElement.files && inputElement.files[0]) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    imageElement.setAttribute("src", event.target.result);
                };
                reader.readAsDataURL(inputElement.files[0]);
            }
        },

        isValidImageFile: function(filename) {
            const ALLOWED_IMAGE_EXTENSIONS = ["jpg", "jpeg", "png", "gif", "bmp", "svg", "webp"];
            const extension = filename.split(".").pop().toLowerCase();
            return ALLOWED_IMAGE_EXTENSIONS.includes(extension);
        },

        // ==========================================
        // INITIALIZATION & DOM LOGIC
        // ==========================================

        /**
         * Ensure action confirmation modal exists in DOM
         */
        ensureActionConfirmModal: function() {
            if ($('#actionConfirmModal').length) return;

            const modalHtml = `
                <div class="modal fade" id="actionConfirmModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg text-center overflow-hidden" style="border-radius: 1.5rem;">
                            <div class="modal-body p-5">
                                <div class="mb-4">
                                    <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle" style="width: 80px; height: 80px;">
                                        <i class="bi bi-exclamation-triangle fs-1"></i>
                                    </div>
                                </div>
                                <h3 class="h4 mb-3 fw-bold">${window.config?.translates?.actionConfirmTitle || 'Confirm Action'}</h3>
                                <p id="actionConfirmMessage" class="text-muted mb-0">${window.config?.translates?.actionConfirm || 'Are you sure you want to perform this action?'}</p>
                            </div>
                            <div class="modal-footer border-0 p-4 bg-light-subtle d-flex gap-2">
                                <button type="button" class="btn border bg-light rounded-pill flex-fill px-4 fw-medium" data-bs-dismiss="modal">
                                    ${window.config?.translates?.cancel || 'Cancel'}
                                </button>
                                <button type="button" class="btn btn-danger btn-confirm rounded-pill flex-fill px-4 fw-medium">
                                    ${window.config?.translates?.confirm || 'Yes, Confirm'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
        },

        /**
         * Initialize action confirmation handler
         */
        initializeActionConfirm: function() {
            let confirmModalInstance = null;
            let $confirmModal = null;
            let $confirmBtn = null;
            let $confirmMessage = null;
            let pendingAction = null;

            $(document).on('click', '.action-confirm', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const $trigger = $(this);

                // Lazy initialize modal
                window.EzyDev.ensureActionConfirmModal();
                if (!$confirmModal) {
                    $confirmModal = $('#actionConfirmModal');
                    $confirmBtn = $confirmModal.find('.btn-confirm');
                    $confirmMessage = $confirmModal.find('#actionConfirmMessage');
                    confirmModalInstance = new bootstrap.Modal($confirmModal[0]);
                }

                // Pre-validation for forms
                // Locate target form
                let $form = $trigger.closest('form');
                if (!$form.length && $trigger.attr('form')) {
                    $form = $('#' + $trigger.attr('form'));
                }

                if ($form.length > 0) {
                    const form = $form[0];
                    if (form.checkValidity && !form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    // Strict check for empty required fields (fallback for some browsers)
                    let hasEmptyRequired = false;
                    $form.find('[required]').each(function () {
                        const $field = $(this);
                        const val = $field.val();
                        if (!val || (typeof val === 'string' && val.trim() === '')) {
                            hasEmptyRequired = true;
                            return false;
                        }
                    });

                    if (hasEmptyRequired) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(window.config?.translates?.fillAllRequiredFields || 'Please fill all required fields');
                        }
                        return;
                    }
                }

                // Set content
                const customMessage = $trigger.data('confirm') || $trigger.data('text');
                $confirmMessage.text(customMessage || window.config?.translates?.actionConfirm || 'Are you sure?');

                // Store context
                pendingAction = {
                    trigger: $trigger,
                    form: $form,
                    isLink: $trigger.is('a'),
                    url: $trigger.data('action') || $trigger.attr('href'),
                    method: $trigger.data('method') || 'POST',
                    isAjaxForm: $form.data('ajax-confirm') === true || $form.hasClass('ajax-form')
                };

                // Show modal
                confirmModalInstance.show();

                // Bind confirm button (one-time handle)
                $confirmBtn.off('click').on('click', function() {
                    if (!pendingAction) return;

                    const { trigger, form, url, method, isAjaxForm } = pendingAction;
                    confirmModalInstance.hide();

                    // Cleanup BS modal artifacts only for terminal actions (Traditional submit/redirect)
                    // We avoid this for AJAX to keep the underlying form modal and its backdrop intact if errors occur
                    if (!trigger.data('action') && !isAjaxForm) {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('overflow', '');
                    }

                    if (trigger.data('action') || (isAjaxForm && form.length)) {
                        // Advanced AJAX Action (either via attribute OR via ajax-confirm form)
                        window.EzyDev.ajaxRequest({
                            url: url || form.attr('action'),
                            method: trigger.data('method') || form.attr('method') || method,
                            data: form.length ? new FormData(form[0]) : null,
                            trigger: trigger,
                            form: form.length ? form : null
                        });
                    } else if (form.length) {
                        // Traditional Form Submit
                        form.submit();
                    } else if (url) {
                        // Standard Redirect
                        window.location.href = url;
                    }

                    pendingAction = null;
                });
            });
        },

        /**
         * Core AJAX Request Handler with UI Feedback
         */
        ajaxRequest: function(options) {
            const defaults = {
                url: '',
                method: 'POST',
                data: null,
                trigger: null,
                form: null,
                contentType: false,
                processData: false
            };

            const settings = $.extend({}, defaults, options);
            const $trigger = settings.trigger;
            const $form = settings.form;

            // Identify submit button
            let $btn = $trigger;
            if ($form && !$btn) {
                const formId = $form.attr('id');
                $btn = $form.find('button[type="submit"]');
                if (!$btn.length && formId) {
                    $btn = $(`button[type="submit"][form="${formId}"]`);
                }
            }

            const originalBtnHtml = ($btn && $btn.length) ? $btn.html() : '';
            const showSpinnerText = ($btn && $btn.length) ? ($btn.data('spinner-text') !== false) : true;

            // Loading State
            if ($btn && $btn.length) {
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>' + (showSpinnerText ? (window.config?.translates?.processing || 'Processing...') : ''));
            }

            // Reset previous errors in form
            if ($form) {
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').remove();
            }

            $.ajax({
                url: settings.url,
                method: settings.method,
                data: settings.data,
                processData: settings.processData,
                contentType: settings.contentType,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    const isSuccess = res.status === 'success' || res.success === true;
                    const isError = res.status === 'error' || res.error === true || res.success === false;

                    if (isSuccess) {
                        if ($form) $form.trigger('ajax-form:success', [res]);

                        if (typeof toastr !== 'undefined') {
                            toastr.success(res.message);
                        }

                        // Determine redirect
                        const redirectUrl = res.redirect || res.redirect_url || res.data?.redirect;
                        if (redirectUrl) {
                            setTimeout(() => window.location.href = redirectUrl, 1000);
                        } else {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(res.message || 'Something went wrong');
                        }
                        if ($btn) $btn.prop('disabled', false).html(originalBtnHtml);
                    }
                },
                error: function (xhr) {
                    if ($btn) $btn.prop('disabled', false).html(originalBtnHtml);
                    if ($form) $form.trigger('ajax-form:error', [xhr]);

                    const error = xhr.responseJSON;
                    const message = error?.message || 'An unexpected error occurred';

                    if (xhr.status === 422 && $form) {
                        const errors = error.errors;
                        let mapped = false;

                        if (errors) {
                            Object.keys(errors).forEach(key => {
                                const $input = $form.find(`[name="${key}"], [name="${key}[]"]`);
                                if ($input.length) {
                                    $input.addClass('is-invalid');
                                    $input.after(`<div class="invalid-feedback">${errors[key][0]}</div>`);
                                    mapped = true;
                                }
                            });
                        }

                        // Always show the main error message in toastr if mapping didn't happen or as a primary alert
                        if (typeof toastr !== 'undefined') {
                            toastr.error(message);
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(message);
                        }
                    }
                }
            });
        },

        /**
         * Initialize AJAX form handler globally
         */
        initializeAjaxForms: function() {
            $(document).on('submit', '.ajax-form', function (e) {
                e.preventDefault();
                const $form = $(this);

                window.EzyDev.ajaxRequest({
                    url: $form.attr('action'),
                    method: $form.attr('method') || 'POST',
                    data: new FormData(this),
                    form: $form
                });
            });
        },

        /**
         * Automatically open modal based on URL parameters
         * Usage: <div class="modal" id="modal-123" data-auto-open="true">
         * URL: ?param=123
         */
        initAutoOpenModals: function() {
            const urlParams = new URLSearchParams(window.location.search);
            const $modals = $('[data-auto-open="true"]');

            if ($modals.length === 0 || urlParams.size === 0) {
                return;
            }

            $modals.each(function () {
                const $modal = $(this);
                const modalId = $modal.attr('id');
                if (!modalId) return;

                urlParams.forEach((value) => {
                    if (value && modalId.endsWith(`-${value}`)) {
                        const modal = new bootstrap.Modal($modal[0]);
                        modal.show();
                    }
                });
            });
        },

        /**
         * Initialize generic AJAX modal loader globally
         */
        initializeAjaxModals: function() {
            $(document).on('show.bs.modal', '.modal', function (e) {
                const $modal = $(this);
                const $trigger = $(e.relatedTarget);
                const action = $trigger.data('action') || $modal.data('action');
                const $content = $modal.find('.modal-content');

                if (!action || !$content.length) return;

                // Loading State
                $content.html(`<div class="p-5 text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted xsmall">${window.config?.translates?.loading || 'Loading...'}</p></div>`);

                $.ajax({
                    url: action,
                    method: 'GET',
                    success: function(html) {
                        $content.html(html);
                        window.EzyDev.initializePlugins($content);
                    },
                    error: function() {
                        $content.html(`<div class="p-5 text-center text-danger"><i class="bi bi-exclamation-triangle fs-2"></i><p class="mt-2">${window.config?.translates?.loadingError || 'Failed to load content.'}</p></div>`);
                    }
                });
            });
        },

        /**
         * Initialize DataTables globally
         */
        initializeDataTables: function() {
            const $dataTables = $(".datatable");
            if ($dataTables.length === 0) return;

            // Global bulk selection events (prevent duplicate bindings)
            $(document).off('change', '.bulk-select-checkbox').on('change', '.bulk-select-checkbox', function () {
                const isChecked = $(this).prop('checked');
                const $table = $(this).closest('table');
                $table.find('.row-checkbox').prop('checked', isChecked).trigger('change');
            });

            $(document).off('change', '.row-checkbox').on('change', '.row-checkbox', function () {
                const $table = $(this).closest('table');
                const $row = $(this).closest('tr');
                const totalCheckboxes = $table.find('.row-checkbox').length;
                const checkedCheckboxes = $table.find('.row-checkbox:checked').length;

                $row.toggleClass('row-selected', $(this).is(':checked'));
                $table.find('.bulk-select-checkbox').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);

                const $wrapper = $table.closest('.dataTables_wrapper');
                const $bulkActionBtn = $wrapper.find('.bulk-action-btn');

                if (checkedCheckboxes > 0) {
                    $bulkActionBtn.removeClass('d-none');
                    $wrapper.find('.bulk-selected-count').text(checkedCheckboxes);

                    const $dropdownToggle = $bulkActionBtn.find('.dt-button-collection');
                    if ($dropdownToggle.length) {
                        const currentText = $dropdownToggle.html();
                        $dropdownToggle.html(currentText.replace(/\d+<\/span>/, checkedCheckboxes + '</span>'));
                    }
                } else {
                    $bulkActionBtn.addClass('d-none');
                    $wrapper.find('.bulk-selected-count').text('0');
                }
            });

            const config = window.config || {};
            const translates = config.translates || {};

            $dataTables.each(function () {
                const $table = $(this);

                // Prevent double initialization
                if ($.fn.DataTable.isDataTable($table)) {
                    return;
                }

                // Disable DataTables default alert error mode
                $.fn.dataTable.ext.errMode = 'none';
                $table.on('error.dt', function (e, settings, techNote, message) {
                    console.warn('DataTables Error:', message);
                });

                const hasAjaxFilter = $table.data('ajax-filter') === true;
                const isServerSide = $table.data('server-side') === true;
                const ajaxUrl = $table.data('ajax-url');
                const showExport = $table.data('export') === true;
                const customButtons = $table.data('custom-buttons') || [];
                const bulkActions = $table.data('bulk-actions') || [];
                const searchPlaceholder = $table.data('search-placeholder') || translates.searchPlaceholder || "Search...";

                const exportFormatter = {
                    body: function (data, row, column, node) {
                        if (typeof data !== 'string') return data;

                        // 1. Remove elements explicitly marked with .export-ignore or .hide-on-export
                        let $temp = $('<div>').html(data);
                        $temp.find('.export-ignore, .hide-on-export').remove();
                        let cleanHtml = $temp.html();

                        // 2. Convert our unique separator ¶ and standard breaks to newlines
                        cleanHtml = cleanHtml.replace(/¶/g, '\n')
                                             .replace(/<\/div>/gi, '\n')
                                             .replace(/<br\s*\/?>/gi, '\n');

                        // 3. Strip all remaining HTML tags
                        let text = cleanHtml.replace(/<[^>]*>?/gm, '').trim();

                        // 4. Return clean multiline string
                        return text.split('\n')
                                   .map(line => line.trim())
                                   .filter(line => line.length > 0)
                                   .join('\n');
                    }
                };

                const exportOptions = {
                    columns: ':visible:not(.no-export)',
                    format: exportFormatter
                };

                const printCustomize = function (win) {
                    // Ensure newlines are rendered as line breaks in the print window
                    $(win.document.body).find('table td').each(function() {
                        const content = $(this).text();
                        if (content.includes('\n')) {
                            $(this).html(content.replace(/\n/g, '<br>'));
                        }
                    });
                };

                const buttonsConfig = [];

                if (showExport) {
                    buttonsConfig.push({
                        extend: 'collection',
                        text: '<i class="bi bi-upload me-1"></i> Export',
                        className: 'btn btn-soft btn-export',
                        buttons: [
                            { extend: 'print', text: '<i class="bi bi-printer"></i> Print', className: 'dropdown-item', exportOptions: exportOptions, customize: printCustomize },
                            { extend: 'pdfHtml5', text: '<i class="bi bi-file-pdf"></i> PDF', className: 'dropdown-item', exportOptions: exportOptions },
                            { extend: 'excelHtml5', text: '<i class="bi bi-file-excel"></i> Excel', className: 'dropdown-item', exportOptions: exportOptions },
                            { extend: 'csvHtml5', text: '<i class="bi bi-filetype-csv"></i> CSV', className: 'dropdown-item', exportOptions: exportOptions },
                            { extend: 'copy', text: '<i class="bi bi-clipboard"></i> Copy', className: 'dropdown-item', exportOptions: exportOptions }
                        ]
                    });
                }

                if (bulkActions && Array.isArray(bulkActions) && bulkActions.length > 0) {
                    const dropdownItems = bulkActions
                        .filter((act) => act && typeof act === "object")
                        .map((act) => {
                            if (act.class === "dropdown-divider" || act.type === "divider") {
                                return {
                                    text: "",
                                    className: "dropdown-divider",
                                    action: function () {},
                                    tag: "div",
                                    init: function (dt, node, config) {
                                        $(node).removeClass("dropdown-item dt-button btn btn-secondary btn-primary");
                                    },
                                };
                            }

                            const iconHtml = act.icon ? `<i class="bi ${act.icon} ${act.iconClass || ""} me-2"></i>` : "";
                            const item = {
                                text: iconHtml + act.text,
                                className: act.class || "dropdown-item",
                            };

                            if (typeof act.action === "function") {
                                item.action = act.action;
                            } else {
                                // Support for configuration-based actions (useful for JSON props)
                                item.action = function (e, dt, node, config) {
                                    const execute = (extraData = {}) => {
                                        window.EzyDev.bulkAction({
                                            url: act.url || act.link || act.action?.url,
                                            method: act.method || act.action?.method || "POST",
                                            confirmMessage: act.confirm || act.confirmMessage || act.action?.confirm || translates.actionConfirm,
                                            data: extraData,
                                        });
                                    };

                                    if (act.prompt) {
                                        const userInput = prompt(act.prompt);
                                        if (userInput === null) return; // User cancelled
                                        const field = act.promptField || "reason";
                                        execute({ [field]: userInput });
                                    } else {
                                        execute();
                                    }
                                };
                            }
                            return item;
                        });

                    buttonsConfig.push({
                        extend: 'collection',
                        text: `${translates.bulkActions || 'Bulk Actions'} <span class="bulk-selected-count">0</span>`,
                        className: 'btn bg-primary-subtle text-primary bulk-action-btn d-none',
                        buttons: dropdownItems,
                        fade: false,
                        autoClose: true,
                        background: false
                    });
                }

                const bulkDeleteBtnConfig = $table.data("bulk-delete-btn");
                if (bulkDeleteBtnConfig) {
                    buttonsConfig.push({
                        text: (bulkDeleteBtnConfig.icon ? `<i class="bi ${bulkDeleteBtnConfig.icon || "bi-trash"} me-1"></i>` : "") + bulkDeleteBtnConfig.text + ' <span class="bulk-selected-count">0</span>',
                        className: (bulkDeleteBtnConfig.class || "btn btn-danger") + " bulk-action-btn d-none",
                        action: function (e, dt, node, config) {
                            window.EzyDev.bulkAction({
                                url: bulkDeleteBtnConfig.url || bulkDeleteBtnConfig.link,
                                method: bulkDeleteBtnConfig.method || "DELETE",
                                confirmMessage: bulkDeleteBtnConfig.confirm || "Are you sure you want to delete the selected items?",
                            });
                        },
                    });
                }

                const allCustomBtns = customButtons.filter((btn, index, self) =>
                    btn && typeof btn === "object" &&
                    self.findIndex(b => b.text === btn.text && b.target === btn.target && b.link === btn.link) === index
                );
                allCustomBtns.forEach((btnConfig) => {
                    const btnOptions = {
                        text: (btnConfig.icon ? `<i class="${btnConfig.icon} me-1"></i>` : "") + btnConfig.text,
                        className: btnConfig.class || "btn btn-primary",
                    };

                    if (btnConfig.type === "modal") {
                        btnOptions.attr = {
                            "data-bs-toggle": "modal",
                            "data-bs-target": btnConfig.target || "",
                        };

                        // Support dynamic AJAX modals
                        const actionUrl = btnConfig.action || btnConfig['data-action'];
                        if (actionUrl && typeof actionUrl === 'string') {
                            btnOptions.attr['data-action'] = actionUrl;
                        }

                        btnOptions.action = function () {}; // Action handled by Bootstrap data-attributes
                    } else if (btnConfig.type === "button") {
                        btnOptions.action = function (e, dt, node, config) {
                            $table.trigger("custom-btn-clicked", [dt, node, config]);
                        };
                    } else {
                        btnOptions.action = function () {
                            if (btnConfig.link) window.location.href = btnConfig.link;
                        };
                    }

                    // Add custom ID or extra attributes if provided
                    if (btnConfig.id) btnOptions.id = btnConfig.id;
                    if (btnConfig.attr) btnOptions.attr = { ...(btnOptions.attr || {}), ...btnConfig.attr };

                    buttonsConfig.push(btnOptions);
                });

                // Check for URL-based filters to show a reset button
                const urlParams = new URLSearchParams(window.location.search);
                const hasUrlFilters = Array.from(urlParams.keys()).some(key =>
                    ['id', 'sale', 'product', 'seller', 'user', 'buyer', 'purchase', 'trx'].includes(key)
                );

                if (hasUrlFilters) {
                    buttonsConfig.unshift({
                        text: '<i class="bi bi-arrow-counterclockwise me-1"></i> Reset',
                        className: 'btn btn-outline-danger btn-padding border-danger-subtle',
                        action: function () {
                            window.location.href = window.location.pathname;
                        }
                    });
                }

                const domLayout = hasAjaxFilter
                    ? '<"top-section d-flex flex-column flex-lg-row align-items-center gap-3 px-4 pt-2 pb-3"<"flex-grow-1"f><"ajax-filter-container"><"flex-sm-wrap flex-lg-shrink-0"B>>rt<"bottom-section d-flex flex-column flex-md-row align-items-center justify-content-between px-4 py-2"<"d-flex align-items-center gap-3"li>p>'
                    : '<"top-section d-flex flex-column flex-lg-row align-items-center gap-3 px-4 pt-2 pb-3"<"flex-grow-1"f><"flex-sm-wrap flex-lg-shrink-0"B>>rt<"bottom-section d-flex flex-column flex-md-row align-items-center justify-content-between px-4 py-2"<"d-flex align-items-center gap-3"li>p>';

                let dataColumns = $table.data('columns') || null;
                if (dataColumns) {
                    dataColumns = dataColumns.map(col => {
                        let classes = col.class || col.className || '';
                        if (col.exportable === false) classes += ' no-export';
                        if (col.centered === true) classes += ' text-center';
                        if (col.orderable === false) classes += ' no-sort';
                        col.className = classes.trim();
                        return col;
                    });
                }

                const tableConfig = {
                    language: {
                        emptyTable: translates.emptyTable || "No data available in table",
                        searchPlaceholder: searchPlaceholder,
                        search: "",
                        zeroRecords: translates.zeroRecords || "No matching records found",
                        sLengthMenu: translates.sLengthMenu || "_MENU_",
                        info: translates.info || "Showing _START_ to _END_ of _TOTAL_ totals",
                        infoEmpty: translates.infoEmpty || "Showing 0 to 0 of 0 totals",
                        infoFiltered: translates.infoFiltered || "(filtered from _MAX_ totals)",
                        paginate: translates.paginate || { previous: "Previous", next: "Next" },
                    },
                    dom: domLayout,
                    paging: $table.data('paging') !== false,
                    pageLength: $table.data('page-length') || 10,
                    searchDelay: 400,
                    serverSide: isServerSide,
                    processing: false,
                    ajax: ajaxUrl ? {
                        url: ajaxUrl,
                        data: function (d) {
                            if (hasAjaxFilter) {
                                const filterData = {};
                                const $wrapper = $table.closest('.dataTables_wrapper');
                                $wrapper.find('.ajax-filter-input').each(function () {
                                    const $input = $(this);
                                    const column = $input.data('column');
                                    const filterType = $input.data('filter-type');
                                    const value = $input.val();
                                    if (value) {
                                        if (filterType === 'date-from') {
                                            filterData[column] = filterData[column] || {};
                                            filterData[column].from = value;
                                        } else if (filterType === 'date-to') {
                                            filterData[column] = filterData[column] || {};
                                            filterData[column].to = value;
                                        } else {
                                            filterData[column] = value;
                                        }
                                    }
                                });
                                d.filters = filterData;
                            }
                            return d;
                        }
                    } : null,
                    columns: dataColumns,
                    order: $table.data('order') || [],
                    buttons: {
                        buttons: buttonsConfig,
                        dom: {
                            button: { className: 'btn', tag: 'button', attr: { 'data-bs-auto-close': 'outside' } }
                        }
                    },
                    lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
                    columnDefs: [
                        { targets: 'no-sort', orderable: false, className: 'no-sort' }
                    ],
                    initComplete: function () {
                        const api = this.api();
                        $table.find('thead th:not(.no-sort):not(.sorting_disabled)').addClass('sortable-column');

                        // Inject Title & Description beside Length Menu
                        const title = $table.data('title');
                        const description = $table.data('description');
                        if (title || description) {
                            const $wrapper = $table.closest('.dataTables_wrapper');
                            const $topSection = $wrapper.find('.top-section');
                            const $headerInfo = $('<div class="dt-header-info me-auto"></div>');
                            if (title) $headerInfo.append(`<h4 class="mb-0 fw-bold">${title}</h4>`);
                            if (description) $headerInfo.append(`<p class="text-muted fs-13 mb-0">${description}</p>`);

                            const $lengthMenu = $topSection.find('.dataTables_length');
                            if ($lengthMenu.length) {
                                $lengthMenu.after($headerInfo);
                            } else {
                                $topSection.prepend($headerInfo);
                            }
                        }

                        if (hasAjaxFilter) {
                            window.EzyDev._initDataTableAjaxFilters($table, api);
                        }

                        const sortingRoute = $table.data('sortable');
                        if (sortingRoute) {
                            window.EzyDev._initDataTableSortable($table, sortingRoute);
                        }
                    },
                    drawCallback: function () {
                        const api = this.api();
                        const $container = $(api.table().container());
                        const paginationElement = $container[0].querySelector(".pagination");
                        if (paginationElement) paginationElement.classList.add("pagination-sm");

                        const $filterContainer = $container.find(".dataTables_filter");
                        const $filterInput = $filterContainer.find("input");
                        $filterInput.attr("type", "text");

                        // Handle Clear Search Icon
                        let $clearBtn = $filterContainer.find(".dt-search-clear");
                        if ($clearBtn.length === 0) {
                            $clearBtn = $('<i class="bi bi-x dt-search-clear"></i>');
                            $filterInput.after($clearBtn);
                        }

                        // Show/Hide based on value
                        if ($filterInput.val().length > 0) {
                            $clearBtn.show();
                        } else {
                            $clearBtn.hide();
                        }

                        // Clear action
                        $clearBtn.off("click").on("click", function() {
                            $filterInput.val("").trigger("input");
                            api.search("").draw();
                        });

                        // Toggle on typing
                        $filterInput.off("keyup.dt-clear").on("keyup.dt-clear", function() {
                            if ($(this).val().length > 0) {
                                $clearBtn.show();
                            } else {
                                $clearBtn.hide();
                            }
                        });
                    }
                };

                if (isServerSide) {
                    $table.on('preXhr.dt', function () {
                        const colCount = $table.find('thead th').length || 8;
                        const rowCount = $table.DataTable().page.len() || 10;
                        let skeletonHtml = '';
                        for (let i = 0; i < rowCount; i++) {
                            skeletonHtml += '<tr class="skeleton-table-row">';
                            for (let j = 0; j < colCount; j++) {
                                skeletonHtml += '<td><div class="skeleton skeleton-text"></div></td>';
                            }
                            skeletonHtml += '</tr>';
                        }
                        $table.find('tbody').html(skeletonHtml);
                    });
                }

                // Add Hover functionality for sorting icons
                $table.on('mouseenter', 'thead th.sortable-column', function() {
                    $(this).addClass('sorting-hover');
                }).on('mouseleave', 'thead th.sortable-column', function() {
                    $(this).removeClass('sorting-hover');
                });

                $table.DataTable(tableConfig);
            });
        },

        /**
         * Initialize Drag-and-Drop sorting for DataTables
         * @private
         */
        _initDataTableSortable: function($table, sortingRoute) {
            if (typeof $.fn.sortable === 'undefined') return;

            $table.find('tbody').sortable({
                handle: '.sortable-table-handle',
                axis: "y",
                cursor: "move",
                placeholder: "sortable-table-placeholder",
                helper: function(e, tr) {
                    const $originals = tr.children();
                    const $helper = tr.clone();
                    $helper.children().each(function(index) {
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                start: function(e, ui) {
                    ui.placeholder.height(ui.helper.outerHeight());
                },
                update: function() {
                    const ids = [];
                    $table.find('tbody tr').each(function() {
                        const id = $(this).attr('data-id'); // Extract from DT_RowAttr
                        if (id) ids.push(id);
                    });

                    if (ids.length === 0) return;

                    window.updateSortedItems(ids.join(','), sortingRoute);
                }
            });
        },

        /**
         * Initialize AJAX filters for DataTables
         * @private
         */
        _initDataTableAjaxFilters: function($table, api) {
            const filterConfig = $table.data('filter-config');
            if (!filterConfig || !filterConfig.filters) return;

            const $filterContainer = $table.closest('.dataTables_wrapper').find('.ajax-filter-container');
            if (!$filterContainer.length) return;

            const autoApply = filterConfig.autoApply === true;
            let filterHtml = `
              <div class="d-flex align-items-center gap-2">
                <div class="dropdown filter-dropdown">
                  <button type="button" class="btn btn-soft btn-filter dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" data-bs-popper-config='{"strategy": "fixed"}'>
                    <i class="bi bi-filter me-1"></i>Filters
                  </button>
                  <div class="dropdown-menu dropdown-menu-end border-0 shadow overflow-y-auto right-0 p-3" style="min-width: 320px; max-height: 400px;">
                    <h6 class="text-muted text-uppercase border-bottom pb-2 mb-3"><i class="bi bi-filter me-1"></i>Filter Table</h6>`;

            const filters = Array.isArray(filterConfig.filters)
                ? filterConfig.filters
                : (typeof filterConfig.filters === 'object' ? Object.values(filterConfig.filters) : []);

            filters.forEach(filter => {
                filterHtml += `<div class="mb-3">
                    <label class="form-label fw-medium small mb-1">${filter.label}</label>`;

                if (filter.type === 'select') {
                    filterHtml += `<select class="form-select form-select-sm ajax-filter-input" data-column="${filter.column}" data-filter-type="select">
                        <option value="">All</option>`;
                    if (Array.isArray(filter.options)) {
                        filter.options.forEach(opt => {
                            const selected = filter.value == opt.value ? 'selected' : '';
                            filterHtml += `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
                        });
                    }
                    filterHtml += `</select>`;
                } else if (filter.type === 'text') {
                    filterHtml += `<input type="text" class="form-control form-control-sm ajax-filter-input" data-column="${filter.column}" data-filter-type="text" placeholder="${filter.placeholder || ''}" value="${filter.value || ''}">`;
                } else if (filter.type === 'date') {
                    filterHtml += `<input type="date" class="form-control form-control-sm ajax-filter-input" data-column="${filter.column}" data-filter-type="date" value="${filter.value || ''}">`;
                } else if (filter.type === 'daterange') {
                    const fromVal = filter.value && typeof filter.value === 'object' ? (filter.value.from || '') : '';
                    const toVal = filter.value && typeof filter.value === 'object' ? (filter.value.to || '') : '';
                    filterHtml += `<label class="form-label small mb-1 ms-1">From</label>
                        <input type="date" class="form-control form-control-sm ajax-filter-input mb-2" data-column="${filter.column}" data-filter-type="date-from" value="${fromVal}">
                        <label class="form-label small mb-1">To</label>
                        <input type="date" class="form-control form-control-sm ajax-filter-input" data-column="${filter.column}" data-filter-type="date-to" value="${toVal}">`;
                }
                filterHtml += `</div>`;
            });

            if (!autoApply) {
                filterHtml += `<button type="button" class="btn btn-primary my-2 w-100 ajax-filter-apply">Apply Filters</button>`;
            }

            filterHtml += `</div></div>
                <button type="button" class="btn btn-outline-danger btn-padding border-danger-subtle ajax-filter-reset d-none ms-2">
                  <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </button>
              </div>`;

            $filterContainer.html(filterHtml);

            let filterTimeout;
            const applyFilters = () => {
                if (filterTimeout) clearTimeout(filterTimeout);

                filterTimeout = setTimeout(() => {
                    const filterData = {};
                    let hasInvalidDate = false;

                    $filterContainer.find('.ajax-filter-input').each(function () {
                        const $input = $(this);
                        const column = $input.data('column');
                        const filterType = $input.data('filter-type');
                        const value = $input.val();

                        if (value) {
                            // Date validation (Year bug fix)
                            if (filterType === 'date-from' || filterType === 'date-to' || filterType === 'date') {
                                const year = parseInt(value.split('-')[0]);
                                if (isNaN(year) || year < 1000) {
                                    hasInvalidDate = true;
                                    return;
                                }
                            }

                            if (filterType === 'date-from') {
                                filterData[column] = filterData[column] || {};
                                filterData[column].from = value;
                            } else if (filterType === 'date-to') {
                                filterData[column] = filterData[column] || {};
                                filterData[column].to = value;
                            } else {
                                filterData[column] = value;
                            }
                        }
                    });

                    if (hasInvalidDate && autoApply) return;

                    $.fn.dataTable.ext.search = [];

                    if (filterConfig.ajaxUrl) {
                        api.settings()[0].oInit.ajax = {
                            url: filterConfig.ajaxUrl,
                            data: function (d) {
                                d.filters = filterData;
                                $.each(filterData, function (column, value) {
                                    if (typeof value === 'object') {
                                        d['filter_' + column + '_from'] = value.from;
                                        d['filter_' + column + '_to'] = value.to;
                                    } else {
                                        d['filter_' + column] = value;
                                    }
                                });
                                return d;
                            }
                        };
                        api.ajax.reload();
                    } else {
                        api.columns().search('');
                        if (Object.keys(filterData).length > 0) {
                            $.fn.dataTable.ext.search.push(function (settings, data) {
                                for (const column in filterData) {
                                    const value = filterData[column];
                                    if (typeof value === 'object') {
                                        const colData = data[column];
                                        const date = colData ? new Date(colData) : null;
                                        const from = value.from ? new Date(value.from) : null;
                                        const to = value.to ? new Date(value.to) : null;
                                        if (date && ((from && date < from) || (to && date > to))) return false;
                                    } else {
                                        const colData = data[column] || '';
                                        const tempDiv = document.createElement('div');
                                        tempDiv.innerHTML = colData;
                                        if ((tempDiv.textContent || tempDiv.innerText || '').trim().indexOf(value) === -1) return false;
                                    }
                                }
                                return true;
                            });
                        }
                        api.draw(false);
                    }

                    $filterContainer.find('.ajax-filter-reset').toggleClass('d-none', Object.keys(filterData).length === 0);
                }, autoApply ? 500 : 0);
            };

            // Trigger initial filter application if values are present
            const hasInitialValues = filters.some(f => {
                if (f.value && typeof f.value === 'object') {
                    return f.value.from || f.value.to;
                }
                return f.value && f.value !== '';
            });

            if (hasInitialValues) {
                applyFilters();
            }

            const closeDropdown = () => {
                const dropdownBtn = $filterContainer.find('[data-bs-toggle="dropdown"]')[0];
                const instance = bootstrap.Dropdown.getInstance(dropdownBtn);
                if (instance) instance.hide();
            };

            if (autoApply) {
                $filterContainer.on('change', '.ajax-filter-input', function () {
                    applyFilters();
                    closeDropdown();
                });
            } else {
                $filterContainer.on('click', '.ajax-filter-apply', function () {
                    applyFilters();
                    closeDropdown();
                }).on('keypress', 'input[type="text"]', function (e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        applyFilters();
                        closeDropdown();
                    }
                });
            }

            $filterContainer.on('click', '.ajax-filter-reset', function () {
                $filterContainer.find('.ajax-filter-input').val('');
                $.fn.dataTable.ext.search = [];
                api.columns().search('').draw();
                $(this).addClass('d-none');
            });
        },

        /**
         * Initialize generic global event listeners
         */
        initializeGlobalWindowEvents: function() {
            window.addEventListener('alert', event => {
                if (typeof toastr !== 'undefined') {
                    toastr[event.detail.type](event.detail.message);
                } else {
                    alert(event.detail.message);
                }
            });

            window.addEventListener('show-modal', event => {
                const modalElement = document.getElementById(event.detail.id);
                if (modalElement) {
                    let modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (!modalInstance) {
                        modalInstance = new bootstrap.Modal(modalElement);
                    }
                    modalInstance.show();
                }
            });

            window.addEventListener('close-modal', event => {
                const modalElement = document.getElementById(event.detail.id);
                if (modalElement) {
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
            });
        },

        /**
         * Initialize common Bootstrap components
         */
        initializeBootstrapComponents: function() {
            // Tooltips
            const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            Array.from(tooltipElements).map(element => new bootstrap.Tooltip(element));

            // Copyright Year
            const currentYear = new Date().getFullYear();
            document.querySelectorAll("[data-year]").forEach(element => {
                element.textContent = ` ${currentYear}`;
            });
        },

        /**
         * Conditional Toggles
         */
        initSlideToggle: function() {
            $(document).on('click', ':not(input)[data-slide-toggle]', function (e) {
                e.preventDefault();
                const targetSelector = $(this).data('slide-toggle');
                const $target = $(targetSelector);

                if ($target.length) {
                    if ($target.is(':visible')) {
                        $target.slideUp(300, function () {
                            $(this).addClass('d-none');
                        });
                    } else {
                        $target.removeClass('d-none').slideDown(300);
                    }
                }
            });

            $(document).on('change', 'input[data-slide-toggle]', function () {
                const targetSelector = $(this).data('slide-toggle');
                const $target = $(targetSelector);

                if ($target.length) {
                    if ($(this).is(':checked')) {
                        $target.removeClass('d-none').slideDown(300);
                    } else {
                        $target.slideUp(300, function () {
                            $(this).addClass('d-none');
                        });
                    }
                }
            });

            $('input[data-slide-toggle]').each(function () {
                const targetSelector = $(this).data('slide-toggle');
                const $target = $(targetSelector);

                if ($target.length) {
                    if ($(this).is(':checked')) {
                        $target.removeClass('d-none').show();
                    } else {
                        $target.addClass('d-none').hide();
                    }
                }
            });
        },

        initConditionalToggle: function(container = document) {
            const $container = $(container);

            const evaluateToggle = function ($element, animate = true) {
                const currentValue = String($element.val());
                const targetSelectors = String($element.data('conditional-toggle')).split(',').map(s => s.trim());

                let triggerValues = $element.data('conditional-value');
                triggerValues = (triggerValues === undefined || triggerValues === null) ? [] : String(triggerValues).split(',').map(s => s.trim());

                let logicValues = $element.data('conditional-logic');
                logicValues = (logicValues === undefined || logicValues === null) ? [] : String(logicValues).split(',').map(s => s.trim());

                targetSelectors.forEach((selector, index) => {
                    const $target = $(selector);

                    let triggerVal = '';
                    if (index < triggerValues.length) {
                        triggerVal = triggerValues[index];
                    } else if (triggerValues.length === 1) {
                        triggerVal = triggerValues[0];
                    }

                    let logicVal = 'equal';
                    if (index < logicValues.length) {
                        logicVal = logicValues[index];
                    } else if (logicValues.length === 1) {
                        logicVal = logicValues[0];
                    }

                    let shouldShow = false;
                    if (logicVal === 'not-equal') {
                        shouldShow = currentValue !== triggerVal;
                    } else {
                        shouldShow = currentValue === triggerVal;
                    }

                    if (shouldShow) {
                        if (animate) $target.removeClass('d-none').slideDown(300);
                        else $target.removeClass('d-none').show();
                    } else {
                        if (animate) {
                            $target.slideUp(300, function () {
                                $(this).addClass('d-none');
                            });
                        } else $target.addClass('d-none').hide();
                    }
                });
            };

            $(document).on('change', '[data-conditional-toggle]', function () {
                evaluateToggle($(this), true);
            });

            $container.find('[data-conditional-toggle]').each(function () {
                evaluateToggle($(this), false);
            });

            $(document).on('shown.bs.modal', '.modal', function () {
                $(this).find('[data-conditional-toggle]').each(function () {
                    const targetSelector = $(this).data('conditional-toggle');
                    const triggerValue = String($(this).data('conditional-value'));
                    const logic = $(this).data('conditional-logic') || 'equal';
                    const $target = $(targetSelector);
                    const currentValue = String($(this).val());

                    let shouldShow = (logic === 'not-equal') ? currentValue !== triggerValue : currentValue === triggerValue;
                    $target.toggleClass('d-none', !shouldShow).toggle(shouldShow);
                });
            });
        },

        /**
         * Universal bulk action handler
         */
        bulkAction: function(options) {
            const config = {
                method: 'POST',
                requireConfirm: true,
                reloadOnSuccess: true,
                ...options
            };

            const selectedIds = $('.row-checkbox:checked').map(function () {
                const val = $(this).val();
                return val ? parseInt(val, 10) : null;
            }).get().filter(id => id !== null && !isNaN(id));

            if (selectedIds.length === 0) {
                toastr.error(window.config?.translates?.selectAtLeastOne || "Select at least one item");
                return;
            }

            const executeAction = () => {
                const formData = new FormData();
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                // Allow DELETE method via spoofing for better compatibility
                if (config.method.toUpperCase() === 'DELETE') {
                    formData.append('_method', 'DELETE');
                }

                // Add IDs
                selectedIds.forEach(id => {
                    formData.append('ids[]', id);
                });

                // Add any additional data from config
                if (config.data) {
                    Object.entries(config.data).forEach(([key, value]) => {
                        formData.append(key, value);
                    });
                }

                window.EzyDev.ajaxRequest({
                    url: config.url,
                    method: 'POST', // Always use POST with _method spoofing if needed
                    data: formData,
                    onSuccess: config.onSuccess,
                    onError: config.onError
                });
            };

            if (config.requireConfirm) {
                window.EzyDev.ensureActionConfirmModal();
                const confirmMessage = config.confirmMessage || window.config?.translates?.actionConfirm;
                $('#actionConfirmMessage').text(confirmMessage);

                const confirmModal = new bootstrap.Modal(document.getElementById('actionConfirmModal'));
                const $confirmBtn = $('.btn-confirm');

                $confirmBtn.off('click').one('click', function (e) {
                    e.preventDefault();
                    $confirmBtn.prop('disabled', true);
                    executeAction();
                    setTimeout(() => {
                        $confirmBtn.prop('disabled', false);
                        confirmModal.hide();
                    }, 500);
                });

                confirmModal.show();
            } else {
                executeAction();
            }
        },

        initializeClipboard: function () {
            if (typeof ClipboardJS !== 'undefined') {
                // Use selector for dynamic element support (delegation)
                if (window._clipboard) window._clipboard.destroy();

                window._clipboard = new ClipboardJS('.btn-copy');
                window._clipboard.on("success", (e) => {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(window.config?.translates?.copied || 'Copied to clipboard');
                    }
                    e.clearSelection();
                });

                window._clipboard.on("error", (e) => {
                   console.error('Clipboard Error:', e);
                });
            }
        },

        initializeNumericInputs: function () {
            $(document).on('input', '.input-numeric', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        },

        initializePriceInputs: function () {
            if ($.fn.priceFormat) {
                const priceInputs = $(".input-price");
                if (priceInputs.length) {
                    priceInputs.priceFormat({
                        prefix: "",
                        thousandsSeparator: "",
                        clearOnEmpty: false,
                    });
                }
            }
        },

        initializeAuthInputGroups: function (container = document) {
            const $container = $(container);
            const toggleActive = (input) => {
                const group = $(input).closest('.auth-input-group');
                if (input.value.trim() !== "" || $(input).is(':focus')) {
                    group.addClass('active');
                } else {
                    group.removeClass('active');
                }
            };

            $container.find('.auth-input-group input').on('focus blur input', function () {
                toggleActive(this);
            });

            // Use delegation for global handling (supporting future dynamic content)
            if (container === document) {
                $(document).on('focus blur input', '.auth-input-group input', function () {
                    toggleActive(this);
                });
            }

            // Initial check for existing content
            $container.find('.auth-input-group input').each(function () {
                toggleActive(this);
            });
        },

        initializePasswordStrength: function (container = document) {
            const $container = $(container);
            const $passwordInput = $container.find('#registerPasswordInput');
            const $indicator = $container.find('#passwordStrengthIndicator');
            const $progressBar = $container.find('#passwordStrengthBar');
            const $strengthText = $container.find('#passwordStrengthText');

            if (!$passwordInput.length || !$indicator.length) return;

            const updateStrength = (password) => {
                if (password.length === 0) {
                    $indicator.stop().fadeOut(200);
                    return;
                }

                $indicator.stop().fadeIn(200);
                const strength = window.EzyDev._calculatePasswordStrength(password);

                $progressBar.css('width', strength.percent + '%');
                $progressBar.removeClass('bg-danger bg-warning bg-info bg-success');
                $progressBar.addClass(strength.colorClass);

                $strengthText.text(strength.label);
                $strengthText.removeClass('text-muted text-danger text-warning text-info text-success');
                $strengthText.addClass('text-' + strength.colorClass.replace('bg-', ''));
            };

            $passwordInput.on('input', function () {
                updateStrength($(this).val());
            });

            $passwordInput.on('blur', function () {
                if ($(this).val().length === 0) {
                    $indicator.stop().fadeOut(200);
                }
            });
        },

        _calculatePasswordStrength: function (password) {
            let score = 0;
            if (password.length > 8) score += 20;
            if (password.length > 12) score += 10;
            if (/[a-z]/.test(password)) score += 15;
            if (/[A-Z]/.test(password)) score += 15;
            if (/[0-9]/.test(password)) score += 15;
            if (/[^A-Za-z0-9]/.test(password)) score += 25;

            if (score < 30) return { percent: 25, label: 'Weak', colorClass: 'bg-danger' };
            if (score < 60) return { percent: 50, label: 'Fair', colorClass: 'bg-warning' };
            if (score < 80) return { percent: 75, label: 'Good', colorClass: 'bg-info' };
            return { percent: 100, label: 'Strong', colorClass: 'bg-success' };
        },

        initializeUsernameAvailability: function (container = document) {
            const $container = $(container);
            const $input = $container.find('#registerUsernameInput');

            if (!$input.length) return;

            const checkUrl = $input.data('check-url');
            const $message = $input.closest('.col-12').find('#usernameStatusText');

            if (!checkUrl || !$message.length) return;

            let timeout = null;
            let currentRequest = null;

            $input.on('input', function () {
                const username = $(this).val();

                if (timeout) clearTimeout(timeout);
                if (currentRequest) currentRequest.abort();

                if (username.length < 6) {
                    $message.stop().fadeOut(200, function() {
                        $(this).text('').attr('class', 'form-text');
                    });
                    return;
                }

                $message.text('Checking...').attr('class', 'form-text text-info').stop().fadeIn(200);

                timeout = setTimeout(() => {
                    currentRequest = $.ajax({
                        url: checkUrl,
                        type: 'POST',
                        data: {
                            username: username,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (res) {
                            if (res.available) {
                                $message.text(res.message).attr('class', 'form-text text-success').stop().fadeIn(200);
                            } else {
                                $message.text(res.message).attr('class', 'form-text text-danger').stop().fadeIn(200);
                            }
                        },
                        error: function (xhr) {
                           if (xhr.statusText === 'abort') return;
                           const res = xhr.responseJSON;
                           $message.text(res?.message || 'Error checking').attr('class', 'form-text text-danger').stop().fadeIn(200);
                        },
                        complete: function() {
                            currentRequest = null;
                        }
                    });
                }, 500);
            });
        },

        initializePasswordToggles: function () {
            $(document).off('click', '.password-toggle').on('click', '.password-toggle', function (e) {
                e.preventDefault();
                const $toggle = $(this);
                const $input = $toggle.closest('.position-relative, .input-group, .form-group').find('input[type="password"], input[type="text"]');

                if ($input.length) {
                    const type = $input.attr('type') === 'password' ? 'text' : 'password';
                    $input.attr('type', type);

                    if ($toggle.hasClass('bi-eye') || $toggle.hasClass('bi-eye-slash')) {
                        $toggle.toggleClass('bi-eye bi-eye-slash');
                    }
                }
            });
        },

        initializeSortableLists: function (container = document) {
            const $container = $(container);
            const $sortableLists = $container.find('.sortable-list');

            if ($sortableLists.length && typeof $.fn.sortable !== 'undefined') {
                $sortableLists.each(function () {
                    const $list = $(this);
                    $list.sortable({
                        handle: '.sortable-list-handle',
                        placeholder: 'sortable-list-placeholder',
                        axis: "y",
                        tolerance: 'pointer',
                        cursor: 'move',
                        helper: function (event, element) {
                            return element.clone().css({
                                'width': element.width(),
                                'opacity': '0.8'
                            });
                        },
                        start: function (event, ui) {
                            ui.placeholder.height(ui.helper.outerHeight());
                        },
                        update: function () {
                            const ids = $list.sortable('toArray', {
                                attribute: 'data-id'
                            });

                            const endpoint = $list.data('sortable') || (typeof sortableRoute !== 'undefined' ? sortableRoute : null);

                            if (endpoint) {
                                $.ajax({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    url: endpoint,
                                    type: "POST",
                                    data: {
                                        ids: ids.join(',')
                                    },
                                    dataType: "JSON",
                                    success: function (response) {
                                        if (!$.isEmptyObject(response.error)) {
                                            if (typeof toastr !== 'undefined') toastr.error(response.error);
                                        }
                                    },
                                    error: function (request, status, error) {
                                        if (typeof toastr !== 'undefined') toastr.error(error);
                                    }
                                });
                            }
                        }
                    });
                });
            }
        },

        initializeLogoutTrigger: function () {
            $(document).on('click', '.logout-trigger', function (e) {
                e.preventDefault();
                const $form = $('#logout-form').length ? $('#logout-form') : $('.logout-form');
                $form.submit();
            });
        },

        initializeImageOutputs: function () {
            // Standard image input
            $(document).on('change', '.image-input', function () {
                if (window.EzyDev.isValidImageFile($(this).val())) {
                    const dataId = $(this).data("id");
                    const imagePreview = $("#image-preview-" + dataId);
                    if (imagePreview.length) {
                        window.EzyDev.previewImageFile(this, imagePreview[0]);
                    }
                }
            });

            // Target based attach buttons
            $(document).on("click", ".attach-image-button", function () {
                const dataId = $(this).data("id");
                const targetedImageInput = $("#attach-image-targeted-input-" + dataId);
                const targetedImagePreview = $("#attach-image-preview-" + dataId);
                const targetedImageDisplay = $("#attach-image-display-" + dataId);

                targetedImageInput.trigger("click");

                targetedImageInput.off("change").on("change", function () {
                    if (this.files && this.files[0]) {
                        if (window.EzyDev.isValidImageFile($(this).val())) {
                            window.EzyDev.previewImageFile(this, targetedImagePreview[0]);
                        }
                        if (targetedImageDisplay.length) {
                            targetedImageDisplay.val(this.files[0].name);
                        }
                    }
                });
            });
        },

        initializeSlugGenerators: function () {
            $(document).on("input", "#create_slug", function () {
                const $this = $(this);
                const showSlugInput = $("#show_slug");
                const slugUrl = $this.data("slug") || (typeof GET_SLUG_URL !== 'undefined' ? GET_SLUG_URL : null);

                if (slugUrl && showSlugInput.length) {
                    $.ajax({
                        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                        type: "GET",
                        url: slugUrl,
                        data: { content: $this.val() },
                        success: function (data) {
                            showSlugInput.val(data.slug);
                        },
                    });
                }
            });
        },

        initializePlugins: function (container = document) {
            const $container = $(container);
            this.initializeAuthInputGroups($container);
            this.initializePasswordStrength($container);
            this.initializeUsernameAvailability($container);
            this.initConditionalToggle($container);

            // Selectpicker
            if ($.fn.selectpicker) {
                const selectPickerElements = $container.find(".selectpicker");
                if (selectPickerElements.length) {
                    selectPickerElements.selectpicker({
                        noneSelectedText: window.config?.translates?.noneSelectedText || 'Nothing selected',
                        noneResultsText: window.config?.translates?.noneResultsText || 'No results matched {0}',
                        countSelectedText: window.config?.translates?.countSelectedText || '{0} items selected',
                    });
                }

                $(document).on('change', '.selectpicker-checkbox-input', function () {
                    const selectedCount = $('.selectpicker-checkbox-input:checked').length;
                    $('.selected-count').text(selectedCount);
                });
            }

            // Datepicker
            if ($.fn.datepicker) {
                const datepickerElements = $container.find(".datepicker");
                if (datepickerElements.length) {
                    datepickerElements.datepicker({
                        format: 'dd-mm-yyyy',
                        autoclose: true,
                        todayHighlight: true,
                        endDate: new Date(),
                        orientation: 'top auto',
                        todayBtn: 'linked'
                    });
                }
            }

            // Colorpicker
            if (typeof Coloris !== 'undefined') {
                const colorPickerElements = $container.find(".colorpicker");
                if (colorPickerElements.length) {
                    Coloris({
                        el: ".coloris",
                        rtl: window.config?.direction === "rtl"
                    });

                    Coloris.setInstance(".coloris", {
                        theme: "pill",
                        themeMode: "light",
                        formatToggle: true,
                        closeButton: true,
                        clearButton: true,
                        swatches: typeof COLOR_SWATCHES !== 'undefined' ? COLOR_SWATCHES : [
                            '#264653', '#2a9d8f', '#e9c46a', '#f4a261', '#e76f51',
                            '#d62828', '#023e8a', '#0077b6', '#0096c7', '#00b4d8', '#48cae4'
                        ],
                    });
                }
            }

            // Tags input
            if ($.fn.tagsinput) {
                const tagsInputElements = $container.find(".tags-input");
                if (tagsInputElements.length) {
                    tagsInputElements.tagsinput({
                        cancelConfirmKeysOnEmpty: false,
                    });
                }
            }
        },

        initializeTextFormatters: function () {
            $(document).on("input", ".remove-spaces", function () {
                $(this).val($(this).val().replace(/\s/g, ""));
            });
        },

        initDarkModeToggle: function () {
            const body = document.body;

            // 1. Dual-button approach (typically frontend)
            const darkBtn = document.querySelector(".btn-dark-mode");
            const lightBtn = document.querySelector(".btn-light-mode");

            // 2. Single-button approach (typically admin backend)
            const themeToggleBtn = document.getElementById('themeToggle');

            // Apply unified logic
            function applyDarkMode() {
                body.classList.add("dark-mode");

                if (darkBtn && lightBtn) {
                    darkBtn.parentElement.classList.add("d-none");
                    lightBtn.parentElement.classList.remove("d-none");
                }

                window.EzyDev.setCookie("dark_mode", "1", 365);
                try { localStorage.setItem("adminTheme", "dark"); } catch(e){}

                document.querySelectorAll(".table-light").forEach(function (table) {
                    table.classList.replace("table-light", "table-dark");
                });
            }

            function applyLightMode() {
                body.classList.remove("dark-mode");

                if (darkBtn && lightBtn) {
                    lightBtn.parentElement.classList.add("d-none");
                    darkBtn.parentElement.classList.remove("d-none");
                }

                window.EzyDev.setCookie("dark_mode", "0", 365);
                try { localStorage.setItem("adminTheme", "light"); } catch(e){}

                document.querySelectorAll(".table-dark").forEach(function (table) {
                    table.classList.replace("table-dark", "table-light");
                });
            }

            // Bind single button
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', function () {
                    if (body.classList.contains('dark-mode')) {
                        applyLightMode();
                    } else {
                        applyDarkMode();
                    }
                    if (!body.style.transition) {
                        body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
                    }
                });
                themeToggleBtn.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        themeToggleBtn.click();
                    }
                });
            }

            // Bind dual buttons
            if (darkBtn) darkBtn.addEventListener("click", applyDarkMode);
            if (lightBtn) lightBtn.addEventListener("click", applyLightMode);

            // Initialization Check
            let currentTheme = 'light';
            try { currentTheme = localStorage.getItem("adminTheme"); } catch(e){}
            const savedMode = window.EzyDev.getCookie("dark_mode");

            // If cookie says dark OR localStorage says dark
            if (savedMode === "1" || currentTheme === "dark") {
                applyDarkMode();
            } else if (savedMode === "0" || currentTheme === "light") {
                applyLightMode();
            } else if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
                applyDarkMode();
            } else {
                applyLightMode();
            }
        },

        /**
         * Initialize AJAX-compatible pagination links
         */
        initializeAjaxPagination: function(container = document) {
            const $container = $(container);
            $container.find('.ajax-pagination, .ajax-pagination-links').each(function() {
                $(this).find('a').attr('data-ajax-tab', 'true');
            });
        },

        /**
         * Universal AJAX Tab Loader
         */
        initializeAjaxTabs: function() {
            const toggleAjaxSpinner = (show) => {
                const $container = $('.ajax-tabs-content');
                if (!$container.length) return;

                let $spinner = $container.find('.ajax-spinner-container');
                if (!$spinner.length && show) {
                    $spinner = $('<div class="ajax-spinner-container"><div class="spinner-border" role="status"></div></div>');
                    $container.prepend($spinner);
                }

                if (show) {
                    $spinner.addClass('active');
                    $container.addClass('opacity-50 pointer-events-none');
                } else {
                    $spinner.removeClass('active');
                    $container.removeClass('opacity-50 pointer-events-none');
                }
            };

            const loadTabContent = ($tab, url) => {
                const $container = $('.ajax-tabs-content');
                if (!$container.length) return;

                toggleAjaxSpinner(true);

                $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        // Identify target tab from URL or trigger element
                        let targetTab = $tab.hasClass('ajax-tabs-item') ? $tab : null;
                        if (!targetTab) {
                            try {
                                const urlObj = new URL(url, window.location.origin);
                                const tabName = urlObj.searchParams.get('tab');
                                if (tabName) {
                                    targetTab = $('.ajax-tabs-item').filter(function() {
                                        return $(this).attr('href').indexOf(`tab=${tabName}`) !== -1;
                                    });
                                }
                            } catch (e) {}
                        }

                        // Support both 'current' (userpanel) and 'active' (standard)
                        $('.ajax-tabs-item').removeClass('current active');
                        if (targetTab && targetTab.length) {
                            targetTab.addClass(targetTab.hasClass('nav-link') ? 'active' : 'current');
                        }

                        const $tempDiv = $('<div>').html(response);
                        const $newContent = $tempDiv.find('.ajax-tabs-content').html();

                        if ($newContent) {
                            $container.html($newContent);
                        } else {
                            $container.html(response);
                        }

                        // Re-init pagination links for the new content
                        window.EzyDev.initializeAjaxPagination($container);

                        // Global re-init
                        if (typeof window.initializeFormComponents === 'function') {
                            window.initializeFormComponents($container[0]);
                        }
                        if (typeof window.initProductCharts === 'function') {
                            window.initProductCharts();
                        }

                        // Scroll to container top smoothly
                        const $tabsWrapper = $tab.closest('.ajax-tabs');
                        if ($tabsWrapper.length) {
                            $('html, body').animate({
                                scrollTop: $tabsWrapper.offset().top - 100
                            }, 200);
                        }
                    },
                    error: function() {
                        window.location.href = url;
                    },
                    complete: function() {
                        toggleAjaxSpinner(false);
                    }
                });
            };

            $(document).on('click', '[data-ajax-tab="true"]', function(e) {
                e.preventDefault();
                const $tab = $(this);
                const url = $tab.attr('href');
                if ($tab.hasClass('current') || $tab.hasClass('active')) return;

                window.history.pushState({ path: url }, '', url);
                loadTabContent($tab, url);
            });

            window.onpopstate = function() {
                const url = window.location.href;
                let $tab = $(`.ajax-tabs-item[href="${url}"]`);

                if (!$tab.length) {
                    try {
                        const urlObj = new URL(url);
                        const tabName = urlObj.searchParams.get('tab');
                        if (tabName) {
                            $tab = $('.ajax-tabs-item').filter(function() {
                                return $(this).attr('href').indexOf(`tab=${tabName}`) !== -1;
                            });
                        }
                    } catch (e) {}
                }

                if ($tab.length) {
                    loadTabContent($tab, url);
                }
            };
        },

        /**
         * Horizontal Scroll Controls for tabs
         */
        initializeTabsScrollControls: function() {
            const $wrapper = $('.ajax-tabs-wrapper');
            if (!$wrapper.length) return;

            $wrapper.each(function() {
                const $this = $(this);
                const $nav = $this.find('.ajax-tabs-nav');
                const $prevBtn = $this.find('.tabs-nav-control.prev');
                const $nextBtn = $this.find('.tabs-nav-control.next');

                if (!$nav.length) return;

                const updateControls = () => {
                    const scrollLeft = $nav.scrollLeft();
                    const scrollWidth = $nav[0].scrollWidth;
                    const clientWidth = $nav[0].clientWidth;

                    if (scrollLeft > 20) {
                        $prevBtn.removeClass('d-none').show();
                    } else {
                        $prevBtn.hide();
                    }

                    if (scrollLeft + clientWidth < scrollWidth - 20) {
                        $nextBtn.removeClass('d-none').show();
                    } else {
                        $nextBtn.hide();
                    }
                };

                setTimeout(updateControls, 300);
                $nav.on('scroll', updateControls);
                $(window).on('resize', updateControls);

                $prevBtn.off('click').on('click', function() {
                    $nav.animate({ scrollLeft: $nav.scrollLeft() - 200 }, 300);
                });

                $nextBtn.off('click').on('click', function() {
                    $nav.animate({ scrollLeft: $nav.scrollLeft() + 200 }, 300);
                });
            });
        }
    };

    // Expose commonly used methods globally for compatibility
    window.bulkAction = window.EzyDev.bulkAction;


    // Auto-init on load if needed
    $(function() {
        window.EzyDev.initializeGlobalWindowEvents();
        window.EzyDev.initializeBootstrapComponents();
        window.EzyDev.initSlideToggle();
        window.EzyDev.initConditionalToggle();

        window.EzyDev.initializeActionConfirm();
        window.EzyDev.initializeAjaxForms();
        window.EzyDev.initializeAjaxModals();
        window.EzyDev.initAutoOpenModals();
        window.EzyDev.initializeDataTables();

        // Advanced utilities
        window.EzyDev.initializeClipboard();
        window.EzyDev.initializeNumericInputs();
        window.EzyDev.initializePriceInputs();
        window.EzyDev.initializeAuthInputGroups();
        window.EzyDev.initializePasswordToggles();
        window.EzyDev.initializeLogoutTrigger();
        window.EzyDev.initializeImageOutputs();
        window.EzyDev.initializeSlugGenerators();
        window.EzyDev.initializePasswordGenerators();
        window.EzyDev.initializeSortableLists();
        window.EzyDev.initializePlugins();
        window.EzyDev.initializeTextFormatters();
        window.EzyDev.initializeAjaxTabs();
        window.EzyDev.initializeTabsScrollControls();
        window.EzyDev.initializeAjaxPagination();
        window.EzyDev.initDarkModeToggle();
    });

})(jQuery);
