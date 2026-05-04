(function ($) {
  "use strict";

  // ============================================
  // CONSTANTS
  // ============================================

  const MENU_ITEM_HEIGHT = 38;
  const COUNTER_CARD_WIDTH_THRESHOLD = 350;
  const ALLOWED_IMAGE_EXTENSIONS = ["jpg", "jpeg", "png", "gif", "bmp", "svg", "webp"];
  const PASSWORD_LENGTH = 16;
  const COLOR_SWATCHES = ["#067bc2", "#84bcda", "#80e377", "#ecc30b", "#f37748", "#d56062"];

  // ============================================
  // GLOBAL EVENT LISTENERS
  // ============================================

  // ============================================
  // UTILITY FUNCTIONS
  // ============================================

  /**
   * Initialize Bootstrap tab manager with sessionStorage and URL updates
   * @param {Object} options - Configuration options
   * @param {string} options.storageKey - Key for sessionStorage
   * @param {string} options.tabSelector - jQuery selector for tab elements (default: 'a[data-bs-toggle="tab"]')
   * @param {string} options.tabSuffix - Suffix for tab IDs (default: '-tab')
   * @param {string} options.baseUrl - Base URL for query param mode (if set, uses ?tab= format)
   * @param {string} options.defaultTab - Default tab name (default: 'details')
   */
  window.initTabManager = function (options) {
    const config = {
      storageKey: options.storageKey || 'activeTab',
      tabSelector: options.tabSelector || 'a[data-bs-toggle="tab"]',
      tabSuffix: options.tabSuffix || '-tab',
      baseUrl: options.baseUrl || null,
      defaultTab: options.defaultTab || 'details'
    };

    // Build URL based on config mode
    function buildUrl(tabName, dataUrl) {
      if (config.baseUrl) {
        // Query param mode: use ?tab= format
        if (tabName === config.defaultTab) {
          return config.baseUrl;
        }
        return config.baseUrl + '?tab=' + tabName;
      }
      // Clean URL mode: use data-url attribute
      return dataUrl;
    }

    // Restore active tab from sessionStorage or URL query param on page load
    function restoreActiveTab() {
      // In query param mode, the server renders the correct active tab
      // We only need to sync sessionStorage with the current tab
      if (config.baseUrl) {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab') || config.defaultTab;
        sessionStorage.setItem(config.storageKey, tabParam);
        return;
      }

      // Clean URL mode: restore from sessionStorage
      const savedTab = sessionStorage.getItem(config.storageKey);

      if (savedTab) {
        const $savedLink = $(`[data-bs-target="#${savedTab}${config.tabSuffix}"]`);

        if ($savedLink.length) {
          const savedUrl = buildUrl(savedTab, $savedLink.data('url'));

          // Activate the saved tab using Bootstrap
          const tab = new bootstrap.Tab($savedLink[0]);
          tab.show();

          // Update URL without reload
          if (window.history && window.history.replaceState) {
            window.history.replaceState({ url: savedUrl, tab: savedTab }, '', savedUrl);
          }
        }
      }
    }

    // Save tab to sessionStorage and update URL when tab is shown
    $(config.tabSelector).on('shown.bs.tab', function (e) {
      const $link = $(e.target);
      const tabName = $link.data('tab');
      const dataUrl = $link.data('url');

      if (tabName) {
        const url = buildUrl(tabName, dataUrl);

        // Save to sessionStorage
        sessionStorage.setItem(config.storageKey, tabName);

        // Update browser URL without reload
        if (url && window.history && window.history.pushState) {
          window.history.pushState({ url: url, tab: tabName }, '', url);
        }
      }
    });

    // Handle browser back/forward buttons
    const popstateHandler = function (event) {
      if (event.state && event.state.tab) {
        const $link = $(`[data-bs-target="#${event.state.tab}${config.tabSuffix}"]`);
        if ($link.length) {
          const tab = new bootstrap.Tab($link[0]);
          tab.show();
          sessionStorage.setItem(config.storageKey, event.state.tab);
        }
      }
    };

    // Remove existing handler if any to prevent duplicates
    window.removeEventListener('popstate', popstateHandler);
    window.addEventListener('popstate', popstateHandler);

    // Restore active tab on page load
    restoreActiveTab();
  };

  /**
   * Format seconds into MM:SS time code
   * @param {number} seconds - Time in seconds
   * @returns {string} Formatted time string
   */
  const formatTimeCode = (seconds) => {
    return new Date(seconds * 1000).toISOString().slice(14, 19);
  };









  // ============================================
  // SIDEBAR MENU MANAGEMENT
  // ============================================

  const dropdownElements = document.querySelectorAll("[data-dropdown]");
  const dropdownV2Elements = document.querySelectorAll("[data-dropdown-v2]");

  /**
   * Initialize sidebar dropdown menus with animated height transitions
   */
  const initializeSidebarDropdowns = () => {
    dropdownElements.forEach(dropdownElement => {
      const menuContainer = dropdownElement.querySelector(".ezydev-sidebar-submenu");
      const titleElement = dropdownElement.querySelector(".ezydev-sidebar-link");

      const updateDropdownHeight = () => {
        const isActive = dropdownElement.classList.contains("active");
        const itemCount = menuContainer.children.length;
        menuContainer.style.height = isActive ? `${itemCount * MENU_ITEM_HEIGHT}px` : "0";
      };

      titleElement.addEventListener("click", () => {
        dropdownElement.classList.toggle("active");
        updateDropdownHeight();
      });

      window.addEventListener("load", updateDropdownHeight);
    });
  };

  /**
   * Initialize v2 dropdowns with click-outside-to-close functionality
   */
  const initializeDropdownsV2 = () => {
    dropdownV2Elements.forEach(dropdownElement => {
      window.addEventListener("click", (event) => {
        if (dropdownElement.contains(event.target)) {
          dropdownElement.classList.toggle("active");
          setTimeout(() => dropdownElement.classList.toggle("animated"), 0);
        } else {
          dropdownElement.classList.remove("active", "animated");
        }
      });
    });
  };

  if (dropdownElements.length) initializeSidebarDropdowns();
  if (dropdownV2Elements.length) initializeDropdownsV2();

  // ============================================
  // DASHBOARD COUNTER CARDS
  // ============================================

  const counterCardElements = document.querySelectorAll(".counter-card");

  /**
   * Toggle counter card layout based on available width
   */
  const updateCounterCardsLayout = () => {
    counterCardElements.forEach(card => {
      const shouldActivate = card.clientWidth <= COUNTER_CARD_WIDTH_THRESHOLD;
      card.classList.toggle("active", shouldActivate);
    });
  };

  if (counterCardElements.length) {
    window.addEventListener("load", updateCounterCardsLayout);
    window.addEventListener("resize", updateCounterCardsLayout);
  }

  // ============================================
  // NOTIFICATION BADGES
  // ============================================

  /**
   * Initialize counter badges with visibility and animation
   * @param {string} selector - CSS selector for counter elements
   * @param {boolean} shouldAnimate - Whether to add animation class
   */
  const initializeCounterBadges = (selector, shouldAnimate = false) => {
    const counterElements = document.querySelectorAll(selector);

    counterElements.forEach(counter => {
      const count = parseInt(counter.innerHTML, 10);

      if (count === 0) {
        counter.classList.add("disabled");
      } else if (shouldAnimate) {
        counter.classList.add("pulse-animation");
      }
    });
  };

  initializeCounterBadges(".ezydev-sidebar-link .counter", true);
  initializeCounterBadges(".codebay-notifications-title .counter", true);


  /**
   * Initialize all form components (CKEditor, Selectpicker, etc.)
   * @param {HTMLElement|Document} container - The container to search for components
   */
  window.initializeFormComponents = function (container = document) {
    const $container = $(container);

    // 1. CKEditor Initialization
    const ckEditorElements = container.querySelectorAll(".ckeditor");
    if (ckEditorElements.length > 0) {
      function UploadAdapterPlugin(editor) {
        editor.plugins.get("FileRepository").createUploadAdapter = (loader) => {
          return new UploadAdapter(loader);
        };
      }

      ckEditorElements.forEach((element) => {
        // Skip if already initialized
        if (element.nextSibling && element.nextSibling.classList && element.nextSibling.classList.contains('ck-editor')) {
          return;
        }

        ClassicEditor.create(element, {
          language: config.lang,
          extraPlugins: [UploadAdapterPlugin],
          mediaEmbed: {
            previewsInData: true,
          },
        }).catch((error) => {
          console.error(error);
        });
      });
    }

    // 2. Selectpicker (if present)
    if ($.fn.selectpicker) {
      const $selects = $container.find(".selectpicker");
      if ($selects.length > 0) {
        $selects.selectpicker('render');
      }
    }
  };

  // Initial call on page load
  initializeFormComponents();

  // ============================================
  // FILE UPLOAD AND PREVIEW
  // ============================================

  const selectFileButton = $("#selectFileBtn");
  const selectedFileInput = $("#selectedFileInput");
  const filePreviewBox = $(".file-preview-box");
  const filePreviewImage = $("#filePreview");

  selectFileButton.on("click", function () {
    selectedFileInput.trigger("click");
  });

  selectedFileInput.on("change", function () {
    if (this.files && this.files[0]) {
      filePreviewBox.removeClass("d-none");
      window.EzyDev.previewImageFile(this, filePreviewImage[0]);
    }
  });


  // ============================================
  // CODE EDITORS (HTML & CSS)
  // ============================================

  const htmlEditorElement = document.getElementById("html-editor");

  if (htmlEditorElement) {
    const htmlEditor = CodeMirror.fromTextArea(htmlEditorElement, {
      lineNumbers: true,
      mode: "htmlmixed",
      theme: "monokai",
      keyMap: "sublime",
      autoCloseBrackets: true,
      matchBrackets: true,
      showCursorWhenSelecting: true,
    });
    htmlEditor.setSize(null, 400);
  }

  const cssEditorElement = document.getElementById("css-editor");

  if (cssEditorElement) {
    const cssEditor = CodeMirror.fromTextArea(cssEditorElement, {
      lineNumbers: true,
      mode: "text/css",
      theme: "monokai",
      keyMap: "sublime",
      autoCloseBrackets: true,
      matchBrackets: true,
      showCursorWhenSelecting: true,
    });
    cssEditor.setSize(null, 700);
  }

  // ============================================
  // SORTABLE LISTS AND TABLES
  // ============================================

  /**
   * @param {string|null} url - Optional URL to send the request to
   */
  window.updateSortedItems = (ids, url = null) => {
    const targetUrl = url || (typeof sortingRoute !== 'undefined' ? sortingRoute : null);

    if (!targetUrl) {
      return;
    }

    $.ajax({
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: targetUrl,
      type: "POST",
      data: { ids: ids },
      dataType: "JSON",
      success: function (response) {
        if (!$.isEmptyObject(response.error)) {
          toastr.error(response.error);
        }
      },
      error: function (request, status, error) {
        toastr.error(error);
      },
    });
  };

  const sortableListElement = $(".sortable-list");

  if (sortableListElement.length) {
    sortableListElement.sortable({
      handle: ".sortable-list-handle",
      placeholder: "sortable-list-placeholder",
      axis: "y",
      helper: function (e, ui) {
        ui.children().each(function () {
          $(this).width($(this).width());
        });
        return ui;
      },
      update: function () {
        const $list = $(this);
        const customUrl = $list.data("sortable") || $list.closest("[data-sortable]").data("sortable");
        const sortedIds = $list.sortable("toArray", {
          attribute: "data-id",
        });
        window.updateSortedItems(sortedIds.join(","), customUrl);
      },
    });
  }
/*
  const sortableTableTbody = $(".sortable-table-tbody");

  if (sortableTableTbody.length) {
    sortableTableTbody.sortable({
      handle: ".sortable-table-handle",
      placeholder: "sortable-table-placeholder",
      axis: "y",
      helper: function (e, tr) {
        const $originals = tr.children();
        const $helper = tr.clone();
        $helper.children().each(function (index) {
          $(this).width($originals.eq(index).width());
        });
        return $helper;
      },
      update: function () {
        const $tbody = $(this);
        const customUrl = $tbody.closest("table").data("sortable");
        const sortedIds = $tbody.sortable("toArray", {
          attribute: "data-id",
        });
        window.updateSortedItems(sortedIds.join(","), customUrl);
      },
    });
  }*/

  // ============================================
  // NESTABLE - Drag & Drop Sorting
  // ============================================

  /**
   * Initialize nestable on a given element or all .nestable elements
   * @param {jQuery|string|null} selector - Optional selector or jQuery object
   */
  function initNestable(selector = null) {
    const nestableElement = selector ? $(selector) : $(".nestable");

    if (!nestableElement.length) return;

    // Check if nestable has items before initializing
    if (nestableElement.find('.dd-list > .dd-item').length === 0) return;

    const maxDepth = typeof nestableMaxDepth !== "undefined" ? nestableMaxDepth : 3;
    const location = nestableElement.data("location") || null;
    const customUrl = nestableElement.data("sortable");

    // Destroy existing instance if any
    if (nestableElement.data('nestable')) {
      nestableElement.nestable('destroy');
    }

    nestableElement.nestable({ maxDepth: maxDepth });

    nestableElement.off('change').on("change", function () {
      const serializedIds = JSON.stringify(nestableElement.nestable("serialize"));

      // If location is specified, send it with the request (for menu management)
      if (location && (customUrl || typeof sortingRoute !== "undefined")) {
        $.ajax({
          headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
          url: customUrl || sortingRoute,
          type: "POST",
          data: {
            ids: serializedIds,
            location: location,
          },
          dataType: "JSON",
          success: function (response) {
            if (response.error) {
              toastr.error(response.error);
            }
          },
          error: function (request, status, error) {
            toastr.error(error);
          },
        });
      } else {
        window.updateSortedItems(serializedIds, customUrl);
      }
    });
  }

  // Initialize on page load
  initNestable();

  // Expose globally for reinit after AJAX
  window.initNestable = initNestable;

  // ============================================
  // COMMENT MANAGEMENT
  // ============================================

  const commentViewButtons = $(".admin-panel-view-comment");
  const viewCommentModal = $("#viewComment");
  const deleteCommentForm = $("#deleteCommentForm");
  const publishCommentForm = $("#publishCommentForm");
  const publishCommentButton = $(".publish-comment-btn");
  const commentInput = $("#comment");

  commentViewButtons.on("click", function (event) {
    event.preventDefault();
    const commentId = $(this).data("id");

    $.ajax({
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: `${config.admin_url}/blog/comments/${commentId}/view`,
      type: "GET",
      dataType: "JSON",
      success: function (response) {
        if ($.isEmptyObject(response.error)) {
          commentInput.val(response.comment);
          deleteCommentForm.attr("action", response.delete_link);

          if (response.status === 1) {
            publishCommentButton.addClass("disabled");
          } else {
            publishCommentButton.removeClass("disabled");
            publishCommentForm.attr("action", response.publish_link);
          }

          viewCommentModal.modal("show");
        } else {
          toastr.error(response.error);
        }
      },
    });
  });


  // ============================================
  // ADDON STATUS TOGGLE
  // ============================================

  const addonStatusToggles = $(".addon-status");

  addonStatusToggles.on("change", function () {
    const updateLink = $(this).data("update-link");
    const status = $(this).is(":checked") ? 1 : 0;

    $.ajax({
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: updateLink,
      type: "POST",
      data: { status: status },
      dataType: "JSON",
      success: function (response) {
        if (!$.isEmptyObject(response.error)) {
          toastr.error(response.error);
        }
      },
      error: function (request, status, error) {
        toastr.error(error);
      },
    });
  });

  // ============================================
  // BADGE TYPE SELECTION
  // ============================================

  const badgeTypeSelect = $("#badgeType");

  badgeTypeSelect.on("change", function () {
    const badgeTypeValue = badgeTypeSelect.val();
    const countriesSection = $("#countries");
    const authorLevelsSection = $("#authorLevels");
    const membershipYearsSection = $("#membershipYears");

    // Hide all sections first
    membershipYearsSection.addClass("d-none").find("input").prop("disabled", true);
    countriesSection.addClass("d-none").find("select").prop("disabled", true);
    authorLevelsSection.addClass("d-none").find("select").prop("disabled", true);

    // Show relevant section
    if (badgeTypeValue === "countries") {
      countriesSection.removeClass("d-none").find("select").prop("disabled", false);
    } else if (badgeTypeValue === "author_levels") {
      authorLevelsSection.removeClass("d-none").find("select").prop("disabled", false);
    } else if (badgeTypeValue === "membership_years") {
      membershipYearsSection.removeClass("d-none").find("input").prop("disabled", false);
    }
  });



  // ============================================
  // ATTACHMENTS MANAGEMENT
  // ============================================

  let attachmentsCounter = 1;

  $(document).on("click", "#addAttachment", function (event) {
    event.preventDefault();
    attachmentsCounter++;

    const attachmentsContainer = $(this).siblings(".attachments");
    // Fallback if not sibling (depends on HTML structure)
    const targetContainer = attachmentsContainer.length ? attachmentsContainer : $(".attachments");

    const attachmentHTML = `
      <div class="attachment-box-${attachmentsCounter} mt-3">
        <div class="input-group">
          <input type="file" name="attachments[]" class="form-control form-control-md">
          <button class="btn btn-danger attachment-remove" data-id="${attachmentsCounter}" type="button" title="Remove">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
    `;

    targetContainer.append(attachmentHTML);
  });

  $(document).on("click", ".attachment-remove", function () {
    const attachmentId = $(this).data("id");
    // attachmentsCounter--; // Don't decrement purely based on remove, unique IDs are safer if increasing.
    $(`.attachment-box-${attachmentId}`).remove();
  });

  // ============================================
  // PERIOD SELECT REDIRECT
  // ============================================

  const periodSelectElement = $("#period-select");

  periodSelectElement.on("change", function () {
    window.location.href = $(this).val();
  });



  // ============================================
  // CUSTOM FEATURES MANAGEMENT
  // ============================================

  let customFeaturesCounter = 0;

  $(document).on("click", ".add-custom-feature", function (event) {
    event.preventDefault();
    customFeaturesCounter++;

    const customFeaturesContainer = $(this).closest(".custom-features");

    const featureHTML = `
      <div class="col-12 custom-feature-box-${customFeaturesCounter}">
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="bi bi-check2"></i></span>
          <input type="text" name="custom_features[]" class="form-control" required placeholder="Feature description...">
          <button class="btn btn-danger custom-feature-remove" type="button" data-id="${customFeaturesCounter}">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
    `;

    customFeaturesContainer.append(featureHTML);
  });

  $(document).on("click", ".custom-feature-remove", function () {
    $(this).closest(".col-12").remove();
  });

  // ============================================
  // VIDEO PLAYER (ITEM VIDEO)
  // ============================================

  const itemVideoElements = document.querySelectorAll(".item-video");

  itemVideoElements.forEach((videoContainer) => {
    const video = videoContainer.querySelector("video");
    const volumeButton = videoContainer.querySelector(".item-video-volume");
    const fullscreenButton = videoContainer.querySelector(".item-video-full");
    const videoProgress = videoContainer.querySelector(".item-video-progress");

    // Set initial mute state
    videoContainer.classList.toggle("muted", video.muted);

    // Play on hover
    videoContainer.addEventListener("mouseenter", () => {
      video.play();
    });

    // Pause and reset on leave
    videoContainer.addEventListener("mouseleave", () => {
      video.pause();
      setTimeout(() => {
        video.currentTime = 0;
      }, 0);
      video.load();
    });

    // Update progress bar
    video.addEventListener("timeupdate", () => {
      const progress = Math.ceil((video.currentTime / video.duration) * 100);
      videoProgress.style.setProperty("width", `${progress}%`);
    });

    // Volume toggle
    volumeButton.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();

      itemVideoElements.forEach((container) => {
        const containerVideo = container.querySelector("video");
        containerVideo.muted = !containerVideo.muted;
        container.classList.toggle("muted", containerVideo.muted);
      });
    });

    // Fullscreen toggle
    const openFullscreen = (event) => {
      event.preventDefault();
      event.stopPropagation();

      if (video.requestFullscreen) {
        video.requestFullscreen();
      } else if (video.webkitRequestFullscreen) {
        video.webkitRequestFullscreen();
      } else if (video.msRequestFullscreen) {
        video.msRequestFullscreen();
      }
    };

    fullscreenButton.addEventListener("click", openFullscreen);
  });

  // ============================================
  // PLYR VIDEO PLAYER
  // ============================================

  const plyrElements = document.querySelectorAll(".video-plyr");

  if (plyrElements.length > 0) {
    plyrElements.forEach((element) => {
      new Plyr(element);
    });
  }

  // ============================================
  // AUDIO PLAYER (WAVEFORM)
  // ============================================

  const audioPlayerElements = document.querySelectorAll(".item-audio-wave");

  audioPlayerElements.forEach((playerContainer) => {
    const waveformElement = playerContainer.querySelector(".waveform");
    const playButton = playerContainer.querySelector(".play-button");
    const pauseButton = playerContainer.querySelector(".pause-button");
    const totalDuration = playerContainer.querySelector(".total-duration");
    const currentTime = playerContainer.querySelector(".current-time");

    /**
     * Initialize WaveSurfer instance
     */
    const initializeWavesurfer = () => {
      return WaveSurfer.create({
        container: waveformElement,
        responsive: true,
        waveColor: "#d2ebd3",
        progressColor: "#4caf50",
        cursorColor: "transparent",
        height: parseInt(waveformElement.getAttribute("data-waveheight")),
        hideScrollbar: true,
        barWidth: 2,
        barMinHeight: 1,
        barHeight: 1,
        barGap: 2,
        barRadius: 3,
      });
    };

    const wavesurfer = initializeWavesurfer();
    wavesurfer.load(waveformElement.getAttribute("data-url"));

    /**
     * Play audio
     */
    const playAudio = () => {
      // Pause all other players
      document.querySelectorAll(".pause-button").forEach((btn) => btn.click());

      wavesurfer.play();
      playButton.classList.add("d-none");
      pauseButton.classList.remove("d-none");
    };

    /**
     * Pause audio
     */
    const pauseAudio = () => {
      wavesurfer.pause();
      pauseButton.classList.add("d-none");
      playButton.classList.remove("d-none");
    };

    playButton.addEventListener("click", playAudio);
    pauseButton.addEventListener("click", pauseAudio);

    wavesurfer.on("ready", () => {
      if (totalDuration) {
        totalDuration.textContent = formatTimeCode(wavesurfer.getDuration());
      }
    });

    wavesurfer.on("audioprocess", () => {
      if (currentTime) {
        const time = wavesurfer.getCurrentTime();
        currentTime.innerHTML = formatTimeCode(time);
      }
    });

    wavesurfer.on("finish", () => {
      pauseButton.classList.add("d-none");
      playButton.classList.remove("d-none");
    });
  });

  // ============================================
  // FORM UNSAVED CHANGES WARNING
  // ============================================

  let hasFormChanges = false;
  let isFormSubmitting = false;

  $('#ezydev-regular-form').on('change input', 'input, select, textarea', function () {
    hasFormChanges = true;
  });

  $('#ezydev-regular-form').on('submit', function () {
    isFormSubmitting = true;
  });

  window.addEventListener('beforeunload', function (event) {
    if (hasFormChanges && !isFormSubmitting) {
      event.preventDefault();
      event.returnValue = config.translates.leaveUnsavedText;
      return event.returnValue;
    }
  });

  $(document).on('click', 'a:not([href^="#"]):not([target="_blank"]), button[type="button"]:not([data-bs-toggle])', function (event) {
    if (hasFormChanges && !isFormSubmitting) {
      const userConfirmed = confirm(config.translates.leaveUnsavedText);
      if (!userConfirmed) {
        event.preventDefault();
        return false;
      }
    }
  });


  // ============================================
  // TOGGLE SWITCH TARGET TOGGLE
  // ============================================

  $('.codebay-toggle-switch').each(function () {
    const $switch = $(this);
    const targetSelector = $switch.data('toggle-target');

    if (!targetSelector) return;

    const $target = $(targetSelector);

    if (!$target.length) return;

    // Set initial state
    if (!$switch.is(':checked')) {
      $target.addClass('d-none');
    } else {
      $target.removeClass('d-none');
    }

    // Handle change event
    $switch.on('change', function () {
      if ($(this).is(':checked')) {
        $target.removeClass('d-none');
      } else {
        $target.addClass('d-none');
      }
    });
  });

  // ============================================
  // MAKE DEFAULT ACTION (Reusable)
  // ============================================

  /**
   * Handle make default action via AJAX
   * Usage: Add class 'make-default-btn' to button/link
   * Required data attributes:
   *   - data-id: Entity ID
   *   - data-name: Entity name for confirmation
   *   - data-route: Route URL (with :id placeholder)
   *   - data-confirm-msg: Optional custom confirmation message
   */
  $(document).on('click', '.make-default-btn', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const entityId = $btn.data('id');
    const entityName = $btn.data('name');
    const routeUrl = $btn.data('route');
    const confirmMsg = $btn.data('confirm-msg') || `Are you sure you want to set "${entityName}" as default?`;

    // Validate required data
    if (!entityId || !routeUrl) {
      return;
    }

    // Confirm action
    if (!confirm(confirmMsg)) {
      return;
    }

    // Disable button during request
    $btn.prop('disabled', true);

    // Send AJAX request
    $.ajax({
      url: routeUrl.replace(':id', entityId),
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        if (response.success) {
          toastr.success(response.message || 'Set as default successfully');

          // Reload page after delay
          setTimeout(function () {
            window.location.reload();
          }, 1000);
        } else {
          toastr.error(response.message || 'Failed to set as default');
          $btn.prop('disabled', false);
        }
      },
      error: function (xhr) {
        const errorMsg = xhr.responseJSON?.message || 'Failed to set as default';
        toastr.error(errorMsg);
        $btn.prop('disabled', false);
      }
    });
  });

  // ============================================
  // COLLAPSIBLE SIDEBAR FUNCTIONALITY
  // ============================================

  const DESKTOP_BREAKPOINT_SIDEBAR = 1199.98;
  const SIDEBAR_STATE_KEY = 'admin_sidebar_collapsed';

  const sidebarElement = document.querySelector(".ezydev-sidebar");
  const pageContentElement = document.querySelector(".ezydev-main-wrapper");
  const sidebarToggleButton = document.getElementById("sidebarToggleBtn");
  const mobileToggleButton = document.getElementById("navToggleBtn");

  if (sidebarElement && pageContentElement) {
    /**
     * Check if current viewport is desktop
     */
    const isDesktopSidebar = () => window.innerWidth > DESKTOP_BREAKPOINT_SIDEBAR;

    /**
     * Get saved sidebar state from localStorage
     */
    const getSavedSidebarState = () => {
      try {
        return localStorage.getItem(SIDEBAR_STATE_KEY) === 'true';
      } catch (e) {
        return false;
      }
    };

    /**
     * Save sidebar state to localStorage
     */
    const saveSidebarState = (isCollapsed) => {
      try {
        localStorage.setItem(SIDEBAR_STATE_KEY, isCollapsed);
      } catch (e) {
        //console.warn('Could not save sidebar state');
      }
    };

    /**
     * Update toggle button icon based on current state and viewport
     */
    const updateToggleIcon = () => {
      if (!sidebarToggleButton) return;

      if (isDesktopSidebar()) {
        const isCollapsed = sidebarElement.classList.contains("collapsed");
        sidebarToggleButton.innerHTML = isCollapsed
          ? '<i class="bi bi-circle"></i>'
          : '<i class="bi bi-record-circle"></i>';
      }
    };

    /**
     * Toggle sidebar for DESKTOP (collapsed/expanded)
     */
    const toggleSidebarDesktop = () => {
      const isCurrentlyCollapsed = sidebarElement.classList.contains("collapsed");

      sidebarElement.classList.toggle("collapsed");
      pageContentElement.classList.toggle("collapsed");

      saveSidebarState(!isCurrentlyCollapsed);
      updateToggleIcon();
      updateAriaExpanded();

      // Trigger layout recalculation for any responsive elements
      window.dispatchEvent(new Event('resize'));
    };

    /**
     * Toggle sidebar for MOBILE (show/hide)
     */
    const toggleSidebarMobile = () => {
      sidebarElement.classList.toggle("active");
      pageContentElement.classList.toggle("active");
      updateToggleIcon();
      updateAriaExpanded();
    };

    /**
     * Close mobile sidebar
     */
    const closeMobileSidebar = () => {
      if (!isDesktopSidebar()) {
        sidebarElement.classList.remove("active");
        pageContentElement.classList.remove("active");
        updateToggleIcon();
      }
    };

    /**
     * Apply saved sidebar state on page load (desktop only)
     */
    const applySavedState = () => {
      if (isDesktopSidebar()) {
        const shouldBeCollapsed = getSavedSidebarState();
        if (shouldBeCollapsed) {
          sidebarElement.classList.add("collapsed");
          pageContentElement.classList.add("collapsed");
        }
      }
      updateToggleIcon();
    };

    /**
     * Handle window resize - switch between desktop/mobile behavior
     */
    const handleResize = () => {
      if (isDesktopSidebar()) {
        // Switched to desktop: remove mobile classes, apply saved state
        sidebarElement.classList.remove("active");
        pageContentElement.classList.remove("active");

        const shouldBeCollapsed = getSavedSidebarState();
        if (shouldBeCollapsed) {
          sidebarElement.classList.add("collapsed");
          pageContentElement.classList.add("collapsed");
        }
      } else {
        // Switched to mobile: remove desktop collapsed classes
        sidebarElement.classList.remove("collapsed");
        pageContentElement.classList.remove("collapsed");
      }
      updateToggleIcon();
      updateAriaExpanded();
    };

    /**
     * Add data-title attributes to sidebar links for tooltips
     */
    const addTooltipAttributes = () => {
      const sidebarLinks = document.querySelectorAll('.ezydev-sidebar-link');
      sidebarLinks.forEach(link => {
        const textSpan = link.querySelector('span:first-child');
        if (textSpan) {
          const textContent = textSpan.textContent.trim();
          link.setAttribute('data-title', textContent);
        }
      });
    };

    /**
     * Handle submenu expansion in collapsed mode on hover
     */
    const handleCollapsedSubmenuHover = () => {
      const dropdownItems = document.querySelectorAll('.ezydev-sidebar-item[data-dropdown]');

      dropdownItems.forEach(item => {
        item.addEventListener('mouseenter', function () {
          if (isDesktopSidebar() && sidebarElement.classList.contains('collapsed') && !sidebarElement.matches(':hover')) {
            // Don't auto-expand submenu when sidebar is collapsed and not being hovered
            return;
          }
        });
      });
    };

    /**
     * Update aria-expanded based on state
     */
    const updateAriaExpanded = () => {
      if (!sidebarToggleButton) return;

      if (isDesktopSidebar()) {
        const isCollapsed = sidebarElement.classList.contains("collapsed");
        sidebarToggleButton.setAttribute('aria-expanded', !isCollapsed);
      } else {
        const isShown = sidebarElement.classList.contains("active");
        sidebarToggleButton.setAttribute('aria-expanded', isShown);
      }
    };

    // Apply saved state on page load
    applySavedState();

    // Add tooltip attributes
    addTooltipAttributes();

    // Handle submenu behavior
    handleCollapsedSubmenuHover();

    // Event Listeners
    if (sidebarToggleButton) {
      sidebarToggleButton.addEventListener("click", () => {
        if (isDesktopSidebar()) {
          toggleSidebarDesktop();
        } else {
          toggleSidebarMobile();
        }
      });

      // Add keyboard support for toggle
      sidebarToggleButton.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          sidebarToggleButton.click();
        }
      });

      // Add aria attributes
      sidebarToggleButton.setAttribute('aria-label', 'Toggle Sidebar');
    }

    // Mobile toggle button (hamburger in navbar)
    if (mobileToggleButton) {
      mobileToggleButton.addEventListener("click", () => {
        toggleSidebarMobile();
      });
    }

    // Close mobile sidebar when clicking overlay
    const overlay = sidebarElement.querySelector(".overlay");
    if (overlay) {
      overlay.addEventListener("click", closeMobileSidebar);
    }

    // Handle window resize with debounce
    let resizeTimer;
    window.addEventListener("resize", () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(handleResize, 150);
    });

    // Add aria attributes for sidebar
    sidebarElement.setAttribute('aria-label', 'Main Navigation');
    updateAriaExpanded();
  }



  // ============================================
  // REUSABLE AJAX MODAL FORM HANDLER
  // ============================================

  /**
   * Initialize AJAX form submission for modal forms
   *
   * @param {Object} options - Configuration options
   * @param {string} options.formSelector - jQuery selector for the form
   * @param {string} options.modalSelector - jQuery selector for the modal
   * @param {string} options.submitButtonSelector - jQuery selector for submit button (optional, defaults to form submit button)
   * @param {Function} options.onSuccess - Callback function on successful submission
   * @param {Function} options.onError - Callback function on error
   * @param {boolean} options.reloadOnSuccess - Whether to reload page after success (default: true)
   * @param {number} options.reloadDelay - Delay before reload in milliseconds (default: 1000)
   * @param {string} options.loadingText - Text to show on submit button during loading
   * @param {boolean} options.resetFormOnSuccess - Whether to reset form after success (default: true)
   * @param {boolean} options.closeModalOnSuccess - Whether to close modal after success (default: true)
   * @param {string} options.successMessage - Success message to display (default: 'Operation completed successfully')
   * @param {string} options.errorMessage - Error message to display (default: 'An error occurred')
   * @param {boolean} options.useDelegation - Use event delegation for dynamically loaded forms (default: true if form doesn't exist, false otherwise)
   *
   * @example
   * // Basic usage for static form
   * initAjaxModalForm({
   *     formSelector: '#myForm',
   *     modalSelector: '#myModal',
   *     successMessage: 'Data saved successfully'
   * });
   *
   * @example
   * // With custom callbacks
   * initAjaxModalForm({
   *     formSelector: '#createUserForm',
   *     modalSelector: '#createUserModal',
   *     loadingText: '<i class="bi bi-hourglass"></i> Creating...',
   *     reloadOnSuccess: false,
   *     onSuccess: function(response) {
   *         console.log('User created:', response.user);
   *         // Update table row instead of reloading
   *     },
   *     onError: function(xhr) {
   *         console.error('Error:', xhr);
   *     }
   * });
   *
   * @example
   * // For dynamically loaded forms (e.g., loaded via AJAX into modal)
   * initAjaxModalForm({
   *     formSelector: '#editBadgeForm',
   *     modalSelector: '#editBadgeModal',
   *     submitButtonSelector: '#editBadgeBtn',
   *     loadingText: 'Updating...',
   *     useDelegation: true  // Enables event delegation for dynamic content
   * });
   */
  window.initAjaxModalForm = function (options) {
    const config = {
      modalSelector: options.modalSelector,
      formSelector: options.formSelector,
      submitButtonSelector: options.submitButtonSelector || null,
      onSuccess: options.onSuccess || null,
      onError: options.onError || null,
      reloadOnSuccess: options.reloadOnSuccess !== false,
      reloadDelay: options.reloadDelay || 1000,
      loadingText: '<div class="spinner-border spinner-border-sm me-2" role="status"><span class="visually-hidden">Loading...</span></div> ' + (options.loadingText || 'Processing...'),
      resetFormOnSuccess: options.resetFormOnSuccess !== false,
      closeModalOnSuccess: options.closeModalOnSuccess !== false,
      successMessage: options.successMessage || 'Operation completed successfully',
      errorMessage: options.errorMessage || 'An error occurred',
      useDelegation: options.useDelegation !== false  // Enable delegation by default for dynamic forms
    };

    const $modal = $(config.modalSelector);

    // If modal doesn't exist, skip initialization
    if (!$modal.length) {
      return;
    }

    // Check if form exists now (for static forms)
    const $form = $(config.formSelector);
    const formExists = $form.length > 0;

    // Use event delegation for dynamically loaded forms, or direct binding for existing forms
    const eventTarget = config.useDelegation || !formExists ? $(document) : $form;
    const eventSelector = config.useDelegation || !formExists ? config.formSelector : null;

    // Handle form submission
    const submitHandler = function (e) {
      e.preventDefault();

      const $currentForm = $(e.currentTarget);
      let $submitBtn = config.submitButtonSelector
        ? $currentForm.find(config.submitButtonSelector)
        : $currentForm.find('button[type="submit"]');

      // If not found inside form, try finding it globally (modal footer buttons are outside form)
      if ($submitBtn.length === 0 && config.submitButtonSelector) {
        $submitBtn = $(config.submitButtonSelector);
      }

      const originalText = $submitBtn.html();

      // Disable submit button and show loading
      $submitBtn.prop('disabled', true).html(config.loadingText);

      // Clear previous errors
      $('.is-invalid').removeClass('is-invalid');
      $('.invalid-feedback').remove();

      // Prepare form data
      let useFormData = $currentForm.find('input[type="file"]').length > 0 || $currentForm.attr('enctype') === 'multipart/form-data';
      let formData;

      if (useFormData) {
        formData = new FormData($currentForm[0]);
        // Manually append custom features that reside outside the form tag but inside the modal
        $modal.find('input[name="custom_features[]"]').each(function () {
          formData.append('custom_features[]', $(this).val());
        });
      } else {
        formData = $currentForm.serialize();
        // Manually append custom features that reside outside the form tag but inside the modal
        $modal.find('input[name="custom_features[]"]').each(function () {
          formData += '&' + encodeURIComponent('custom_features[]') + '=' + encodeURIComponent($(this).val());
        });
      }

      $.ajax({
        url: $currentForm.attr('action'),
        type: $currentForm.attr('method') || 'POST',
        data: formData,
        processData: !useFormData,
        contentType: useFormData ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            // Show success message
            toastr.success(response.message || config.successMessage);
            $submitBtn.prop('disabled', false).html(originalText);

            // Close modal if configured
            if (config.closeModalOnSuccess) {
              $modal.modal('hide');
            }

            // Reset form if configured
            if (config.resetFormOnSuccess) {
              $currentForm[0].reset();
            }

            // Call custom success callback if provided
            if (typeof config.onSuccess === 'function') {
              config.onSuccess(response);
            }

            // Reload page if configured
            if (config.reloadOnSuccess) {
              setTimeout(function () {
                window.location.reload();
              }, config.reloadDelay);
            }
          } else {
            toastr.error(response.message || config.errorMessage);
            $submitBtn.prop('disabled', false).html(originalText);

            // Call custom error callback if provided
            if (typeof config.onError === 'function') {
              config.onError(response);
            }
          }
        },
        error: function (xhr) {
          // Handle validation errors
          if (xhr.status === 422) {
            const errors = xhr.responseJSON?.errors || {};

            $.each(errors, function (field, messages) {
              const $input = $currentForm.find('[name="' + field + '"]');
              $input.addClass('is-invalid');
              $input.after('<div class="invalid-feedback d-block">' + messages[0] + '</div>');
            });

            toastr.error(xhr.responseJSON?.message || 'Please check the form for errors');
          } else {
            toastr.error(xhr.responseJSON?.message || config.errorMessage);
          }

          $submitBtn.prop('disabled', false).html(originalText);

          // Call custom error callback if provided
          if (typeof config.onError === 'function') {
            config.onError(xhr);
          }
        }
      });
    };

    // Attach the event handler with or without delegation
    if (eventSelector) {
      // Use event delegation for dynamic forms
      eventTarget.on('submit', eventSelector, submitHandler);
    } else {
      // Direct binding for existing forms
      eventTarget.on('submit', submitHandler);
    }

    // Reset form when modal is closed
    $modal.on('hidden.bs.modal', function () {
      const $currentForm = $(config.formSelector);
      if ($currentForm.length) {
        $currentForm[0].reset();
        $currentForm.find('.is-invalid').removeClass('is-invalid');
        $currentForm.find('.invalid-feedback').remove();
      }
    });
  };


  // ============================================
  // AJAX MODAL CONTENT
  // ============================================

  /**
   * Initialize AJAX modal content loader for dynamic content loading
   * @param {Object} options - Configuration options
   * @param {string} options.triggerSelector - jQuery selector for trigger elements
   * @param {string} options.modalId - ID of the modal element
   * @param {string} options.dataAttribute - Data attribute containing the resource ID (optional, auto-detects 'data-id' if not specified)
   * @param {function} options.urlBuilder - Function to build the AJAX URL from the ID
   * @param {string} options.loadingMessage - Loading message text
   * @param {string} options.errorMessage - Error message text
   * @param {string} options.iconClass - Icon class for modal title (optional)
   */
  window.initAjaxModalContent = function (options) {
    const config = {
      triggerSelector: options.triggerSelector,
      modalId: options.modalId,
      dataAttribute: options.dataAttribute || null,
      urlBuilder: options.urlBuilder,
      loadingMessage: options.loadingMessage || 'Loading...',
      errorMessage: options.errorMessage || 'Failed to load content',
      iconClass: options.iconClass || null
    };

    $(document).on('click', config.triggerSelector, function (e) {
      e.preventDefault();

      const $trigger = $(this);
      let resourceId;

      // Auto-detect resource ID from common data attributes if not specified
      if (config.dataAttribute) {
        resourceId = $trigger.attr(config.dataAttribute);
      } else {
        resourceId = $trigger.data('id');
      }

      if (!resourceId) {
        return;
      }

      const modal = $(`#${config.modalId}`);
      const modalContent = modal.find('#modalContent');
      const modalLoader = modal.find('#modalLoader');
      const modalTitle = modal.find('.modal-title');

      // Show modal and reset state
      modal.modal('show');
      modalContent.hide();
      modalLoader.removeClass('d-none').show();

      // Build the AJAX URL
      const url = config.urlBuilder(resourceId);

      // Load content via AJAX
      $.ajax({
        url: url,
        method: 'GET',
        success: function (response) {
          // Update title with optional icon
          const titleHtml = config.iconClass
            ? `<i class="bi ${config.iconClass} me-2"></i>${response.title}`
            : response.title;
          modalTitle.html(titleHtml);

          // Hide loader completely and show content
          modalLoader.addClass('d-none').hide();
          modalContent.removeClass('d-none').show();
          modalContent.html(response.content);
        },
        error: function (xhr) {
          const errorMsg = xhr.responseJSON?.message || config.errorMessage;

          // Hide loader and show error
          modalLoader.addClass('d-none').hide();
          modalContent.removeClass('d-none').show();
          modalContent.html(
            '<div class="alert alert-danger m-3">' +
            '<i class="bi bi-exclamation-triangle me-2"></i>' +
            errorMsg +
            '</div>'
          );
        }
      });
    });
  };

})(jQuery);
/**
 * Admin Menu Search Component
 * Handles menu search functionality with AJAX
 */

