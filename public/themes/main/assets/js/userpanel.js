(function($) {
    "use strict";

    let lastDraftData = '';
    let autoSaveTimer = null;
	window.productEditors = {};

    /**
	 * Safe integer value parse
	 */
	function parseIntSafe(value) {
		return (value !== null && value !== undefined && !isNaN(value)) ? parseInt(value, 10) : null;
	}

	/**
	 * Format bytes to human readable format
	 */
	function formatBytes(bytes, decimals = 2) {
		const sizes = uploadOptions?.format_bytes || ['B', 'KB', 'MB', 'GB', 'TB'];
		if (bytes === 0) return "0 " + sizes[0];

		const k = 1024;
		const dm = decimals < 0 ? 0 : decimals;
		const i = Math.floor(Math.log(bytes) / Math.log(k));

		return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
	}

	/**
	 * Truncate string for display
	 */
	function truncateString(string) {
		if (string.length > 40) {
			return string.slice(0, 20) + ".." + string.slice(string.length - 4);
		}
		return string;
	}

	/**
	 * Text to array converter
	 */
	function convertTextareaToArray(textareaValue) {
		if (!textareaValue || textareaValue.trim() === '') return [];
		return textareaValue.split(',').map(product => product.trim()).filter(product => product.length > 0).slice(0, 6);
	}

	/**
	 * Global translation helper for JavaScript
	 */
	window.translate = function(key) {
		return window.config?.translates?.[key] || key;
	};

	/**
	 * Get product configuration from data attribute
	 */
	function getProductConfig() {
		return $('.product-dashboard, .ajax-tabs, #productSubmission, #productSubmissionForm').filter(
			(i, el) => $(el).data('config')).first().data('config') || {};
	}

	/**
	 * Centralized AJAX error handler
	 */
	function handleAjaxError(xhr, defaultMsg = 'Something went wrong') {
		if (typeof toastr === 'undefined') return;

		const error = xhr.responseJSON;
		if (error && error.errors && Object.keys(error.errors).length > 0) {
			Object.values(error.errors).flat().forEach(msg => toastr.error(msg));
		} else if (error && (error.message || error.error)) {
			toastr.error(error.message || error.error);
		} else {
			const statusError = xhr.statusText || defaultMsg;
			toastr.error(statusError);
		}
	}

	/**
	 * Initialize tags input with configuration
	 */
	function initializeTagsInput() {
		const $productTagsInput = $('#product-tags');

		if ($productTagsInput.length && typeof $.fn.tagsinput !== 'undefined') {
			const config = getProductConfig();
			$productTagsInput.tagsinput({
				trimValue: true,
				confirmKeys: [13, 44],
				cancelConfirmKeysOnEmpty: false,
				maxTags: parseIntSafe(config?.max_tags),
			});
		}
	}

	/**
	 * Handle main file source switching between upload and external URL
	 */
	function initializeMainFileSourceSwitcher() {
		const $mainFileSource = $('#mainFileSource');

		function handleMainFileSourceChange() {
			const $mainFileSource1 = $('.main-file-source-1');
			const $mainFileSource2 = $('.main-file-source-2');
			const selectedValue = $mainFileSource.val();

			if (selectedValue === '0') {
				$mainFileSource2.prop('disabled', true).addClass('d-none');
				$mainFileSource1.prop('disabled', false).removeClass('d-none');
				$mainFileSource1.closest('.bootstrap-select').removeClass('d-none');
			} else {
				$mainFileSource1.prop('disabled', true).addClass('d-none');
				$mainFileSource1.closest('.bootstrap-select').addClass('d-none');
				$mainFileSource2.prop('disabled', false).removeClass('d-none');
			}
		}

		$mainFileSource.on('change', handleMainFileSourceChange);
		setTimeout(handleMainFileSourceChange, 100);
	}


	// ==========================================
	// GLOBAL VARIABLES FOR FILE UPLOAD
	// ==========================================
	let maxFiles = 0;
	let imageTypes = ["image/jpeg", "image/jpg", "image/png"];
	let dropzoneInstance = null;
	let dropzonePreviewTemplate = null;

	if (typeof Dropzone !== 'undefined') {
		Dropzone.autoDiscover = false;
	}

	/**
	 * Initialize Dropzone file upload functionality
	 */
	function initializeDropzoneUpload(retryCount = 0) {
		if (typeof Dropzone === 'undefined') {
			if (retryCount < 10) {
				setTimeout(() => initializeDropzoneUpload(retryCount + 1), 200);
			}
			return;
		}

		// Always disable autoDiscover when library is found
		Dropzone.autoDiscover = false;

		const $uploadFilesBox = $('#upload-files-box');
		if (!$uploadFilesBox.length) return;

		const config = getProductConfig();
		const uploadConfig = config?.upload;

		if (!uploadConfig) return;

		const previewNode = document.querySelector("#upload-previews");
		if (previewNode) {
			dropzonePreviewTemplate = previewNode.innerHTML;
			previewNode.id = "";
			previewNode.parentNode.removeChild(previewNode);
		}

		if (!dropzonePreviewTemplate) {
			return;
		}

		let maxFilesize = parseInt(uploadConfig.max_file_size);
		let acceptedFiles = uploadConfig.allowed_types;
			maxFiles = parseInt(uploadConfig.max_files);
			imageTypes = ["image/jpeg", "image/jpg", "image/png"];

		// Destroy existing instance if any
		if (dropzoneInstance) {
			try {
				dropzoneInstance.destroy();
			} catch (e) {}
		}

		const dropzoneWrapper = document.querySelector("#dropzone-wrapper");
		if (!dropzoneWrapper) return;

		dropzoneInstance = new Dropzone(dropzoneWrapper, {
			...(window.uploadOptions || {}),
			previewTemplate: dropzonePreviewTemplate,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: uploadConfig.url,
			method: "POST",
			paramName: 'file',
			filesizeBase: 1024,
			parallelUploads: maxFiles,
			maxFiles: maxFiles,
			maxFilesize: maxFilesize,
			acceptedFiles: acceptedFiles,
			autoProcessQueue: true,
			timeout: 0,
			chunking: true,
			forceChunking: true,
			chunkSize: 52428800,
			retryChunks: true,
			clickable: "[data-dz-click]",
			previewsContainer: "#dropzone",
		});

		const dropzone = dropzoneInstance;

		// ==========================================
		// DROPZONE EVENT HANDLERS
		// ==========================================

		/**
		 * Update dropzone visual state based on file count
		 */
		function updateDropzoneState() {
			const $dropzoneBox = $(".dropzone-box");
			if (dropzone.files.length > 0) {
				$dropzoneBox.addClass('active');
			} else {
				$dropzoneBox.removeClass('active');
			}
		}

		/**
		 * Handle file addition with validation
		 */
		function handleFileAdd(file) {
			// Validate file count
			if (dropzone.files.length > maxFiles) {
				dropzone.removeFile(file);
				toastr.error(uploadOptions.errors.max_files_exceeded);
				return;
			}

			// Check for duplicates
			if (dropzone.files.length > 1) {
				for (let i = 0; i < dropzone.files.length - 1; i++) {
					if (dropzone.files[i].name === file.name) {
						dropzone.removeFile(file);
						toastr.error(uploadOptions.errors.file_duplicate);
						return;
					}
				}
			}

			// Validate file size
			if (file.size === 0) {
				dropzone.removeFile(file);
				toastr.error(uploadOptions.errors.file_empty);
				return;
			}

			if (file.size > (maxFilesize * 1024 * 1024)) {
				dropzone.removeFile(file);
				toastr.error(uploadOptions.errors.max_file_size_exceeded);
				return;
			}

			updateDropzoneState();
			setupFilePreview(file);
		}

		/**
		 * Setup file preview elements
		 */
		function setupFilePreview(file) {
			const $preview = $(file.previewElement);
			const $previewFileSize = $preview.find('.dz-size');

			// Update file size display
			$previewFileSize.html('(' + formatBytes(file.size) + ')');

			// Handle file extension display
			const $previewFileExt = $preview.find("[data-dz-extension]");
			if (imageTypes.includes(file.type)) {
				$previewFileExt.remove();
			} else {
				const fileExtension = file.name.split('.').pop();
				const $previewFileThumbnail = $preview.find("[data-dz-thumbnail]");
				$previewFileThumbnail.remove();

				if (fileExtension !== "") {
					$previewFileExt.attr('data-type', fileExtension.substring(0, 4));
				} else {
					$previewFileExt.attr('data-type', '?');
				}
			}

			// Update file name display
			$preview.find('[data-dz-name]').text(truncateString(file.upload.filename));
		}

		/**
		 * Handle file removal
		 */
		function handleFileRemove() {
			updateDropzoneState();
		}

		/**
		 * Handle upload progress
		 */
		function handleUploadProgress(file, progress) {
			const $preview = $(file.previewElement);
			$preview.find(".dz-upload-percentage").html(progress.toFixed(0) + "%");
		}

		/**
		 * Handle file upload errors
		 */
		function handleFileError(file, message = null) {
			dropzone.removeFile(file);
			toastr.error(message);
		}

		/**
		 * Handle successful file upload
		 */
		function handleUploadComplete(file) {
			if (file.status !== "success") return;

			const $preview = $(file.previewElement);
			const response = JSON.parse(file.xhr.response);

			if (response.type === 'success') {
				dropzone.removeFile(file);
				addUploadedFileToList(response);

				setTimeout(() => {
					loadUploadedFiles();
				}, 500);

				maxFiles--;
				initializeFileRemoval();
				$('.dropzone-drag-inner').removeClass('is-invalid');
			} else {
				$preview.removeClass('dz-success').addClass('dz-error');
				dropzone.removeFile(file);
				toastr.error(response.message);
			}
		}

		/**
		 * Add uploaded file to the list
		 */
		function addUploadedFileToList(response) {
			const $uploadedFiles = $('.uploaded-files');
			const thumbnail = imageTypes.includes(response.mime_type)
				? `<img src="${response.link}" alt="${response.name}" />`
				: `<span class="bi bi-file-earmark-zip fs-3 text-primary" data-type="${response.extension}"></span>`;

			const fileHtml = `
				<div class="uploaded-file uploaded-file-${response.id}">
					<div class="uploaded-file-icon">${thumbnail}</div>
					<div class="uploaded-file-info">
						<h6 class="uploaded-file-name">
							<span class="success-mark"><i class="bi bi-check-circle me-1"></i></span>
							${truncateString(response.name)}
						</h6>
						<p class="uploaded-file-time mb-0">
							${response.time}<span class="dot-seperator"></span>${response.size}
						</p>
					</div>
					<button type="button" class="uploaded-file-remove" title="${translate('Remove')}"
							data-id="${response.id}" data-delete-link="${response.delete_link}">
						<i class="bi bi-trash"></i>
					</button>
				</div>
			`;

			$uploadedFiles.prepend(fileHtml);
		}

		// Bind Dropzone events
		dropzone.on("addedfile", handleFileAdd);
		dropzone.on("removedfile", handleFileRemove);
		dropzone.on('uploadprogress', handleUploadProgress);
		dropzone.on('error', handleFileError);
		dropzone.on('complete', handleUploadComplete);

		// Initialize file removal functionality
		initializeFileRemoval();
	}

	// ==========================================
	// FILE MANAGEMENT UTILITIES
	// ==========================================

	/**
	 * Initialize file removal functionality
	 */
	function initializeFileRemoval() {
		$(document).off('click', '.uploaded-file-remove').on('click', '.uploaded-file-remove', function(e) {
			e.preventDefault();

			const $btn = $(this);
			const uploadedFileId = $btn.data('id');
			const uploadedFileDeleteLink = $btn.data('delete-link');

			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				url: uploadedFileDeleteLink,
				type: "DELETE",
				dataType: "JSON",
				beforeSend: function() {
					$btn.prop('disabled', true)
						.empty()
						.append('<div class="spinner-border spinner-border-sm me-2"></div>');
				},
				success: function(response) {
					$btn.prop('disabled', false)
						.empty()
						.append('<i class="bi bi-trash"></i>');

					if ($.isEmptyObject(response.error)) {
						$(`.uploaded-file-${uploadedFileId}`).remove();
						loadUploadedFiles();

						if (typeof maxFiles === 'number') {
							maxFiles++;
						}

						toastr.success(response.message);
					} else {
						toastr.error(response.error);
					}
				},
				error: function(request) {
					$btn.prop('disabled', false)
						.empty()
						.append('<i class="bi bi-trash"></i>');
					handleAjaxError(request, 'Error deleting file');
				}
			});
		});
	}

	/**
	 * Determine if file should be included in select dropdown
	 */
	function shouldIncludeFile(selectName, fileData) {
		const fileType = fileData.file_type || '';

		const allowedTypes = {
			'thumbnail': ['image'],
			'preview_image': ['image'],
			'gallery[]': ['image'],
			'preview_audio': ['audio'],
			'audio_file': ['audio'],
			'preview_video': ['video'],
			'video_file': ['video'],
			'main_file': ['archive', 'document'],
			'demo_file': ['image', 'audio', 'video', 'document']
		};

		const selectAllowedTypes = allowedTypes[selectName] || ['image', 'audio', 'video', 'archive', 'document'];
		return selectAllowedTypes.includes(fileType);
	}

	/**
	 * Load uploaded files into select dropdowns
	 */
	function loadUploadedFiles(data = null) {
		// Save current selections
		const savedValues = {};
		$('select.product-files-select').each(function() {
			const $select = $(this);
			const name = $select.attr('name');
			savedValues[name] = $select.val();
		});

		const processFiles = (response) => {
			const $productFilesSelect = $('select.product-files-select');
			$productFilesSelect.selectpicker('destroy');

			if ($productFilesSelect.length > 0) {
				$productFilesSelect.each(function() {
					const $select = $(this);
					const name = $select.attr('name');

					// Clear and rebuild options
					$select.empty();

					if (name !== 'gallery[]') {
						$select.append('<option class="d-none" value="">--Choose one--</option>');
					}

					// Add file options
					$.each(response, function(fileId, fileData) {
						if (shouldIncludeFile(name, fileData)) {
							const $option = $('<option></option>')
								.val(fileId)
								.text(fileData.text)
								.attr('data-content', fileData.data_content)
								.attr('data-width', fileData.data_width)
								.attr('data-height', fileData.data_height);

							$select.append($option);
						}
					});

					// Restore saved value
					const savedValue = savedValues[name];
					if (savedValue && savedValue !== '' && (!Array.isArray(savedValue) || (Array.isArray(savedValue) && savedValue.length > 0))) {
						$select.val(savedValue);
					}
				});
			}

			// Reinitialize selectpicker
			$productFilesSelect.selectpicker({
				sanitize: false,
				escapeTitle: false,
				style: 'btn-default btn-md',
			});
		};

		if (data) {
			processFiles(data);
			return;
		}

		const config = getProductConfig();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: config.load_files_route,
			type: "POST",
			dataType: "JSON",
			success: processFiles,
			error: function(request) {
				handleAjaxError(request, 'Error loading files');
			}
		});
	}

	// ==========================================
	// PRICING CALCULATIONS
	// ==========================================

	/**
	 * Initialize pricing calculation system
	 */
	function initializePricingCalculations() {
		const $regularLicensePrice = $('#regular-license-price');
		const $regularLicensePurchasePrice = $('#regular-license-purchase-price');
		const $extendedLicensePrice = $('#extended-license-price');
		const $extendedLicensePurchasePrice = $('#extended-license-purchase-price');
		const $regularLicensePercentage = $('#regular-license-percentage');
		const $extendedLicensePercentage = $('#extended-license-percentage');
		const config = getProductConfig();
		const maxDiscountPercentage = parseIntSafe(config?.max_discount_percentage);

		/**
		 * Update regular license purchase price with optional discount
		 */
		function updateRegularLicensePurchasePrice(discountPercentage = 0) {
			if (!$regularLicensePrice.length) return;

			const config = getProductConfig();
			const inputVal = $regularLicensePrice.val();
			const regularBuyerFee = config?.buyer_fee?.regular ? parseFloat(config.buyer_fee.regular) : 0;
			const numericValue = parseFloat(inputVal) || 0;
			let price = numericValue > 0 ? numericValue + regularBuyerFee : 0;

			$('#regular-buyer-fee').val(regularBuyerFee.toFixed(2));

			if (discountPercentage > 0) {
				const discountAmount = config?.prices?.regular ? (config.prices.regular * discountPercentage) / 100 : 0;
				const regularPrice = config?.prices?.regular ? config.prices.regular - discountAmount : 0;
				price = Math.round(regularPrice + regularBuyerFee);
			}

			$regularLicensePurchasePrice.val(price.toFixed(2));
		}

		/**
		 * Update extended license purchase price with optional discount
		 */
		function updateExtendedLicensePurchasePrice(discountPercentage = 0) {
			if (!$extendedLicensePrice.length) return;

			const config = getProductConfig();
			const inputVal = $extendedLicensePrice.val();
			let extendedBuyerFee = config?.buyer_fee?.extended ? parseFloat(config.buyer_fee.extended) : 0;

			// Check if input is exactly 0
			const isExactlyZero = inputVal === '0' || parseFloat(inputVal) === 0;
			const numericValue = parseFloat(inputVal) || 0;

			// Update buyer fee field dynamically
			if (isExactlyZero && inputVal.trim() !== '') {
				$('#extended-buyer-fee').val('0.00');
				extendedBuyerFee = 0;
			} else {
				$('#extended-buyer-fee').val((extendedBuyerFee || 0).toFixed(2));
			}

			let price = numericValue > 0 ? numericValue + extendedBuyerFee : 0;

			if (discountPercentage > 0) {
				const discountAmount = config?.prices?.extended ? (config.prices.extended * discountPercentage) / 100 : 0;
				const extendedPrice = config?.prices?.extended ? config.prices.extended - discountAmount : 0;
				const extendedFee = config?.buyer_fee?.extended ? parseFloat(config.buyer_fee.extended) : 0;

				price = Math.round(extendedPrice + extendedFee);
			}

			$extendedLicensePurchasePrice.val(price.toFixed(2));
		}

		/**
		 * Handle discount percentage input with validation
		 */
		function handleDiscountPercentage($percentageInput, updateFunction) {
			$percentageInput.on('input', function() {
				const percentageValue = $(this).val();
				const percentage = (percentageValue !== null &&
								  percentageValue.trim() !== '' &&
								  !isNaN(percentageValue)) ? parseFloat(percentageValue) : 0;

				if (percentage > maxDiscountPercentage) {
					$(this).val(maxDiscountPercentage);
					const config = getProductConfig();
					alert(translate('max_discount_percentage_error') || 'Maximum discount percentage is not correct');
					updateFunction(maxDiscountPercentage);
				} else {
					updateFunction(percentage);
				}
			});
		}

		// Bind events
		handleDiscountPercentage($regularLicensePercentage, updateRegularLicensePurchasePrice);
		handleDiscountPercentage($extendedLicensePercentage, updateExtendedLicensePurchasePrice);

		$regularLicensePrice.on('input', () => updateRegularLicensePurchasePrice());
		$extendedLicensePrice.on('input', () => updateExtendedLicensePurchasePrice());

		// Initial calculations
		updateRegularLicensePurchasePrice();
		updateExtendedLicensePurchasePrice();
	}

	// ==========================================
	// Support & free product radio
	// ==========================================
	function initializeSupportOptions() {
		$(document).on('click', '.support-option-card', function() {
			const $card = $(this);
			const $radio = $card.find('input[type="radio"]');
			const radioName = $radio.attr('name');
			const radioValue = $radio.val();

			// Update selection
			$radio.prop('checked', true);
			$(`input[name="${radioName}"]`).closest('.support-option-card').removeClass('selected');
			$card.addClass('selected');

			// Handle conditional sections
			if (radioName === 'support') {
				$('#supportInstructions').toggleClass('d-none', radioValue !== '1');
			}

			if (radioName === 'free_product') {
				$('.purchasing-option').toggleClass('d-none', radioValue !== '1');
			}
		});

		setTimeout(function() {
			$('input[type="radio"]:checked').each(function() {
				const $radio = $(this);
				const $card = $radio.closest('.support-option-card');
				const radioName = $radio.attr('name');
				const radioValue = $radio.val();

				$card.addClass('selected');

				if (radioName === 'support') {
					$('#supportInstructions').toggleClass('d-none', radioValue !== '1');
				}

				if (radioName === 'free_product') {
					$('.purchasing-option').toggleClass('d-none', radioValue !== '1');
				}
			});
		}, 100);
	}

	// ==========================================
	// Initialize Custom Services Switch
	// ==========================================
	function initializeSwitch(switchId, groupId, textareaSelector) {
		const $switch = $(switchId);
		const $group = $(groupId);
		const $textarea = $(textareaSelector);

		function handleSwitchToggle() {
			if ($switch.is(':checked')) {
				$group.removeClass('d-none').hide().slideDown(300);
			} else {
				$group.slideUp(300, function() {
					$(this).addClass('d-none');
				});
				$textarea.val('');
			}
		}

		$switch.on('change', handleSwitchToggle);

		// Initial state
		if ($switch.is(':checked')) {
			$group.removeClass('d-none');
		} else {
			$group.addClass('d-none');
		}
	}

	// ==========================================
	// INITIALIZATION
	// ==========================================
	function initializeFormComponents(container = document) {
		initializeTagsInput();
		initializeMainFileSourceSwitcher();
		initializeDropzoneUpload();
		initializePricingCalculations();
		initializeSubmissionFeatures();
		initializeSupportOptions();
		initializeSwitch('#additionalFeatureSwitch', '#additionalFeatureGroup', '.additional-features-text');
		initializeSwitch('#customServicesSwitch', '#customServicesGroup', '.custom-services-text');
		initializeSwitch('#messageReviewerSwitch', '#messageReviewerGroup', '.message-to-reviewer');
		initializeSelectPicker(container);
		initializeCKEditor(container);
		initializePasswordStrengthCheck();
		initializeIdVerification();
		initializeClipboard(container);
		if (window.EzyDev && typeof window.EzyDev.initializeSortableLists === 'function') {
			window.EzyDev.initializeSortableLists(container);
		}
	}
	window.initializeFormComponents = initializeFormComponents;

	/**
	 * Initialize ClipboardJS for copy buttons
	 */
	function initializeClipboard(container = document) {
		if (typeof ClipboardJS === 'undefined') return;

		const $container = $(container);
		const $copyBtns = $container.find('.btn-copy');

		if (!$copyBtns.length) return;

		const clipboard = new ClipboardJS('.btn-copy');

		clipboard.on('success', function(e) {
			const $btn = $(e.trigger);
			const originalHtml = $btn.html();
			const successHtml = $btn.data('success-html') || '<i class="bi bi-check2"></i>';

			$btn.addClass('btn-copy-success').html(successHtml);
			if (typeof toastr !== 'undefined') {
				toastr.success(window.config?.translates?.copied || 'Copied to clipboard!');
			}

			setTimeout(() => {
				$btn.removeClass('btn-copy-success').html(originalHtml);
			}, 2000);

			e.clearSelection();
		});

		clipboard.on('error', function(e) {
			if (typeof toastr !== 'undefined') {
				toastr.error('Failed to copy');
			}
		});
	}

	/**
	 * Initialize password strength check
	 */
	function initializePasswordStrengthCheck() {
		const $passwordInput = $('#new-password');
		const $confirmInput = $('#password-confirmation');
		const $requirements = $('.pw-req');

		if (!$passwordInput.length) return;

		const checkRequirements = () => {
			const val = $passwordInput.val();
			const confirmVal = $confirmInput.val();

			const checks = {
				length: val.length >= 8,
				uppercase: /[A-Z]/.test(val),
				lowercase: /[a-z]/.test(val),
				number: /[0-9]/.test(val),
				special: /[!@#$%^&*(),.?":{}|<>]/.test(val),
				match: val.length > 0 && val === confirmVal
			};

			$requirements.each(function() {
				const req = $(this).data('req');
				const $icon = $(this).find('.req-icon');
				const isPassed = checks[req];

				if (isPassed) {
					$(this).removeClass('text-muted').addClass('text-success');
					$icon.removeClass('bi-circle bi-dash-circle').addClass('bi-check-circle-fill');
				} else {
					$(this).removeClass('text-success').addClass('text-muted');
					$icon.removeClass('bi-check-circle-fill').addClass('bi-dash-circle');
				}
			});
		};

		$passwordInput.on('input', checkRequirements);
		$confirmInput.on('input', checkRequirements);
	}

	/**
	 * Initialize ID verification form interactions
	 */
	function initializeIdVerification() {
		// Event delegation ensures these work even after AJAX injections
		$(document).off('change', '#kycDocument').on('change', '#kycDocument', function() {
			const type = $(this).val();
			if (type === 'national_id') {
				$('#nationalId, #nationalIDNumber').removeClass('d-none');
				$('#passport, #passportNumber').addClass('d-none');
				$('#nationalIDNumber input').attr('required', true);
				$('#passportNumber input').attr('required', false);
			} else {
				$('#nationalId, #nationalIDNumber').addClass('d-none');
				$('#passport, #passportNumber').removeClass('d-none');
				$('#nationalIDNumber input').attr('required', false);
				$('#passportNumber input').attr('required', true);
			}
		});

		// Trigger initial state if element exists when initialized
		if ($('#kycDocument').length) {
			$('#kycDocument').trigger('change');
		}

		// Image Preview delegation
		$(document).off('change', '.image-input').on('change', '.image-input', function() {
			const id = $(this).data('id');
			const file = this.files[0];
			if (file) {
				const reader = new FileReader();
				reader.onload = function(e) {
					$(`#image-preview-${id}`).attr('src', e.target.result);
				}
				reader.readAsDataURL(file);
			}
		});
	}

	/**
	 * Initialize bootstrap-select (SelectPicker) plugins
	 */
	function initializeSelectPicker(container = document) {
		const $container = $(container);
		const $selectpickers = $container.find('.selectpicker');

		if ($selectpickers.length && typeof $.fn.selectpicker !== 'undefined') {
			try {
				$selectpickers.selectpicker('destroy');
			} catch(e) {}

			$selectpickers.selectpicker({
				noneSelectedText: window.config?.translates?.noneSelectedText || 'Nothing selected',
				noneResultsText: window.config?.translates?.noneResultsText || 'No results match',
				countSelectedText: window.config?.translates?.countSelectedText || '{0} of {1} selected'
			});
		}
	}

	/**
	 * Initialize CKEditor 5 for description fields
	 */
	function initializeCKEditor(container = document) {
		const ckeditors = container.querySelectorAll('.ckeditor');

		if (ckeditors.length && typeof ClassicEditor !== 'undefined') {
			function UploadAdapterPlugin(editor) {
				editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
					return new UploadAdapter(loader);
				};
			}

			class CustomPlugins {
				constructor(editor) {
					this.editor = editor;
				}

				init() {
					const editor = this.editor;

					// Dynamically find ButtonView constructor to support basic builds
					let ButtonView;
					try {
						// Create a temporary standard component to get its constructor
						const tempButton = editor.ui.componentFactory.create('bold');
						if (tempButton) {
							ButtonView = tempButton.constructor;
						}
					} catch (e) {
						console.warn('Could not find ButtonView constructor natively');
					}

					if (!ButtonView) return;

					// Fullscreen Plugin
					// Register a formal command for fullscreen to support native shortcuts
					const Command = editor.commands.get('bold').constructor;
					class FullscreenCommand extends Command {
						execute() {
							const editor = this.editor;
							const editorElement = editor.ui.view.element;
							const isFullscreen = editorElement.classList.toggle('ck-editor-fullscreen');

							if (isFullscreen) {
								// Create placeholder to remember original position
								editor.fullscreenPlaceholder = document.createElement('div');
								editorElement.parentElement.insertBefore(editor.fullscreenPlaceholder, editorElement);

								// Move editor to body root to escape any parent constraints
								document.body.appendChild(editorElement);
								document.body.classList.add('ck-fullscreen-mode');
								window.scrollTo(0, 0);
							} else {
								// Restore editor to its original position
								if (editor.fullscreenPlaceholder && editor.fullscreenPlaceholder.parentElement) {
									editor.fullscreenPlaceholder.parentElement.insertBefore(editorElement, editor.fullscreenPlaceholder);
									editor.fullscreenPlaceholder.remove();
								}
								document.body.classList.remove('ck-fullscreen-mode');
							}

							this.value = isFullscreen;
							editor.editing.view.focus();
						}

						refresh() {
							const editorElement = this.editor.ui.view.element;
							this.value = editorElement.classList.contains('ck-editor-fullscreen');
							this.isEnabled = true;
						}
					}
					editor.commands.add('toggleFullscreen', new FullscreenCommand(editor));

					// Fullscreen Button
					editor.ui.componentFactory.add('fullscreen', locale => {
						const view = new ButtonView(locale);
						view.set({
							label: 'Fullscreen (Ctrl+Q)',
							icon: '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 2v6h2V4h4V2H2zm16 0h-6v2h4v4h2V2zM2 18h6v-2H4v-4H2v6zm16 0v-6h-2v4h-4v2h6z"/></svg>',
							tooltip: true,
							isToggleable: true
						});

						// Bind button state to the command state
						const command = editor.commands.get('toggleFullscreen');
						view.bind('isOn').to(command, 'value');
						view.bind('isEnabled').to(command, 'isEnabled');

						view.on('execute', () => {
							editor.execute('toggleFullscreen');
						});

						return view;
					});

					// Bind Keystroke to Command
					editor.keystrokes.set('Ctrl+Q', 'toggleFullscreen');

					// Add Esc support to exit fullscreen
					editor.keystrokes.set('Esc', (data, cancel) => {
						const command = editor.commands.get('toggleFullscreen');
						if (command.value) {
							editor.execute('toggleFullscreen');
							cancel();
						}
					});

					// Smart Enter support for themed blocks (Perforated & Cards):
					// - Pressing Enter in text creates a new line of the same style (Multi-line support).
					// - Pressing Enter on an empty styled line 'breaks out' (Reverts to normal).
					editor.commands.get('enter').on('execute', (evt) => {
						const selection = editor.model.document.selection;
						const parent = selection.getFirstPosition().parent;
						const cls = parent.getAttribute('class') || '';

						if ((cls === 'editor-code-perforated' || cls.includes('editor-card-alert')) && parent.isEmpty) {
							editor.model.change(writer => {
								writer.removeAttribute('class', parent);
							});
							evt.stop(); // Stop the default enter logic
						}
					}, { priority: 'high' });

					// Card Variants - Selection-Only Toggle Mode
					const variants = [
						{ name: 'cardPrimary', label: 'Select Text to Highlight', class: 'editor-card-primary', color: '#cfe2ff' },
						{ name: 'cardDanger', label: 'Select Text to Highlight', class: 'editor-card-danger', color: '#f8d7da' },
						{ name: 'cardInfo', label: 'Select Text to Highlight', class: 'editor-card-info', color: '#cff4fc' }
					];

					variants.forEach(variant => {
						editor.ui.componentFactory.add(variant.name, locale => {
							const view = new ButtonView(locale);
							view.set({
								label: variant.label,
								icon: `<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="12" x="2" y="4" rx="2" fill="${variant.color}" stroke="#666" stroke-width="1"/><path d="M5 8h10M5 11h6" stroke="#666" stroke-width="1" stroke-linecap="round"/></svg>`,
								tooltip: true,
								isToggleable: true
							});

							view.on('execute', () => {
								editor.model.change(writer => {
									const selection = editor.model.document.selection;
									const blocks = Array.from(selection.getSelectedBlocks());

									// Check if already is a card (any card variant)
									const isAlreadyCard = blocks.some(block => {
										const cls = block.getAttribute('class') || '';
										return cls.includes('editor-card-alert');
									});

									if (isAlreadyCard) {
										// TOGGLE OFF: Remove card branding
										for (const block of blocks) {
											writer.removeAttribute('class', block);
										}
									} else if (!selection.isCollapsed) {
										// TOGGLE ON: Apply card branding
										for (const block of blocks) {
											writer.setAttribute('class', `editor-card-alert ${variant.class}`, block);
										}
									}
								});
							});
							return view;
						});
					});

					// Perforated (Code Style) - Selection-Only Toggle Mode
					editor.ui.componentFactory.add('perforated', locale => {
						const view = new ButtonView(locale);
						view.set({
							label: 'Select Text to Perforated',
							icon: '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M7 2a2 2 0 00-2 2v1h10V4a2 2 0 00-2-2H7zM4 6v10a2 2 0 002 2h8a2 2 0 002-2V6H4zm3 3h6v2H7V9zm0 4h4v2H7v-2z"/></svg>',
							tooltip: true,
							isToggleable: true
						});

						view.on('execute', () => {
							editor.model.change(writer => {
								const selection = editor.model.document.selection;
								const blocks = Array.from(selection.getSelectedBlocks());

								// Check if the current area is already perforated
								const isAlreadyPerforated = blocks.some(block => block.getAttribute('class') === 'editor-code-perforated');

								if (isAlreadyPerforated) {
									// TOGGLE OFF: Remove styling from all selected blocks
									for (const block of blocks) {
										writer.removeAttribute('class', block);
									}
								} else if (!selection.isCollapsed) {
									// TOGGLE ON: Only if text is selected
									for (const block of blocks) {
										writer.setAttribute('class', 'editor-code-perforated', block);
									}
								}
							});
						});

						return view;
					});

					// Post-fixer to automatically clean up empty themed blocks (Perforated & Cards)
					editor.model.document.registerPostFixer(writer => {
						let changed = false;
						// Universal scan of the root element to find empty themed blocks
						for (const rootName of editor.model.document.getRootNames()) {
							const root = editor.model.document.getRoot(rootName);
							for (const node of root.getChildren()) {
								if (node.is('element', 'paragraph') && node.isEmpty) {
									const cls = node.getAttribute('class') || '';
									if (cls === 'editor-code-perforated' || cls.includes('editor-card-alert')) {
										writer.removeAttribute('class', node);
										changed = true;
									}
								}
							}
						}
						return changed;
					});
				}

				afterInit() {
					const editor = this.editor;
					// Now safe to extend "paragraph" as core plugins have finished init
					if (editor.model.schema.isRegistered('paragraph')) {
						editor.model.schema.extend('paragraph', { allowAttributes: ['class'] });
					}

					// Extend all heading models to allow 'class' attribute
					['heading2', 'heading3', 'heading4', 'heading5'].forEach(modelName => {
						if (editor.model.schema.isRegistered(modelName)) {
							editor.model.schema.extend(modelName, { allowAttributes: ['class'] });
						}
					});

					// Enable conversion for 'class' attribute globally
					editor.conversion.attributeToAttribute({ model: 'class', view: 'class' });
				}
			}

			ckeditors.forEach(ckeditor => {
				// Prevent double initialization
				if (ckeditor.nextElementSibling && ckeditor.nextElementSibling.classList.contains('ck-editor')) {
					return;
				}

				const editorType = ckeditor.getAttribute('data-editor-type') || 'full';
				const config = {
					language: window.config?.lang || 'en',
					extraPlugins: [UploadAdapterPlugin, CustomPlugins],
					mediaEmbed: { previewsInData: true },
				};

				if (editorType === 'basic') {
					config.toolbar = {
						items: [
							'bold', 'italic', 'underline', '|',
							'fontColor', 'alignment', '|',
							'bulletedList', 'numberedList', '|',
							'link', 'perforated', '|',
							'undo', 'redo'
						]
					};
				} else {
					config.toolbar = {
						items: [
							'heading', '|', 'bold', 'italic', 'underline', '|',
							'fontColor', 'fontBackgroundColor', '|',
							'bulletedList', 'numberedList', 'alignment', 'outdent', 'indent', '|',
							'blockQuote', 'link', 'imageInsert', '|',
							'cardPrimary', 'cardDanger', 'cardInfo', '|',
							'perforated', '|',
							'undo', 'redo', '|', 'fullscreen'
						]
					};
					config.heading = {
						options: [
							{ model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
							{ model: 'heading1', view: 'h2', title: 'Heading 1', class: 'ck-heading_heading1' },
							{ model: 'heading2', view: 'h3', title: 'Heading 2', class: 'ck-heading_heading2' },
							{ model: 'heading3', view: 'h4', title: 'Heading 3', class: 'ck-heading_heading3' },
							{
								model: 'perforated',
								view: { name: 'p', classes: 'editor-code-perforated' },
								title: 'Perforated Code',
								class: 'ck-heading_perforated'
							}
						]
					};
				}

				ClassicEditor.create(ckeditor, config)
				.then(editor => {
					// Store by ID if available, otherwise by name
					const key = ckeditor.id || ckeditor.name;
					if (key) {
						window.productEditors[key] = editor;
					}

					// Special legacy handling for description if needed
					if (ckeditor.id === 'productSubmitDescription') {
						window.productDescriptionEditor = editor;
					}
				})
				.catch(error => {
					console.error('CKEditor Error:', error);
				});
			});
		}
	}

	/**
	 * Multi-Step product Submission Form Handler
	 * Manages form navigation, validation, and draft saving
	 */
	/**
	 * Initialize submission-specific features (Counters, Support Price, Drafts)
	 */
	function initializeSubmissionFeatures() {
		const $form = $('#productSubmissionForm');
		if (!$form.length) return;

		// Initialize State
		lastDraftData = JSON.stringify(collectFormData($form));

		// Initialize Character Counters
		initializeTitleCharCounter();
		initializeCKEditor5CharCounter();

		// Initialize Validation Listeners
		initializeValidationListeners();

		// Initialize Support Price Calculation
		initializeSupportPriceCalculation();

		// Handle Save Draft Button
		$('#saveDraftBtn').on('click', function() {
			saveDraft($form, false);
		});

		// Handle Form Submission (AJAX)
		$form.on('submit', function(e) {
			e.preventDefault();

			// Ensure form is valid before submission
			if (!$form[0].checkValidity()) {
				$form[0].reportValidity();
				return;
			}

			const $submitBtn = $form.find('button:not([type="button"])');
			if ($submitBtn.prop('disabled')) return;

			const originalHtml = $submitBtn.html();

			$submitBtn.html(`<span class="spinner-border spinner-border-sm text-white me-2" role="status" aria-hidden="true"></span>${translate('Submitting...')}`)
					  .prop('disabled', true);

			const data = collectFormData($form);
			const formData = new FormData();

			// Clean and convert data to FormData (Required for file uploads and mixed data)
			Object.keys(data).forEach(key => {
				const value = data[key];
				if (Array.isArray(value)) {
					const suffix = key.endsWith('[]') ? '' : '[]';
					value.forEach(val => formData.append(key + suffix, val));
				} else {
					formData.append(key, value !== null && value !== undefined ? value : '');
				}
			});

			// Handle CSRF if not in data
			if (!formData.has('_token')) {
				formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
			}

			$.ajax({
				url: $form.attr('action'),
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(result) {
					if (result.success) {
						if (typeof toastr !== 'undefined') {
							toastr.success(result.message);
						}

						// Redirect if URL provided
						if (result.data?.redirect) {
							setTimeout(() => {
								window.location.href = result.data.redirect;
							}, 1000);
						} else {
							$submitBtn.html(originalHtml).prop('disabled', false);
						}
					} else {
						if (typeof toastr !== 'undefined') {
							toastr.error(result.message || 'Submission failed');
						}
						$submitBtn.html(originalHtml).prop('disabled', false);
					}
				},
				error: function(xhr) {
					handleAjaxError(xhr, 'Submission failed');
					$submitBtn.html(originalHtml).prop('disabled', false);
				}
			});
		});

		// Initialize Category & Sub-Category Selection
		initializeCategorySelection($form);

		// Initialize Slug Generators (from utility.js)
		if (window.EzyDev && typeof window.EzyDev.initializeSlugGenerators === 'function') {
			window.EzyDev.initializeSlugGenerators();
		}

		// Handle Slug Wrapper Visibility
		$(document).on('input', '#create_slug', function() {
			const $slugWrapper = $('#slugWrapper');
			if ($(this).val().length > 0) {
				if ($slugWrapper.hasClass('d-none')) {
					$slugWrapper.removeClass('d-none').hide().fadeIn(300);
				}
			} else {
				$slugWrapper.fadeOut(300, function() {
					$(this).addClass('d-none');
					$('#show_slug').val('');
				});
			}
		});

		// Start Auto-save Timer (60 seconds)
		startAutoSaveTimer($form);
	}

	function initializeValidationListeners() {
		const selectors = [
			'.product-title-input[name="name"]',
			'.ck-editor__editable',
			'#regular-license-price',
			'#extended-license-price',
			'.support-instructions-text',
			'#regular_extra_features',
			'#extended_extra_features',
			'#custom_services',
		].join(', ');

		$(document).on('input', selectors, function() {
			$(this).removeClass('is-invalid');
		});

		$(document).on('input change', '#product-tags', function() {
			$(this).siblings('.bootstrap-tagsinput').removeClass('is-invalid');
		});
	}

	function initializeTitleCharCounter() {
		$(document).on('input', '.product-title-input', function() {
			const currentLength = $(this).val().length;
			const maxLength = $(this).attr('maxlength') || 100;
			const $counter = $('#titleCharCount');

			if (currentLength > 0) {
				$counter.removeClass('d-none').hide().fadeIn(300);
			} else {
				$counter.fadeOut(300, function() { $(this).addClass('d-none'); });
			}

			$counter.removeClass('text-muted text-warning text-success text-danger');
			if (currentLength > maxLength * 0.9) {
				$counter.text(`Title Length (Max. ${maxLength}) - Not SEO friendly`).addClass('text-danger');
			} else if (currentLength > maxLength * 0.6) {
				$counter.text('Title Length - Excellent').addClass('text-success');
			} else if (currentLength > maxLength * 0.3) {
				$counter.text('Title Length - Fair').addClass('text-warning');
			} else {
				$counter.text('Title Length - Not Enough').addClass('text-muted');
			}
		});
	}

	function initializeCKEditor5CharCounter() {
		const updateCharCount = () => {
			const maxLength = 5000;
			const $editable = $('.ck-editor__editable');
			if ($editable.length) {
				const textContent = $editable.text().trim();
				const currentLength = textContent.length;
				const $counter = $('#descriptionCharCount');

				if (currentLength > 0) {
					$counter.removeClass('d-none').hide().fadeIn(300);
				} else {
					$counter.fadeOut(300, function() { $(this).addClass('d-none'); });
				}

				const characterText = currentLength <= 1 ? 'character' : 'characters';
				$counter.text(`Description length ${currentLength} ${characterText}`);
				$counter.removeClass('text-muted text-success text-warning text-danger');

				if (currentLength > maxLength) $counter.addClass('text-danger');
				else if (currentLength > maxLength * 0.7) $counter.addClass('text-danger');
				else if (currentLength > maxLength * 0.4) $counter.addClass('text-warning');
				else if (currentLength > maxLength * 0.1) $counter.addClass('text-success');
				else $counter.addClass('text-muted');
			}
		};

		const checkCKEditor = setInterval(() => {
			const $editable = $('.ck-editor__editable');
			if ($editable.length) {
				$editable.on('input keyup paste', updateCharCount);
				const observer = new MutationObserver(updateCharCount);
				observer.observe($editable[0], { childList: true, subtree: true, characterData: true });
				updateCharCount();
				clearInterval(checkCKEditor);
			}
		}, 100);
	}

	function initializeSupportPriceCalculation() {
		const $supportPackageSelect = $('#supportPackageSelect');
		if (!$supportPackageSelect.length) return;

		const $regularSupportPriceDisplay = $('#regularSupportPriceDisplay');
		const $extendedSupportPriceDisplay = $('#extendedSupportPriceDisplay');
		const $regularLicensePrice = $('input[name="regular_license_price"]');
		const $extendedLicensePrice = $('input[name="extended_license_price"]');

		const calculateSupportPrices = function() {
			const selectedOption = $supportPackageSelect.find('option:selected');
			const percentage = parseFloat(selectedOption.data('rate-percentage')) || 0;
			const fixed = parseFloat(selectedOption.data('rate-fixed')) || 0;
			const regularLicensePrice = parseFloat($regularLicensePrice.val()) || 0;
			const extendedLicensePrice = parseFloat($extendedLicensePrice.val()) || 0;

			if (selectedOption.val() && (percentage > 0 || fixed > 0)) {
				const regularSupportPrice = Math.ceil((regularLicensePrice * percentage / 100) + fixed);
				const extendedSupportPrice = Math.ceil((extendedLicensePrice * percentage / 100) + fixed);
				$regularSupportPriceDisplay.val(regularSupportPrice.toFixed(2));
				$extendedSupportPriceDisplay.val(extendedSupportPrice.toFixed(2));
			} else {
				$regularSupportPriceDisplay.val('0.00');
				$extendedSupportPriceDisplay.val('0.00');
			}
		};

		$supportPackageSelect.on('change', calculateSupportPrices);
		$regularLicensePrice.on('input', calculateSupportPrices);
		$extendedLicensePrice.on('input', calculateSupportPrices);
		calculateSupportPrices();
	}

	function saveDraft($form, isAuto = false) {
		const $btn = $('#saveDraftBtn');
		const originalText = $btn.html();

		if (!isAuto) {
			$btn.html(`<span class="spinner-border spinner-border-sm text-muted me-2"></span>Saving...`).prop('disabled', true);
		}

		const data = collectFormData($form);
		const formData = new FormData();

		// Clean and convert data to FormData
		Object.keys(data).forEach(key => {
			const value = data[key];
			if (Array.isArray(value)) {
				const suffix = key.endsWith('[]') ? '' : '[]';
				value.forEach(val => formData.append(key + suffix, val));
			} else {
				formData.append(key, value !== null && value !== undefined ? value : '');
			}
		});

		const config = getProductConfig();
		const draftUrl = config?.save_draft_route ?? '/user/product/save-draft';

		// Track state
		lastDraftData = JSON.stringify(data);

		$.ajax({
			url: draftUrl,
			method: 'POST',
			headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
			data: formData,
			processData: false,
			contentType: false,
			success: function(result) {
				if (result.success) {
					// If this was a new draft, increment local counter
					if (!$('input[name="draft_id"]').val() && result.data?.product_id) {
						if (typeof config.current_drafts_count === 'number') {
							config.current_drafts_count++;
						}
					}

					if (result.data?.product_id) {
						let $draftInput = $('input[name="draft_id"]');
						
						if (!$draftInput.length) {
							$draftInput = $('<input type="hidden" name="draft_id">').appendTo($form);
						}

						const isFirstSave = !$draftInput.val();

						$draftInput.val(result.data.product_id);

						// Update URL with draft ID for better UX (so refresh resumes the draft)
						if (isFirstSave && window.history.replaceState) {
							const url = new URL(window.location.href);
							if (!url.searchParams.has('draft')) {
								url.searchParams.set('draft', result.data.product_id);
								window.history.replaceState({ path: url.toString() }, '', url.toString());
							}
						}
					}

					// Only show success feedback for manual saves
					if (!isAuto) {
						$('#draftSuccessMsg').stop(true, true).fadeIn().delay(3000).fadeOut();
					}
				} else {
					if (!isAuto) {
						toastr.error(result.message || 'Error saving draft');
					} else if (result.message && result.message.includes('drafts')) {
						// For auto-save, only notify about limit-specific errors to avoid spam
						toastr.warning(result.message);
						if (autoSaveTimer) clearInterval(autoSaveTimer);
					}
				}
			},
			error: function(xhr) {
				const error = xhr.responseJSON;
				if (!isAuto) {
					handleAjaxError(xhr, 'Failed to save draft');
				} else if (error && (error.message || '').includes('drafts')) {
					// Stop timer and notify if limit reached during auto-save
					toastr.warning(error.message);
					if (autoSaveTimer) clearInterval(autoSaveTimer);
				}
			},
			complete: function() {
				if (!isAuto) {
					$btn.html(originalText).prop('disabled', false);
				}
			}
		});
	}

	function startAutoSaveTimer($form) {
		const config = getProductConfig();
		if (!config.save_draft_route) return;

		if (autoSaveTimer) clearInterval(autoSaveTimer);

		autoSaveTimer = setInterval(() => {
			const config = getProductConfig();
			const currentData = JSON.stringify(collectFormData($form));
			const isActionsDisabled = $('#saveDraftBtn').prop('disabled') || $('#productSubmitBtn').prop('disabled');
			const isNewDraft = !$('input[name="draft_id"]').val();

			// Condition: No changes OR actions disabled OR (Is new draft AND limit already reached)
			if (currentData === lastDraftData || isActionsDisabled) return;

			if (isNewDraft && typeof config.current_drafts_count === 'number' && config.current_drafts_count >= config.maximum_drafts) {
				return;
			}

			saveDraft($form, true);
		}, 30000); // 30 seconds
	}

	function collectFormData($form) {
		const data = {};
		const formData = new FormData($form[0]);

		// Extract standard fields
		for (let [key, value] of formData.entries()) {
			if (key.includes('[') || ['description', 'tags', 'regular_extra_features', 'extended_extra_features', 'slug'].includes(key)) continue;

			if (key.endsWith('[]')) {
				const cleanKey = key.slice(0, -2);
				(data[cleanKey] = data[cleanKey] || []).push(value);
			} else {
				data[key] = value;
			}
		}

		// Slug field
		data['slug'] = $('#show_slug').val() || '';

		// Category Options (Selectpicker)
		$form.find('select[name*="options["]').each(function() {
			const $select = $(this);
			data[$select.attr('name')] = $select.selectpicker('val') || '';
		});

		// Rich Text (CKEditor 5)
		data['description'] = window.productEditors?.['productSubmitDescription']?.getData() ?? ($('#productSubmitDescription').val() || '');
		data['support_instructions'] = window.productEditors?.['support_instructions_editor']?.getData() ?? ($('#support_instructions_editor').val() || '');

		// Tags (Bootstrap Tagsinput)
		const $tagsInput = $('#product-tags');
		if ($tagsInput.length) {
			try {
				const items = $tagsInput.tagsinput('items');
				data['tags'] = Array.isArray(items) ? items : (items ? [items] : []);
			} catch(e) {
				data['tags'] = ($tagsInput.val() || '').split(',').map(t => t.trim()).filter(Boolean);
			}
		} else {
			data['tags'] = [];
		}

		// Extra Features
		const $hasExtraFeatures = $('#additionalFeatureSwitch');
		if (!$hasExtraFeatures.length || $hasExtraFeatures.is(':checked')) {
			data['regular_extra_features'] = $('#regular_extra_features').val() || '';
			data['extended_extra_features'] = $('#extended_extra_features').val() || '';
		}
		data['has_additional_features'] = $hasExtraFeatures.is(':checked') ? 1 : 0;

		// Gallery (Multiple Selectpicker)
		const $gallery = $('select[name="gallery[]"]');
		if ($gallery.length) {
			data['gallery[]'] = $gallery.selectpicker('val') || [];
		}

		// Booleans & Radios
		data['has_custom_services'] = $('#customServicesSwitch').is(':checked') ? 1 : 0;
		data['free_product'] = $('.free-product-option:checked').val() || 0;
		data['purchasing_status'] = $('input[name="purchasing_status"]:checked').val() || 1;

		return data;
	}

	/**
	 * Handle dynamic category selection, sub-categories, and category options
	 */
	function initializeCategorySelection($form) {
		const $categorySelect = $('#productCategorySelect');
		const $subCategoryWrapper = $('#subCategoryWrapper');
		const $optionsWrapper = $('#categoryOptionsWrapper');
		const $filesBoxWrapper = $('#filesBoxWrapper');

		if (!$categorySelect.length) return;

		$categorySelect.on('change', function() {
			const slug = $(this).val();
			if (!slug) return;

			// Show loading state
			$subCategoryWrapper.addClass('opacity-50 pointer-events-none');
			$optionsWrapper.addClass('opacity-50 pointer-events-none');
			$filesBoxWrapper.addClass('opacity-50 pointer-events-none');

			const config = getProductConfig();
			const url = config.category_data_route.replace(':slug', slug);

			$.ajax({
				url: url,
				type: 'GET',
				success: function(response) {
					// Update Sub-Categories
					let subHtml = `<label class="form-label fw-medium">${translate('Sub Category')}</label>
								 <select name="sub_category" id="productSubCategorySelect" class="form-select form-select-md selectpicker" title="${translate('Select a Sub Category')}" data-size="5" data-live-search="true">`;
					if (response.sub_categories.length > 0) {
						response.sub_categories.forEach(sub => {
							subHtml += `<option value="${sub.slug}">${sub.name}</option>`;
						});
					}
					subHtml += `</select>`;
					$subCategoryWrapper.html(subHtml).find('.selectpicker').selectpicker('render');

					// Update Category Options
					let optionsHtml = '<div class="row g-4">';
					if (response.category_options.length > 0) {
						response.category_options.forEach(opt => {
							const isMultiple = opt.type === 2;
							const required = opt.is_required === true;
							const titleText = isMultiple ? translate('Select one or more') : translate('Select one');
							optionsHtml += `<div class="col-lg-6">
								<label class="form-label fw-medium">${opt.name}${required ? '<span class="text-danger ms-1">*</span>' : ''}</label>
								<select name="options[${opt.id}]${isMultiple ? '[]' : ''}" class="form-select form-select-md selectpicker" title="${titleText}" ${isMultiple ? 'multiple' : ''} ${required ? 'required' : ''} data-size="5" data-live-search="true">`;

							if (!required) optionsHtml += `<option value="">${translate('None')}</option>`;

							if (opt.options) {
								opt.options.forEach(val => {
									optionsHtml += `<option value="${val}">${val}</option>`;
								});
							}
							optionsHtml += `</select></div>`;
						});
					}
					optionsHtml += '</div>';
					$optionsWrapper.html(optionsHtml).find('.selectpicker').selectpicker('render');

					// Update Config
					config.buyer_fee = response.file_config.buyer_fee;
					config.upload = response.file_config.upload;
					config.load_files_route = response.file_config.load_files_route;

					$form.data('config', config);

					// Full sync: fetch new HTML partial and re-init
					fetchFilesBox(slug, $filesBoxWrapper);

					// Also refresh selects with the data we already have
					loadUploadedFiles(response.uploaded_files);
				},
				complete: function() {
					$subCategoryWrapper.removeClass('opacity-50 pointer-events-none');
					$optionsWrapper.removeClass('opacity-50 pointer-events-none');
					$filesBoxWrapper.removeClass('opacity-50 pointer-events-none');
				}
			});
		});

		function fetchFilesBox(slug, $wrapper) {
			const currentUrl = new URL(window.location.href);
			currentUrl.searchParams.set('category', slug);

			$.get(currentUrl.toString(), function(data) {
				const $newData = $(data);
				$wrapper.html($newData.find('#filesBoxWrapper').html());
				$wrapper.find('.selectpicker').selectpicker('render');

				// Re-initialize Dropzone and other components
				setTimeout(() => {
					initializeDropzoneUpload();
					initializeMainFileSourceSwitcher();
				}, 10);
			});
		}
	}

	/**
	 * Start document ready function
	 */
	$(document).ready(function() {

		let inputPrice = $('.input-price');
		if (inputPrice.length) {
			inputPrice.priceFormat({
				prefix: '',
				thousandsSeparator: '',
				clearOnEmpty: true
			});
		}

		// Components are initialized via initializeFormComponents() which is called below


		let kycDocument = $('#kycDocument');

		kycDocument.on('change', function() {
			let kycDocumentVal = $(this).val();

			let nationalId = $('#nationalId'),
				nationalIDNumber = $('#nationalIDNumber'),
				passport = $('#passport'),
				passportNumber = $('#passportNumber');
			if (kycDocumentVal == "national_id") {
				passport.addClass('d-none');
				passportNumber.addClass('d-none');
				nationalId.removeClass('d-none');
				nationalIDNumber.removeClass('d-none');
			} else if (kycDocumentVal == "passport") {
				nationalId.addClass('d-none');
				nationalIDNumber.addClass('d-none');
				passport.removeClass('d-none');
				passportNumber.removeClass('d-none');
			}
		});

		initializeFormComponents();
	});

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

})(jQuery);



