(function ($) {
	"use strict";

	const hasLower = /[a-z]/;
	const hasUpper = /[A-Z]/;
	const hasNumber = /[0-9]/;
	const hasSymbol = /[!@#$%^&*()_+\-=\[\]{};':"\|,.<>\/?~`]/;

	function checkPasswordStrength(password) {
		let strength = 0;
		const length = password.length;

		if (length >= 8) strength++;
		if (length >= 12) strength++;

		let charTypes = 0;
		if (hasLower.test(password)) charTypes++;
		if (hasUpper.test(password)) charTypes++;
		if (hasNumber.test(password)) charTypes++;
		if (hasSymbol.test(password)) charTypes++;

		if (charTypes >= 3) strength++;
		if (charTypes === 4) strength++;

		let strengthText = '';
		let progressBarWidth = 0;
		let progressBarClass = 'bg-danger';

		if (length === 0) {
			strengthText = '';
			progressBarWidth = 0;
			progressBarClass = 'bg-secondary';
		} else if (strength <= 1) {
			strengthText = 'Very Weak';
			progressBarWidth = 25;
			progressBarClass = 'bg-danger';
		} else if (strength === 2) {
			strengthText = 'Weak';
			progressBarWidth = 50;
			progressBarClass = 'bg-warning';
		} else if (strength === 3) {
			strengthText = 'Medium';
			progressBarWidth = 75;
			progressBarClass = 'bg-info';
		} else {
			strengthText = 'Strong';
			progressBarWidth = 100;
			progressBarClass = 'bg-success';
		}

		const $strengthBar = $('#passwordStrengthBar');
		const $strengthText = $('#passwordStrengthText');

		$strengthBar
			.css('width', `${progressBarWidth}%`)
			.removeClass('bg-danger bg-warning bg-info bg-success bg-secondary')
			.addClass(progressBarClass)
			.attr('aria-valuenow', progressBarWidth);

		$strengthText
			.text(strengthText)
			.removeClass('text-muted text-danger text-warning text-info text-success')
			.addClass(length === 0 ? 'text-muted' : progressBarClass.replace('bg-', 'text-'));
	}

	function checkConfirmPasswordMatch() {
		const $passwordInput = $('#registerPasswordInput');
		const $confirmInput = $('#registerConfirmPasswordInput');
		const $matchText = $('#passwordMatchText');

		if (!$confirmInput.length) return;

		const password = $passwordInput.val() || '';
		const confirmPassword = $confirmInput.val() || '';

		if (confirmPassword.length === 0) {
			$matchText.text('').removeClass('text-success text-danger').addClass('text-muted');
			return;
		}

		if (password === confirmPassword) {
			$matchText.text('Passwords match').removeClass('text-danger text-muted').addClass('text-success');
		} else {
			$matchText.text('Passwords do not match').removeClass('text-success text-muted').addClass('text-danger');
		}
	}



	$(document).ready(() => {

		if ($("[data-aos]").length > 0) {
			const aosFunc = () => AOS.init({ once: true });
			window.addEventListener("load", aosFunc);
			window.addEventListener("scroll", aosFunc);
		}

		initializeLiveSearch();
		initializeMobileSearch();
		initializeDropdowns();
		initializeToggles();
		initializeAnnouncement();
		initializeSwipers();
		initializePreview();
		initializeCookies();
		initializeCart();
		initializeFavorites();
		initializeSearchFilters();
		initializePriceSearch();
		initializeTicketAttachments();
		initializeVideoPlayers();
		initializeAudioPlayers();
		initializeAttributeToggle();
		initializePaymentMethodInfo();
		initializeNavSearch();
		initializeCustomTooltips();
		initializeCountdowns();
		initializeCustomSorting();
		initializeBankwire();
		initializeCheckout();
	});

	function initializeCustomSorting() {
		$(document).on('change', '.custom-sort-select', function () {
			const url = $(this).val();
			if (url) window.location.href = url;
		});
	}

	function initializeBankwire() {
		$(document).on('change', '#payment_proof', function () {
			const input = this;
			const $label = $('#file-label');
			if (input.files && input.files.length > 0) {
				const fileName = input.files[0].name;
				$label.html(`<span class="text-success fw-bold"><i class="bi bi-file-earmark-check me-1"></i>${fileName}</span>`);
			} else {
				const placeholder = $label.data('placeholder') || 'Click to select or drag and drop your file here';
				$label.text(placeholder);
			}
		});
	}

	function initializeCheckout() {
		$(document).on('click', '.checkout-button', function (e) {
			const $checkedPaymentMethod = $('input[name="payment_method"]:checked');
			if ($checkedPaymentMethod.val() === "balance") {
				const confirmMsg = config.translates?.actionConfirm || 'Are you sure you want to proceed?';
				if (!confirm(confirmMsg)) {
					e.preventDefault();
				}
			}
		});
	}

	function initializeCountdowns() {
		const countdowns = document.querySelectorAll('[data-countdown]');
		if (!countdowns.length) return;

		countdowns.forEach(container => {
			const targetDateStr = container.dataset.countdown;
			if (!targetDateStr) return;

			const targetDate = new Date(targetDateStr).getTime();

			// Initial update
			update(container, targetDate);

			// Interval
			const interval = setInterval(() => {
				const shouldContinue = update(container, targetDate);
				if (!shouldContinue) clearInterval(interval);
			}, 1000);
		});

		function update(container, targetDate) {
			const now = new Date().getTime();
			const distance = targetDate - now;

			if (distance < 0) {
				container.innerHTML = `<div class="h4 mb-0 text-muted w-100 text-center">${config.translates?.expired || 'Expired'}</div>`;
				return false;
			}

			const days = Math.floor(distance / (1000 * 60 * 60 * 24));
			const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			const seconds = Math.floor((distance % (1000 * 60)) / 1000);

			updateElement(container, '[data-days]', days);
			updateElement(container, '[data-hours]', hours);
			updateElement(container, '[data-minutes]', minutes);
			updateElement(container, '[data-seconds]', seconds);

			return true;
		}

		function updateElement(container, selector, value) {
			const el = container.querySelector(selector);
			if (el) el.innerText = value < 10 ? '0' + value : value;
		}
	}

	function initializeLiveSearch() {
		$('.live-search-component').each(function () {
			const $wrapper = $(this);
			const $searchInput = $wrapper.find('.live-search-input');
			const $searchResults = $wrapper.find('.live-search-results');
			const $clearButton = $wrapper.find('.clear-search-button');
			const $backdrop = $wrapper.siblings('.search-backdrop');
			let searchTimer;

			const showBackdrop = () => {
				$backdrop.removeClass('d-none');
				setTimeout(() => $backdrop.addClass('active'), 10);
			};

			const hideBackdrop = () => {
				$backdrop.removeClass('active');
				setTimeout(() => $backdrop.addClass('d-none'), 200);
			};

			const toggleClearButton = () => {
				if (($searchInput.val() || '').length > 0) {
					$clearButton.removeClass('d-none').stop(true, true).animate({ opacity: 1 }, 100);
				} else {
					$clearButton.stop(true, true).animate({ opacity: 0 }, 100, function () {
						$(this).addClass('d-none');
					});
				}
			};

			toggleClearButton();
			$searchInput.on('input', toggleClearButton);

			$clearButton.on('click', () => {
				$searchInput.val('');
				$searchResults.removeClass('show').addClass('d-none');
				toggleClearButton();
				hideBackdrop();
			});

			const performLiveSearch = () => {
				const query = $searchInput.val();
				if (query.length < 2) {
					$searchResults.empty().removeClass('show').addClass('d-none');
					hideBackdrop();
					return;
				}

				if (!window.Laravel?.routes?.liveSearch) return;

				$.ajax({
					url: window.Laravel.routes.liveSearch,
					method: 'GET',
					data: { query },
					beforeSend: () => {
						$searchResults.html('<div class="p-3 text-center"><span class="spinner-border spinner-border-sm text-primary" role="status"></span><span class="text-muted ms-2">Searching...</span></div>').removeClass('d-none').addClass('show');
						showBackdrop();
					},
					success: res => {
						$searchResults.html(res);
						if (res.trim() === '') {
							$searchResults.removeClass('show').addClass('d-none');
							hideBackdrop();
						} else {
							$searchResults.removeClass('d-none').addClass('show');
							showBackdrop();
						}
					},
					error: () => {
						$searchResults.html('<div class="p-3 text-danger text-center">Error loading results.</div>').removeClass('d-none').addClass('show');
						showBackdrop();
					}
				});
			};

			$searchInput.on('keyup', e => {
				if (e.which === 13) {
					e.preventDefault();
					if ($searchInput.val().length >= 2) {
						window.location.href = `${window.Laravel.routes.productIndex}?query=${encodeURIComponent($searchInput.val())}`;
					}
				} else {
					clearTimeout(searchTimer);
					searchTimer = setTimeout(performLiveSearch, 300);
				}
			});

			$searchInput.on('focus', showBackdrop);

			$(document).on('click', e => {
				if (!$(e.target).closest($wrapper).length && !$(e.target).closest($backdrop).length) {
					$searchResults.removeClass('show').addClass('d-none');
					hideBackdrop();
				}
			});

			$backdrop.on('click', () => {
				$searchResults.removeClass('show').addClass('d-none');
				hideBackdrop();
				$searchInput.blur();
			});
		});
	}

	function initializeMobileSearch() {
		const $mobileBtn = $('#mobileSearchBtn');
		const $mobileBox = $('#mobileSearchBox');
		const $mobileInput = $mobileBox.find('.live-search-input');
		const $mobileBackdrop = $mobileBox.find('.search-backdrop');

		const showMobileBackdrop = () => $mobileBackdrop.removeClass('d-none').stop(true, true).fadeIn(150);
		const hideMobileBackdrop = () => $mobileBackdrop.stop(true, true).fadeOut(150, function () {
			$mobileBackdrop.addClass('d-none');
		});

		$mobileBtn.on('click', e => {
			e.stopPropagation();
			if ($mobileBox.is(':visible')) {
				$mobileBox.slideUp(200, () => $mobileBox.addClass('d-none'));
				hideMobileBackdrop();
			} else {
				$mobileBox.removeClass('d-none').hide().slideDown(200);
				showMobileBackdrop();
				$mobileInput.focus();
			}
		});

		$(document).on('click', e => {
			if (!$(e.target).closest($mobileBox).length && !$(e.target).closest($mobileBtn).length) {
				if ($mobileBox.is(':visible')) {
					$mobileBox.slideUp(200, () => $mobileBox.addClass('d-none'));
					hideMobileBackdrop();
				}
			}
		});

		$mobileBackdrop.on('click', () => {
			$mobileBox.slideUp(200, () => $mobileBox.addClass('d-none'));
			hideMobileBackdrop();
		});

		$mobileInput.on('click', e => e.stopPropagation());
	}

	function initializeDropdowns() {
		const dropdowns = document.querySelectorAll("[data-dropdown]");

		dropdowns.forEach(dropdown => {
			const dropdownMenu = dropdown.querySelector(".drop-down-menu");
			if (!dropdownMenu) return;

			const adjustPosition = () => {
				const rect = dropdown.getBoundingClientRect();
				const shouldPositionTop = rect.top + dropdownMenu.offsetHeight > window.innerHeight - 60
					&& dropdown.getAttribute("data-dropdown-position") !== "top";

				if (shouldPositionTop) {
					dropdownMenu.style.top = "auto";
					dropdownMenu.style.bottom = "40px";
				} else {
					dropdownMenu.style.top = "40px";
					dropdownMenu.style.bottom = "auto";
				}
			};

			window.addEventListener("click", e => {
				if (dropdown.contains(e.target)) {
					dropdown.classList.toggle("active");
					setTimeout(() => dropdown.classList.toggle("animated"), 0);
				} else {
					dropdown.classList.remove("active", "animated");
				}
				adjustPosition();
			});

			["resize", "scroll"].forEach(event => {
				window.addEventListener(event, adjustPosition);
			});
		});
	}

	function initializeToggles() {
		const toggles = document.querySelectorAll('[data-toggle]');

		toggles.forEach((toggle, index) => {
			const toggleTitle = toggle.querySelector(".toggle-title");
			if (!toggleTitle) return;

			toggleTitle.addEventListener("click", () => {
				toggles.forEach((otherToggle, otherIndex) => {
					if (otherIndex !== index) {
						otherToggle.classList.remove("active", "animated");
					}
				});

				if (toggle.classList.contains("active")) {
					toggle.classList.remove("active", "animated");
				} else {
					toggle.classList.add("active");
					setTimeout(() => toggle.classList.add("animated"), 0);
				}
			});
		});
	}

	function initializeAnnouncement() {
		const announcement = document.querySelector(".announcement");
		const announcementClose = document.querySelector(".announcement-close");

		if (!announcement || !announcementClose) return;

		announcementClose.addEventListener("click", () => {
			announcement.remove();
			window.EzyDev.setCookie("announce_close", "true", 1);
		});

		if (window.EzyDev.getCookie("announce_close") === "true") {
			announcement.remove();
		}
	}

	function initializeSwipers() {
		initializeCategoriesSwiper();
		initializeProductsSwipers();
		initializeTestimonialsSwiper();
	}

	function initializeCategoriesSwiper() {
		if (typeof Swiper === 'undefined') return;

		document.querySelectorAll(".categories-swiper").forEach(function (container) {
			var swiperEl = container.querySelector(".swiper");
			if (!swiperEl) return;

			// Prevent double initialization
			if (swiperEl.classList.contains('swiper-initialized')) return;

			var nextBtn = container.querySelector(".swiper-button-next");
			var prevBtn = container.querySelector(".swiper-button-prev");

			var swiper = new Swiper(swiperEl, {
				slidesPerView: "auto",
				spaceBetween: 12,
				observer: true,
				observeParents: true,
				watchSlidesProgress: true,
				freeMode: {
					enabled: true,
				},
				pagination: {
					el: container.querySelector('.swiper-pagination'),
					clickable: true,
					dynamicBullets: true,
				},
				navigation: {
					nextEl: nextBtn,
					prevEl: prevBtn,
				},
			});

			// Hide nav buttons if all slides fit on screen
			function toggleNav() {
				var hide = swiper.isBeginning && swiper.isEnd;
				if (nextBtn) nextBtn.style.display = hide ? 'none' : '';
				if (prevBtn) prevBtn.style.display = hide ? 'none' : '';
			}
			toggleNav();
			swiper.on('resize', toggleNav);
		});
	}

	function initializeProductsSwipers() {
		const productsSwipers = document.querySelectorAll(".products-swiper");

		productsSwipers.forEach(swiperContainer => {
			const slidesPerViewLarge = parseInt(swiperContainer.getAttribute('data-slide')) || 4;

			new Swiper(swiperContainer.querySelector(".swiper"), {
				slidesPerView: 1,
				autoplay: false,
				spaceBetween: 20,
				autoHeight: true,
				pagination: {
					el: swiperContainer.querySelector('.swiper-pagination'),
					clickable: true,
					dynamicBullets: true,
				},
				navigation: {
					nextEl: swiperContainer.querySelector(".swiper-button-next"),
					prevEl: swiperContainer.querySelector(".swiper-button-prev"),
				},
				breakpoints: {
					768: { slidesPerView: 2 },
					992: { slidesPerView: 3 },
					1200: {
						slidesPerView: slidesPerViewLarge,
						pagination: { enabled: false }
					}
				},
			});
		});
	}

	function initializeTestimonialsSwiper() {
		const testimonialsSwiperContainer = document.querySelector(".testimonials-swiper");
		if (!testimonialsSwiperContainer) return;

		const swiperEl = testimonialsSwiperContainer.querySelector(".testimonialsSwiper");
		if (!swiperEl) return;

		const noAutoplay = swiperEl.hasAttribute('data-no-autoplay');

		const swiperInstance = new Swiper(swiperEl, {
			autoplay: noAutoplay ? false : { delay: 8000, disableOnInteraction: false },
			autoHeight: true,
			slidesPerView: "auto",
			spaceBetween: 20,
			observer: true,
			observeParents: true,
			navigation: {
				nextEl: testimonialsSwiperContainer.querySelector(".swiper-button-next"),
				prevEl: testimonialsSwiperContainer.querySelector(".swiper-button-prev"),
			},
		});

		if (!noAutoplay) {
			testimonialsSwiperContainer.addEventListener("mouseenter", () => {
				swiperInstance.autoplay.stop();
			});

			testimonialsSwiperContainer.addEventListener("mouseleave", () => {
				swiperInstance.autoplay.start();
			});
		}
	}

	function initializePreview() {
		if (!$('.preview-nav-action').length) return;

		$(document).on('click', '.preview-nav-action', function(e) {
			e.preventDefault();
			const $el = $(this);

			$('.preview-nav-action').removeClass('active');
			$el.addClass('active');

			const $body = $('.preview-body');
			if ($el.hasClass('preview-desktop')) {
				$body.removeClass('tablet mobile');
			} else if ($el.hasClass('preview-tablet')) {
				$body.removeClass('mobile').addClass('tablet');
			} else if ($el.hasClass('preview-mobile')) {
				$body.removeClass('tablet').addClass('mobile');
			}
		});
	}
	function initializeCookies() {
		const cookies = document.querySelector('.cookies');
		if (!cookies) return;

		window.addEventListener('load', () => {
			setTimeout(() => cookies.classList.add('show'), 1000);
		});

		const $acceptCookie = $('#acceptCookie');
		const $cookieNotice = $('.cookies');

		$acceptCookie.on('click', e => {
			e.preventDefault();
			$.ajax({
				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
				url: `${config.url}/cookie/accept`,
				type: 'POST',
			});
			$cookieNotice.remove();
		});
	}


	function initializeCart() {
		const updateCartCounter = (count) => {
			const $cartCounter = $('.cart-counter');

			if ($cartCounter.length) {
				$cartCounter.each(function () {
					$(this).removeClass('d-none');
					if (count !== undefined) {
						$(this).text(count >= 99 ? '+99' : count);
					} else {
						const currentCount = parseInt($(this).text().trim());
						const newCount = !isNaN(currentCount) ? (currentCount >= 99 ? '+99' : currentCount + 1) : 1;
						$(this).text(newCount);
					}
				});
			} else {
				$('.cart-btn').append('<span class="cart-counter notification-badge bg-success text-white rounded fw-500 text-xsmall">1</span>');
			}
		};

		const refreshCartOffcanvas = () => {
			const $offcanvas = $('[id^="offcanvasCart"]');
			if (!$offcanvas.length) return;
			const offcanvasId = $offcanvas.attr('id');

			$.ajax({
				url: window.location.href,
				type: 'GET',
				dataType: 'html',
				success: (responseHtml) => {
					const parser = new DOMParser();
					const doc = parser.parseFromString(responseHtml, 'text/html');
					const newOffcanvas = doc.getElementById(offcanvasId);

					if (newOffcanvas) {
						$offcanvas[0].innerHTML = newOffcanvas.innerHTML;
					}

					// Update counter from fresh page
					const newCounter = doc.querySelector('.cart-counter');
					if (newCounter) {
						const freshCount = parseInt(newCounter.textContent.trim());
						if (!isNaN(freshCount)) {
							updateCartCounter(freshCount);
						}
					}
				}
			});
		};

		const $addToCartForms = $('.add-to-cart-form');
		$addToCartForms.on('submit', function (e) {
			e.preventDefault();
			const $form = $(this);
			const action = $form.data('action');
			const formData = $form.serializeArray();

			$.ajax({
				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
				url: action,
				type: "POST",
				data: formData,
				dataType: 'json',
				beforeSend: () => $form.find('button').prop('disabled', true),
				success: response => {
					$form.find('button').prop('disabled', false);
					if ($.isEmptyObject(response.error)) {
						toastr.success(response.success);
						updateCartCounter();
						refreshCartOffcanvas();
					} else {
						toastr.error(response.error);
					}
				},
				error: (jqXHR, textStatus, errorThrown) => {
					$form.find('button').prop('disabled', false);
					toastr.error(errorThrown);
				}
			});
		});
	}

	function initializeFavorites() {
		const updateFavoritesCounter = (count) => {
			const $favoritesCounter = $('.favorites-counter');

			if ($favoritesCounter.length) {
				$favoritesCounter.each(function () {
					const $counter = $(this);
					if (count !== undefined) {
						if (count <= 0) {
							$counter.addClass('d-none');
						} else {
							$counter.removeClass('d-none');
							$counter.text(count >= 99 ? '+99' : count);
						}
					} else {
						// Fallback: simple increment if no count provided
						const currentText = $counter.text().trim();
						let currentCount = parseInt(currentText);
						if (isNaN(currentCount)) currentCount = 0;

						const newCount = currentCount + 1;
						$counter.removeClass('d-none');
						$counter.text(newCount >= 99 ? '+99' : newCount);
					}
				});
			}
		};

		// Listen for Livewire event
		window.addEventListener('favorites-updated', event => {
			if (event.detail && typeof event.detail.count !== 'undefined') {
				updateFavoritesCounter(event.detail.count);
			} else {
				updateFavoritesCounter();
			}
		});
	}

	function initializeSearchFilters() {
		const $searchFiltersMenu = $('#searchFiltersMenu');
		const $applyBtn = $('#btnApplyFilters');
		const $resetBtn = $('#btnResetFilters');
		const currentUrl = new URL(location.href);
		const params = new URLSearchParams(currentUrl.search);

		if (!$searchFiltersMenu.length) return;

		// Handle Reset Filters
		$resetBtn.on('click', function() {
			window.location.href = window.location.pathname;
		});

		// Set initial states based on URL parameters
		$searchFiltersMenu.find('.filter-input').each(function () {
			const $input = $(this);
			const name = $input.attr('name');
			const value = $input.val();

			if (params.has(name)) {
				const paramValues = params.getAll(name);
				if (paramValues.includes(value)) {
					$input.prop('checked', true);
				}
			}
		});

		// Handle radio button groups behavior (uncheck others in same group)
		$searchFiltersMenu.on('change', 'input[type="radio"].filter-input', function() {
			const name = $(this).attr('name');
			$searchFiltersMenu.find(`input[name="${name}"].filter-input`).not(this).prop('checked', false);
		});

		// Manual Apply Logic
		$applyBtn.on('click', function(e) {
			e.preventDefault();
			let url = new URL(location.pathname, location.origin);
			const newParams = new URLSearchParams();

			// 1. Collect Filter Inputs (Checkboxes & Radios)
			$searchFiltersMenu.find('.filter-input:checked').each(function() {
				const name = $(this).attr('name');
				const value = $(this).val();
				newParams.append(name, value);
			});

			// 2. Collect Price Inputs
			const minPrice = $('#priceForm').val();
			const maxPrice = $('#priceTo').val();
			if (minPrice) newParams.set('min_price', minPrice);
			if (maxPrice) newParams.set('max_price', maxPrice);

			// 3. Preserve other important params (like sort_by)
			params.forEach((val, key) => {
				const filterNames = ['min_price', 'max_price', 'stars', 'date', 'free', 'premium', 'on_sale', 'best_selling', 'trending', 'featured'];
				// Also include dynamic category options which we don't know the exact names of,
				// but they are in the offcanvas, so we only skip what's NOT in our filter-input names.
				if (!newParams.has(key) && !filterNames.includes(key)) {
					// Check if it's a dynamic slug from the offcanvas
					const isDynamicFilter = $searchFiltersMenu.find(`.filter-input[name^="${key}"]`).length > 0;
					if (!isDynamicFilter) {
						newParams.append(key, val);
					}
				}
			});

			const queryString = newParams.toString();
			window.location.href = url.pathname + (queryString ? '?' + queryString : '');
		});
	}


	function initializePriceSearch() {
		const $searchByPrice = $('#searchByPrice');

		$searchByPrice.on('click', e => {
			e.preventDefault();
			let url = new URL(location.href);
			const $priceForm = $('#priceForm');
			const $priceTo = $('#priceTo');

			[$priceForm, $priceTo].forEach($field => {
				const fieldName = $field.attr('name');
				const fieldValue = $field.val();

				url = removeParameterFromUrl(url, fieldName);
				if (fieldValue !== '') {
					url = addParameterToUrl(url, fieldName, fieldValue);
				}
			});

			window.location.href = url;
		});
	}

	function initializeTicketAttachments() {
		let attachmentCount = 1;
		const $attachments = $('.attachments');
		const $addAttachment = $('#addAttachment');

		if (!$addAttachment.length) return;

		$addAttachment.on('click', e => {
			e.preventDefault();

			if (attachmentCount < (ticketsConfig?.max_file || 5)) {
				attachmentCount++;
				const attachmentHtml = `
					<div class="attachment-box-${attachmentCount} mt-3">
						<div class="input-group">
							<input type="file" name="attachments[]" class="form-control form-control-md">
							<button class="btn btn-danger attachment-remove" data-id="${attachmentCount}" type="button" title="Remove">
								<i class="bi bi-trash"></i>
							</button>
						</div>
					</div>`;
				$attachments.append(attachmentHtml);
			} else {
				toastr.error(ticketsConfig?.max_files_error || 'Maximum file limit reached');
			}
		});

		$(document).on('click', '.attachment-remove', function () {
			const id = $(this).data("id");
			attachmentCount--;
			$(`.attachment-box-${id}`).remove();
		});
	}

	function initializeVideoPlayers() {
		const productVideos = document.querySelectorAll(".product-video");

		productVideos.forEach(videoContainer => {
			if (videoContainer.dataset.initialized) return;
			videoContainer.dataset.initialized = "true";

			const video = videoContainer.querySelector("video");
			const volumeBtn = videoContainer.querySelector(".product-video-volume");
			const fullBtn = videoContainer.querySelector(".product-video-full");
			const videoProgress = videoContainer.querySelector(".product-video-progress");

			if (!video) return;

			// Set initial mute state
			videoContainer.classList.toggle("muted", video.muted);

			// Video controls
			videoContainer.addEventListener("mouseenter", () => {
				video.play();
				videoContainer.classList.add("playing");
			});

			videoContainer.addEventListener("mouseleave", () => {
				video.pause();
				videoContainer.classList.remove("playing");
				setTimeout(() => {
					video.currentTime = 0;
					video.load();
				}, 0);
			});

			video.addEventListener("timeupdate", () => {
				const progress = Math.ceil(video.currentTime / video.duration * 100);
				videoProgress?.style.setProperty("width", `${progress}%`);
			});

			volumeBtn?.addEventListener("click", e => {
				e.preventDefault();
				e.stopPropagation();

				productVideos.forEach(container => {
					const containerVideo = container.querySelector("video");
					containerVideo.muted = !containerVideo.muted;
					container.classList.toggle("muted", containerVideo.muted);
				});
			});

			fullBtn?.addEventListener("click", e => {
				e.preventDefault();
				e.stopPropagation();

				const requestFullscreen = video.requestFullscreen ||
					video.webkitRequestFullscreen ||
					video.msRequestFullscreen;
				requestFullscreen?.call(video);
			});
		});

		const plyrPlayers = document.querySelectorAll(".video-plyr");
		plyrPlayers.forEach(player => new Plyr(player));
	}

	function initializeAudioPlayers() {
		const audioPlayers = document.querySelectorAll(".product-audio-wave");

		audioPlayers.forEach(playerContainer => {
			if (playerContainer.dataset.initialized) return;
			playerContainer.dataset.initialized = "true";

			const waveForm = playerContainer.querySelector(".waveform");
			const playButton = playerContainer.querySelector(".play-button");
			const pauseButton = playerContainer.querySelector(".pause-button");
			const totalDuration = playerContainer.querySelector(".total-duration");
			const currentTime = playerContainer.querySelector(".current-time");

			if (!waveForm) return;

			const waveColor = window.EzyDev.applyOpacityToHex(config.colors.primary_color, 0.4);

			const wavesurfer = WaveSurfer.create({
				container: waveForm,
				responsive: true,
				waveColor: waveColor,
				progressColor: config.colors.primary_color,
				cursorColor: "transparent",
				height: waveForm.getAttribute("data-waveheight") || 50,
				hideScrollbar: true,
				barWidth: 2,
				barMinHeight: 1,
				barHeight: 1,
				barGap: 2,
				barRadius: 3
			});

			const formatTimeCode = seconds => new Date(seconds * 1000).toISOString().slice(14, 19);

			const play = () => {
				document.querySelectorAll(".pause-button").forEach(btn => btn.click());
				wavesurfer.play();
				playButton?.classList.add("d-none");
				pauseButton?.classList.remove("d-none");
			};

			const pause = () => {
				wavesurfer.pause();
				pauseButton?.classList.add("d-none");
				playButton?.classList.remove("d-none");
			};

			wavesurfer.load(waveForm.getAttribute("data-url"));

			playButton?.addEventListener("click", play);
			pauseButton?.addEventListener("click", pause);

			wavesurfer.on("ready", () => {
				if (totalDuration) {
					totalDuration.textContent = formatTimeCode(wavesurfer.getDuration());
				}
			});

			wavesurfer.on("audioprocess", () => {
				if (currentTime) {
					currentTime.innerHTML = formatTimeCode(wavesurfer.getCurrentTime());
				}
			});

			wavesurfer.on("finish", () => {
				pauseButton?.classList.add("d-none");
				playButton?.classList.remove("d-none");
			});
		});
	}

	function initializeAttributeToggle() {
		const $dpAttrMore = $('.dp-attribute-more');
		const $dpAttrBtn = $('.dp-attribute-btn');
		const $dpAttrBtnText = $dpAttrBtn.find('.dp-attribute-btn-text');
		const $dpAttrIcon = $dpAttrBtn.find('.bi-chevron-down');

		if (!$dpAttrBtn.length) return;

		const toggleAttributes = open => {
			if (open) {
				$dpAttrMore.stop(true, true).slideDown(200);
				$dpAttrBtnText.text('Show less');
				$dpAttrIcon.addClass('rotate');
			} else {
				$dpAttrMore.stop(true, true).slideUp(200);
				$dpAttrBtnText.text('Show more');
				$dpAttrIcon.removeClass('rotate');
			}
			$dpAttrBtn.data('open', open).attr('aria-expanded', open);
		};

		$dpAttrBtn.on('click', e => {
			e.stopPropagation();
			const isOpen = !!$dpAttrBtn.data('open');
			toggleAttributes(!isOpen);
		});

		$dpAttrMore.on('click', e => e.stopPropagation());
		$(document).on('click', () => {
			if ($dpAttrBtn.data('open')) toggleAttributes(false);
		});

		toggleAttributes(false);
	}

	function initializePaymentMethodInfo() {
		const $paymentMethodInfo = $(".payment-title .payment-method-info");
		const $paymentMethodBtn = $(".payment-title .payment-method-info-btn");

		$paymentMethodBtn.on("click", e => {
			e.stopPropagation();
			$paymentMethodInfo.toggle();
		});

		$paymentMethodInfo.on("click", e => e.stopPropagation());
		$(document).on("click", () => $paymentMethodInfo.hide());
	}



	function initializeNavSearch() {
		const $topNavSearch = $('#topNavSearch');
		const scrollThreshold = 420;

		const isHomePage = () => $('body').hasClass('home-page');

		const toggleTopNavSearchVisibility = show => {
			if (show) {
				$topNavSearch.css({ 'display': 'block', 'opacity': 0 });
				setTimeout(() => $topNavSearch.css('opacity', 1), 10);
			} else {
				$topNavSearch.css('opacity', 0);
				setTimeout(() => $topNavSearch.css('display', 'none'), 300);
			}
		};

		const handleTopNavSearchVisibility = () => {
			if (isHomePage()) {
				const currentScroll = $(window).scrollTop();
				const shouldShow = currentScroll >= scrollThreshold;

				$topNavSearch.toggleClass('top-nav-search-visible-xl', shouldShow);
				toggleTopNavSearchVisibility(shouldShow);
			}
		};

		$(window).on('scroll', handleTopNavSearchVisibility);
		handleTopNavSearchVisibility();
	}

	function initializeCustomTooltips() {
		const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');

		tooltipElements.forEach(element => {
			if (element._tooltip) return;

			const tooltip = new bootstrap.Tooltip(element, {
				html: true,
				placement: element.dataset.bsPlacement || 'top',
				trigger: 'manual',
				offset: [0, 0]
			});

			element._tooltip = tooltip;
			let hideTimeout;
			const leaveDelay = 150;

			const showTooltip = () => {
				clearTimeout(hideTimeout);
				tooltip.show();

				const popper = tooltip._popper;
				if (popper) {
					popper.setOptions({
						modifiers: [{
							name: 'offset',
							options: { offset: [0, 5] }
						}]
					});
				}
			};

			const startHideTimeout = () => {
				clearTimeout(hideTimeout);
				hideTimeout = setTimeout(() => {
					if (!element.matches(':hover') && !document.querySelector('.tooltip:hover')) {
						tooltip.hide();
					}
				}, leaveDelay);
			};

			element.addEventListener('mouseenter', showTooltip);
			element.addEventListener('click', () => {
				if (window.matchMedia('(hover: none)').matches) {
					tooltip.toggle();
				}
			});
			element.addEventListener('mouseleave', startHideTimeout);

			document.addEventListener('mouseover', e => {
				if (e.target.closest('.tooltip')) {
					clearTimeout(hideTimeout);
				}
			});

			document.addEventListener('mouseout', e => {
				if (e.target.closest('.tooltip') && !e.relatedTarget?.closest('.tooltip')) {
					startHideTimeout();
				}
			});
		});
	}

	function initializeRatingStars() {
		const ratings = document.querySelectorAll(".ratings-selective");
		if (ratings) {
			ratings.forEach((el) => {
				const rating = el.querySelectorAll(".rating");
				rating.forEach((ratingEl, id) => {
					ratingEl.addEventListener("click", () => {
						ratingEl.querySelector("input[type=radio]").checked = true;
						rating.forEach((ratingActive, activeId) => {
							ratingActive.classList.remove("rating-active");
							if (id >= activeId) {
								ratingActive.classList.add("rating-active");
							}
						});
					});
				});
			});
		}
	}

	// Initialize period selector
	$(document).ready(() => {
		const $periodSelect = $('#period-select');
		$periodSelect.on('change', function () {
			location.href = $(this).val();
		});
	});


	/************* Start DOMContentLoaded function *******************/
	document.addEventListener('DOMContentLoaded', function () {
		const elements = {
			dropdowns: document.querySelectorAll('.nav-dropdown'),
			allNavLinks: document.querySelectorAll('.nav-swiper-container .nav-link'),
			dropdownPortal: document.querySelector('.dropdown-portal'),
			navContainer: document.querySelector('.container.container-custom'),
			navButtonsWrapper: document.querySelector('.nav-buttons-wrapper'),
			mobileOffcanvasElement: document.getElementById('mobileOffcanvas'),
			mobileDropdownTriggers: document.querySelectorAll('.mobile-dropdown-trigger'),
			loginModalForm: document.querySelector('.form-needs-login-modal'),
			viewGalleryBtns: document.querySelectorAll('.view-gallery-btn'),
			productShareBtn: document.getElementById('productShareBtn'),
			customDropdowns: document.querySelectorAll('.custom-dropdown'),
			videoControlBtn: document.getElementById('videoControlBtn'),
			videoControlIcon: document.getElementById('videoControlIcon'),
			headerVideo: document.getElementById('headerVideo'),
			currencyLinks: document.querySelectorAll('.multi-currency-title')
		};

		const constants = {
			DEFAULT_DROPDOWN_WIDTH: 220,
			MAX_CONTAINER_COLUMNS: 4,
			DROPDOWN_HIDE_DELAY: 200,
			SHOW_DROPDOWN_DELAY: 10,
			DROPDOWN_TRANSITION_TIME: 300,
			NAV_SCROLL_THRESHOLD: 150
		};

		// State variables
		let state = {
			currentDropdown: null,
			globalHoverTimeout: null,
			isPlaying: true
		};

		// Initialize Swiper
		if (typeof Swiper !== 'undefined') {
			initializeSwiper();
		}

		initializePreview();

		// Initialize all components
		initializeNavDropdowns();
		initializeMobileDropdowns();
		initializeLoginModal();
		initializeLicenseConfigs();
		initializeGallery();
		initializeProductShare();
		initializeCustomDropdowns();
		initializeVideoControl();
		initializeCurrencyLinks();
		initializeNavToggle();
		initializeClickOutsideHandler();
		initializeResizeHandler();
		initializeThemeToggle();
		initializeHeaderSearch();
		initializeStickyHeader();
		initializeMobileHeaderPadding();
		initializeDelegatedDropdowns();
		initializeGlobalLoadMore();

		function initializeSwiper() {
			const swiper = new Swiper('#bottom-navbar-swiper', {
				slidesPerView: 'auto',
				spaceBetween: 0,
				slidesOffsetAfter: 0,
				freeMode: true,
				navigation: {
					nextEl: '#nav-next',
					prevEl: '#nav-prev',
				},
				on: {
					init() {
						const swiperEl = document.getElementById('bottom-navbar-swiper');
						if (swiperEl && swiperEl.offsetParent !== null) {
							this.update();
						}
						checkAndTogglePointerEvents();
					},
					slideChangeTransitionEnd: checkAndTogglePointerEvents,
					resize: checkAndTogglePointerEvents
				},
			});
		}

		function isUnderNavButtons(element) {
			if (!elements.navButtonsWrapper) return false;

			const elementRect = element.getBoundingClientRect();
			const navButtonsRect = elements.navButtonsWrapper.getBoundingClientRect();

			return (elementRect.left < navButtonsRect.right && elementRect.right > navButtonsRect.left) &&
				(elementRect.top < navButtonsRect.bottom && elementRect.bottom > navButtonsRect.top);
		}

		function checkAndTogglePointerEvents() {
			elements.allNavLinks.forEach(link => {
				const slideParent = link.closest('.swiper-slide');
				if (!slideParent) return;

				const isUnder = isUnderNavButtons(link);
				slideParent.classList.toggle('under-nav-buttons', isUnder);

				if (isUnder && link.classList.contains('dropdown-trigger')) {
					const parentDropdown = link.closest('.nav-dropdown');
					if (parentDropdown && state.currentDropdown &&
						state.currentDropdown.id === parentDropdown.getAttribute('data-dropdown-id')) {
						hideDropdownLogic(parentDropdown);
					}
				}
			});
		}

		function hideDropdownLogic(targetDropdownContainer) {
			const trigger = targetDropdownContainer.querySelector('.dropdown-trigger');
			const dropdownContent = document.getElementById(targetDropdownContainer.getAttribute('data-dropdown-id'));

			if (state.currentDropdown === dropdownContent) {
				state.currentDropdown.classList.remove('show');
				trigger?.classList.remove('nav-link-active');

				setTimeout(() => {
					if (state.currentDropdown !== dropdownContent) {
						Object.assign(dropdownContent.style, {
							left: '',
							width: '',
							minWidth: ''
						});
					}
				}, constants.DROPDOWN_TRANSITION_TIME);

				state.currentDropdown = null;
			}

			clearHoverTimeout();
		}

		function scheduleHide(dropdown) {
			clearHoverTimeout();
			state.globalHoverTimeout = setTimeout(() => {
				hideDropdownLogic(dropdown);
				state.globalHoverTimeout = null;
			}, constants.DROPDOWN_HIDE_DELAY);
		}

		function clearHoverTimeout() {
			if (state.globalHoverTimeout) {
				clearTimeout(state.globalHoverTimeout);
				state.globalHoverTimeout = null;
			}
		}

		function calculateDropdownPosition(trigger, dropdownContent, navContainerRect) {
			const triggerRect = trigger.getBoundingClientRect();
			const numColumns = parseInt(dropdownContent.dataset.cols || '0');
			let targetWidth, targetLeft;

			if (numColumns > 0) {
				const computedStyle = getComputedStyle(elements.navContainer);
				const paddingLeft = parseFloat(computedStyle.paddingLeft);
				const paddingRight = parseFloat(computedStyle.paddingRight);
				const navContainerInnerWidth = navContainerRect.width - paddingLeft - paddingRight;

				targetWidth = ((navContainerInnerWidth / constants.MAX_CONTAINER_COLUMNS) * numColumns) - 150;
				targetLeft = triggerRect.left + (triggerRect.width / 2) - (targetWidth / 2);
			} else {
				targetWidth = constants.DEFAULT_DROPDOWN_WIDTH;
				targetLeft = triggerRect.left;
			}

			// Ensure dropdown stays within viewport
			targetLeft = Math.max(0, Math.min(targetLeft, window.innerWidth - targetWidth));

			return { targetWidth, targetLeft, triggerRect };
		}

		function showDropdown(trigger, dropdownContent) {
			const navContainerRect = elements.navContainer?.getBoundingClientRect();
			if (!navContainerRect) return;

			const { targetWidth, targetLeft, triggerRect } = calculateDropdownPosition(trigger, dropdownContent, navContainerRect);

			Object.assign(dropdownContent.style, {
				left: `${targetLeft}px`,
				width: `${targetWidth}px`,
				minWidth: `${targetWidth}px`,
				top: `${triggerRect.bottom + window.scrollY}px`
			});

			setTimeout(() => {
				dropdownContent.classList.add('show');
				trigger.classList.add('nav-link-active');
				state.currentDropdown = dropdownContent;
			}, constants.SHOW_DROPDOWN_DELAY);
		}

		function initializeNavDropdowns() {
			elements.dropdowns.forEach(dropdown => {
				const trigger = dropdown.querySelector('.dropdown-trigger');
				const dropdownId = dropdown.getAttribute('data-dropdown-id');
				const dropdownContent = document.getElementById(dropdownId);

				if (!trigger || !dropdownContent) return;

				trigger.addEventListener('mouseenter', function () {
					const slideParent = this.closest('.nav-swiper-slide');

					clearHoverTimeout();

					if (slideParent?.classList.contains('under-nav-buttons')) return;

					// Hide other dropdowns
					if (state.currentDropdown && state.currentDropdown !== dropdownContent) {
						const prevDropdownContainer = document.querySelector(`[data-dropdown-id="${state.currentDropdown.id}"]`);
						prevDropdownContainer && hideDropdownLogic(prevDropdownContainer);
					}

					showDropdown(trigger, dropdownContent);
				});

				trigger.addEventListener('mouseleave', function (e) {
					if (e.relatedTarget && (dropdownContent.contains(e.relatedTarget) || e.relatedTarget === dropdownContent)) {
						return;
					}
					scheduleHide(dropdown);
				});

				dropdownContent.addEventListener('mouseenter', clearHoverTimeout);

				dropdownContent.addEventListener('mouseleave', function (e) {
					if (e.relatedTarget && (trigger.contains(e.relatedTarget) || e.relatedTarget === trigger)) {
						return;
					}
					scheduleHide(dropdown);
				});
			});
		}

		function initializeMobileDropdowns() {
			elements.mobileDropdownTriggers.forEach(trigger => {
				trigger.addEventListener('click', function (event) {
					const href = trigger.getAttribute('href');
					if (href === `#mobile-dropdown-${trigger.id.replace('mobile-dropdown-', '')}`) {
						event.preventDefault();
					}

					const dropdownContentId = href.replace('#mobile-dropdown-', '');
					const dropdownContent = document.getElementById(`mobile-dropdown-${dropdownContentId}`);

					if (!dropdownContent) return;

					// Close other dropdowns
					elements.mobileDropdownTriggers.forEach(otherTrigger => {
						if (otherTrigger === trigger) return;

						const otherContentId = otherTrigger.getAttribute('href').replace('#mobile-dropdown-', '');
						const otherContent = document.getElementById(`mobile-dropdown-${otherContentId}`);

						if (otherContent?.classList.contains('show')) {
							otherTrigger.classList.remove('active');
							otherContent.classList.remove('show');
							otherContent.style.maxHeight = '0px';
						}
					});

					// Toggle current dropdown
					trigger.classList.toggle('active');
					dropdownContent.classList.toggle('show');
					dropdownContent.style.maxHeight = dropdownContent.classList.contains('show')
						? `${dropdownContent.scrollHeight}px`
						: '0px';
				});
			});

			if (elements.mobileOffcanvasElement) {
				elements.mobileOffcanvasElement.addEventListener('hidden.bs.offcanvas', function () {
					elements.mobileDropdownTriggers.forEach(trigger => {
						trigger.classList.remove('active');
						const dropdownContentId = trigger.getAttribute('href').replace('#mobile-dropdown-', '');
						const dropdownContent = document.getElementById(`mobile-dropdown-${dropdownContentId}`);

						if (dropdownContent) {
							dropdownContent.classList.remove('show');
							dropdownContent.style.maxHeight = '0px';
						}
					});
				});
			}
		}

		function initializeLoginModal() {
			if (!elements.loginModalForm) return;

			elements.loginModalForm.addEventListener('submit', function (e) {
				if (typeof window.Laravel !== 'undefined' && !window.Laravel.isLoggedIn) {
					e.preventDefault();

					if (typeof LoginModal !== 'undefined') {
						window.LoginModal.show('Please log in to access this feature');
					} else if (typeof toastr !== 'undefined') {
						toastr.error('Please log in to continue your purchase. (Login modal function not available)');
					} else {
						alert('Please log in to continue your purchase.');
					}
				}
			});
		}

		function initializeLicenseConfigs() {
			const licenseConfigs = [
				{
					selectId: 'regularPriceSupportList',
					displaySelector: '.regular-support-price',
					inputId: 'regularBuyNowSupportInput',
					priceAttribute: 'data-regular-price',
					formId: 'regularBuyNowForm'
				},
				{
					selectId: 'extendedPriceSupportList',
					displaySelector: '.extended-support-price',
					inputId: 'extendedBuyNowSupportInput',
					priceAttribute: 'data-extended-price',
					formId: 'extendedBuyNowForm'
				}
			];

			function createPriceUpdater(config) {
				const select = document.getElementById(config.selectId);
				const display = document.querySelector(config.displaySelector);
				const input = document.getElementById(config.inputId);
				const form = document.getElementById(config.formId);

				const updatePriceAndForm = () => {
					if (!select) return;

					try {
						const selectedOption = select.options[select.selectedIndex];

						if (!selectedOption) {
							if (display) display.textContent = 'N/A';
							if (input) input.value = '';
							return;
						}

						const price = selectedOption.getAttribute(config.priceAttribute);
						const originalPrice = selectedOption.getAttribute('data-original-price');
						const supportId = selectedOption.value;

						if (display && price) {
							if (originalPrice && originalPrice !== price && price !== 'Free') {
								display.innerHTML = `<span class="text-decoration-line-through text-muted me-1" style="font-size: 0.85em;">${originalPrice}</span><span>${price}</span>`;
							} else {
								display.textContent = price;
							}
						}

						if (input && supportId) input.value = supportId;

					} catch (error) {
						console.error(`Error updating ${config.selectId}:`, error);
					}
				};

				select?.addEventListener('change', updatePriceAndForm);
				form?.addEventListener('submit', updatePriceAndForm);

				updatePriceAndForm();
				return updatePriceAndForm;
			}

			licenseConfigs.forEach(createPriceUpdater);
		}

		function initializeGallery() {
			// Use event delegation so it works after AJAX tab switches
			$(document).on('click', '.view-gallery-btn', function (e) {
				e.preventDefault();
				if ($.fancybox) {
					$.fancybox.close(true);
					setTimeout(function () {
						const $links = $('[data-fancybox="product-gallery"]');
						if ($links.length > 0) {
							$.fancybox.open($links, {
								loop: true,
								buttons: ["zoom", "slideShow", "thumbs", "close"],
							}, 0);
						}
					}, 50);
				}
			});
		}

		function initializeProductShare() {
			if (!elements.productShareBtn) return;

			elements.productShareBtn.addEventListener('click', function () {
				const modal = new bootstrap.Modal(document.getElementById('productShareModal'));
				modal.show();
			});
		}

		function initializeCustomDropdowns() {
			elements.customDropdowns.forEach(dropdown => {
				const customToggle = dropdown.querySelector('.custom-dropdown-toggle');
				const customMenu = dropdown.querySelector('.custom-dropdown-menu');
				const customproducts = dropdown.querySelectorAll('.custom-dropdown-product');
				const selectedCategorySpan = dropdown.querySelector('.selected-category');

				if (!customToggle || !customMenu) return;

				customToggle.addEventListener('click', function (e) {
					e.stopPropagation();

					// Close other dropdowns
					elements.customDropdowns.forEach(otherDropdown => {
						if (otherDropdown !== dropdown) {
							otherDropdown.classList.remove('open');
						}
					});

					dropdown.classList.toggle('open');
				});

				customproducts.forEach(product => {
					product.addEventListener('click', function () {
						customproducts.forEach(i => i.classList.remove('active'));
						this.classList.add('active');

						const categoryName = this.getAttribute('data-category-name');
						if (selectedCategorySpan && categoryName) {
							selectedCategorySpan.textContent = categoryName;
						}

						dropdown.classList.remove('open');
					});
				});
			});
		}

		function initializeVideoControl() {
			if (!elements.headerVideo || !elements.videoControlBtn) return;

			elements.videoControlBtn.addEventListener('click', function () {
				if (state.isPlaying) {
					elements.headerVideo.pause();
					elements.videoControlIcon.className = 'bi bi-play-fill video-control-icon';
					elements.videoControlBtn.title = 'Play Video';
				} else {
					elements.headerVideo.play();
					elements.videoControlIcon.className = 'bi bi-pause-fill video-control-icon';
					elements.videoControlBtn.title = 'Pause Video';
				}
				state.isPlaying = !state.isPlaying;
			});

			document.addEventListener('keydown', function (event) {
				if (event.code === 'Space' &&
					!['INPUT', 'TEXTAREA'].includes(event.target.tagName)) {
					event.preventDefault();
					elements.videoControlBtn.click();
				}
			});
		}

		function initializeCurrencyLinks() {
			elements.currencyLinks.forEach(link => {
				link.addEventListener('click', function (e) {
					elements.currencyLinks.forEach(l => l.classList.remove('active'));
					this.classList.add('active');
				});
			});
		}

		function initializeNavToggle() {
			const handleNavToggle = () => {
				const navToggle = document.getElementById('navToggle');
				if (!navToggle) return;

				const isXL = window.matchMedia("(min-width: 1200px)").matches;
				if (!isXL) return;

				const scrollTop = window.pageYOffset;
				navToggle.classList.toggle('d-xl-none', scrollTop <= constants.NAV_SCROLL_THRESHOLD);
			};

			['scroll', 'resize'].forEach(event => {
				window.addEventListener(event, handleNavToggle);
			});

			handleNavToggle();
		}

		function initializeClickOutsideHandler() {
			document.addEventListener('click', function (e) {
				// Close nav dropdowns when clicking outside
				if (!e.target.closest('.nav-dropdown') &&
					!e.target.closest('.dropdown-portal .dropdown-content.show')) {
					if (state.currentDropdown) {
						const activeDropdownContainer = document.querySelector(`[data-dropdown-id="${state.currentDropdown.id}"]`);
						activeDropdownContainer && hideDropdownLogic(activeDropdownContainer);
					}
				}

				// Close custom dropdowns when clicking outside
				elements.customDropdowns.forEach(dropdown => {
					dropdown.classList.remove('open');
				});
			});
		}

		function initializeResizeHandler() {
			window.addEventListener('resize', function () {
				if (!state.currentDropdown) return;

				const activeDropdownTrigger = document.querySelector(`[data-dropdown-id="${state.currentDropdown.id}"] .dropdown-trigger`);
				const navContainerRect = elements.navContainer?.getBoundingClientRect();

				if (activeDropdownTrigger && navContainerRect) {
					const { targetWidth, targetLeft, triggerRect } = calculateDropdownPosition(
						activeDropdownTrigger,
						state.currentDropdown,
						navContainerRect
					);

					Object.assign(state.currentDropdown.style, {
						left: `${targetLeft}px`,
						width: `${targetWidth}px`,
						minWidth: `${targetWidth}px`,
						top: `${triggerRect.bottom + window.scrollY}px`
					});
				}
			});
		}

		function initializeThemeToggle() {
			const themeParams = {
				light: 'light',
				dark: 'dark',
				auto: 'auto',
				storageKey: 'theme'
			};

			function setTheme(theme) {
				if (theme === themeParams.auto) {
					if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
						document.documentElement.setAttribute('data-bs-theme', themeParams.dark);
					} else {
						document.documentElement.setAttribute('data-bs-theme', themeParams.light);
					}
					localStorage.removeItem(themeParams.storageKey);
				} else {
					document.documentElement.setAttribute('data-bs-theme', theme);
					localStorage.setItem(themeParams.storageKey, theme);
				}
				updateThemeToggles();
			}

			function toggleTheme() {
				const currentStored = localStorage.getItem(themeParams.storageKey);
				let effectiveTheme = currentStored;
				if (!effectiveTheme) {
					effectiveTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? themeParams.dark : themeParams.light;
				}

				const newTheme = effectiveTheme === themeParams.light ? themeParams.dark : themeParams.light;
				setTheme(newTheme);
			}

			function updateThemeToggles() {
				const currentStored = localStorage.getItem(themeParams.storageKey);
				let isDark = currentStored === themeParams.dark;
				if (!currentStored) {
					isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
				}

				// Sync Switch Inputs
				document.querySelectorAll('[data-theme-action="toggle-switch"]').forEach(el => {
					el.checked = isDark;
				});

				// Sync Dropdown Active States
				const activeValue = currentStored || themeParams.auto;
				document.querySelectorAll('[data-theme-value]').forEach(el => {
					if (el.dataset.themeValue === activeValue) {
						el.classList.add('active');
					} else {
						el.classList.remove('active');
					}
				});
			}

			// Initialize
			const storedTheme = localStorage.getItem(themeParams.storageKey);
			if (storedTheme) {
				setTheme(storedTheme);
			} else {
				setTheme(themeParams.auto);
			}

			// Check system preference changes if in auto mode
			window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
				if (!localStorage.getItem(themeParams.storageKey)) {
					setTheme(themeParams.auto);
				}
			});

			// Event Delegation
			document.addEventListener('click', function (e) {
				const toggleBtn = e.target.closest('[data-theme-action="toggle"]');
				if (toggleBtn) {
					e.preventDefault();
					toggleTheme();
				}

				const setBtn = e.target.closest('[data-theme-value]');
				if (setBtn) {
					e.preventDefault();
					setTheme(setBtn.dataset.themeValue);
				}
			});

			document.addEventListener('change', function (e) {
				if (e.target.matches('[data-theme-action="toggle-switch"]')) {
					toggleTheme();
				}
			});

			window.toggleTheme = toggleTheme;
			window.setTheme = setTheme;
		}

		function initializeHeaderSearch() {
			const focusSearchInput = (container) => {
				const input = container.querySelector('input[name="query"]');
				if (input) setTimeout(() => input.focus(), 100);
			};

			// Modal
			document.querySelectorAll('.header-search-modal').forEach(modalEl => {
				modalEl.addEventListener('shown.bs.modal', () => focusSearchInput(modalEl));
			});

			// Collapse (Expandable/Full Width)
			document.querySelectorAll('.header-search-collapse').forEach(collapseEl => {
				const uniqueId = collapseEl.getAttribute('data-unique-id');
				const backdrop = document.getElementById(`searchBackdrop-${uniqueId}`);

				collapseEl.addEventListener('shown.bs.collapse', () => {
					focusSearchInput(collapseEl);
					if (backdrop) backdrop.classList.remove('d-none');
				});

				collapseEl.addEventListener('hide.bs.collapse', () => {
					if (backdrop) backdrop.classList.add('d-none');
				});

				if (backdrop) {
					backdrop.addEventListener('click', () => {
						const toggleBtn = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
						if (toggleBtn) {
							toggleBtn.click();
						} else {
							const bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
							if (bsCollapse) bsCollapse.hide();
						}
					});
				}
			});

			document.addEventListener('click', (e) => {
				document.querySelectorAll('.header-search-collapse.show').forEach(collapseEl => {
					const uniqueId = collapseEl.getAttribute('data-unique-id');
					const hasBackdrop = !!document.getElementById(`searchBackdrop-${uniqueId}`);

					if (!hasBackdrop) {
						const toggle = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
						if (!collapseEl.contains(e.target) && (!toggle || !toggle.contains(e.target))) {
							const bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
							if (bsCollapse) bsCollapse.hide();
						}
					}
				});
			});
		}

		function initializeStickyHeader() {
			const stickyHeaders = document.querySelectorAll('.sticky-header');

			stickyHeaders.forEach((header, index) => {
				const type = header.dataset.stickyType || 'none';
				if (type === 'none') {
					// Clean up if controls changed to none
					header.classList.remove('is-stuck');
					header.style.position = '';
					header.style.top = '';
					const placeholder = header.parentElement.querySelector('.sticky-placeholder');
					if (placeholder) placeholder.remove();
					return;
				};

				// Create a placeholder to prevent layout shift
				let placeholder = header.parentElement.querySelector('.sticky-placeholder');
				if (!placeholder) {
					placeholder = document.createElement('div');
					placeholder.className = 'sticky-placeholder d-none';
					header.parentNode.insertBefore(placeholder, header);
				}

				// Ensure offset and transition are numbers or default to avoid issues
				const offsetAttr = header.dataset.stickyOffset;
				const offset = (offsetAttr !== undefined && offsetAttr !== "") ? parseInt(offsetAttr) : 100;

				const transitionAttr = header.dataset.stickyTransition;
				const transition = (transitionAttr !== undefined && transitionAttr !== "") ? parseInt(transitionAttr) : 300;

				// Initial styling for smoothing
				const transitionStyle = transition > 0
					? `transform ${transition}ms cubic-bezier(0.33, 1, 0.68, 1), background-color 0.2s, box-shadow 0.2s`
					: 'none';

				header.style.transition = transitionStyle;
				header.style.width = '100%';
				// header.style.zIndex = '1030'; // Removed to respect server-side cascading z-index
				header.style.willChange = 'transform'; // Optimize for animations

				let lastScroll = window.scrollY;
				let isSticky = false;
				let ticking = false;
				let headerHeight = header.offsetHeight; // Cache height

				const updateStickyState = () => {
					const currentScroll = window.scrollY;

					// --- ACTIVATION LOGIC ---
					// We activate sticky mode once we've scrolled past the offset
					if (currentScroll > offset) {
						if (!isSticky) {
							isSticky = true;

							// Set placeholder height to occupy the space
							placeholder.style.height = `${headerHeight}px`;
							placeholder.classList.remove('d-none');

							// Fix the header
							header.style.position = 'fixed';
							header.style.left = '0';
							header.classList.add('is-stuck');
							header.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';

							// Animate in based on type.
							const shouldAnimate = (type === 'smart' || type === 'delay') && transition > 0 && offset > 0;

							if (shouldAnimate) {
								// Temporarily disable transition for instantaneous setup
								header.style.transition = 'none';
								header.style.transform = 'translateY(-100%)';

								// Force Reflow
								void header.offsetHeight;

								// Re-enable transition and animate to 0
								header.style.transition = transitionStyle;
								header.style.transform = 'translateY(0)';
							} else {
								header.style.transform = 'translateY(0)';
							}
						}

						// Dynamic Stacking: Adjust top position based on previous stuck headers
						let stackTop = 0;
						for (let i = 0; i < index; i++) {
							const prevHeader = stickyHeaders[i];
							if (prevHeader.classList.contains('is-stuck')) {
								// If the previous header is "Smart" and transformed out, we might want to ignore its height?
								// But strict requirement is "outer height".
								// We use offsetHeight which is the expanded height.
								stackTop += prevHeader.offsetHeight;
							}
						}
						header.style.top = `${stackTop}px`;

					} else {
						// Deactivate sticky mode if we scroll back to top
						if (isSticky) {
							isSticky = false;

							// Temporarily disable transition during layout reset to prevent jumping
							header.style.transition = 'none';

							placeholder.classList.add('d-none');
							header.style.position = '';
							header.style.top = '';
							header.style.left = '';
							header.style.boxShadow = '';
							header.style.transform = '';
							header.classList.remove('is-stuck');

							// Force reflow to apply 'none' transition
							void header.offsetHeight;

							// Restore transition style in case it was modified
							header.style.transition = transitionStyle;
						}
					}

					// --- BEHAVIOR LOGIC (Once Sticky) ---
					if (isSticky) {
						if (type === 'smart') {
							// Smart: Hide on scroll down, Show on scroll up
							// We add a small buffer (10px) to prevent jitter
							if (currentScroll > lastScroll + 5 && currentScroll > (offset + headerHeight)) {
								// Scrolling Down -> Hide
								header.style.transform = 'translateY(-100%)';
								header.style.boxShadow = 'none';
							} else if (currentScroll < lastScroll - 5) {
								// Scrolling Up -> Show
								header.style.transform = 'translateY(0)';
								header.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
							}
						} else if (type === 'delay') {
							// Ensure it stays visible
							// Note: We don't constantly set translateY(0) to allow potential CSS overrides
							if (header.style.transform !== 'translateY(0px)' && header.style.transform !== 'translateY(0)') {
								header.style.transform = 'translateY(0)';
							}
						}
					}

					lastScroll = currentScroll;
					ticking = false;
				};

				const onScroll = () => {
					if (!ticking) {
						window.requestAnimationFrame(updateStickyState);
						ticking = true;
					}
				};

				window.addEventListener('scroll', onScroll, { passive: true });

				// Handle window resize to update placeholder height
				window.addEventListener('resize', () => {
					headerHeight = header.offsetHeight; // Update cached height
					if (isSticky) {
						placeholder.style.height = `${headerHeight}px`;
					}
				});
			});
		}

		function initializeMobileHeaderPadding() {
			function updateBodyPadding() {
				var el = document.getElementById('mobile-header-bottom');
				var footer = document.getElementById('ezymarket-footer');
				var isModern = el && el.getAttribute('data-mobile-style') === 'modern';

				// Reset first
				document.body.style.paddingBottom = '';
				if (footer) footer.style.paddingBottom = '';

				if (el && window.innerWidth < 992) {
					var height = el.offsetHeight;

					if (isModern) {
						// Modern: Add padding to footer to extend its background behind the floating pill
						// Height + Bottom Offset (20px) + Breathing Room (10px) = +30px
						var spacer = height + 30;
						if (footer) {
							footer.style.paddingBottom = spacer + 'px';
						} else {
							document.body.style.paddingBottom = spacer + 'px';
						}
					} else {
						// Default: Fixed bottom bar needs body padding to push content up
						document.body.style.paddingBottom = height + 'px';
					}
				}
			}
			// Run immediately since we are in DOMContentLoaded
			updateBodyPadding();
			window.addEventListener('resize', updateBodyPadding);
		}

		function initializeDelegatedDropdowns() {
			document.addEventListener('click', function (e) {
				// Check if click is on a trigger inside a click-mode dropdown
				const trigger = e.target.closest('.nav-dropdown[data-trigger-type="click"] > .dropdown-trigger');

				if (trigger) {
					e.preventDefault();
					e.stopPropagation();

					const dropdown = trigger.closest('.nav-dropdown');
					if (!dropdown) return;

					const wasOpen = dropdown.classList.contains('is-open');

					// Close all other dropdowns
					document.querySelectorAll('.nav-dropdown.is-open').forEach(d => {
						if (d !== dropdown) d.classList.remove('is-open');
					});

					// Toggle current
					if (wasOpen) {
						dropdown.classList.remove('is-open');
					} else {
						dropdown.classList.add('is-open');
					}
				} else {
					// Click outside logic
					if (!e.target.closest('.nav-dropdown')) {
						document.querySelectorAll('.nav-dropdown.is-open').forEach(d => {
							d.classList.remove('is-open');
						});
					}
				}
			});
		}

		function initializeGlobalLoadMore() {
			$(document).on('click', '.load-more-btn', function (e) {
				e.preventDefault();
				var $btn = $(this);
				var url = $btn.data('url');
				var selectors = $btn.data('target').split(',');

				if (!url) return;

				var $btnText = $btn.find('.load-more-text');
				var $btnIcon = $btn.find('.load-more-icon');
				var $btnSpinner = $btn.find('.load-more-loader');

				$btn.attr('disabled', true);
				$btnText.addClass('d-none');
				$btnIcon.addClass('d-none');
				$btnSpinner.removeClass('d-none');

				$.ajax({
					url: url,
					type: 'GET',
					success: function (response) {
						var targetContainer = null;
						var $newHtml = null;

						// If button is inside a tab-pane, target that specific pane's row
						var $tabPane = $btn.closest('.tab-pane');
						if ($tabPane.length && $tabPane.attr('id')) {
							var tabSel = '#' + $tabPane.attr('id') + ' .row';
							if ($(response).find(tabSel).length > 0) {
								targetContainer = tabSel;
								$newHtml = $(response).find(tabSel).html();
							}
						}

						// Fallback to data-target selectors
						if (!targetContainer) {
							for (var i = 0; i < selectors.length; i++) {
								var sel = $.trim(selectors[i]);
								if ($(response).find(sel).length > 0 && $(sel).length > 0) {
									targetContainer = sel;
									$newHtml = $(response).find(sel).html();
									break;
								}
							}
						}

						if (targetContainer && $newHtml) {
							var $container = $(targetContainer).first();
							$container.append($newHtml);

							if (typeof AOS !== 'undefined') {
								AOS.refresh();
							}

							if (typeof initializeVideoPlayers === 'function') {
								initializeVideoPlayers();
							}
							if (typeof initializeAudioPlayers === 'function') {
								initializeAudioPlayers();
							}
						}

						// Look for the load-more-btn in the EXACT SAME block/tab it just paginated
						var $newBtn = null;
						if ($tabPane.length && $tabPane.attr('id')) {
							$newBtn = $(response).find('#' + $tabPane.attr('id') + ' .load-more-btn');
						}
						if (!$newBtn || !$newBtn.length) {
							var btnBlockId = $btn.closest('.section').attr('id') || $btn.closest('[id]').attr('id');
							$newBtn = $(response).find('#' + btnBlockId + ' .load-more-btn');
						}

						if ($newBtn.length && $newBtn.data('url')) {
							$btn.data('url', $newBtn.data('url'));
							$btn.attr('disabled', false);
							$btnText.removeClass('d-none');
							$btnIcon.removeClass('d-none');
							$btnSpinner.addClass('d-none');
						} else {
							$btn.parent().remove(); // Remove button if no more pages
						}
					},
					error: function () {
						$btn.attr('disabled', false);
						$btnText.removeClass('d-none');
						$btnIcon.removeClass('d-none');
						$btnSpinner.addClass('d-none');
					}
				});
			});
		}
	});


	// Category dropdown label update on tab change
	$(document).on('click', '.custom-dropdown-item[data-category-name]', function () {
		var label = $(this).closest('.category-dropdown').find('.selected-category');
		if (label.length) label.text($(this).data('category-name'));
	});

	// Clean up page-1 pagination links (remove ?latest_page=1 etc.)
	$('.pagination a').each(function () {
		var url = new URL(this.href, window.location.origin);
		var changed = false;
		var keys = [];
		url.searchParams.forEach(function (v, k) { keys.push(k); });
		keys.forEach(function (k) {
			if (k.endsWith('_page') && parseInt(url.searchParams.get(k)) <= 1) {
				url.searchParams.delete(k);
				changed = true;
			}
		});
		if (changed) {
			this.href = url.searchParams.toString() ? url.pathname + '?' + url.searchParams.toString() : url.pathname;
		}
	});

})(jQuery);
