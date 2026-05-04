(function ($) {
    'use strict';

    // ===== PRODUCT REPORT MODAL =====
    class ProductReportModal {
        constructor() {
            this.modal = document.getElementById('reportProductModal');
            this.form = document.getElementById('reportProductForm');
            this.button = document.querySelector('[data-bs-target="#reportProductModal"]');
            this.selectedFiles = [];

            if (this.modal && this.form && this.button) {
                this.init();
            }
        }

        init() {
            // Set form action on modal show
            this.modal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                if (button) {
                    const reportUrl = button.getAttribute('data-report-url');
                    if (reportUrl) {
                        this.form.setAttribute('action', reportUrl);
                    }
                }
            });

            // File input handler
            $(document).on("change", "#screenshotInput", (e) => this.handleFileSelect(e));

            // Form submission
            this.form.addEventListener("submit", (e) => this.handleSubmit(e));
        }

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (this.selectedFiles.length >= 3) {
                alert("You can only upload maximum 3 gallery images.");
                event.target.value = "";
                return;
            }

            this.selectedFiles.push(file);

            const reader = new FileReader();
            reader.onload = (e) => {
                const wrapper = $(`
                    <div class="position-relative d-inline-block" style="width:60px; height:60px;">
                        <img src="${e.target.result}" class="img-thumbnail" style="width:60px; height:60px; object-fit:cover;">
                        <i class="bi bi-x-circle-fill text-danger position-absolute" style="top:-6px; right:-6px; cursor:pointer; font-size:16px;"></i>
                    </div>
                `);

                wrapper.find("i").on("click", () => {
                    const index = this.selectedFiles.indexOf(file);
                    if (index > -1) this.selectedFiles.splice(index, 1);
                    wrapper.remove();
                });

                $("#previewContainer").append(wrapper);
            };

            reader.readAsDataURL(file);
            event.target.value = "";
        }

        handleSubmit(e) {
            e.preventDefault();

            const reportUrl = this.form.getAttribute('action');
            if (!reportUrl) {
                toastr.error('Report URL not found');
                return;
            }

            const formData = new FormData(this.form);
            this.selectedFiles.forEach((file) => {
                formData.append("screenshots[]", file);
            });

            const submitBtn = document.getElementById("submitReportBtn");
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm text-white me-2"></span>Submitting...`;

            fetch(reportUrl, {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.modal.querySelector(".modal-body").innerHTML = `
                        <div class="card border-success text-center py-4">
                            <div><i class="bi bi-check2-circle text-success fs-1"></i></div>
                            <h5 class="text-success mt-3">${data.message}</h5>
                            <p>We will take action soon and notify you!</p>
                        </div>
                    `;
                        setTimeout(() => bootstrap.Modal.getInstance(this.modal).hide(), 5000);
                        this.reset();
                    } else {
                        let error = data.message || "An error occurred";
                        if (data.errors && Object.keys(data.errors).length > 0) {
                            error = Object.values(data.errors)[0][0];
                        }
                        toastr.error(error);
                    }
                })
                .catch(() => toastr.error("An unknown error occurred"))
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Submit Report';
                });
        }

        reset() {
            this.form.reset();
            $("#previewContainer").empty();
            this.selectedFiles = [];
        }
    }

    // ===== COMMENT REPORT MODAL =====
    class CommentReportModalHandler {
        static init() {
            window.addEventListener('show-comment-report-modal', function (event) {
                const data = event.detail;

                if (!data || !data.id) {
                    return;
                }

                // Populate modal content
                const previewElement = document.getElementById('reportCommentPreview');
                if (!previewElement) {
                    return;
                }

                previewElement.innerHTML = `
                    <div class="row row-cols-auto flex-nowrap g-3 pb-0">
                        <div class="col flex-grow-0">
                            <a href="${data.user.profile_link}" class="user-avatar rounded">
                                <img src="${data.user.avatar}" alt="${data.user.username}">
                            </a>
                        </div>
                        <div class="col flex-grow-1">
                            <div class="row row-cols-auto align-items-center justify-content-between g-2 mb-2">
                                <div class="col">
                                    <a href="${data.user.profile_link}" class="text-dark">${data.user.name}</a>
                                </div>
                                <div class="col small">
                                    <span class="text-muted">${data.created_at}</span>
                                </div>
                            </div>
                            <div class="fw-light">${data.body}</div>
                        </div>
                    </div>
                `;

                // Open modal
                const modalElement = document.getElementById('reportProductCommentModal');
                if (!modalElement) {
                    return;
                }

                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            });
        }
    }

    // ===== STAR RATING =====
    class StarRating {
        static init() {
            document.querySelectorAll(".ratings-selective").forEach((container) => {
                const stars = container.querySelectorAll(".rating");

                stars.forEach((star, index) => {
                    const radioInput = star.querySelector("input[type=radio]");

                    star.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        if (radioInput) radioInput.checked = true;
                        StarRating.updateStars(stars, index);
                    });

                    star.addEventListener('mouseenter', () => {
                        StarRating.updateStars(stars, index);
                    });
                });

                container.addEventListener('mouseleave', () => {
                    const checkedInput = container.querySelector("input[type=radio]:checked");
                    const checkedIndex = checkedInput ? parseInt(checkedInput.value) - 1 : -1;
                    StarRating.updateStars(stars, checkedIndex);
                });
            });
        }

        static updateStars(stars, activeIndex) {
            stars.forEach((star, index) => {
                star.classList.toggle("rating-active", index <= activeIndex);
            });
        }
    }

    // ===== LOGIN MODAL =====
    class LoginModalHandler {
        static init() {
            document.querySelectorAll('.comment-needs-login-modal').forEach((element) => {
                element.removeEventListener('click', LoginModalHandler.handle);
                element.addEventListener('click', LoginModalHandler.handle);
            });
        }

        static handle(e) {
            e.preventDefault();

            if (typeof LoginModal !== 'undefined') {
                window.LoginModal.show('Please log in to continue');
            } else if (typeof toastr !== 'undefined') {
                toastr.info('Please log in to continue');
            } else {
                alert('Please log in to continue');
            }
        }
    }

    // ===== AJAX TABS =====
    class ProductTabs {
        constructor() {
            this.container = document.getElementById('product-tab-content-area');
            this.buttonsContainer = document.getElementById('product-tab-container-for-js');

            if (!this.container || !this.buttonsContainer) {
                return;
            }

            this.init();
        }

        init() {
            // Tab button clicks - use delegation for robustness
            $(document).on('click', '.ajax-tab-button', (e) => {
                const button = e.currentTarget;
                e.preventDefault();
                const tabUrl = $(button).data('url');
                const tabName = $(button).data('tab');
                if (tabUrl && tabName) {
                    this.loadTab(tabUrl, tabName, true);
                }
            });

            // Browser back/forward
            window.addEventListener('popstate', () => {
                const tab = this.getCurrentTabFromURL();
                const button = document.querySelector(`.ajax-tab-button[data-tab="${tab}"]`);
                if (button) {
                    this.loadTab(button.dataset.url, tab, false);
                    this.updateActiveButton(tab);
                }
            });

            // Set initial active tab
            this.updateActiveButton(this.getCurrentTabFromURL());
        }

        loadTab(url, name, pushHistory) {
            this.container.innerHTML = '<div class="text-center p-5"><i class="spinner-border text-primary"></i></div>';

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => {
                            throw new Error(`${res.status}: ${res.statusText}`);
                        });
                    }
                    return res.text();
                })
                .then(html => {
                    this.container.innerHTML = html;

                    // Reinitialize components
                    StarRating.init();
                    LoginModalHandler.init();
                    if ($.fn.selectpicker) {
                        $(this.container).find('.selectpicker').selectpicker();
                    }
                    this.initLivewireComponents();

                    if (pushHistory) {
                        this.updateHistory(name);
                        this.updateActiveButton(name);
                    }
                })
                .catch(error => {
                    this.container.innerHTML = '<div class="alert alert-danger">Failed to load content.</div>';
                });
        }

        initLivewireComponents() {
            if (typeof Livewire === 'undefined') return;

            setTimeout(() => {
                const elements = this.container.querySelectorAll('[wire\\:id]');
                if (elements.length === 0) return;

                elements.forEach(el => {
                    if (!el.__livewire && !el._x_dataStack) {
                        if (typeof Alpine !== 'undefined' && Alpine.initTree) {
                            try {
                                Alpine.initTree(el);
                            } catch (e) {
                                //console.warn('Livewire init failed:', e);
                            }
                        }
                    }
                });
            }, 150);
        }

        updateHistory(tabName) {
            const { itemSlug, itemId, itemName, siteName } = this.buttonsContainer.dataset;
            const url = `/products/${itemSlug}/${itemId}${tabName !== 'details' ? '/' + tabName : ''}`;
            const siteTitle = siteName || document.querySelector('meta[property="og:site_name"]')?.content || 'EasyMarket';
            const title = tabName === 'details'
                ? `${itemName} | ${siteTitle}`
                : `${tabName.charAt(0).toUpperCase() + tabName.slice(1)} - ${itemName} | ${siteTitle}`;

            history.pushState({ tab: tabName }, '', url);
            document.title = title;
        }

        updateActiveButton(activeTab) {
            document.querySelectorAll('.ajax-tab-button').forEach(button => {
                button.classList.toggle('active', button.dataset.tab === activeTab);
            });
        }

        getCurrentTabFromURL() {
            const segments = window.location.pathname.split('/').filter(s => s);
            if (segments.length >= 4 && segments[segments.length - 2] !== 'tab') {
                const lastSegment = segments[segments.length - 1];
                const buttons = document.querySelectorAll('.ajax-tab-button');
                const isKnownTab = Array.from(buttons).some(b => b.dataset.tab === lastSegment);
                if (isKnownTab) return lastSegment;
            }
            return 'details';
        }
    }

    // ===== REVIEW TAB =====
    class ReviewTab {
        static init() {
            // Handle sorting change
            $(document).on('change', '#reviewSortBy', function () {
                ReviewTab.reload($(this).val(), 1);
            });

            // Handle pagination clicks
            $(document).on('click', '.product-reviews-ajax-tab .pagination a', function (e) {
                e.preventDefault();
                const href = $(this).attr('href');
                if (!href) return;

                const url = new URL(href);
                const page = url.searchParams.get('page') || 1;
                const sortSelection = $('#reviewSortBy').val() || 'newest';

                ReviewTab.reload(sortSelection, page);
            });
        }

        static reload(sort, page) {
            const container = $('.product-reviews-ajax-tab');
            const baseUrl = container.data('ajax-url');
            if (!baseUrl) return;

            const ajaxUrl = `${baseUrl}?review_sort_by=${sort}&page=${page}`;
            const contentArea = $('#product-tab-content-area');

            // Show loader and dim content
            contentArea.css('opacity', '0.5').css('pointer-events', 'none');

            // Add a temporary overlay spinner if not already present
            if (contentArea.find('.review-temp-loader').length === 0) {
                contentArea.append('<div class="review-temp-loader d-flex align-items-center justify-content-center position-absolute top-0 start-0 end-0 bottom-0 z-2" style="background: rgba(255,255,255,0.3);"><div class="spinner-border text-primary" role="status"></div></div>');
            }

            fetch(ajaxUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
                .then(res => res.text())
                .then(html => {
                    contentArea.html(html);
                    contentArea.css('opacity', '1').css('pointer-events', 'auto');

                    // Re-initialize theme components
                    StarRating.init();
                    LoginModalHandler.init();

                    // Scroll to top of tab container
                    $('html, body').animate({
                        scrollTop: contentArea.offset().top - 100
                    }, 200);
                })
                .catch(err => {
                    console.error('Failed to reload reviews:', err);
                    contentArea.css('opacity', '1').css('pointer-events', 'auto');
                    contentArea.find('.review-temp-loader').remove();
                });
        }
    }

    // ===== INITIALIZATION =====
    // Initialize when DOM is ready (scripts are at bottom, so likely already ready)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeProductPage);
    } else {
        initializeProductPage();
    }

    function initializeProductPage() {
        new ProductReportModal();
        CommentReportModalHandler.init();
        new ProductTabs();
        ReviewTab.init();
        StarRating.init();
        LoginModalHandler.init();
        initializeLicenseTypes();
        initializeModernPriceCard();
        ProductReview.init();
    }

    // ===== PRODUCT REVIEW MODAL =====
    class ProductReview {
        static init() {
            // Main stars hover/click (Event Delegation)
            $(document).on('mouseover', '.star-hover', function () {
                const $container = $(this).closest('.user-rating-stars');
                const val = $(this).data('value');
                $container.find('.star-hover').each(function () {
                    $(this).toggleClass('active', $(this).data('value') <= val);
                });
            });

            $(document).on('mouseout', '.star-hover', function () {
                const $container = $(this).closest('.user-rating-stars');
                if ($container.length) {
                    $container.find('.star-hover').removeClass('active');
                }
            });

            $(document).on('click', '.star-hover', function (e) {
                const $container = $(this).closest('.user-rating-stars');
                const alreadyReviewed = $container.data('already-reviewed') === true || $container.data('already-reviewed') === 'true';
                const reviewMsg = $container.data('review-msg');
                const val = $(this).data('value');

                if (alreadyReviewed) {
                    if (typeof toastr !== 'undefined') toastr.info(reviewMsg);
                    return;
                }

                // Set radio and update modal stars
                const radio = document.getElementById('modalStar' + val);
                if (radio) {
                    radio.checked = true;
                    ProductReview.updateModalStars(val);
                }

                const modalEl = document.getElementById('productReviewModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.show();
                }
            });

            // Modal star click
            $(document).on('click', '.modal-star-icon', function () {
                ProductReview.updateModalStars($(this).data('value'));
            });
        }

        static updateModalStars(val) {
            $('.modal-star-icon').each(function () {
                const isActive = $(this).data('value') <= val;
                $(this).removeClass('text-gray-700 text-warning');
                $(this).addClass(isActive ? 'text-warning' : 'text-gray-700');
            });
        }
    }

    function initializeLicenseTypes() {
        // --- Generic Support Checkbox Sync Handler ---
        $(document).on('change', '[class*="-support-checkbox-"] input[type="checkbox"]', function () {
            const $this = $(this);
            const isExtended = $this.closest('[class*="-support-checkbox-extended"]').length > 0;
            const style = $this.closest('[class*="-price-card"]').attr('class')?.match(/style-\d/)?.[0] || 'modern';

            // Find the active container/pane
            let $pane = $this.closest('.tab-pane, .s2-pane, .s3-pane, .modern-price-card');
            if (!$pane.length) $pane = $this.closest('.widget-product-price-card');

            const $checkboxes = $pane.find('input[type="checkbox"]');
            const defaultSupportId = $pane.find('.add-to-cart-form').attr('data-default-support') || '';

            if (this.checked) {
                $checkboxes.not(this).prop('checked', false);
                const val = this.value;
                $pane.find('input[name="support"]').val(val);

                // Also update by IDs just in case for older partials
                const prefix = isExtended ? 'extended' : 'regular';
                const idSelector = `#${prefix}AddCartSupport, #${prefix}BuyNowSupportInput, #s2${prefix.charAt(0).toUpperCase() + prefix.slice(1)}AddCartSupport, #s2${prefix.charAt(0).toUpperCase() + prefix.slice(1)}BuyNowSupport, #s3${prefix.charAt(0).toUpperCase() + prefix.slice(1)}AddCartSupport, #s3${prefix.charAt(0).toUpperCase() + prefix.slice(1)}BuyNowSupport`;
                $pane.find(idSelector).val(val);
            } else {
                $pane.find('input[name="support"]').val(defaultSupportId);
            }
        });

        // --- Style 2 Layout Dropdown Logic ---
        $(document).on('click', '.s2-license-option', function (e) {
            e.preventDefault();
            const $this = $(this);
            const $container = $this.closest('.style-2-price-card');
            const targetPane = $this.data('target');
            const name = $this.data('name');
            const price = $this.data('price');
            const oldPrice = $this.data('old-price');

            // Update Dropdown Header
            $container.find('#s2ActiveLicenseName').text(name);
            $container.find('#s2ActivePrice').html(price);
            if (oldPrice) {
                $container.find('#s2ActiveOldPrice').html(oldPrice).removeClass('d-none');
            } else {
                $container.find('#s2ActiveOldPrice').addClass('d-none');
            }

            // Update dropdown states
            $container.find('.s2-license-option').removeClass('active bg-light');
            $container.find('.s2-license-option .s2-selected-badge').addClass('d-none');

            $this.addClass('active bg-light');
            $this.find('.s2-selected-badge').removeClass('d-none');

            // Toggle Panes
            $container.find('.s2-pane').addClass('d-none');
            $container.find(targetPane).removeClass('d-none');
        });

        // --- Style 3 Layout Tab Logic ---
        $(document).on('click', '.s3-tab-btn', function (e) {
            e.preventDefault();
            const $this = $(this);
            const $container = $this.closest('.style-3-price-card');
            const targetPane = $this.data('target');

            // Toggle active button styles
            $container.find('.s3-tab-btn').removeClass('active bg-white shadow-sm fw-semibold border').css('color', '#555').css('box-shadow', 'none').css('outline', 'none');
            $this.addClass('active bg-white shadow-sm fw-semibold border').css('color', '#000').css('box-shadow', 'none').css('outline', 'none');

            // Toggle Panes
            $container.find('.s3-pane').addClass('d-none');
            $container.find(targetPane).removeClass('d-none');

            // Remove focus state explicitly
            $this.blur();
        });
    }

    // ===== PRODUCT DETAILS WIDGET TOGGLE =====
    $(document).on('click', '.widget-product-details-card .widget-attribute-btn', function () {
        const $btn = $(this);
        const $card = $btn.closest('.widget-product-details-card');
        const $more = $card.find('.widget-attribute-more');
        const $text = $btn.find('.widget-attribute-btn-text');
        const $icon = $btn.find('i');

        // Handle transiting from initial d-none state to jQuery animation
        if ($more.hasClass('d-none')) {
            $more.removeClass('d-none').hide();
        }

        const isVisible = $more.is(':visible');

        if (isVisible) {
            $more.slideUp(300);
            $text.text($btn.data('text-more'));
            if ($icon.length) {
                $icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
            }
        } else {
            $more.slideDown(300);
            $text.text($btn.data('text-less'));
            if ($icon.length) {
                $icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
            }
        }
    });

    function initializeModernPriceCard() {
        // License toggle (Regular <-> Extended)
        $(document).on('click', '[data-license-toggle]', function () {
            const target = $(this).data('license-toggle');
            if (target === 'extended') {
                $('#modern-regular-pane').addClass('d-none');
                $('#modern-extended-pane').removeClass('d-none');
            } else {
                $('#modern-extended-pane').addClass('d-none');
                $('#modern-regular-pane').removeClass('d-none');
            }
        });

        // Feature box toggle
        $(document).on('click', '.product-feature-btn', function () {
            $(this).closest('.list-product').next('.product-features-box').toggleClass('d-none');
            const $chevron = $(this).find('.feature-chevron');
            $chevron.toggleClass('bi-chevron-down bi-chevron-up');
        });
    }


    // Livewire events - reinitialize after Livewire updates
    document.addEventListener('livewire:load', () => LoginModalHandler.init());
    document.addEventListener('livewire:update', () => LoginModalHandler.init());

})(jQuery);