/**
 * User Panel Sidebar & Navigation Handler
 * Minimalist refactor for "Pill/Floating" Dashboard
 * Strictly manages mobile drawer state; Desktop sidebar is static.
 */
const UserPanel = (() => {
  "use strict";

  const CONFIG = {
    breakpoint: 1199.98,
  };

  const DOM = {};

  const isDesktop = () => window.innerWidth > CONFIG.breakpoint;

  const toggle = (forceClose = false) => {
    // Only toggle "active" class for mobile sidebar drawer
    if (isDesktop()) return;

    if (forceClose) {
      DOM.sidebar?.classList.remove("active");
      DOM.body?.classList.remove("active");
    } else {
      DOM.sidebar?.classList.toggle("active");
      DOM.body?.classList.toggle("active");
    }

    if (DOM.toggleBtn) {
        DOM.toggleBtn.setAttribute('aria-expanded', DOM.sidebar?.classList.contains("active"));
    }
  };

  const handleResize = () => {
    // Ensure mobile classes are cleaned up on desktop resize
    if (isDesktop()) {
      DOM.sidebar?.classList.remove("active");
      DOM.body?.classList.remove("active");
    }
  };

  const setupSearch = () => {
    const searchContainers = document.querySelectorAll(".userpanel-search");
    if (!searchContainers.length) return;

    const debounce = (fn, wait) => {
      let timeout;
      return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn.apply(this, args), wait);
      };
    };

    searchContainers.forEach(container => {
      const input = container.querySelector(".userpanel-search-input");
      const results = container.querySelector(".userpanel-search-results");
      const clearBtn = container.querySelector(".userpanel-search-clear");

      if (!input || !results) return;

      const performSearch = debounce(async (query) => {
        if (query.length < 2) {
          results.innerHTML = "";
          container.classList.remove("active");
          return;
        }

        try {
          const response = await fetch(`/user/search?query=${encodeURIComponent(query)}`);
          const data = await response.json();

          if (data.length > 0) {
            results.innerHTML = data.map(item => `
              <a href="${item.url}" class="userpanel-search-result-item">
                <div class="userpanel-search-result-icon">
                  <i class="bi ${item.icon}"></i>
                </div>
                <div class="userpanel-search-result-info">
                  <span class="userpanel-search-result-title">${item.title}</span>
                  <span class="userpanel-search-result-type">${item.type}</span>
                </div>
              </a>
            `).join("");
          } else {
            results.innerHTML = `
              <div class="userpanel-search-no-results">
                <i class="bi bi-search"></i>
                <span>No results found for "${query}"</span>
              </div>
            `;
          }
          container.classList.add("active");
        } catch (error) {
          console.error("Search failed:", error);
        }
      }, 300);

      input.addEventListener("input", (e) => performSearch(e.target.value));

      clearBtn?.addEventListener("click", () => {
        input.value = "";
        results.innerHTML = "";
        container.classList.remove("active");
        input.focus();
      });

      document.addEventListener("click", (e) => {
        if (!container.contains(e.target)) {
          container.classList.remove("active");
        }
      });
    });
  };

  const setupPayoutCalculator = () => {
    // Delegated input listener for real-time calculations
    $(document).on('input', 'input#amount[data-fees-type]', function() {
      const $amountInput = $(this);
      const $container = $amountInput.closest('.modal, .userpanel-card').parent();

      const feesType = $amountInput.data('fees-type');
      const feesValue = Number($amountInput.data('fees-value') || 0);
      const currencySymbol = $amountInput.data('currency-symbol') || '';
      const currencyPosition = $amountInput.data('currency-position') || 'left';
      const availableBalance = Number($amountInput.data('available') || 0);
      const minAmount = Number($amountInput.data('min') || 0);

      const formatAmount = (amount) => {
        const formatted = Number(amount).toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
        return currencyPosition === 'left' ? currencySymbol + formatted : formatted + currencySymbol;
      };

      const amount = parseFloat($amountInput.val()) || 0;
      const fees = feesType === 'percentage' ? (amount * feesValue) / 100 : feesValue;
      const actualFees = amount > 0 ? fees : 0;
      const total = Math.max(0, amount - actualFees);

      $container.find('#displayAmount').text(formatAmount(amount));
      $container.find('#displayFees').text((actualFees > 0 ? '-' : '') + formatAmount(actualFees));
      $container.find('#displayTotal').text(formatAmount(total));

      // Progress Bar Logic
      const $progressBar = $container.find('#payoutProgressBar');
      const $percentText = $container.find('#payoutPercent');

      if ($progressBar.length) {
        const percentage = availableBalance > 0 ? Math.min(100, (amount / availableBalance) * 100) : 0;
        $progressBar.css('width', percentage + '%');
        if ($percentText.length) $percentText.text(Math.round(percentage) + '%');

        // Remove all state classes first
        $progressBar.removeClass('bg-warning bg-success bg-danger bg-primary');
        if ($percentText.length) $percentText.removeClass('text-dark text-danger text-success text-warning text-primary');

        if (amount <= 0) {
          $progressBar.addClass('bg-primary');
          if ($percentText.length) $percentText.addClass('text-dark');
        } else if (amount > availableBalance) {
          $progressBar.addClass('bg-danger');
          if ($percentText.length) $percentText.addClass('text-danger');
        } else if (amount >= minAmount) {
          $progressBar.addClass('bg-success');
          if ($percentText.length) $percentText.addClass('text-success');
        } else {
          $progressBar.addClass('bg-warning');
          if ($percentText.length) $percentText.addClass('text-warning');
        }
      }
    });

    // Auto-initialize on modal show
    $(document).on('shown.bs.modal', '#payoutModal', function() {
        const $input = $(this).find('input#amount[data-fees-type]');
        if ($input.length) {
            $input.trigger('input');
            $input.focus();
        }
    });
  };

  const setupFilters = () => {
    // Auto-submit filter forms on select change
    const filterForms = document.querySelectorAll('.userpanel-container form[method="GET"]');
    filterForms.forEach(form => {
      const selects = form.querySelectorAll('select');
      selects.forEach(select => {
        select.addEventListener('change', () => form.submit());
      });
    });
  };

  const setupPurchasesModals = () => {
    // Purchase Code Modal Trigger
    $('.product-purchase-code').on('click', function(e) {
      e.preventDefault();
      const purchaseCode = $(this).data('purchase-code');
      const $modal = $('#purchaseCodeModal');
      $modal.find('input[id=purchaseCode]').val(purchaseCode);
      $modal.modal('show');
    });

    // Support Buying Modal Trigger
    $('.support-purchase').on('click', function() {
      const action = $(this).data('action');
      const $modal = $('#supportPurchaseModal');
      $modal.find('form').attr('action', action);
      $modal.modal('show');
    });

    // Support Renewal Modal Trigger
    $('.support-extend').on('click', function() {
      const action = $(this).data('action');
      const $modal = $('#supportExtendModal');
      $modal.find('form').attr('action', action);
      $modal.modal('show');
    });
  };

  /**
   * Setup restoration notice dismissal (AJAX)
   */
  const setupRestorationNotices = () => {
    $(document).on('click', '[data-dismiss-restoration]', function(e) {
      e.preventDefault();
      const $btn = $(this);
      const actionUrl = $btn.data('action');
      const $notice = $btn.closest('.restoration-notice');

      if (!actionUrl) return;

      $btn.prop('disabled', true).addClass('opacity-50');

      $.ajax({
        url: actionUrl,
        type: 'POST',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          if (response.success) {
            $notice.fadeOut(300, function() {
              $(this).remove();
            });
          } else {
            if (typeof toastr !== 'undefined') {
              toastr.error(response.message || 'Error dismissing notice');
            }
            $btn.prop('disabled', false).removeClass('opacity-50');
          }
        },
        error: function(xhr) {
          handleAjaxError(xhr, 'An error occurred. Please try again.');
          $btn.prop('disabled', false).removeClass('opacity-50');
        }
      });
    });
  };

  /**
   * Specialized initialization for the Refund Creation Modal
   */
  const initRefundModal = () => {
    const $modal = $('#createRefundModal');
    if ($modal.length === 0) return;

    // Handle data-handoff from trigger button
    $modal.on('show.bs.modal', function (e) {
      const $trigger = $(e.relatedTarget);
      const purchaseId = $trigger.data('purchase');
      if (purchaseId) {
        $modal.find('select[name="purchase"]').val(purchaseId);
      }
    });

    // Handle URL-based auto-selection
    const urlParams = new URLSearchParams(window.location.search);
    const urlPurchaseId = urlParams.get('purchase');
    if (urlPurchaseId) {
      $modal.find('select[name="purchase"]').val(urlPurchaseId);
    }
  };

  /**
   * Setup financial balance visibility toggle (Privacy Mode)
   */
  const setupBalanceToggle = () => {
    const $toggleBtn = $('#balanceToggle');
    if (!$toggleBtn.length) return;

    $toggleBtn.on('click', function() {
      const $btn = $(this);
      const $icon = $btn.find('i');
      const isHidden = $('.amount-real').first().hasClass('d-none');

      if (isHidden) {
        $('.amount-masked').addClass('d-none');
        $('.amount-real').removeClass('d-none');
        $icon.removeClass('bi-eye').addClass('bi-eye-slash');
      } else {
        $('.amount-masked').removeClass('d-none');
        $('.amount-real').addClass('d-none');
        $icon.removeClass('bi-eye-slash').addClass('bi-eye');
      }
    });
  };


  const init = () => {
    DOM.sidebar = document.querySelector(".userpanel-sidebar");
    DOM.body = document.querySelector(".userpanel-body");
    DOM.toggleBtn = document.querySelector(".userpanel-toggle-btn");
    DOM.overlay = document.querySelector(".userpanel-sidebar-overlay");

    // Setup non-sidebar dependent components
    setupSearch();
    setupFilters();
    setupPurchasesModals();
    setupPayoutCalculator();
    setupBalanceToggle();
    setupRestorationNotices();
    initRefundModal();

    // Re-initialize Payout Calculator for AJAX-loaded withdrawal modal
    $(document).on('shown.bs.modal', '#payoutModal', function() {
        setupPayoutCalculator();
    });

    if (!DOM.sidebar || !DOM.body) return;

    // Sidebar & Mobile specific events
    DOM.toggleBtn?.addEventListener("click", () => toggle());
    DOM.overlay?.addEventListener("click", () => toggle(true));
    window.addEventListener("resize", handleResize);

    // Initial ARIA
    DOM.toggleBtn?.setAttribute('aria-expanded', DOM.sidebar.classList.contains("active"));
  };

  return { init, setupPayoutCalculator };
})();

document.addEventListener("DOMContentLoaded", UserPanel.init);

// Standalone grace period countdown (independent of UserPanel init chain)
(function() {
    function startGraceCountdown() {
        var el = document.getElementById('grace-period-wrapper');
        if (!el) return;
        var secs = parseInt(el.getAttribute('data-grace-seconds'), 10);
        if (!secs || isNaN(secs) || secs <= 0 || el.dataset.timerStarted) return;
        el.dataset.timerStarted = 'true';
        var counter = document.getElementById('grace-seconds');
        var timer = setInterval(function() {
            secs--;
            if (counter) counter.textContent = secs;
            if (secs <= 0) {
                clearInterval(timer);
                el.style.transition = 'opacity 0.3s';
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 300);
            }
        }, 1000);
    }

    // Run on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startGraceCountdown);
    } else {
        startGraceCountdown();
    }

})();