document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('adminMenuSearch');
  const searchResults = document.getElementById('searchResults');
  const clearBtn = document.getElementById('clearSearch');
  const resultsContent = searchResults?.querySelector('.search-results-content');
  const searchToggleBtn = document.getElementById('searchToggleBtn');
  const searchWrapper = document.querySelector('.admin-menu-search .search-wrapper');

  // Exit if elements don't exist
  if (!searchInput || !searchResults || !clearBtn || !resultsContent) {
    return;
  }

  let searchTimeout;
  let currentRequest;

  // Toggle search on mobile
  if (searchToggleBtn) {
    searchToggleBtn.addEventListener('click', function () {
      searchWrapper.classList.toggle('active');
      if (searchWrapper.classList.contains('active')) {
        searchInput.focus();
      } else {
        searchInput.value = '';
        searchResults.classList.add('d-none');
        clearBtn.classList.add('d-none');
      }
    });
  }

  // Clear search
  clearBtn.addEventListener('click', function () {
    searchInput.value = '';
    searchResults.classList.add('d-none');
    clearBtn.classList.add('d-none');
    searchInput.focus();
  });

  // Handle input
  searchInput.addEventListener('input', function () {
    const query = this.value.trim();

    // Show/hide clear button
    if (query.length > 0) {
      clearBtn.classList.remove('d-none');
    } else {
      clearBtn.classList.add('d-none');
      searchResults.classList.add('d-none');
      return;
    }

    // Debounce search
    clearTimeout(searchTimeout);

    if (query.length < 2) {
      searchResults.classList.add('d-none');
      return;
    }

    // Show loading
    resultsContent.innerHTML = `
      <div class="search-loading">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
          <span class="visually-hidden">${window.translate ? translate('Loading...') : 'Loading...'}</span>
        </div>
        <div class="mt-2">${window.translate ? translate('Searching...') : 'Searching...'}</div>
      </div>
    `;
    searchResults.classList.remove('d-none');

    // Add mobile class if needed
    if (window.innerWidth < 768) {
      searchResults.classList.add('active-results');
    }

    searchTimeout = setTimeout(function () {
      performSearch(query);
    }, 300);
  });

  // Handle click outside
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.admin-menu-search')) {
      searchResults.classList.add('d-none');
      searchResults.classList.remove('active-results');
      if (window.innerWidth < 768) {
        searchWrapper.classList.remove('active');
      }
    }
  });

  // Handle focus
  searchInput.addEventListener('focus', function () {
    if (this.value.trim().length >= 2 && resultsContent.children.length > 0) {
      searchResults.classList.remove('d-none');
    }
  });

  // Perform AJAX search
  function performSearch(query) {
    // Cancel previous request
    if (currentRequest) {
      currentRequest.abort();
    }

    const searchUrl = document.querySelector('[data-menu-search-url]')?.dataset.menuSearchUrl || '/admin/search';

    currentRequest = $.ajax({
      url: searchUrl,
      method: 'GET',
      data: { query: query },
      success: function (response) {
        currentRequest = null;
        displayResults(response, query);
      },
      error: function (xhr) {
        currentRequest = null;
        if (xhr.statusText !== 'abort') {
          resultsContent.innerHTML = `
            <div class="search-no-results">
              <i class="bi bi-exclamation-triangle"></i>
              <p class="mb-0">${window.translate ? translate('An error occurred') : 'An error occurred'}</p>
            </div>
          `;
        }
      }
    });
  }

  // Display search results
  function displayResults(results, query) {
    if (results.length === 0) {
      resultsContent.innerHTML = `
        <div class="search-no-results">
          <i class="bi bi-search"></i>
          <p class="mb-0">${window.translate ? translate('No results found') : 'No results found'}</p>
        </div>
      `;
      return;
    }

    const html = results.map(item => {
      const title = highlightText(item.title, query);
      const breadcrumb = item.breadcrumb ? highlightText(item.breadcrumb, query) : '';

      return `
        <a href="${item.url}" class="search-result-item">
          <div class="search-result-content">
            <p class="search-result-title">${title}</p>
            ${breadcrumb ? `<p class="search-result-breadcrumb">${breadcrumb}</p>` : ''}
          </div>
        </a>
      `;
    }).join('');

    resultsContent.innerHTML = html;
  }

  // Highlight matching text
  function highlightText(text, query) {
    if (!query) return text;
    const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
    return text.replace(regex, '<mark>$1</mark>');
  }

  // Escape regex special characters
  function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  }

  // ============================================
  // THEME SETTINGS AJAX SUBMIT
  // ============================================

  const themeSettingsForm = $('#themeSettingsForm');
  if (themeSettingsForm.length) {
    const saveBtn = $('button[form="themeSettingsForm"]');
    const saveBtnHtml = saveBtn.html();

    themeSettingsForm.on('submit', function (e) {
      e.preventDefault();

      saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

      $.ajax({
        url: themeSettingsForm.attr('action'),
        type: 'POST',
        data: new FormData(this),
        processData: false,
        contentType: false,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function (res) {
          if (res.success) {
            toastr.success(res.message || 'Updated Successfully');
            if (typeof hasFormChanges !== 'undefined') hasFormChanges = false;
          } else {
            toastr.error(res.message || 'Something went wrong');
          }
        },
        error: function (xhr) {
          const msg = xhr.responseJSON?.message || 'Failed to save settings';
          toastr.error(msg);
        },
        complete: function () {
          saveBtn.prop('disabled', false).html(saveBtnHtml);
        }
      });
    });
  }

});
