/**
 * Dashboard Analytics Manager
 * Reusable class for analytics with Chart.js integration
 *
 * @version 1.0.0
 * @author EzyDev
 * @requires jQuery, Chart.js, ChartDataLabels Plugin
 *
 * @dependencies
 * <script src="{{ asset('vendor/libs/chartjs/chart.min.js') }}"></script>
 * <script src="{{ asset('vendor/libs/chartjs/chartjs-datalabels.min.js') }}"></script>
 *
 * @managers
 * - AnalyticsManager: Main analytics chart with year/period navigation
 * - CountryAnalyticsManager: Country-based analytics with world map
 * - GaugeChartManager: Gauge/arc chart for metrics with progress
 * - StatisticsManager: Statistics cards with numeric metrics
 * - combinedBarsManager: Combined revenue/expense bar charts
 * - TrafficSourceManager: Traffic source analytics with visitor counts
 * - DonutChartManager: Donut charts for categorical data
 * - GeoChartManager: Geographic data visualization
 * - DualStatsManager: Dual statistics with comparison metrics
 */

(function($) {
  'use strict';

  /**
   * Common Translations - Shared translations for all dashboard managers
   * These can be overridden in Blade template initialization
   */
  window.CommonTranslations = {
    sales: 'Sales',
    revenue: 'Revenue',
    earnings: 'Earnings',
    expense: 'Expense',
    members: 'Members',
    visitors: 'Visitors',
    today: 'Today',
    yesterday: 'Yesterday',
    last7Days: 'Last 7 Days',
    last28Days: 'Last 28 Days',
    thisMonth: 'This Month',
    thisYear: 'This Year',
    lifetime: 'Lifetime',
    weekly: 'Weekly',
    monthly: 'Monthly',
    yearly: 'Yearly',
    loadFailed: 'Failed to load data',
    loading: 'Loading...',
    noData: 'No data available!',
    hideComparison: 'Hide Comparison',
  };

  /**
   * DashboardHelpers - Global utility functions for dashboard managers
   */
  window.DashboardHelpers = {
    /**
     * Convert hex color to rgba
     * @param {string} hex - Hex color code
     * @param {number} alpha - Alpha value (0-1)
     * @returns {string} RGBA color string
     */
    hexToRgba(hex, alpha = 1) {
      const r = parseInt(hex.slice(1, 3), 16);
      const g = parseInt(hex.slice(3, 5), 16);
      const b = parseInt(hex.slice(5, 7), 16);
      return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    },

    /**
     * Format number with K/M/B suffix
     * @param {number} value - Number to format
     * @returns {string} Formatted number
     */
    formatNumber(value) {
      if (value === 0) return '0';

      const absValue = Math.abs(value);
      const sign = value < 0 ? '-' : '';

      if (absValue >= 1000000000) {
        return sign + (absValue / 1000000000).toFixed(1).replace(/\.0$/, '') + 'B';
      }
      if (absValue >= 1000000) {
        return sign + (absValue / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
      }
      if (absValue >= 1000) {
        return sign + (absValue / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
      }
      return sign + absValue.toString();
    },

    /**
     * Format value with currency symbol
     * @param {number} value - Number to format
     * @param {string} currencySymbol - Currency symbol
     * @param {string} dataFormat - Format type ('number' or 'currency')
     * @returns {string} Formatted value
     */
    formatValue(value, currencySymbol = '$', dataFormat = 'currency') {
      const formatted = this.formatNumber(value);
      return dataFormat === 'number' ? formatted : currencySymbol + formatted;
    },

    /**
     * Format number with thousand separators
     * @param {number} num - Number to format
     * @returns {string} Formatted number
     */
    formatWithCommas(num) {
      num = parseFloat(num);
      if (isNaN(num)) return '0';
      return num.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },

    /**
     * Check if cached data is still valid
     * @param {Object} cached - Cached data object with timestamp
     * @param {number} ttl - Time to live in milliseconds
     * @returns {boolean} Whether cache is valid
     */
    isCacheValid(cached, ttl) {
      if (!cached || !cached.timestamp) return false;
      const age = Date.now() - cached.timestamp;
      return age < ttl;
    },

    /**
     * Show error message inline
     * @param {jQuery} $container - Container element
     * @param {string} message - Error message to display (optional, defaults to CommonTranslations.loadFailed)
     * @param {string} icon - Bootstrap icon class (default: 'bi-exclamation-triangle')
     */
    showError($container, message = null, icon = 'bi-exclamation-triangle') {
      DashboardHelpers.showEmptyState($container, {
        title: 'Error',
        message: message || window.CommonTranslations.loadFailed,
        icon: icon
      });
    },

    /**
     * Handle AJAX error response with inline message
     * @param {jQuery} $container - Container element to show error in
     * @param {Object} xhr - jQuery XHR object
     * @param {string} defaultMessage - Default error message (optional, defaults to CommonTranslations.loadFailed)
     */
    handleAjaxError($container, xhr, defaultMessage = null) {
      if (xhr.statusText === 'abort') return; // Don't show error for aborted requests

      let errorMessage = defaultMessage || window.CommonTranslations.loadFailed;
      if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
      }
      this.showError($container, errorMessage);
    },

    /**
     * Cancel pending AJAX request
     * @param {Object} request - jQuery AJAX request object
     * @returns {null}
     */
    cancelRequest(request) {
      if (request) {
        request.abort();
      }
      return null;
    },

    /**
     * Batch update multiple DOM elements
     * @param {Object} updates - Object with element IDs as keys and values as values
     */
    batchUpdateDOM(updates) {
      Object.entries(updates).forEach(([id, value]) => {
        const $elem = $(`#${id}`);
        if ($elem.length) {
          $elem.text(value);
        }
      });
    },

    /**
     * Get optimized canvas context
     * @param {HTMLCanvasElement} canvas - Canvas element
     * @returns {CanvasRenderingContext2D} Canvas context
     */
    getCanvasContext(canvas) {
      return canvas.getContext('2d', { willReadFrequently: false });
    },

    /**
     * Clear and prepare canvas for rendering
     * @param {HTMLCanvasElement} canvas - Canvas element
     * @param {CanvasRenderingContext2D} ctx - Canvas context
     */
    clearCanvas(canvas, ctx) {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    },

    /**
     * Debounce function to limit execution rate
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @returns {Function} Debounced function
     */
    debounce(func, wait = 300) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    },

    /**
     * Create cache key from multiple parameters
     * @param {...any} params - Parameters to create key from
     * @returns {string} Cache key
     */
    createCacheKey(...params) {
      return params.join('_');
    },

    /**
     * Safe parse JSON with fallback
     * @param {string} jsonString - JSON string to parse
     * @param {*} fallback - Fallback value if parse fails
     * @returns {*} Parsed object or fallback
     */
    safeJsonParse(jsonString, fallback = null) {
      try {
        return JSON.parse(jsonString);
      } catch (e) {
        return fallback;
      }
    },

    /**
     * Get dynamic loader HTML based on global settings
     * @param {string} style - Loader style (spinner, dots, pulse, bars, ring, bounce)
     * @param {string} size - Loader size (sm, md, lg)
     * @param {string} color - Loader color (primary, secondary, success, etc.)
     * @param {boolean} centered - Whether to center the loader
     * @returns {string} Loader HTML
     */
    getLoaderHTML(style = 'spinner', size = 'md', color = 'primary', centered = false) {
      // Get loader style from window if available (set by Blade template)
      const loaderStyle = window.loaderStyle || style;
      const centerClass = centered ? 'd-flex justify-content-center align-items-center' : '';

      // Size classes
      const sizeClass = {
        'sm': 'loader-sm',
        'md': 'loader-md',
        'lg': 'loader-lg'
      }[size] || 'loader-md';

      const colorClass = `text-${color}`;

      // Generate HTML based on style
      const loaderMap = {
        'spinner': `<div class="spinner-border ${colorClass} ${sizeClass}" role="status"><span class="visually-hidden">${window.CommonTranslations.loading}</span></div>`,
        'dots': `<div class="loader-dots ${sizeClass}"><span class="dot ${colorClass}"></span><span class="dot ${colorClass}"></span><span class="dot ${colorClass}"></span></div>`,
        'pulse': `<div class="loader-pulse ${sizeClass}"><span class="pulse-circle ${colorClass}"></span></div>`,
        'bars': `<div class="loader-bars ${sizeClass}"><span class="bar ${colorClass}"></span><span class="bar ${colorClass}"></span><span class="bar ${colorClass}"></span><span class="bar ${colorClass}"></span></div>`,
        'ring': `<div class="loader-ring ${sizeClass}"><div class="ring ${colorClass}"></div></div>`,
        'bounce': `<div class="loader-bounce ${sizeClass}"><span class="bounce-ball ${colorClass}"></span><span class="bounce-ball ${colorClass}"></span><span class="bounce-ball ${colorClass}"></span></div>`
      };

      const loaderHTML = loaderMap[loaderStyle] || loaderMap['spinner'];

      if (centered) {
        return `<div class="${centerClass}">${loaderHTML}</div>`;
      }

      return loaderHTML;
    },

    /**
     * Format date for display
     * @param {Date|string} date - Date to format
     * @param {string} format - Format type ('short', 'long', 'time')
     * @returns {string} Formatted date
     */
    formatDate(date, format = 'short') {
      const d = date instanceof Date ? date : new Date(date);
      if (isNaN(d.getTime())) return '';

      const formats = {
        short: { year: 'numeric', month: 'short', day: 'numeric' },
        long: { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' },
        time: { hour: '2-digit', minute: '2-digit' }
      };

      return d.toLocaleDateString('en-US', formats[format] || formats.short);
    },

    /**
     * Throttle function to limit execution rate
     * @param {Function} func - Function to throttle
     * @param {number} limit - Time limit in milliseconds
     * @returns {Function} Throttled function
     */
    throttle(func, limit = 300) {
      let inThrottle;
      return function(...args) {
        if (!inThrottle) {
          func.apply(this, args);
          inThrottle = true;
          setTimeout(() => inThrottle = false, limit);
        }
      };
    },

    /**
     * Show loading spinner
     * @param {jQuery} $loader - Loader element
     * @param {jQuery} $content - Content element (optional)
     */
    showLoader($loader, $content = null) {
      $loader.removeClass('d-none');
      if ($content) {
        $content.addClass('d-none');
      }
    },

    /**
     * Hide loading spinner
     * @param {jQuery} $loader - Loader element
     * @param {jQuery} $content - Content element (optional)
     */
    hideLoader($loader, $content = null) {
      $loader.addClass('d-none');
      if ($content) {
        $content.removeClass('d-none');
      }
    },

    /**
     * Show "No data available" message
     * @param {jQuery} $container - Container element
     * @param {string} message - Message to display (optional, defaults to CommonTranslations.noData)
     * @param {string} icon - Bootstrap icon class (default: 'bi-inbox')
     * @param {jQuery} $loader - Loader element to replace (optional)
     * @param {jQuery} $content - Content element to hide (optional)
     */
    showNoDataMessage($container, message = null, icon = 'bi-inbox', $loader = null, $content = null) {
      // Hide content if provided
      if ($content) {
        $content.addClass('d-none');
      }

      // Use CommonTranslations.noData if message not provided
      const displayMessage = message || window.CommonTranslations.noData;

      // Create no-data message HTML
      const noDataHtml = `
        <div class="no-data-message text-center py-4 text-muted">
          <i class="bi ${icon} fs-2 d-block mb-2 opacity-50"></i>
          <p class="mb-0">${displayMessage}</p>
        </div>
      `;

      if ($loader) {
        // Replace loader content with no-data message
        $loader.removeClass('d-none').html(noDataHtml);
      } else {
        // Remove existing no-data message and append new one
        $container.find('.no-data-message').remove();
        $container.append(noDataHtml);
      }
    },

    /**
     * Hide "No data available" message
     * @param {jQuery} $container - Container element
     * @param {jQuery} $loader - Loader element to restore (optional)
     * @param {string} loaderHtml - HTML to restore in loader (optional)
     */
    hideNoDataMessage($container, $loader = null, loaderHtml = null) {
      // Remove no-data message from container
      $container.find('.no-data-message').remove();

      // Restore loader HTML if needed
      if ($loader && $loader.find('.no-data-message').length > 0) {
        const defaultSpinner = `
          <div class="text-center py-4">
            ${DashboardHelpers.getLoaderHTML()}
          </div>
        `;
        $loader.html(loaderHtml || defaultSpinner);
      }
    },

    /**
     * Toggle loader visibility
     * @param {jQuery} $loader - Loader element
     * @param {boolean} show - Whether to show or hide
     * @param {jQuery} $content - Content element (optional)
     */
    toggleLoader($loader, show = true, $content = null) {
      if (show) {
        this.showLoader($loader, $content);
      } else {
        this.hideLoader($loader, $content);
      }
    },

    /**
     * Show empty state with custom content
     * @param {jQuery} $container - Container element
     * @param {Object} options - Configuration options
     * @param {string} options.title - Title text
     * @param {string} options.message - Message text
     * @param {string} options.icon - Bootstrap icon class
     * @param {string} options.actionText - Action button text (optional)
     * @param {Function} options.actionCallback - Action button callback (optional)
     */
    showEmptyState($container, options = {}) {
      const defaults = {
        title: 'No Data',
        message: 'There is no data to display at the moment.',
        icon: 'bi-inbox',
        actionText: null,
        actionCallback: null
      };

      const config = { ...defaults, ...options };

      const actionButton = config.actionText ?
        `<button class="btn btn-sm btn-primary mt-3 empty-state-action">${config.actionText}</button>` : '';

      const emptyStateHtml = `
        <div class="empty-state text-center py-5">
          <i class="bi ${config.icon} fs-1 d-block mb-3 text-muted opacity-50"></i>
          <h5 class="mb-2">${config.title}</h5>
          <p class="text-muted mb-0">${config.message}</p>
          ${actionButton}
        </div>
      `;

      $container.html(emptyStateHtml);

      // Bind action callback if provided
      if (config.actionCallback && config.actionText) {
        $container.find('.empty-state-action').on('click', config.actionCallback);
      }
    },

    /**
     * Generic AJAX request handler with consistent error handling and loader management
     * @param {Object} options - Configuration options
     * @param {string} options.url - AJAX endpoint URL (required)
     * @param {string} options.type - HTTP method (default: 'GET')
     * @param {Object} options.data - Request data
     * @param {Function} options.onSuccess - Success callback (receives response data)
     * @param {Function} options.onError - Error callback (receives xhr object)
     * @param {jQuery} options.$container - Container for error messages
     * @param {jQuery} options.$loader - Loader element
     * @param {jQuery} options.$content - Content element to show/hide
     * @param {boolean} options.showLoader - Auto show loader (default: true)
     * @param {boolean} options.hideLoaderOnSuccess - Auto hide loader on success (default: true)
     * @param {boolean} options.hideLoaderOnError - Auto hide loader on error (default: true)
     * @param {boolean} options.checkSuccess - Check response.success property (default: true)
     * @param {Object} options.pendingRequest - Reference to track pending request
     * @returns {jqXHR} jQuery AJAX object
     */
    ajaxRequest(options) {
      const defaults = {
        type: 'GET',
        data: {},
        onSuccess: null,
        onError: null,
        $container: null,
        $loader: null,
        $content: null,
        showLoader: true,
        hideLoaderOnSuccess: true,
        hideLoaderOnError: true,
        checkSuccess: true,
        pendingRequest: null
      };

      const config = $.extend({}, defaults, options);

      // Validate required parameters
      if (!config.url) {
        return null;
      }

      // Show loader if configured
      if (config.showLoader && config.$loader) {
        if (config.$content) {
          this.showLoader(config.$loader, config.$content);
        } else {
          this.showLoader(config.$loader);
        }
      }

      // Make AJAX request
      const xhr = $.ajax({
        url: config.url,
        type: config.type,
        data: config.data,
        success: (response) => {
          // Check response.success if enabled
          if (config.checkSuccess) {
            if (response.success) {
              // Call success callback with response data or full response
              if (config.onSuccess) {
                config.onSuccess(response.data || response, response);
              }
            } else {
              // Response not successful - show error
              if (config.$container) {
                this.handleAjaxError(config.$container, { statusText: '', responseJSON: response });
              }
            }
          } else {
            // Skip success check, call callback directly
            if (config.onSuccess) {
              config.onSuccess(response);
            }
          }

          // Hide loader on success if configured
          if (config.hideLoaderOnSuccess && config.$loader) {
            if (config.$content) {
              this.hideLoader(config.$loader, config.$content);
            } else {
              this.hideLoader(config.$loader);
            }
          }
        },
        error: (xhr, status, error) => {
          // Ignore aborted requests
          if (xhr.statusText === 'abort') {
            return;
          }

          // Call custom error handler or use default
          if (config.onError) {
            config.onError(xhr, status, error);
          } else if (config.$container) {
            this.handleAjaxError(config.$container, xhr);
          }

          // Hide loader on error if configured
          if (config.hideLoaderOnError && config.$loader) {
            if (config.$content) {
              this.hideLoader(config.$loader, config.$content);
            } else {
              this.hideLoader(config.$loader);
            }
          }
        }
      });

      return xhr;
    }
  };

  /**
   * AnalyticsManager - Reusable class for analytics with charts
   * Handles both single-year and multi-year comparison charts
   *
   * @class AnalyticsManager
   * @param {Object} options - Configuration options
   * @param {string} options.canvasId - Canvas element ID (required)
   * @param {string} options.analyticsUrl - Single year analytics endpoint (required)
   * @param {string} options.comparisonUrl - Multi-year comparison endpoint (optional)
   * @param {string} options.loaderId - Loader element ID (default: 'analyticsLoader')
   * @param {string} options.yearDisplayId - Year display element ID (default: 'analyticsYear')
   * @param {number} options.currentYear - Initial year (default: current year)
   * @param {string} options.defaultType - Default analytics type (default: 'revenue')
   * @param {string} options.currencySymbol - Currency symbol (default: '$')
   * @param {boolean} options.useUniqueIds - Enable unique IDs for multiple instances (default: false)
   * @param {boolean} options.enableComparison - Enable comparison mode (default: true)
   * @param {string} options.tabDesign - Tab design: 'button' or 'nav-tabs' (default: 'button')
   * @param {boolean} options.enableCache - Enable caching (default: true)
   * @param {number} options.cacheTTL - Cache time-to-live in ms (default: 300000 = 5 min)
   * @param {Object} options.translations - Translation strings
   * @param {Object} options.colors - Chart colors
   * @param {Array} options.buttons - Custom button configurations [{id, type, label, icon, color}]
   */
  window.AnalyticsManager = class {
    constructor(options) {
      // Optional unique ID (disabled by default, enable for multiple instances)
      this.useUniqueIds = options.useUniqueIds === true;
      this.uniqueId = this.useUniqueIds ? AnalyticsManager._generateUniqueId() : '';

      // Store original IDs
      this.canvasId = options.canvasId;
      this.loaderId = options.loaderId || 'analyticsLoader';
      this.yearDisplayId = options.yearDisplayId || 'analyticsYear';

      this.canvas = $(`#${this.canvasId}`);
      this.analyticsUrl = options.analyticsUrl;
      this.comparisonUrl = options.comparisonUrl;
      this.loader = $(`#${this.loaderId}`);
      this.yearDisplay = $(`#${this.yearDisplayId}`);
      this.cardSubtitle = this.canvas.closest('.card').find('.card-subtitle');
      this.actualCurrentYear = new Date().getFullYear();
      this.currentYear = options.currentYear || this.actualCurrentYear;
      this.currentType = options.defaultType || 'revenue';
      this.isCompareMode = false;
      this.chart = null;
      this.currencySymbol = options.currencySymbol || '$';

      // Data formatting type: 'currency' or 'number'
      this.dataFormat = options.dataFormat || 'currency';

      // Period-based mode configuration (for week/month/year with offset)
      this.isPeriodBased = options.isPeriodBased === true;
      this.periodOffset = options.periodOffset || 0;

      // Comparison configuration
      this.enableComparison = options.enableComparison !== false; // Enabled by default

      // Tab design configuration
      this.tabDesign = options.tabDesign || 'button'; // 'button' or 'nav-tabs'

      // Cache configuration
      this.enableCache = options.enableCache !== false; // Enabled by default
      this.cacheTTL = options.cacheTTL || 5 * 60 * 1000; // 5 minutes default
      this.cache = {
        analytics: {}, // Format: { 'type_year': { data, timestamp } }
        comparison: {} // Format: { 'type': { data, timestamp } }
      };

      this.translations = $.extend({}, window.CommonTranslations, options.translations || {});
      this.colors = options.colors || {
        primary: '#6366f1',
        success: '#10b981',
        info: '#06b6d4'
      };

      // Custom buttons configuration with unique IDs
      const defaultButtons = [
        { id: 'btn-1', type: 'revenue', label: 'Revenue', icon: 'bi-currency-dollar', color: 'primary' },
        { id: 'btn-2', type: 'earnings', label: 'Earnings', icon: 'bi-wallet2', color: 'success' },
        { id: 'btn-3', type: 'members', label: 'Members', icon: 'bi-people', color: 'info' }
      ];

      this.originalButtons = options.buttons || defaultButtons;
      this.buttons = this.useUniqueIds
        ? this.originalButtons.map(btn => ({
            ...btn,
            originalId: btn.id,
            id: `${btn.id}${this.uniqueId}`
          }))
        : this.originalButtons;

      if (!this.canvas.length) {
        return;
      }

      if (typeof Chart === 'undefined') {
        return;
      }

      // Create throttled versions of load methods to prevent rapid API calls
      this.loadAnalyticsThrottled = DashboardHelpers.throttle(
        (type, year) => this.loadAnalytics(type, year),
        500
      );
      this.loadComparisonThrottled = DashboardHelpers.throttle(
        (type) => this.loadComparison(type),
        500
      );
      this.loadPeriodAnalyticsThrottled = DashboardHelpers.throttle(
        (type, offset) => this.loadPeriodAnalytics(type, offset),
        500
      );
      this.loadPeriodComparisonThrottled = DashboardHelpers.throttle(
        (type) => this.loadPeriodComparison(type),
        500
      );
    }

    /**
     * Initialize analytics with event handlers
     * @param {Object} selectors - DOM element selectors
     */
    init(selectors = {}) {
      // Store original selectors
      const originalSelectors = {
        singleYearTabs: selectors.singleYearTabs || 'singleYearTabs',
        compareTabs: selectors.compareTabs || 'compareTabs',
        prevYearBtn: selectors.prevYearBtn || 'viewPrevYear',
        nextYearBtn: selectors.nextYearBtn || 'viewNextYear',
        toggleCompareBtn: selectors.toggleCompareBtn || 'toggleCompare',
        yearNav: selectors.yearNav || 'yearNavigationButtons'
      };

      // Add unique suffix only if enabled
      const suffix = this.uniqueId;

      // Allow custom tab selectors (for period-based mode)
      const analyticsTabClass = selectors.analyticsTab || `.analytics-tab${suffix}`;
      const compareTabClass = selectors.compareTab || `.compare-tab${suffix}`;

      this.selectors = {
        singleYearTabs: `#${originalSelectors.singleYearTabs}${suffix}`,
        compareTabs: `#${originalSelectors.compareTabs}${suffix}`,
        analyticsTab: analyticsTabClass,
        compareTab: compareTabClass,
        prevYearBtn: `#${originalSelectors.prevYearBtn}${suffix}`,
        nextYearBtn: `#${originalSelectors.nextYearBtn}${suffix}`,
        toggleCompareBtn: `#${originalSelectors.toggleCompareBtn}${suffix}`,
        yearNav: `#${originalSelectors.yearNav}${suffix}`
      };

      this._bindEvents();

      if (this.isPeriodBased) {
        this._updatePeriodButtons();
      } else {
        this._updateYearButtons();
      }

      // Hide comparison elements if disabled
      if (!this.enableComparison) {
        $(this.selectors.toggleCompareBtn).hide();
        $(this.selectors.compareTabs).hide();
      }

      // Update toggle button tooltip
      this._updateToggleButtonTooltip();

      // Load initial data
      if (this.isPeriodBased) {
        this.loadPeriodAnalytics(this.currentType, this.periodOffset);
      } else {
        this.loadAnalytics(this.currentType, this.currentYear);
      }
    }

    /**
     * Bind all event handlers
     * @private
     */
    _bindEvents() {
      const ns = '.analyticsManager'; // Namespace for events

      // Unbind existing events first to prevent duplicates
      $(document).off('click' + ns, this.selectors.analyticsTab);
      $(document).off('click' + ns, this.selectors.compareTab);
      $(document).off('click' + ns, this.selectors.prevYearBtn);
      $(document).off('click' + ns, this.selectors.nextYearBtn);
      $(document).off('click' + ns, this.selectors.toggleCompareBtn);

      // Use class-based selectors only (avoid duplicate event binding)
      $(document).on('click' + ns, this.selectors.analyticsTab, (e) => {
        e.preventDefault();
        const $target = $(e.currentTarget);
        this._handleTabClick($target, false);
      });

      $(document).on('click', this.selectors.compareTab, (e) => {
        e.preventDefault();
        const $target = $(e.currentTarget);
        this._handleTabClick($target, true);
      });

      // Year/Period navigation with throttling
      $(document).on('click', this.selectors.prevYearBtn, () => {
        if (this.isPeriodBased) {
          this.periodOffset++;
          this._updatePeriodButtons();
          this.loadPeriodAnalyticsThrottled(this.currentType, this.periodOffset);
        } else {
          this.currentYear--;
          this._updateYearButtons();
          this.loadAnalyticsThrottled(this.currentType, this.currentYear);
        }
      });

      $(document).on('click', this.selectors.nextYearBtn, () => {
        if (this.isPeriodBased) {
          if (this.periodOffset > 0) {
            this.periodOffset--;
            this._updatePeriodButtons();
            this.loadPeriodAnalyticsThrottled(this.currentType, this.periodOffset);
          }
        } else {
          this.currentYear++;
          this._updateYearButtons();
          this.loadAnalyticsThrottled(this.currentType, this.currentYear);
        }
      });

      // Toggle comparison mode
      $(document).on('click', this.selectors.toggleCompareBtn, () => {
        this._toggleCompareMode();
      });
    }

    /**
     * Handle tab click event
     * @private
     */
    _handleTabClick($tab, isComparison) {
      const tabClass = isComparison ? this.selectors.compareTab : this.selectors.analyticsTab;

      // Update styles based on tab design
      if (this.tabDesign === 'nav-tabs') {
        // Nav-tabs design (codebay-nav-tabs)
        $(tabClass).removeClass('active');
        $tab.addClass('active');
      } else {
        // Button design (default)
        $(tabClass).each(function() {
          $(this).removeClass('btn-primary active').addClass('btn-outline-primary');
          $(this).find('.rounded-circle').removeClass('bg-white bg-opacity-25').addClass('bg-light');
        });

        $tab.removeClass('btn-outline-primary').addClass('btn-primary active');
        $tab.find('.rounded-circle').removeClass('bg-light').addClass('bg-white bg-opacity-25');
      }

      this.currentType = $tab.data('type');

      // Update toggle button tooltip
      this._updateToggleButtonTooltip();

      if (isComparison) {
        if (this.isPeriodBased) {
          this.loadPeriodComparisonThrottled(this.currentType);
        } else {
          this.loadComparisonThrottled(this.currentType);
        }
      } else {
        if (this.isPeriodBased) {
          this.periodOffset = 0; // Reset offset when switching tabs
          this.maxOffset = undefined; // Reset boundary for new period type
          this.loadPeriodAnalyticsThrottled(this.currentType, this.periodOffset);
        } else {
          this.loadAnalyticsThrottled(this.currentType, this.currentYear);
        }
      }
    }

    /**
     * Toggle between single year and comparison mode
     * @private
     */
    _toggleCompareMode() {
      if (!this.enableComparison) {
        return; // Do nothing if comparison is disabled
      }

      this.isCompareMode = !this.isCompareMode;
      const $toggleBtn = $(this.selectors.toggleCompareBtn);

      if (this.isCompareMode) {
        $toggleBtn.removeClass('btn-outline-primary').addClass('btn-primary');
        $(this.selectors.yearNav).hide();
        $(this.selectors.singleYearTabs).removeClass('d-flex').addClass('d-none');
        $(this.selectors.compareTabs).removeClass('d-none').addClass('d-flex');
        this.yearDisplay.hide();

        // Get the active comparison tab's type
        const $activeCompareTab = $(this.selectors.compareTab).filter('.active');
        if ($activeCompareTab.length) {
          this.currentType = $activeCompareTab.data('type');
        }

        // Update tooltip after getting the correct comparison tab type
        this._updateToggleButtonTooltip();

        if (this.isPeriodBased) {
          this.loadPeriodComparisonThrottled(this.currentType);
        } else {
          this.loadComparisonThrottled(this.currentType);
        }
      } else {
        $toggleBtn.removeClass('btn-primary').addClass('btn-outline-primary');
        $(this.selectors.yearNav).show();
        $(this.selectors.singleYearTabs).removeClass('d-none').addClass('d-flex');
        $(this.selectors.compareTabs).removeClass('d-flex').addClass('d-none');
        this.yearDisplay.show();

        // Get the active single view tab's type
        const $activeSingleTab = $(this.selectors.analyticsTab).filter('.active');
        if ($activeSingleTab.length) {
          this.currentType = $activeSingleTab.data('type');
        }

        // Update tooltip after getting the correct single view tab type
        this._updateToggleButtonTooltip();

        if (this.isPeriodBased) {
          this.periodOffset = 0;
          this.maxOffset = undefined; // Reset boundary when toggling back from compare
          this.loadPeriodAnalyticsThrottled(this.currentType, this.periodOffset);
        } else {
          this.loadAnalyticsThrottled(this.currentType, this.currentYear);
        }
      }
    }

    /**
     * Update toggle button tooltip text
     * @private
     */
    _updateToggleButtonTooltip() {
      if (!this.enableComparison) return;

      const $toggleBtn = $(this.selectors.toggleCompareBtn);
      if (!$toggleBtn.length) return;

      let tooltipText = 'Compare Data';

      // If comparison mode is active, show "Hide comparison"
      if (this.isCompareMode) {
        tooltipText = this.translations.hideComparison;
      } else {
        // Show appropriate comparison text based on mode
        if (this.isPeriodBased) {
          // Period-based: "Compare weekly data", "Compare monthly data", "Compare yearly data"
          const periodLabels = {
            week: this.translations.weekly || 'weekly',
            month: this.translations.monthly || 'monthly',
            year: this.translations.yearly || 'yearly'
          };
          const periodLabel = periodLabels[this.currentType] || this.currentType;
          tooltipText = `Compare ${periodLabel.toLowerCase()} data`;
        } else {
          // Type-based: "Compare revenue data", "Compare earnings data", "Compare members data"
          const typeLabels = {
            revenue: this.translations.revenue || 'revenue',
            earnings: this.translations.earnings || 'earnings',
            members: this.translations.members || 'members',
            sales: this.translations.sales || 'sales',
            expense: this.translations.expense || 'expense',
            visitors: this.translations.visitors || 'visitors'
          };
          const typeLabel = typeLabels[this.currentType] || this.currentType;
          tooltipText = `Compare ${typeLabel.toLowerCase()} data`;
        }
      }

      // Update tooltip attributes
      $toggleBtn.attr('title', tooltipText).attr('data-bs-original-title', tooltipText);

      // Reinitialize Bootstrap tooltip if it exists
      if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltip = bootstrap.Tooltip.getInstance($toggleBtn[0]);
        if (tooltip) {
          tooltip.dispose();
          new bootstrap.Tooltip($toggleBtn[0]);
        }
      }
    }

    /**
     * Update year navigation buttons state
     * @private
     */
    _updateYearButtons() {
      const $prevBtn = $(this.selectors.prevYearBtn);
      const $nextBtn = $(this.selectors.nextYearBtn);
      const oldestYear = this.actualCurrentYear - 4;
      const prevYear = this.currentYear - 1;
      const nextYear = this.currentYear + 1;

      // Update both title and data-bs-original-title for Bootstrap tooltips
      $prevBtn.attr('title', prevYear).attr('data-bs-original-title', prevYear);
      $nextBtn.attr('title', nextYear).attr('data-bs-original-title', nextYear);

      $prevBtn.prop('disabled', this.currentYear <= oldestYear).toggleClass('disabled', this.currentYear <= oldestYear);
      $nextBtn.prop('disabled', this.currentYear >= this.actualCurrentYear).toggleClass('disabled', this.currentYear >= this.actualCurrentYear);

      // Reinitialize Bootstrap tooltips
      if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        $('[data-bs-toggle="tooltip"]').each(function() {
          const tooltip = bootstrap.Tooltip.getInstance(this);
          if (tooltip) tooltip.dispose();
          new bootstrap.Tooltip(this);
        });
      }
    }

    /**
     * Update period navigation buttons state
     * @private
     */
    _updatePeriodButtons() {
      const $prevBtn = $(this.selectors.prevYearBtn);
      const $nextBtn = $(this.selectors.nextYearBtn);

      // Next button is disabled when offset is 0 (current period)
      $nextBtn.prop('disabled', this.periodOffset === 0).toggleClass('disabled', this.periodOffset === 0);

      // Previous button is disabled when reaching maxOffset boundary
      const maxOffset = this.maxOffset !== undefined ? this.maxOffset : 999;
      $prevBtn.prop('disabled', this.periodOffset >= maxOffset).toggleClass('disabled', this.periodOffset >= maxOffset);
    }

    /**
     * Load period-based analytics (week/month/year with offset)
     * @param {string} type - Period type (week, month, year)
     * @param {number} offset - Period offset
     */
    loadPeriodAnalytics(type, offset) {
      // Hide no-data message immediately
      this._hideNoDataMessage();

      // Check cache first
      if (this.enableCache) {
        const cacheKey = `${type}_${offset}`;
        const cached = this._getCachedData('analytics', cacheKey);
        if (cached) {
          this._renderPeriodChart(cached, type);
          if (cached.period) {
            this.yearDisplay.text(cached.period);
          }
          this._updateSubtitle();
          return;
        }
      }

      DashboardHelpers.ajaxRequest({
        url: this.analyticsUrl,
        data: { type, offset },
        checkSuccess: false,
        $loader: this.loader,
        $content: this.canvas,
        onSuccess: (response) => {
          // Cache the response
          if (this.enableCache) {
            const cacheKey = `${type}_${offset}`;
            this._setCachedData('analytics', cacheKey, response);
          }

          // Store maxOffset for boundary checking
          if (response.maxOffset !== undefined) {
            this.maxOffset = response.maxOffset;
          }

          this._renderPeriodChart(response, type);
          if (response.period) {
            this.yearDisplay.text(response.period);
          }

          // Update subtitle
          this._updateSubtitle();

          // Update navigation buttons
          this._updatePeriodButtons();
        },
        onError: (xhr) => {
          this._handleAjaxError(xhr);
        }
      });
    }

    /**
     * Load single year analytics
     * @param {string} type - Analytics type (revenue, earnings, members)
     * @param {number} year - Year to load
     */
    loadAnalytics(type, year) {
      // Hide no-data message immediately
      this._hideNoDataMessage();

      // Cancel any pending request
      if (this.pendingRequest) {
        this.pendingRequest.abort();
      }

      // Check cache first
      if (this.enableCache) {
        const cacheKey = `${type}_${year}`;
        const cached = this._getCachedData('analytics', cacheKey);
        if (cached) {
          this._renderBarChart(cached, type);
          this.yearDisplay.text(cached.year);
          this._updateSubtitle();
          return;
        }
      }

      this.pendingRequest = DashboardHelpers.ajaxRequest({
        url: this.analyticsUrl,
        data: { type, year },
        checkSuccess: true,
        $loader: this.loader,
        $content: this.canvas,
        onSuccess: (data, response) => {
          // Cache the response
          if (this.enableCache) {
            const cacheKey = `${type}_${year}`;
            this._setCachedData('analytics', cacheKey, response);
          }

          this._renderBarChart(response, type);
          this.yearDisplay.text(response.year);
          this._updateSubtitle();
          this.pendingRequest = null;
        },
        onError: (xhr) => {
          this._handleAjaxError(xhr);
          this.pendingRequest = null;
        }
      });
    }

    /**
     * Load period-based comparison (last 5 weeks/months/years)
     * @param {string} type - Period type (week, month, year)
     */
    loadPeriodComparison(type) {
      if (!this.enableComparison || !this.comparisonUrl) {
        return;
      }

      // Hide no-data message immediately
      this._hideNoDataMessage();

      // Cancel any pending request
      if (this.pendingRequest) {
        this.pendingRequest.abort();
      }

      // Check cache first
      if (this.enableCache) {
        const cacheKey = `compare_${type}`;
        const cached = this._getCachedData('comparison', cacheKey);
        if (cached) {
          this._renderLineChart(cached, type);
          this._updateSubtitle();
          return;
        }
      }

      this.pendingRequest = DashboardHelpers.ajaxRequest({
        url: this.comparisonUrl,
        data: { type },
        checkSuccess: false,
        $loader: this.loader,
        $content: this.canvas,
        onSuccess: (response) => {
          // Cache the response
          if (this.enableCache) {
            const cacheKey = `compare_${type}`;
            this._setCachedData('comparison', cacheKey, response);
          }

          this._renderLineChart(response, type);
          this._updateSubtitle();
          this.pendingRequest = null;
        },
        onError: (xhr) => {
          this._handleAjaxError(xhr);
          this.pendingRequest = null;
        }
      });
    }

    /**
     * Load multi-year comparison
     * @param {string} type - Analytics type
     */
    loadComparison(type) {
      if (!this.enableComparison || !this.comparisonUrl) {
        return; // Do nothing if comparison is disabled or URL not provided
      }

      // Hide no-data message immediately
      this._hideNoDataMessage();

      // Cancel any pending request
      if (this.pendingRequest) {
        this.pendingRequest.abort();
      }

      // Check cache first
      if (this.enableCache) {
        const cached = this._getCachedData('comparison', type);
        if (cached) {
          this._renderLineChart(cached, type);
          this._updateSubtitle();
          return;
        }
      }

      this.pendingRequest = DashboardHelpers.ajaxRequest({
        url: this.comparisonUrl,
        data: { type },
        checkSuccess: true,
        $loader: this.loader,
        $content: this.canvas,
        onSuccess: (data, response) => {
          // Cache the response
          if (this.enableCache) {
            this._setCachedData('comparison', type, response);
          }

          this._renderLineChart(response, type);
          this._updateSubtitle();
          this.pendingRequest = null;
        },
        onError: (xhr) => {
          this._handleAjaxError(xhr);
          this.pendingRequest = null;
        }
      });
    }

    /**
     * Render chart for period-based analytics (line or bar based on type)
     * @private
     */
    _renderPeriodChart(response, type) {
      if (this.chart) {
        this.chart.destroy();
        this.chart = null;
      }

      const ctx = this.canvas[0].getContext('2d');

      // Clear canvas before rendering
      ctx.clearRect(0, 0, this.canvas[0].width, this.canvas[0].height);

      const chartColor = this._getChartColor(type);

      // Check if all data is zero
      const hasData = response.data && response.data.some(value => value > 0);

      if (!hasData) {
        this._showNoDataMessage();
        return;
      }

      // Ensure canvas is visible
      this.canvas.removeClass('d-none');

      // All period-based single views use bar charts
      this.chart = new Chart(ctx, {
        type: 'bar',
        plugins: typeof ChartDataLabels !== 'undefined' ? [ChartDataLabels] : [],
        data: {
          labels: response.labels,
          datasets: [{
            label: this.translations[type] || type,
            data: response.data,
            backgroundColor: (context) => {
              // Highlight current period (week/month/year) with full color, others with 30% opacity
              const currentIndex = response.currentIndex ?? response.currentMonth ?? response.currentDay ?? -1;
              return (currentIndex >= 0 && context.dataIndex === currentIndex)
                ? chartColor
                : DashboardHelpers.hexToRgba(chartColor, 0.3);
            },
            borderColor: 'transparent',
            borderWidth: 0,
            borderRadius: 8,
            borderSkipped: false,
            barPercentage: 0.7,
            categoryPercentage: 0.8,
            hoverBackgroundColor: chartColor
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(17, 24, 39, 0.95)',
              titleColor: '#fff',
              bodyColor: '#e5e7eb',
              padding: 12,
              cornerRadius: 8,
              displayColors: false,
              callbacks: {
                label: (context) => {
                  const label = context.dataset.label || '';
                  const value = context.parsed.y;
                  return `${label}: ${value}`;
                }
              }
            },
            datalabels: {
              display: true,
              anchor: 'end',
              align: 'top',
              offset: 4,
              formatter: (value) => {
                if (value === 0) return '';
                return this._formatValue(value);
              },
              color: '#6b7280',
              font: { size: 10, weight: '500' }
            }
          },
          layout: {
            padding: {
              top: 25
            }
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: { color: '#9ca3af', font: { size: 11 } }
            },
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(156, 163, 175, 0.1)',
                drawBorder: false
              },
              ticks: {
                color: '#9ca3af',
                precision: 0
              }
            }
          }
        }
      });
    }

    /**
     * Render bar chart for single year
     * @private
     */
    _renderBarChart(response, type) {
      if (this.chart) {
        this.chart.destroy();
        this.chart = null;
      }

      const ctx = this.canvas[0].getContext('2d');

      // Clear canvas before rendering
      ctx.clearRect(0, 0, this.canvas[0].width, this.canvas[0].height);

      const chartColor = this._getChartColor(type);

      // Check if all data is zero
      const hasData = response.data && response.data.some(value => value > 0);

      if (!hasData) {
        this._showNoDataMessage();
        return;
      }

      // Ensure canvas is visible
      this.canvas.removeClass('d-none');

      this.chart = new Chart(ctx, {
        type: 'bar',
        plugins: typeof ChartDataLabels !== 'undefined' ? [ChartDataLabels] : [],
        data: {
          labels: response.labels,
          datasets: [{
            label: this._getChartLabel(type),
            data: response.data,
            backgroundColor: (context) => {
              return (response.currentMonth >= 0 && context.dataIndex === response.currentMonth)
                ? chartColor
                : DashboardHelpers.hexToRgba(chartColor, 0.3);
            },
            borderColor: 'transparent',
            borderWidth: 0,
            borderRadius: 8,
            borderSkipped: false,
            barPercentage: 0.7,
            categoryPercentage: 0.8,
            hoverBackgroundColor: chartColor
          }]
        },
        options: this._getBarChartOptions(type)
      });
    }

    /**
     * Render line chart for comparison
     * @private
     */
    _renderLineChart(response, type) {
      if (this.chart) {
        this.chart.destroy();
        this.chart = null;
      }

      const ctx = this.canvas[0].getContext('2d');

      // Clear canvas before rendering
      ctx.clearRect(0, 0, this.canvas[0].width, this.canvas[0].height);

      const colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

      // Check if all datasets have no data
      const hasData = response.datasets && response.datasets.some(dataset =>
        dataset.data && dataset.data.some(value => value > 0)
      );

      if (!hasData) {
        this._showNoDataMessage();
        return;
      }

      // Ensure canvas is visible
      this.canvas.removeClass('d-none');

      const datasets = response.datasets.map((dataset, index) => ({
        label: dataset.label,
        data: dataset.data,
        borderColor: colors[index],
        backgroundColor: DashboardHelpers.hexToRgba(colors[index], 0.1),
        borderWidth: 2,
        tension: 0.4,
        fill: false,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: colors[index],
        pointBorderColor: '#fff',
        pointBorderWidth: 2
      }));

      this.chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: response.labels,
          datasets: datasets
        },
        options: this._getLineChartOptions(type)
      });
    }

    /**
     * Get bar chart options
     * @private
     */
    _getBarChartOptions(type) {
      return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        layout: { padding: { top: 25 } },
        plugins: {
          legend: { display: false },
          datalabels: {
            display: typeof ChartDataLabels !== 'undefined',
            anchor: 'end',
            align: 'top',
            formatter: (value) => {
              if (value === 0) return '';
              return this._formatValue(value);
            },
            color: '#6b7280',
            font: { size: 10, weight: '500' }
          },
          tooltip: {
            backgroundColor: 'rgba(17, 24, 39, 0.95)',
            titleColor: '#fff',
            bodyColor: '#e5e7eb',
            padding: 12,
            cornerRadius: 8,
            displayColors: false,
            callbacks: {
              label: (context) => {
                let label = context.dataset.label || '';
                if (label) label += ': ';
                label += this._formatValue(context.parsed.y);
                return label;
              }
            }
          }
        },
        scales: this._getChartScales(type)
      };
    }

    /**
     * Get line chart options
     * @private
     */
    _getLineChartOptions(type) {
      return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
          legend: {
            display: true,
            position: 'top',
            align: 'end',
            labels: {
              usePointStyle: true,
              padding: 15,
              font: { size: 12 }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(17, 24, 39, 0.95)',
            titleColor: '#fff',
            bodyColor: '#e5e7eb',
            padding: 12,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
              label: (context) => {
                let label = context.dataset.label || '';
                if (label) label += ': ';
                label += this._formatValue(context.parsed.y);
                return ' ' + label;
              }
            }
          }
        },
        scales: this._getChartScales(type)
      };
    }

    /**
     * Get chart scales configuration
     * @private
     */
    _getChartScales(type) {
      return {
        x: {
          grid: { display: false },
          ticks: { color: '#9ca3af', font: { size: 11 } }
        },
        y: {
          grid: {
            color: 'rgba(156, 163, 175, 0.1)',
            drawBorder: false
          },
          ticks: {
            color: '#9ca3af',
            callback: (value) => {
              return this._formatValue(value);
            }
          }
        }
      };
    }

    /**
     * Get chart color based on type
     * @private
     */
    _getChartColor(type) {
      const colorMap = {
        revenue: this.colors.primary,
        earnings: this.colors.success,
        members: this.colors.info
      };
      return colorMap[type] || this.colors.primary;
    }

    /**
     * Get chart label based on type
     * @private
     */
    _getChartLabel(type) {
      const labelMap = {
        revenue: this.translations.revenue || 'Total Revenue',
        earnings: this.translations.earnings || 'Seller Earnings',
        members: this.translations.members || 'Total Members'
      };
      return labelMap[type] || '';
    }

    /**
     * Format value based on dataFormat setting
     * @private
     */
    _formatValue(value) {
      return DashboardHelpers.formatValue(value, this.currencySymbol, this.dataFormat);
    }

    /**
     * Show error message inline
     * @private
     */
    _showError(message) {
      // Hide loader first
      DashboardHelpers.hideLoader(this.loader);

      // Show error in canvas parent container
      const $container = this.canvas.parent();
      DashboardHelpers.showEmptyState($container, {
        title: 'Error',
        message: message,
        icon: 'bi-exclamation-triangle'
      });

      // Hide canvas to ensure error is visible
      this.canvas.addClass('d-none');
    }

    /**
     * Handle AJAX error
     * @private
     */
    _handleAjaxError(xhr) {
      const defaultMessage = this.translations.loadFailed || 'Failed to load data';
      if (xhr.statusText === 'abort') return;

      let errorMessage = defaultMessage;
      this._showError(errorMessage);
    }

    /**
     * Apply unique IDs to HTML elements
     * Call this method after initialization to update element IDs/classes in DOM
     * @param {string} containerSelector - Parent container selector
     * @returns {Object} - Mapping of original to new IDs
     */
    applyUniqueIds(containerSelector) {
      const $container = $(containerSelector);
      if (!$container.length) {
        return {};
      }

      const mapping = {
        elements: {},
        buttons: []
      };

      // Update element IDs
      const elementIds = [
        { original: 'singleYearTabs', selector: '[id*="singleYearTabs"]' },
        { original: 'compareTabs', selector: '[id*="compareTabs"]' },
        { original: 'viewPrevYear', selector: '[id*="viewPrevYear"]' },
        { original: 'viewNextYear', selector: '[id*="viewNextYear"]' },
        { original: 'toggleCompare', selector: '[id*="toggleCompare"]' },
        { original: 'yearNavigationButtons', selector: '[id*="yearNavigationButtons"]' }
      ];

      elementIds.forEach(({ original, selector }) => {
        const $elem = $container.find(selector).first();
        if ($elem.length) {
          const newId = `${original}${this.uniqueId}`;
          $elem.attr('id', newId);
          mapping.elements[original] = newId;
        }
      });

      // Update button IDs and classes
      this.buttons.forEach((btn, index) => {
        const $btns = $container.find(`[id="${btn.originalId}"]`);
        $btns.each(function() {
          const $this = $(this);
          const isCompare = $this.closest('[id*="compareTabs"]').length > 0;

          // Update ID
          $this.attr('id', btn.id);

          // Add unique class
          const className = isCompare ? 'compare-tab' : 'analytics-tab';
          $this.addClass(`${className}${btn.id.replace(btn.originalId, '')}`);

          mapping.buttons.push({
            original: btn.originalId,
            new: btn.id,
            type: btn.type
          });
        });
      });

      return mapping;
    }

    /**
     * Get unique ID suffix for this instance
     * @returns {string}
     */
    getUniqueId() {
      return this.uniqueId;
    }

    /**
     * Get button configuration with unique IDs
     * @returns {Array}
     */
    getButtons() {
      return this.buttons;
    }

    /**
     * Get cached data if valid
     * @private
     * @param {string} category - 'analytics' or 'comparison'
     * @param {string} key - Cache key
     * @returns {Object|null}
     */
    _getCachedData(category, key) {
      const cached = this.cache[category][key];
      if (!cached) return null;

      const now = Date.now();
      const age = now - cached.timestamp;

      // Check if cache is still valid
      if (age > this.cacheTTL) {
        delete this.cache[category][key];
        return null;
      }

      return cached.data;
    }

    /**
     * Set cached data
     * @private
     * @param {string} category - 'analytics' or 'comparison'
     * @param {string} key - Cache key
     * @param {Object} data - Data to cache
     */
    _setCachedData(category, key, data) {
      this.cache[category][key] = {
        data: data,
        timestamp: Date.now()
      };
    }

    /**
     * Clear specific cache
     * @param {string} category - 'analytics' or 'comparison' or 'all'
     * @param {string} key - Specific key (optional, clears all if not provided)
     */
    clearCache(category = 'all', key = null) {
      if (category === 'all') {
        this.cache = { analytics: {}, comparison: {} };
      } else if (key) {
        delete this.cache[category][key];
      } else {
        this.cache[category] = {};
      }
    }

    /**
     * Get cache statistics
     * @returns {Object}
     */
    getCacheStats() {
      const stats = {
        enabled: this.enableCache,
        ttl: this.cacheTTL,
        analytics: {
          count: Object.keys(this.cache.analytics).length,
          keys: Object.keys(this.cache.analytics)
        },
        comparison: {
          count: Object.keys(this.cache.comparison).length,
          keys: Object.keys(this.cache.comparison)
        }
      };
      return stats;
    }

    /**
     * Refresh analytics data by clearing cache and reloading current view
     * @param {Function} callback - Optional callback after refresh
     */
    refreshData(callback) {
      // Clear all cache
      this.clearCache('all');

      // Reload current view based on mode
      if (this.isCompareMode) {
        // In comparison mode
        if (this.isPeriodBased) {
          this.loadPeriodComparison(this.currentType);
        } else {
          this.loadComparison(this.currentType);
        }
      } else {
        // In single view mode
        if (this.isPeriodBased) {
          this.loadPeriodAnalytics(this.currentType, this.periodOffset);
        } else {
          this.loadAnalytics(this.currentType, this.currentYear);
        }
      }

      // Execute callback if provided
      if (typeof callback === 'function') {
        callback();
      }
    }

    /**
     * Show "No data found" message in chart area
     * @private
     */
    _showNoDataMessage() {
      const canvasParent = this.canvas.parent();

      // Remove any existing no-data messages first
      canvasParent.find('.no-data-message').remove();

      // Hide canvas and show no-data message
      this.canvas.addClass('d-none');
      DashboardHelpers.showNoDataMessage(canvasParent, this.translations.noData, 'bi-inbox');
    }

    /**
     * Hide "No data found" message and show canvas
     * @private
     */
    _hideNoDataMessage() {
      const canvasParent = this.canvas.parent();

      // Remove no-data message from parent
      canvasParent.find('.no-data-message').remove();

      // Show canvas
      this.canvas.removeClass('d-none');
    }

    /**
     * Update card subtitle with type and period information
     * @private
     */
    _updateSubtitle() {
      if (!this.cardSubtitle || !this.cardSubtitle.length) {
        return;
      }

      // Get type label
      const typeLabels = {
        revenue: this.translations.revenue || 'Revenue',
        sales: this.translations.sales || 'Sales',
        members: this.translations.members || 'Members'
      };
      // Period-based comparison (last 5 weeks/months/years)
      const periodLabels = {
        week: this.translations.weekly,
        month: this.translations.monthly,
        year: this.translations.yearly
      };
      const subtitlePrefixLabel = this.translations.subtitlePrefix || 'Analytics';

      let subtitle = '';

      if (this.isCompareMode) {
        // Comparison mode
        if (this.isPeriodBased) {
          const periodLabel = periodLabels[this.currentType] || 'Period';
          subtitle = `${periodLabel} ${subtitlePrefixLabel} Comparison`;
        } else {
          // Multi-year comparison mode
          const typeLabel = typeLabels[this.currentType] || this.currentType.charAt(0).toUpperCase() + this.currentType.slice(1);
          subtitle = `${periodLabels.year} ${typeLabel} Comparison`;
        }
      } else if (this.isPeriodBased) {
        const periodLabel = periodLabels[this.currentType] || 'Overview';
        subtitle = `${periodLabel} ${subtitlePrefixLabel} Overview`;
      } else {
        // Single year analytics
        const typeLabel = typeLabels[this.currentType] || this.currentType.charAt(0).toUpperCase() + this.currentType.slice(1);
        subtitle = `${periodLabels.year} ${typeLabel} Overview`;
      }

      this.cardSubtitle.text(subtitle);
    }

    /**
     * Destroy chart instance and cleanup
     */
    destroy() {
      // Cancel any pending requests
      if (this.pendingRequest) {
        this.pendingRequest.abort();
        this.pendingRequest = null;
      }

      // Unbind all events with namespace
      const ns = '.analyticsManager';
      if (this.selectors) {
        $(document).off('click' + ns, this.selectors.analyticsTab);
        $(document).off('click' + ns, this.selectors.compareTab);
        $(document).off('click' + ns, this.selectors.prevYearBtn);
        $(document).off('click' + ns, this.selectors.nextYearBtn);
        $(document).off('click' + ns, this.selectors.toggleCompareBtn);
      }

      // Destroy chart
      if (this.chart) {
        this.chart.destroy();
        this.chart = null;
      }

      // Clear cache on destroy
      this.clearCache();
    }
  };

  /**
   * CountryAnalyticsManager - Manages country sales analytics with period filtering
   *
   * @class CountryAnalyticsManager
   * @param {Object} options - Configuration options
   * @param {string} options.containerId - Container element ID (default: 'countryAnalyticsContent')
   * @param {string} options.loaderId - Loader element ID (default: 'countryAnalyticsLoader')
   * @param {string} options.cardId - Card element ID for subtitle updates (default: 'countrySalesCard')
   * @param {string} options.apiUrl - Country analytics endpoint (required)
   * @param {string} options.defaultPeriod - Default period type (default: 'this_month')
   * @param {string} options.currencySymbol - Currency symbol (default: '$')
   * @param {boolean} options.enableCache - Enable caching (default: true)
   * @param {number} options.cacheTTL - Cache time-to-live in ms (default: 300000 = 5 min)
   * @param {Object} options.translations - Translation strings
   */
  window.CountryAnalyticsManager = class {
    constructor(options) {
      this.containerId = options.containerId || 'countryAnalyticsContent';
      this.loaderId = options.loaderId || 'countryAnalyticsLoader';
      this.cardId = options.cardId || 'countrySalesCard';
      this.apiUrl = options.apiUrl;
      this.currentPeriod = options.defaultPeriod || 'this_month';
      this.currencySymbol = options.currencySymbol || '$';

      // Cache configuration
      this.enableCache = options.enableCache !== false;
      this.cacheTTL = options.cacheTTL || 5 * 60 * 1000; // 5 minutes default
      this.cache = {}; // Format: { 'period': { data, timestamp } }

      // Translations
      this.translations = $.extend({}, window.CommonTranslations, options.translations);

      // Get DOM elements
      this.container = $(`#${this.containerId}`);
      this.loader = $(`#${this.loaderId}`);
      this.card = $(`#${this.cardId}`);
      this.pendingRequest = null; // Track pending AJAX requests

      // Create throttled load method to prevent rapid API calls
      this.loadAnalyticsThrottled = DashboardHelpers.throttle(
        (period) => this.loadAnalytics(period),
        500
      );
    }

    /**
     * Initialize the country analytics manager
     */
    init() {
      this._bindEvents();
      this.loadAnalytics(this.currentPeriod);
    }

    /**
     * Bind event handlers
     * @private
     */
    _bindEvents() {
      const self = this;
      const ns = '.countryAnalytics';

      // Unbind first to prevent duplicates
      $(document).off('click' + ns, '.country-period-option');

      // Handle period selection with throttling
      $(document).on('click' + ns, '.country-period-option', function(e) {
        e.preventDefault();

        const period = $(this).data('period');
        self.currentPeriod = period;

        // Update active state
        $('.country-period-option').removeClass('active');
        $(this).addClass('active');

        // Load data for selected period with throttling
        self.loadAnalyticsThrottled(period);
      });
    }

    /**
     * Check if cached data is still valid
     * @private
     */
    _isCacheValid(cacheKey) {
      if (!this.enableCache || !this.cache[cacheKey]) {
        return false;
      }

      const now = Date.now();
      const cacheAge = now - this.cache[cacheKey].timestamp;
      return cacheAge < this.cacheTTL;
    }

    /**
     * Get data from cache
     * @private
     */
    _getFromCache(cacheKey) {
      if (this._isCacheValid(cacheKey)) {
        return this.cache[cacheKey].data;
      }
      return null;
    }

    /**
     * Save data to cache
     * @private
     */
    _saveToCache(cacheKey, data) {
      if (this.enableCache) {
        this.cache[cacheKey] = {
          data: data,
          timestamp: Date.now()
        };
      }
    }

    /**
     * Load country analytics for a specific period
     * @param {string} period - Period type ('week', 'month', 'year')
     */
    loadAnalytics(period) {
      const self = this;
      const cacheKey = period;

      // Cancel any pending request
      if (this.pendingRequest) {
        this.pendingRequest.abort();
      }

      // Check cache first
      const cachedData = this._getFromCache(cacheKey);
      if (cachedData) {
        this._renderData(cachedData, period);
        return;
      }

      this.pendingRequest = DashboardHelpers.ajaxRequest({
        url: this.apiUrl,
        data: { period: period },
        $loader: this.loader,
        $content: this.container,
        $container: this.container,
        onSuccess: (data) => {
          // Save to cache
          this._saveToCache(cacheKey, data);
          this._renderData(data, period);
          this.pendingRequest = null;
        },
        onError: () => {
          this.pendingRequest = null;
        }
      });
    }

    /**
     * Render country data
     * @private
     */
    _renderData(countries, period) {
      // Update subtitle
      const subtitles = {
        last_7_days: this.translations.last7Days,
        last_28_days: this.translations.last28Days,
        this_month: this.translations.thisMonth,
        this_year: this.translations.thisYear,
        lifetime: this.translations.lifetime
      };

      const subtitle = subtitles[period] || this.translations.thisMonth;
      this.card.find('.card-subtitle').text(subtitle);

      // Check if no data
      if (!countries || countries.length === 0) {
        this.container.find('.country-item').remove(); // Clear existing items
        DashboardHelpers.hideLoader(this.loader, this.container);
        DashboardHelpers.showNoDataMessage(this.container);
        return;
      }

      // Remove no-data message if it exists
      this.container.find('.no-data-message').remove();

      // Get template and container
      const template = document.getElementById('countryItemTemplate');
      const itemsContainer = document.getElementById('countryAnalyticsItems');

      if (!template || !itemsContainer) {
        return;
      }

      // Clear existing items (except template)
      itemsContainer.querySelectorAll('.country-item').forEach(item => item.remove());

      // Populate data using template
      countries.forEach((country) => {
        const clone = template.content.cloneNode(true);
        const item = clone.querySelector('.country-item');

        // Set data
        item.dataset.country = country.country_code || '';

        // Update flag
        const flag = clone.querySelector('.country-flag');
        flag.src = country.flag;
        flag.alt = country.country_name;

        // Update amount
        const formattedAmount = this._formatAmount(country.amount);
        clone.querySelector('.country-amount').textContent = formattedAmount;

        // Update name
        clone.querySelector('.country-name').textContent = country.country_name;

        // Update badge
        const badge = clone.querySelector('.country-badge');
        const arrow = clone.querySelector('.country-arrow');
        const percentage = clone.querySelector('.country-percentage');

        const percentageClass = country.is_positive ? 'bg-text-green' : 'bg-text-red';
        const arrowIcon = country.is_positive ? 'bi-arrow-up' : 'bi-arrow-down';
        const percentageSign = country.percentage_change >= 0 ? '+' : '';

        badge.className = `badge ${percentageClass}`;
        arrow.className = arrowIcon;
        percentage.textContent = `${percentageSign}${Math.abs(country.percentage_change)}%`;

        itemsContainer.appendChild(clone);
      });
    }

    /**
     * Format amount with K, M, B suffix
     * @private
     */
    _formatAmount(amount) {
      return DashboardHelpers.formatValue(amount, this.currencySymbol, 'currency');
    }

    /**
     * Clear all cached data
     */
    clearCache() {
      this.cache = {};
    }

    /**
     * Refresh data by clearing cache and reloading
     * @param {Function} callback - Optional callback after refresh
     */
    refreshData(callback) {
      this.clearCache();
      this.loadAnalytics(this.currentPeriod);

      if (typeof callback === 'function') {
        callback();
      }
    }

    /**
     * Destroy instance and cleanup
     */
    destroy() {
      // Cancel any pending requests
      if (this.pendingRequest) {
        this.pendingRequest.abort();
        this.pendingRequest = null;
      }

      // Unbind events with namespace
      const ns = '.countryAnalytics';
      $(document).off('click' + ns, '.country-period-option');

      // Clear cache
      this.clearCache();
    }
  };

  /**
   * GaugeChartManager - Manages gauge chart statistics with period filtering
   *
   * @class GaugeChartManager
   * @param {Object} options - Configuration options
   * @param {string} options.cardId - Card container element ID (default: 'gaugeChartCard')
   * @param {string} options.contentId - Content container element ID (default: 'gaugeChartContent')
   * @param {string} options.loaderId - Loader element ID (default: 'gaugeChartLoader')
   * @param {string} options.canvasId - Chart canvas element ID (default: 'gaugeChart')
   * @param {string} options.apiUrl - API endpoint for data (required)
   * @param {string} options.defaultPeriod - Default period: last_7_days/last_28_days/this_month/this_year (default: 'this_month')
   * @param {string} options.periodOptionClass - Period option button class (default: 'gauge-period-option')
   * @param {boolean} options.enableCache - Enable caching (default: true)
   * @param {number} options.cacheTTL - Cache time-to-live in ms (default: 300000 = 5 min)
   * @param {Object} options.translations - Translation strings
   */
  window.GaugeChartManager = class {
    constructor(options) {
      this.cardId = options.cardId || 'gaugeChartCard';
      this.contentId = options.contentId || 'gaugeChartContent';
      this.loaderId = options.loaderId || 'gaugeChartLoader';
      this.canvasId = options.canvasId || 'gaugeChart';
      this.apiUrl = options.apiUrl;
      this.currentPeriod = options.defaultPeriod || 'this_month';
      this.chart = null;

      // Element IDs (configurable)
      this.newItemId = options.newItemId || 'newItems';
      this.openItemId = options.openItemId || 'openItems';
      this.totalItemId = options.totalItemId || 'totalItems';

      // Period option class (configurable)
      this.periodOptionClass = options.periodOptionClass || 'gauge-period-option';

      // Cache configuration
      this.enableCache = options.enableCache !== false;
      this.cacheTTL = options.cacheTTL || 5 * 60 * 1000; // 5 minutes
      this.cache = {}; // Format: { 'period': { data, timestamp } }

      // Elements
      this.card = $(`#${this.cardId}`);
      this.content = $(`#${this.contentId}`);
      this.loader = $(`#${this.loaderId}`);
      this.pendingRequest = null; // Track pending AJAX requests

      // Translations
      this.translations = $.extend({}, window.CommonTranslations, options.translations);

      // Chart colors
      this.chartColor = options.chartColor || '#6366f1';

      // Create throttled load method to prevent rapid API calls
      this.loadDataThrottled = DashboardHelpers.throttle(
        (period) => this.loadData(period),
        500
      );
    }

    /**
     * Initialize the manager
     */
    init() {
      this.bindEvents();
      // Only load data if we have a period or if period is explicitly null for non-period cards
      if (this.currentPeriod !== undefined) {
        this.loadData(this.currentPeriod);
      }
    }

    /**
     * Bind UI events
     * @private
     */
    bindEvents() {
      const ns = '.gaugeManager';
      $(document).off('click' + ns, `.${this.periodOptionClass}`); // Unbind first
      $(document).on('click' + ns, `.${this.periodOptionClass}`, (e) => {
        e.preventDefault();
        const $btn = $(e.currentTarget);
        const period = $btn.data('period');

        $(`.${this.periodOptionClass}`).removeClass('active');
        $btn.addClass('active');

        this.loadDataThrottled(period);
      });
    }

    /**
     * Check if cached data is valid
     * @private
     */
    _isCacheValid(period) {
      if (!this.enableCache || !this.cache[period]) {
        return false;
      }

      const now = Date.now();
      const cacheAge = now - this.cache[period].timestamp;
      return cacheAge < this.cacheTTL;
    }

    /**
     * Load gauge chart data
     * @param {string} period - Period type: 'week', 'month', 'year'
     */
    loadData(period) {
      this.currentPeriod = period;

      // Cancel any pending request
      if (this.pendingRequest) {
        this.pendingRequest.abort();
      }

      // Restore spinner HTML if no-data message was showing
      this.hideNoDataMessage();

      // Check cache first
      if (this._isCacheValid(period)) {
        const cachedData = this.cache[period].data;
        // Don't show loader for cached data, directly update UI
        this.updateUI(cachedData);
        this.renderChart(cachedData.percentage);
        return;
      }

      this.pendingRequest = DashboardHelpers.ajaxRequest({
        url: this.apiUrl,
        data: { period: period },
        $loader: this.loader,
        $content: this.content,
        $container: this.content,
        onSuccess: (data) => {
          // Cache only the data portion
          if (this.enableCache) {
            this.cache[period] = {
              data: data,
              timestamp: Date.now()
            };
          }

          this.updateUI(data);
          this.renderChart(data.percentage);
          this.pendingRequest = null;
        }
      });
    }

    /**
     * Update UI with data
     * @private
     */
    updateUI(data) {
      if (!data) return;

      // Update subtitle first (before checking data availability)
      if (this.currentPeriod && this.translations) {
        const subtitle = {
          last_7_days: this.translations.last7Days,
          last_28_days: this.translations.last28Days,
          this_month: this.translations.thisMonth,
          this_year: this.translations.thisYear,
          lifetime: this.translations.lifetime
        };
        if (subtitle[this.currentPeriod]) {
          this.card.find('.card-subtitle').text(subtitle[this.currentPeriod]);
        }
      }

      // Check if no data available
      const total = data.total || 0;
      const newItems = data.new || 0;
      const openItems = data.open || 0;

      if (total === 0 && newItems === 0 && openItems === 0) {
        this.showNoDataMessage();
        return;
      }

      // Remove no-data message if it exists
      this.content.find('.no-data-message').remove();

      // Hide loader, show content
      DashboardHelpers.hideLoader(this.loader, this.content);

      // Batch DOM updates
      const updates = {};
      if (this.totalItemId) updates[this.totalItemId] = total;
      updates[this.newItemId] = newItems;
      updates[this.openItemId] = openItems;

      Object.entries(updates).forEach(([id, value]) => {
        $(`#${id}`).text(value);
      });
    }

    /**
     * Render gauge chart
     * @private
     */
    renderChart(percentage) {
      const canvas = document.getElementById(this.canvasId);
      if (!canvas) return;

      if (this.chart) {
        this.chart.destroy();
      }

      const ctx = canvas.getContext('2d', { willReadFrequently: false });

      // Create segmented gauge data with gradient effect
      const totalSegments = 20;
      const filledSegments = Math.round((percentage / 100) * totalSegments);
      const segmentData = [];
      const segmentColors = [];

      for (let i = 0; i < totalSegments; i++) {
        segmentData.push(1);
        if (i < filledSegments) {
          // Create gradient from faded (0.4) to deep (1.0)
          const opacity = 0.4 + (i / (filledSegments - 1)) * 0.6;
          segmentColors.push(DashboardHelpers.hexToRgba(this.chartColor, opacity));
        } else {
          segmentColors.push('#e5e7eb');
        }
      }

      this.chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          datasets: [{
            data: segmentData,
            backgroundColor: segmentColors,
            borderWidth: 6,
            borderColor: '#fff',
            cutout: '70%',
            circumference: 225,
            rotation: 225,
            borderRadius: 2,
            spacing: 4,
            hoverBackgroundColor: segmentColors,
            hoverBorderColor: '#fff',
            hoverBorderWidth: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: null
          },
          plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
          }
        },
        plugins: [{
          afterDraw: (chart) => {
            const { width, height, ctx } = chart;
            ctx.restore();

            // Draw percentage text
            ctx.font = 'bold 20px Inter, sans-serif';
            ctx.textBaseline = 'middle';
            ctx.textAlign = 'center';
            ctx.fillStyle = '#111827';
            const text = percentage + '%';
            ctx.fillText(text, width / 2, height / 2 - 5);

            // Draw label
            ctx.font = '13px Inter, sans-serif';
            ctx.fillStyle = '#72767bff';
            const label = this.translations.labelText;
            ctx.fillText(label, width / 2, height / 2 + 15);
            ctx.save();
          }
        }]
      });
    }

    /**
     * Show "No data available" message
     * @private
     */
    showNoDataMessage() {
      // Hide loader first
      DashboardHelpers.hideLoader(this.loader, this.content);
      // Hide all content children
      this.content.children().addClass('d-none');
      // Show no-data message in content area (not in loader)
      DashboardHelpers.showNoDataMessage(this.content);
    }

    /**
     * Hide "No data available" message
     * @private
     */
    hideNoDataMessage() {
      // Remove no-data message from content area
      this.content.find('.no-data-message').remove();
      // Show all content children
      this.content.children().removeClass('d-none');
    }

    /**
     * Clear all cached data
     */
    clearCache() {
      this.cache = {};
    }

    /**
     * Refresh data by clearing cache and reloading
     * @param {Function} callback - Optional callback after refresh
     */
    refreshData(callback) {
      this.clearCache();
      this.loadData(this.currentPeriod);

      if (typeof callback === 'function') {
        callback();
      }
    }

    /**
     * Destroy instance and cleanup
     */
    destroy() {
      // Cancel any pending requests
      if (this.pendingRequest) {
        this.pendingRequest.abort();
        this.pendingRequest = null;
      }

      // Unbind events with namespace
      const ns = '.gaugeManager';
      $(document).off('click' + ns, `.${this.periodOptionClass}`);

      // Destroy chart
      if (this.chart) {
        this.chart.destroy();
        this.chart = null;
      }

      // Clear cache
      this.clearCache();
    }
  };

  /**
   * StatisticsManager - Manages dashboard statistics filter
   *
   * @class StatisticsManager
   * @param {Object} options - Configuration options
   * @param {string} options.apiUrl - Statistics API endpoint (required)
   * @param {string} options.cardId - Statistics card element ID (default: 'statisticsCard')
   * @param {string} options.defaultPeriod - Default period (default: 'lifetime')
   * @param {string} options.currencySymbol - Currency symbol (default: '$')
   * @param {number} options.currencyPosition - Currency position: 1=before, 2=after (default: 1)
   * @param {boolean} options.enableCache - Enable caching (default: true)
   * @param {number} options.cacheTTL - Cache time-to-live in ms (default: 300000 = 5 min)
   * @param {Object} options.translations - Translation strings
   */
  window.StatisticsManager = class {
    constructor(options) {
      this.apiUrl = options.apiUrl;
      this.cardId = options.cardId || 'statisticsCard';
      this.card = $(`#${this.cardId}`);
      this.currentPeriod = options.defaultPeriod || 'lifetime';
      this.currencySymbol = options.currencySymbol || '$';
      this.currencyPosition = options.currencyPosition || 1;

      // Cache configuration
      this.enableCache = options.enableCache !== false;
      this.cacheTTL = options.cacheTTL || 5 * 60 * 1000;
      this.cache = {};
      this.pendingRequest = null; // Track pending AJAX requests

      // Translations
      this.translations = $.extend({}, window.CommonTranslations, options.translations || {});

      // Create throttled load method to prevent rapid API calls
      this.loadDataThrottled = DashboardHelpers.throttle(
        (period) => this.loadData(period),
        500
      );
    }

    /**
     * Initialize the statistics manager
     */
    init() {
      this.bindEvents();
    }

    /**
     * Bind dropdown click events
     */
    bindEvents() {
      const self = this;
      const ns = '.statsManager';

      $(document).off('click' + ns, '.stats-period-option'); // Unbind first
      $(document).on('click' + ns, '.stats-period-option', function(e) {
        e.preventDefault();

        const period = $(this).data('period');
        const $btn = $(this);

        // Update active state
        $('.stats-period-option').removeClass('active');
        $btn.addClass('active');

        // Load data with throttling
        self.loadDataThrottled(period);
      });
    }

    /**
     * Load statistics data
     * @param {string} period - Period to load
     */
    loadData(period) {
      const self = this;

      // Cancel any pending request
      if (this.pendingRequest) {
        this.pendingRequest.abort();
      }

      // Check cache
      if (this.enableCache && this.cache[period]) {
        const cached = this.cache[period];
        const now = Date.now();

        if (now - cached.timestamp < this.cacheTTL) {
          this.updateUI(cached.data);
          return;
        }
      }

      // Create loader element if it doesn't exist (dynamically generated)
      let $loader = this.card.find('.statistics-loader');
      if ($loader.length === 0) {
        $loader = $('<div class="statistics-loader position-absolute top-50 start-50 translate-middle d-none">' + DashboardHelpers.getLoaderHTML() + '</div>');
        this.card.css('position', 'relative').append($loader);
      }

      // Show loader
      $('[data-counter]').css('opacity', '0.5');

      // Make AJAX request
      this.pendingRequest = DashboardHelpers.ajaxRequest({
        url: this.apiUrl,
        data: { period: period },
        $loader: $loader,
        $container: this.card,
        onSuccess: (data, response) => {
          // Cache the data
          if (this.enableCache) {
            this.cache[period] = {
              data: response.counters,
              timestamp: Date.now()
            };
          }

          this.updateUI(response.counters);
          $('[data-counter]').css('opacity', '1');
          this.pendingRequest = null;
        },
        onError: () => {
          $('[data-counter]').css('opacity', '1');
          this.pendingRequest = null;
        }
      });
    }

    /**
     * Update UI with new counter values
     * @param {Object} counters - Counter values
     */
    updateUI(counters) {
      this.updateCounter('sellers_sales', counters.sellers_sales, true);
      this.updateCounter('total_sellers', counters.total_sellers, false);
      this.updateCounter('total_products', counters.total_products, false);
      this.updateCounter('platform_total_revenues', counters.platform_total_revenues, true);
      this.updateCounter('payout_amount', counters.payout_amount, true);
      this.updateCounter('total_refunds', counters.total_refunds, false);
      this.updateCounter('buyer_fees_seller_fees', counters.buyer_fees + counters.seller_fees, true);
      this.updateCounter('buyer_tax_seller_tax', counters.buyer_tax + counters.seller_tax, true);
    }

    /**
     * Update individual counter
     * @param {string} key - Counter key
     * @param {number} value - Counter value
     * @param {boolean} isCompact - Whether to use compact format with currency
     */
    updateCounter(key, value, isCompact) {
      const $counter = $('[data-counter="' + key + '"]');
      if ($counter.length) {
        let formattedValue;

        if (isCompact) {
          // Format as compact with currency
          formattedValue = this.formatCompactNumber(value);
          if (this.currencyPosition === 1) {
            formattedValue = this.currencySymbol + formattedValue;
          } else {
            formattedValue = formattedValue + this.currencySymbol;
          }
        } else {
          // Format with thousand separators
          formattedValue = this.formatNumber(value);
        }

        $counter.text(formattedValue);
      }
    }

    /**
     * Format number in compact notation (K/M/B)
     * @param {number} num - Number to format
     * @returns {string} Formatted number
     */
    formatCompactNumber(num) {
      return DashboardHelpers.formatNumber(num);
    }

    /**
     * Format number with thousand separators
     * @param {number} num - Number to format
     * @returns {string} Formatted number
     */
    formatNumber(num) {
      return DashboardHelpers.formatWithCommas(num);
    }

    /**
     * Clear all cached data
     */
    clearCache() {
      this.cache = {};
    }

    /**
     * Refresh data by clearing cache and reloading
     * @param {Function} callback - Optional callback after refresh
     */
    refreshData(callback) {
      this.clearCache();
      this.loadData(this.currentPeriod);

      if (typeof callback === 'function') {
        callback();
      }
    }

    /**
     * Destroy instance and cleanup
     */
    destroy() {
      // Cancel any pending requests
      if (this.pendingRequest) {
        this.pendingRequest.abort();
        this.pendingRequest = null;
      }

      // Unbind events with namespace
      const ns = '.statsManager';
      $(document).off('click' + ns, '.stats-period-option');

      // Remove loader
      this.card.find('.statistics-loader').remove();

      // Clear cache
      this.clearCache();
    }
  };

  /**
   * combinedBarsManager
   *
   * @class combinedBarsManager
   * @param {Object} options - Configuration options
   * @param {string} options.apiUrl - API endpoint for revenue/expenses data (required)
   * @param {string} options.cardId - Card element ID (default: 'revenueExpenseCard')
   * @param {string} options.contentId - Content container ID (default: 'revenueExpensesContent')
   * @param {string} options.loaderId - Loader element ID (default: 'revenueExpenseLoader')
   * @param {string} options.revenueChartId - Profit chart canvas ID (default: 'profitChart')
   * @param {string} options.defaultPeriod - Default period (default: 'this_month')
   * @param {string} options.currencySymbol - Currency symbol (default: '$')
   * @param {number} options.currencyPosition - Currency position: 1=left, 2=right (default: 1)
   * @param {boolean} options.enableCache - Enable caching (default: true)
   * @param {number} options.cacheTTL - Cache time-to-live in ms (default: 300000 = 5 min)
   * @param {Object} options.translations - Translation strings
   */
  window.combinedBarsManager = class {
    constructor(options) {
      this.apiUrl = options.apiUrl;
      this.cardId = options.cardId || 'revenueExpenseCard';
      this.contentId = options.contentId || 'revenueExpensesContent';
      this.loaderId = options.loaderId || 'revenueExpenseLoader';
      this.revenueChartId = options.revenueChartId || 'revenueExpenseChart';
      this.defaultPeriod = options.defaultPeriod || 'this_month';
      this.currencySymbol = options.currencySymbol || '$';
      this.currencyPosition = options.currencyPosition || 1;

      this.currentPeriod = this.defaultPeriod;
      this.revenueChartInstance = null;
      this.expenseChartInstance = null;

      // Cache configuration
      this.enableCache = options.enableCache !== false;
      this.cacheTTL = options.cacheTTL || 5 * 60 * 1000; // 5 minutes
      this.cache = {};

      // Translations
      this.translations = $.extend({}, window.CommonTranslations, options.translations);

      // Elements
      this.card = $(`#${this.cardId}`);
      this.content = $(`#${this.contentId}`);
      this.loader = $(`#${this.loaderId}`);
      this.pendingRequest = null; // Track pending AJAX requests

      // Create throttled load method to prevent rapid API calls
      this.loadDataThrottled = DashboardHelpers.throttle(
        (period) => this.loadData(period),
        500
      );
    }

    /**
     * Initialize the manager
     */
    init() {
      // Ensure proper initial state
      DashboardHelpers.showLoader(this.loader, this.content);

      this.initializeCharts();
      this.loadData(this.currentPeriod);
      this.bindEvents();
    }

    /**
     * Initialize Chart.js charts
     */
    initializeCharts() {
      // Initialize Revenue Report Chart (combined earning and expense)
      const revenueCtx = document.getElementById('revenueExpenseChart');
      if (revenueCtx) {
        this.revenueChartInstance = new Chart(revenueCtx, {
          type: 'bar',
          data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
              {
                label: this.translations.revenue,
                data: [280, 200, 170, 190, 250, 280, 240, 260, 290, 140, 180, 220],
                backgroundColor: 'rgba(99, 102, 241, 0.75)',
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 8,
                stack: 'combined',
                base: 6
              },
              {
                label: this.translations.expense,
                data: [-120, -150, -180, -140, -90, -80, -60, -100, -90, -180, -130, -170],
                backgroundColor: 'rgba(245, 158, 11, 0.75)',
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 8,
                stack: 'combined',
                base: -6
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
              mode: 'index',
              intersect: false
            },
            layout: {
              padding: {
                top: 5,
                bottom: 5
              }
            },
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                enabled: true,
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: {
                  size: 13
                },
                bodyFont: {
                  size: 12
                },
                callbacks: {
                  label: (context) => {
                    let label = context.dataset.label || '';
                    if (label) {
                      label += ': ';
                    }
                    const value = Math.abs(context.parsed.y);
                    label += this.currencyPosition == 1
                      ? this.currencySymbol + value.toFixed(0)
                      : value.toFixed(0) + this.currencySymbol;
                    return ' ' + label;
                  }
                }
              }
            },
            scales: {
              x: {
                grid: {
                  display: false
                },
                border: {
                  display: false
                },
                ticks: {
                  font: {
                    size: 11
                  },
                  color: '#6b7280'
                }
              },
              y: {
                beginAtZero: true,
                min: function(context) {
                  const min = Math.min(...context.chart.data.datasets[1].data);
                  return min - Math.abs(min * 0.1); // Add 10% padding below
                },
                max: function(context) {
                  const max = Math.max(...context.chart.data.datasets[0].data);
                  return max + (max * 0.1); // Add 10% padding above
                },
                grid: {
                  color: '#f3f4f6',
                  drawBorder: false
                },
                border: {
                  display: false,
                  dash: [5, 5]
                },
                ticks: {
                  font: {
                    size: 11
                  },
                  color: '#6b7280',
                  padding: 8,
                  precision: 0,
                  stepSize: 50,
                  callback: function(value) {
                    return Math.abs(Math.round(value));
                  }
                }
              }
            }
          }
        });
      }
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
      const self = this;
      const ns = '.revenueManager';
      $('.revenue-period-option').off('click' + ns).on('click' + ns, function() {
        const period = $(this).data('period');
        $('.revenue-period-option').removeClass('active');
        $(this).addClass('active');
        self.loadDataThrottled(period);
      });
    }

    /**
     * Load data from API
     * @param {string} period - Period filter
     */
    loadData(period) {
      this.currentPeriod = period;

      // Check cache first
      if (this.enableCache && this.cache[period]) {
        const cached = this.cache[period];
        const now = Date.now();
        if (now - cached.timestamp < this.cacheTTL) {
          this.updateUI(cached.data, period);
          return;
        }
      }

      // Cancel any pending request
      if (this.pendingRequest) {
        this.pendingRequest.abort();
      }

      this.pendingRequest = DashboardHelpers.ajaxRequest({
        url: this.apiUrl,
        data: { period: period },
        $loader: this.loader,
        $content: this.content,
        $container: this.content,
        onSuccess: (data) => {
          // Cache the response
          if (this.enableCache) {
            this.cache[period] = {
              data: data,
              timestamp: Date.now()
            };
          }
          this.updateUI(data, period);
          this.pendingRequest = null;
        },
        onError: () => {
          this.pendingRequest = null;
        }
      });
    }

    /**
     * Update UI with new data
     * @param {Object} data - Response data
     * @param {string} period - Current period
     */
    updateUI(data, period) {
      if (!data) return;

      // Update subtitle based on period
      const subtitles = {
        'last_7_days': this.translations.last7Days,
        'last_28_days': this.translations.last28Days,
        'this_month': this.translations.thisMonth,
        'this_year': this.translations.thisYear,
        'lifetime': this.translations.lifetime
      };
      this.card.find('.card-subtitle').text('Comparison . ' + subtitles[period]);

      // Check if data is empty
      if (data.revenue === 0 && data.expense === 0) {
        // Hide loader and content first
        DashboardHelpers.hideLoader(this.loader, this.content);
        // Hide all content children
        this.content.children().addClass('d-none');
        // Show no data message in content area (not in loader)
        DashboardHelpers.showNoDataMessage(this.content);
        return;
      }

      // Remove no-data message if it exists
      this.content.find('.no-data-message').remove();
      // Show all content children
      this.content.children().removeClass('d-none');

      // Update revenue amount
      $('#revenueAmount').text(this.formatAmount(data.revenue));

      // Update revenue change badge
      this.updateBadge('#revenueBadge', '#revenueChange', data.revenue_change, 'revenue');

      // Update expense amount
      $('#expenseAmount').text(this.formatAmount(data.expense));

      // Update expense change badge (use 'expense' type for different color logic)
      this.updateBadge('#expenseBadge', '#expenseChange', data.expense_change, 'expense');

      // Update revenue report chart with realistic data
      if (this.revenueChartInstance) {
        // For lifetime with yearly breakdown, use actual data
        let chartData;
        if (period === 'lifetime' && data.yearly_breakdown && data.yearly_breakdown.length > 0) {
          chartData = {
            labels: data.yearly_breakdown.map(item => item.year),
            revenue: data.yearly_breakdown.map(item => parseFloat(item.revenue)),
            expense: data.yearly_breakdown.map(item => -Math.abs(parseFloat(item.expense)))
          };
        } else {
          // Generate realistic data based on period
          chartData = this.generateChartData(data.revenue, data.expense, period);
        }

        this.revenueChartInstance.data.labels = chartData.labels;
        this.revenueChartInstance.data.datasets[0].data = chartData.revenue;
        this.revenueChartInstance.data.datasets[1].data = chartData.expense;
        this.revenueChartInstance.update('none'); // Update without animation for faster response
      }

      // Show content
      DashboardHelpers.hideLoader(this.loader, this.content);
    }

    /**
     * Update badge with change percentage
     * @param {string} badgeSelector - Badge element selector
     * @param {string} changeSelector - Change text element selector
     * @param {number} changeValue - Percentage change value
     * @param {string} type - Type of badge: 'revenue' or 'expense'
     */
    updateBadge(badgeSelector, changeSelector, changeValue, type = 'revenue') {
      const $badge = $(badgeSelector);
      const $change = $(changeSelector);
      const isPositive = changeValue >= 0;

      $badge.removeClass('bg-success-subtle text-success bg-danger-subtle text-danger');

      // For expense, positive change is bad (red), negative is good (green)
      // For revenue, positive change is good (green), negative is bad (red)
      if (type === 'expense') {
        if (isPositive) {
          $badge.addClass('bg-danger-subtle text-danger');
          $badge.find('i').removeClass('bi-arrow-down').addClass('bi-arrow-up');
          $change.text('+' + Math.abs(changeValue) + '%');
        } else {
          $badge.addClass('bg-success-subtle text-success');
          $badge.find('i').removeClass('bi-arrow-up').addClass('bi-arrow-down');
          $change.text('-' + Math.abs(changeValue) + '%');
        }
      } else {
        // Default profit logic
        if (isPositive) {
          $badge.addClass('bg-success-subtle text-success');
          $badge.find('i').removeClass('bi-arrow-down').addClass('bi-arrow-up');
          $change.text('+' + Math.abs(changeValue) + '%');
        } else {
          $badge.addClass('bg-danger-subtle text-danger');
          $badge.find('i').removeClass('bi-arrow-up').addClass('bi-arrow-down');
          $change.text('-' + Math.abs(changeValue) + '%');
        }
      }
    }

    /**
     * Generate chart data for chart based on period
     * @param {number} totalRevenue - Total profit for period
     * @param {number} totalExpense - Total expenses for period
     * @param {string} period - Current period
     * @returns {Object} Labels, earnings and expenses arrays
     */
    generateChartData(totalRevenue, totalExpense, period) {
      let labels = [];
      let dataPoints = 0;
      let variance = [];

      // Determine labels and data points based on period
      switch(period) {
        case 'last_7_days':
          labels = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'];
          dataPoints = 7;
          variance = [0.9, 1.0, 1.1, 1.15, 1.2, 0.8, 0.85];
          break;

        case 'last_28_days':
          // Show every 4th day for 28 days
          labels = ['Day 4', 'Day 8', 'Day 12', 'Day 16', 'Day 20', 'Day 24', 'Day 28'];
          dataPoints = 7;
          variance = [0.85, 0.9, 1.0, 1.05, 1.1, 1.0, 0.95];
          break;

        case 'this_month':
          // Show dates for current month (30 days simplified to 10 points)
          labels = ['1', '3', '6', '9', '12', '15', '18', '21', '24', '27', '30'];
          dataPoints = 11;
          variance = [0.85, 0.9, 1.0, 1.05, 1.1, 1.15, 1.1, 1.0, 0.95, 0.9, 0.8];
          break;

        case 'this_year':
          labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
          dataPoints = 12;
          variance = [0.8, 0.9, 0.85, 1.0, 1.1, 1.15, 1.05, 1.1, 1.2, 0.75, 0.9, 1.0];
          break;

        case 'lifetime':
          // Show last 5 years for lifetime
          const currentYear = new Date().getFullYear();
          labels = [
            (currentYear - 4).toString(),
            (currentYear - 3).toString(),
            (currentYear - 2).toString(),
            (currentYear - 1).toString(),
            currentYear.toString()
          ];
          dataPoints = 5;
          variance = [0.7, 0.85, 0.95, 1.05, 1.15];
          break;
      }

      const revenue = [];
      const expense = [];
      const sumVariance = variance.reduce((a, b) => a + b, 0);

      for (let i = 0; i < dataPoints; i++) {
        // Distribute total amounts across data points with variance
        revenue.push(Math.round((totalRevenue / sumVariance) * variance[i]));
        expense.push(-Math.round((totalExpense / sumVariance) * variance[i])); // Negative for downward bars
      }

      return { labels, revenue, expense };
    }

    /**
     * Format amount with currency symbol
     * @param {number} amount - Amount to format
     * @returns {string} Formatted amount
     */
    formatAmount(amount) {
      return DashboardHelpers.formatValue(amount, this.currencySymbol, 'currency');
    }

    /**
     * Clear all cached data
     */
    clearCache() {
      this.cache = {};
    }

    /**
     * Refresh data by clearing cache and reloading
     * @param {Function} callback - Optional callback after refresh
     */
    refreshData(callback) {
      this.clearCache();
      this.loadData(this.currentPeriod);

      if (typeof callback === 'function') {
        callback();
      }
    }

    /**
     * Destroy instance and cleanup
     */
    destroy() {
      // Cancel any pending requests
      if (this.pendingRequest) {
        this.pendingRequest.abort();
        this.pendingRequest = null;
      }

      // Destroy chart
      if (this.revenueChartInstance) {
        this.revenueChartInstance.destroy();
        this.revenueChartInstance = null;
      }

      // Unbind events with namespace
      const ns = '.revenueManager';
      $('.revenue-period-option').off('click' + ns);

      // Clear cache
      this.clearCache();
    }
  };

  /**
   * TrafficSourceManager - Manages traffic source analytics with period filtering
   *
   * @class TrafficSourceManager
   * @param {Object} options - Configuration options
   * @param {string} options.apiUrl - Traffic source analytics endpoint (required)
   * @param {string} options.cardId - Card element ID (default: 'trafficSourceCard')
   * @param {string} options.contentId - Content element ID (default: 'trafficSourceContent')
   * @param {string} options.defaultPeriod - Default period (default: 'this_month')
   * @param {boolean} options.enableCache - Enable caching (default: true)
   * @param {number} options.cacheTTL - Cache time-to-live in ms (default: 300000 = 5 min)
   * @param {Object} options.translations - Translation strings
   */
  window.TrafficSourceManager = class {
    constructor(options) {
      this.apiUrl = options.apiUrl;
      this.cardId = options.cardId || 'trafficSourceCard';
      this.contentId = options.contentId || 'trafficSourceContent';
      this.defaultPeriod = options.defaultPeriod || 'this_month';
      this.currentPeriod = this.defaultPeriod;

      // Cache configuration
      this.enableCache = options.enableCache !== false;
      this.cacheTTL = options.cacheTTL || 5 * 60 * 1000; // 5 minutes
      this.cache = {};

      // Translations
      this.translations = $.extend({}, window.CommonTranslations, options.translations || {
        visitors: 'Visitors',
        today: 'Today'
      });

      // DOM elements
      this.$card = $(`#${this.cardId}`);
      this.$content = $(`#${this.contentId}`);
      this.$subtitle = this.$card.find('.card-subtitle');
      this.$loader = options.loaderId ? $(`#${options.loaderId}`) : this.$content;
      this.pendingRequest = null;

      // Create throttled load method to prevent rapid API calls
      this.loadDataThrottled = DashboardHelpers.throttle(
        (period) => this.loadData(period),
        500
      );
    }

    /**
     * Initialize the manager
     */
    init() {
      this.attachEventHandlers();
      // Initial data is already loaded from server, no need to fetch again
    }

    /**
     * Attach event handlers
     * @private
     */
    attachEventHandlers() {
      const self = this;

      $('.traffic-period-option').on('click', function(e) {
        e.preventDefault();
        const period = $(this).data('period');

        // Update active state
        $('.traffic-period-option').removeClass('active');
        $(this).addClass('active');

        // Load data for selected period with throttling
        self.loadDataThrottled(period);
      });
    }

    /**
     * Load traffic source data
     * @param {string} period - Period to load
     */
    loadData(period) {
      this.currentPeriod = period;

      // Check cache first
      if (this.enableCache && this.cache[period]) {
        const cached = this.cache[period];
        const now = Date.now();

        if (now - cached.timestamp < this.cacheTTL) {
          this.renderData(cached.data);
          return;
        }
      }

      // Cancel any pending request
      if (this.pendingRequest) {
        this.pendingRequest.abort();
      }

      // Fetch data
      this.pendingRequest = DashboardHelpers.ajaxRequest({
        url: this.apiUrl,
        data: { period: period },
        $loader: this.$loader,
        $content: this.$content,
        $container: this.$content,
        onSuccess: (data) => {
          // Cache the data
          if (this.enableCache) {
            this.cache[period] = {
              data: data,
              timestamp: Date.now()
            };
          }

          this.renderData(data);
          this.pendingRequest = null;
        },
        onError: () => {
          this.pendingRequest = null;
        }
      });
    }

    /**
     * Render traffic source data
     * @private
     * @param {Object} data - Data object containing total_visitors and sources
     */
    renderData(data) {
      const periodLabels = {
        'last_7_days': this.translations.last7Days,
        'last_28_days': this.translations.last28Days,
        'this_month': this.translations.thisMonth,
        'this_year': this.translations.thisYear,
        'lifetime': this.translations.lifetime
      };

      const periodLabel = periodLabels[this.currentPeriod] || 'This Month';

      // Parse total visitors to check if it's 0
      const totalVisitorsNum = parseInt(data.total_visitors.toString().replace(/,/g, '')) || 0;

      // Check if no data
      if (!data.sources || data.sources.length === 0 || totalVisitorsNum === 0) {
        // Hide loader first
        DashboardHelpers.hideLoader(this.$loader, this.$content);
        // Hide all content children
        this.$content.children().addClass('d-none');
        // Show no data message in content area
        DashboardHelpers.showNoDataMessage(this.$content);
        // Update subtitle to show 0 visitors
        this.$subtitle.text(`0 ${this.translations.visitors} · ${periodLabel}`);
        return;
      }

      // Update subtitle with total visitors and period
      this.$subtitle.text(`${data.total_visitors} ${this.translations.visitors} · ${periodLabel}`);

      // Remove no-data message if it exists
      this.$content.find('.no-data-message').remove();
      // Show all content children
      this.$content.children().removeClass('d-none');

      // Update existing source elements
      data.sources.forEach((source) => {
        const $sourceElement = this.$content.find(`[data-source="${source.name}"]`);
        if ($sourceElement.length) {
          // Update count
          $sourceElement.find('.source-count').text(source.formatted_count);

          // Update change indicator
          const $change = $sourceElement.find('.source-change');
          const $arrow = $sourceElement.find('.source-arrow');
          const $percentage = $sourceElement.find('.source-percentage');

          // Update classes
          $change.removeClass('text-success text-danger').addClass(source.is_positive ? 'text-success' : 'text-danger');

          // Update arrow icon
          $arrow.removeClass('bi-arrow-up bi-arrow-down').addClass(source.is_positive ? 'bi-arrow-up' : 'bi-arrow-down');

          // Update percentage text
          $percentage.text(`${source.is_positive ? '+' : ''}${source.percentage_change}%`);
        }
      });
    }

    /**
     * Clear all cached data
     */
    clearCache() {
      this.cache = {};
    }

    /**
     * Refresh data by clearing cache and reloading
     * @param {Function} callback - Optional callback after refresh
     */
    refreshData(callback) {
      this.clearCache();
      this.loadData(this.currentPeriod);

      if (typeof callback === 'function') {
        callback();
      }
    }

    /**
     * Destroy instance and cleanup
     */
    destroy() {
      // Cancel any pending requests
      if (this.pendingRequest) {
        this.pendingRequest.abort();
        this.pendingRequest = null;
      }

      // Unbind events
      $('.traffic-period-option').off('click');

      // Clear cache
      this.clearCache();
    }
  };

  /**
   * DonutChartManager - Generic donut chart manager for various analytics
   *
   * @class DonutChartManager
   * @param {Object} options - Configuration options
   * @param {string} options.apiUrl - API endpoint for chart data (required)
   * @param {string} options.cardId - Card element ID (required)
   * @param {string} options.canvasId - Canvas element ID (required)
   * @param {string} options.loaderId - Loader element ID (default: 'chartLoader')
   * @param {string} options.contentId - Content container ID (default: 'chartContent')
   * @param {string} options.percentageId - Percentage display element ID (default: 'chartPercentage')
   * @param {string} options.labelId - Label display element ID (default: 'chartLabel')
   * @param {string} options.defaultPeriod - Default period (default: 'last_7_days')
   * @param {boolean} options.enableCache - Enable caching (default: true)
   * @param {number} options.cacheTTL - Cache time-to-live in ms (default: 300000 = 5 min)
   * @param {Object} options.translations - Translation strings
   */
  window.DonutChartManager = class {
    constructor(options) {
      this.apiUrl = options.apiUrl;
      this.$card = $(`#${options.cardId}`);
      this.$loader = $(`#${options.loaderId || 'productStatusLoader'}`);
      this.$content = $(`#${options.contentId || 'productStatusContent'}`);
      this.$percentage = $(`#${options.percentageId || 'productStatusPercentage'}`);
      this.$label = $(`#${options.labelId || 'productStatusLabel'}`);
      this.canvasId = options.canvasId;
      this.currentPeriod = options.defaultPeriod || 'this_month';
      this.cutoutPercentage = options.cutoutPercentage || 75;
      this.chart = null;

      // Display mode: 'percentage' or 'total'
      this.displayMode = options.displayMode || 'percentage';

      // Currency configuration
      this.currencySymbol = options.currencySymbol || '';
      this.currencyPosition = options.currencyPosition || 1; // 1 = before, 2 = after

      // Show legend
      this.showLegend = options.showLegend || true;

      // Cache configuration
      this.enableCache = options.enableCache !== false;
      this.cacheTTL = options.cacheTTL || 5 * 60 * 1000; // 5 minutes
      this.cache = {};
      this.pendingRequest = null;

      // Translations
      this.translations = $.extend({}, window.CommonTranslations, options.translations || {
        segment1: 'Segment 1',
        segment2: 'Segment 2',
        segment3: 'Segment 3',
        segment4: 'Segment 4',
        title: 'Chart'
      });

      // Colors
      this.colors = options.colors || {
        segment1: '#10b981',
        segment2: '#f59e0b',
        segment3: '#ef4444',
        segment4: '#8b5cf6'
      };

      // Option class for click events (customizable for multiple instances)
      this.optionClass = options.optionClass || 'product-status-option';

      // Throttle the loadData method to prevent rapid API calls
      this.loadDataThrottled = DashboardHelpers.throttle(
        (period) => this.loadData(period),
        500
      );
    }

    /**
     * Initialize the manager
     */
    init() {
      this.attachEventListeners();
      this.loadData(this.currentPeriod);
    }

    /**
     * Attach event listeners
     */
    attachEventListeners() {
      $(`.${this.optionClass}`).on('click', (e) => {
        e.preventDefault();
        const period = $(e.currentTarget).data('period');
        this.updatePeriod(period);
      });
    }

    /**
     * Update period and reload data
     * @param {string} period - Period identifier
     */
    updatePeriod(period) {
      this.currentPeriod = period;
      $(`.${this.optionClass}`).removeClass('active');
      $(`.${this.optionClass}[data-period=\"${period}\"]`).addClass('active');

      // Update card subtitle
      const prefix = this.translations.subtitlePrefix || '';
      const subtitle = this.getPeriodLabel(period);

      this.$card.find('.card-subtitle').text(`${prefix} ${subtitle}`);
      this.loadDataThrottled(period);
    }

    /**
     * Get period label from translation
     * @param {string} period - Period identifier
     * @returns {string} Period label
     */
    getPeriodLabel(period) {
      const labels = {
        'last_7_days': this.translations.last7Days,
        'last_28_days': this.translations.last28Days,
        'this_month': this.translations.thisMonth,
        'this_year': this.translations.thisYear,
        'lifetime': this.translations.lifetime
      };
      return labels[period] || labels['last_7_days'];
    }

    /**
     * Load data from API or cache
     * @param {string} period - Period identifier
     */
    loadData(period) {
      // Check cache first
      if (this.enableCache && this.cache[period]) {
        const cached = this.cache[period];
        const now = Date.now();

        if (now - cached.timestamp < this.cacheTTL) {
          this.renderChart(cached.data);
          return;
        }
      }

      // Cancel any pending request
      if (this.pendingRequest) {
        this.pendingRequest.abort();
      }

      this.pendingRequest = DashboardHelpers.ajaxRequest({
        url: this.apiUrl,
        data: { period: period },
        $loader: this.$loader,
        $content: this.$content,
        $container: this.$content,
        onSuccess: (data) => {
          // Cache the data
          if (this.enableCache) {
            this.cache[period] = {
              data: data,
              timestamp: Date.now()
            };
          }

          this.renderChart(data);
          this.pendingRequest = null;
        }
      });
    }

    /**
     * Render donut chart
     * @param {Object} data - Chart data with segment1, segment2, segment3, segment4 properties
     */
    renderChart(data) {
      const total = data.segment1 + data.segment2 + data.segment3 + data.segment4;
      const hasData = total > 0;

      // Check if no data
      if (!hasData) {
        // Hide loader first
        DashboardHelpers.hideLoader(this.$loader, this.$content);
        // Hide all content children
        this.$content.children().addClass('d-none');
        // Show no data message in content area (not in loader)
        DashboardHelpers.showNoDataMessage(this.$content);
        return;
      }

      // Hide no-data message if it's showing
      this.$content.find('.no-data-message').remove();
      // Show all content children
      this.$content.children().removeClass('d-none');

      // Show content if hidden
      DashboardHelpers.hideLoader(this.$loader, this.$content);

      const ctx = document.getElementById(this.canvasId).getContext('2d');

      // Destroy existing chart
      if (this.chart) {
        this.chart.destroy();
      }

      const display = data.display || data.segment1 || 0;
      const percentage = total > 0 ? Math.round((display / total) * 100) : 0;

      // Update center text based on display mode
      if (this.displayMode === 'total') {
        this.$percentage.text(DashboardHelpers.formatNumber(total));
      } else {
        // Default: show percentage
        this.$percentage.text(percentage + '%');
        this.$label.text(this.translations.displayLabel || this.translations.segment1);
      }

      // Prepare labels, colors and data based on actual values
      const labels = [];
      const colors = [];
      const values = [];
      const actualValues = []; // Store actual values for tooltip

      if (data.segment1 > 0) {
        labels.push(this.translations.segment1);
        colors.push(this.colors.segment1);
        values.push(data.segment1);
        actualValues.push(data.segment1);
      }
      if (data.segment2 > 0) {
        labels.push(this.translations.segment2);
        colors.push(this.colors.segment2);
        values.push(data.segment2);
        actualValues.push(data.segment2);
      }
      if (data.segment3 > 0) {
        labels.push(this.translations.segment3);
        colors.push(this.colors.segment3);
        values.push(data.segment3);
        actualValues.push(data.segment3);
      }
      if (data.segment4 > 0 && this.translations.segment4) {
        labels.push(this.translations.segment4);
        colors.push(this.colors.segment4);
        values.push(data.segment4);
        actualValues.push(data.segment4);
      }

      const backgroundColor = colors;
      const displayData = values;

      this.chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: labels,
          datasets: [{
            data: displayData,
            backgroundColor: backgroundColor,
            borderWidth: 0,
            cutout: `${this.cutoutPercentage}%`
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: this.showLegend,
              position: 'bottom',
              labels: {
                usePointStyle: true,
                padding: 24,
                boxWidth: 6,
                font: {
                  size: 13
                }
              },
              align: 'center',
              maxHeight: 80
            },
            tooltip: {
              enabled: true,
              callbacks: {
                label: (context) => {
                  const label = context.label || '';
                  const value = actualValues[context.dataIndex] || 0;
                  const percentage = total > 0 ? Math.round((value / total) * 100) : 0;

                  // Format value with currency if configured
                  let formattedValue = value;
                  if (this.currencySymbol) {
                    formattedValue = this.currencyPosition === 1
                      ? `${this.currencySymbol}${value}`
                      : `${value}${this.currencySymbol}`;
                  }

                  return `  ${label}:  ${formattedValue} (${percentage}%)`;
                }
              }
            }
          }
        }
      });
    }

    /**
     * Clear all cached data
     */
    clearCache() {
      this.cache = {};
    }

    /**
     * Refresh data by clearing cache and reloading
     * @param {Function} callback - Optional callback after refresh
     */
    refreshData(callback) {
      this.clearCache();
      this.loadData(this.currentPeriod);

      if (typeof callback === 'function') {
        callback();
      }
    }

    /**
     * Destroy instance and cleanup
     */
    destroy() {
      // Cancel any pending requests
      if (this.pendingRequest) {
        this.pendingRequest.abort();
        this.pendingRequest = null;
      }

      // Destroy chart
      if (this.chart) {
        this.chart.destroy();
      }

      // Unbind events
      $(`.${this.optionClass}`).off('click');

      // Clear cache
      this.clearCache();
    }
  };

  /**
   * GeoChartManager - Manages geographic sales map visualization
   *
   * @class GeoChartManager
   * @param {Object} options - Configuration options
   * @param {string} options.apiUrl - API endpoint for geo data (required)
   * @param {string} options.containerId - Container element ID (default: 'geoChartContainer')
   * @param {boolean} options.enableCache - Enable caching (default: true)
   * @param {number} options.cacheTTL - Cache time-to-live in ms (default: 600000 = 10 min)
   * @param {Object} options.translations - Translation strings
   */
  window.GeoChartManager = class {
    constructor(options) {
      this.apiUrl = options.apiUrl;
      this.mapsApiKey = options.mapsApiKey || '';
      this.containerId = options.containerId || 'geoChartContainer';
      this.$container = $(`#${this.containerId}`);
      this.chart = null;
      this.googleChartsLoaded = false;
      this.loaderId = options.loaderId || 'geoChartLoader';
      this.loader = $(`#${this.loaderId}`);

      // Period filtering
      this.cardId = options.cardId || null;
      this.$card = this.cardId ? $(`#${this.cardId}`) : null;
      this.currentPeriod = options.defaultPeriod || 'this_month';

      // Cache configuration
      this.enableCache = options.enableCache !== false;
      this.cacheTTL = options.cacheTTL || 10 * 60 * 1000; // 10 minutes
      this.cachedData = {};  // Cache per period
      this.cacheTimestamp = {};  // Timestamp per period
      this.pendingRequest = null;

      // Translations
      this.translations = $.extend({}, window.CommonTranslations, options.translations || {
        sales: 'Sales'
      });

      // Throttle the loadData method to prevent rapid API calls
      this.loadDataThrottled = DashboardHelpers.throttle(
        (period) => this.loadData(period),
        500
      );
    }

    /**
     * Initialize the manager and load Google Charts library
     */
    init() {
      this._loadGoogleCharts(() => {
        this.attachEventHandlers();
        this.loadData(this.currentPeriod);
      });
    }

    /**
     * Load Google Charts library
     * @private
     * @param {Function} callback - Callback after library loads
     */
    _loadGoogleCharts(callback) {
      // Check if google object exists
      if (typeof google === 'undefined') {
        this.$container.html(`
          <div class="alert alert-danger m-3">
            Google Charts library is missing.
          </div>
        `);
        return;
      }

      // Check if already fully loaded
      if (typeof google.visualization !== 'undefined' && typeof google.visualization.GeoChart !== 'undefined') {
        this.googleChartsLoaded = true;
        if (callback) callback();
        return;
      }

      // Load the geochart package
      try {
        google.charts.load('current', {
          'packages': ['geochart'],
          'mapsApiKey': this.mapsApiKey // Optional: Add your Google Maps API key if needed
        });

        google.charts.setOnLoadCallback(() => {
          this.googleChartsLoaded = true;
          if (callback) callback();
        });
      } catch (error) {
        DashboardHelpers.showEmptyState(this.$container, {
          title: 'Error',
          message: this.translations.loadFailed,
          icon: 'bi-exclamation-triangle'
        });
      }
    }

    /**
     * Check if cached data is valid
     * @private
     * @param {string} period - The period to check cache for
     * @returns {boolean}
     */
    _isCacheValid(period) {
      if (!this.enableCache || !this.cachedData[period] || !this.cacheTimestamp[period]) {
        return false;
      }

      const now = Date.now();
      const cacheAge = now - this.cacheTimestamp[period];
      return cacheAge < this.cacheTTL;
    }

    /**
     * Load geographic data from API or cache
     * @param {string} period - The period to load data for (last_7_days, this_month, this_year)
     */
    loadData(period = this.currentPeriod) {
      // Check cache first
      if (this._isCacheValid(period)) {
        this.renderChart(this.cachedData[period]);
        return;
      }

      // Cancel any pending request
      if (this.pendingRequest) {
        this.pendingRequest.abort();
      }

      this.pendingRequest = DashboardHelpers.ajaxRequest({
        url: this.apiUrl,
        data: { period: period },
        $loader: this.loader,
        $content: this.$container,
        $container: this.$container,
        onSuccess: (data) => {
          // Check if data has actual countries (more than just header row)
          const hasData = data.length > 1;

          if (hasData) {
            // Cache the data
            if (this.enableCache) {
              this.cachedData[period] = data;
              this.cacheTimestamp[period] = Date.now();
            }

            this.renderChart(data);
          } else {
            // Clear any existing chart from container
            this.$container.empty();
            // Hide loader and show standard no data message
            DashboardHelpers.hideLoader(this.loader, this.$container);
            DashboardHelpers.showNoDataMessage(this.$container);
          }
          this.pendingRequest = null;
        },
        onError: () => {
          this.pendingRequest = null;
        }
      });
    }

    /**
     * Render Google GeoChart
     * @private
     * @param {Array} chartData - Array of arrays: [['Country', 'Sales'], ['US', 100], ...]
     */
    renderChart(chartData) {
      // Double check Google Charts is loaded
      if (!this.googleChartsLoaded || typeof google === 'undefined' || typeof google.visualization === 'undefined') {
        return;
      }

      // Create data table
      const data = google.visualization.arrayToDataTable(chartData);
      const containerWidth = this.$container.width();
      const containerHeight = this.$container.height();

      // Chart options
      const options = {
        width: containerWidth,
        height: containerHeight,
        region: 'world',
        displayMode: 'regions',
        resolution: 'countries',
        colorAxis: {
            colors: ['#e0e0ff', '#b3b3f5', '#8080eb', '#5555e5', config.colors?.primary_color || '#3b82f6'],
            minValue: 0
        },
        backgroundColor: {
            fill: 'transparent',
            stroke: 'transparent',
            strokeWidth: 0
        },
        datalessRegionColor: '#f9fafb',
        defaultColor: '#f3f4f6',
        legend: {
            textStyle: {
                color: '#6b7280',
                fontSize: 13,
                fontName: 'Inter, system-ui, sans-serif'
            },
            numberFormat: 'short'
        },
        tooltip: {
            textStyle: {
                color: '#1f2937',
                fontSize: 13
            },
            showColorCode: true,
            trigger: 'focus'
        },
        magnifyingGlass: {
            enable: true,
            zoomFactor: 5.0
        },
        keepAspectRatio: true,
        enableRegionInteractivity: true
      };

      // Clear container and render chart
      this.$container.empty();

      // Create chart
      this.chart = new google.visualization.GeoChart(this.$container[0]);
      this.chart.draw(data, options);

      // Handle window resize
      $(window).off('resize.geochart').on('resize.geochart', () => {
        if (this.chart && chartData) {
          this.renderChart(chartData);
        }
      });
    }

    /**
     * Attach event handlers for period filtering
     * @private
     */
    attachEventHandlers() {
      if (!this.$card) return;

      const self = this;

      // Period filter click handler
      this.$card.find('.geo-period-option').off('click').on('click', function () {
        const $option = $(this);
        const period = $option.data('period');

        // Update active state
        self.$card.find('.geo-period-option').removeClass('active');
        $option.addClass('active');

        const now = new Date();
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                          'July', 'August', 'September', 'October', 'November', 'December'];
        const currentMonth = monthNames[now.getMonth()];
        const currentYear = now.getFullYear();

        // Update current period
        self.currentPeriod = period;

        // Update card subtitle
        const periodLabels = {
          'last_7_days': self.translations.last7Days,
          'last_28_days': self.translations.last28Days,
          'this_month': `${self.translations.thisMonth} (${currentMonth})`,
          'this_year': `${self.translations.thisYear} (${currentYear})`,
          'lifetime': self.translations.lifetime
        };
        self.$card.find('.card-subtitle').text(periodLabels[period] || '');

        // Load data for the new period
        self.loadDataThrottled(period);
      });
    }

    /**
     * Clear cached data
     * @param {string} period - Optional period to clear. If not provided, clears all
     */
    clearCache(period = null) {
      if (period) {
        delete this.cachedData[period];
        delete this.cacheTimestamp[period];
      } else {
        this.cachedData = {};
        this.cacheTimestamp = {};
      }
    }

    /**
     * Refresh data by clearing cache and reloading
     * @param {string} period - Optional period to refresh. If not provided, refreshes current period
     * @param {Function} callback - Optional callback after refresh
     */
    refreshData(period = null, callback = null) {
      const targetPeriod = period || this.currentPeriod;
      this.clearCache(targetPeriod);
      this.loadData(targetPeriod);

      if (typeof callback === 'function') {
        callback();
      }
    }

    /**
     * Destroy instance and cleanup
     */
    destroy() {
      // Cancel any pending requests
      if (this.pendingRequest) {
        this.pendingRequest.abort();
        this.pendingRequest = null;
      }

      // Unbind resize event
      $(window).off('resize.geochart');

      // Clear container
      this.$container.empty();

      // Clear chart reference
      this.chart = null;

      // Clear cache
      this.clearCache();
    }
  };

  /**
   * Dual Stats Manager
   * Generic manager for loading and displaying dual comparison statistics
   * Supports multiple data types:
   * - Product Issues: reported and restricted products
   * - User Verification: email verified and ID verified users
   * - Any cascading percentage comparison (A vs B, where B% is calculated from A)
   *
   * @class DualStatsManager
   * @param {Object} options - Configuration options
   * @param {string} options.apiUrl - API endpoint URL (required)
   * @param {string} options.loaderId - Loader element ID (default: 'productIssuesLoader')
   * @param {string} options.contentId - Content element ID (default: 'productIssuesContent')
   * @param {boolean} options.enableCache - Enable caching (default: true)
   * @param {number} options.cacheTTL - Cache time-to-live in ms (default: 300000 = 5 min)
   * @param {Object} options.translations - Translation strings
   */
  window.DualStatsManager = class {
    constructor(options) {
      this.options = $.extend({
        apiUrl: options.apiUrl,
        loaderId: options.loaderId || 'productIssuesLoader',
        contentId: options.contentId || 'productIssuesContent',
        enableCache: true,
        cacheTTL: 5 * 60 * 1000 // 5 minutes default
      }, options);

      // Merge translations with CommonTranslations
      this.options.translations = $.extend({}, window.CommonTranslations, this.options.translations || {});

      this.$loader = $('#' + this.options.loaderId);
      this.$content = $('#' + this.options.contentId);

      // Initialize cache
      this.cache = null;
      this.cacheTimestamp = null;
      this.enableCache = this.options.enableCache !== false;
      this.cacheTTL = this.options.cacheTTL;
      this.pendingRequest = null;

      // Throttle the loadData method to prevent rapid API calls
      this.loadDataThrottled = DashboardHelpers.throttle(
        () => this.loadData(),
        500
      );

      this.init();
    }

    init() {
      // Load initial data
      this.loadData();
    }

    loadData() {
      // Check cache first
      if (this.enableCache && this.cache) {
        const now = Date.now();
        const age = now - this.cacheTimestamp;

        if (age <= this.cacheTTL) {
          this.updateUI(this.cache);
          return;
        }
      }

      // Cancel any pending request
      if (this.pendingRequest) {
        this.pendingRequest.abort();
      }

      this.pendingRequest = DashboardHelpers.ajaxRequest({
        url: this.options.apiUrl,
        $loader: this.$loader,
        $content: this.$content,
        $container: this.$content,
        onSuccess: (data) => {
          // Cache the response
          if (this.enableCache) {
            this.cache = data;
            this.cacheTimestamp = Date.now();
          }
          this.updateUI(data);
          this.pendingRequest = null;
        },
        onError: () => {
          this.pendingRequest = null;
        }
      });
    }

    updateUI(data) {
      // Check if this is product issues data or user verification data
      if (data.reported && data.restricted) {
        // Product Issues data
        const reportedData = data.reported;
        const restrictedData = data.restricted;

        // Check if no data available
        if (reportedData.total === 0 && restrictedData.total === 0) {
          DashboardHelpers.showNoDataMessage(this.$loader, null, 'bi-inbox', this.$loader, this.$content);
          return;
        }

        // Hide no data message if it was showing
        DashboardHelpers.hideNoDataMessage(this.$loader.parent(), this.$loader);

        // Update reported products
        $('#reportedProductsPercentage').text(reportedData.percentage + '%');
        $('#reportedProductsCount').text(reportedData.total);

        // Update restricted products
        $('#restrictedProductsPercentage').text(restrictedData.percentage + '%');
        $('#restrictedProductsCount').text(restrictedData.total);

        // Update progress bar (restricted percentage of reported)
        $('#productIssuesProgressBar').css('width', restrictedData.percentage + '%');
      } else if (data.email_verified && data.id_verified) {
        // User Verification data
        const emailVerifiedData = data.email_verified;
        const idVerifiedData = data.id_verified;

        // Check if no data available
        if (emailVerifiedData.total === 0 && idVerifiedData.total === 0) {
          DashboardHelpers.showNoDataMessage(this.$loader, null, 'bi-inbox', this.$loader, this.$content);
          return;
        }

        // Hide no data message if it was showing
        DashboardHelpers.hideNoDataMessage(this.$loader.parent(), this.$loader);

        // Update email verified
        $('#emailVerifiedPercentage').text(emailVerifiedData.percentage + '%');
        $('#emailVerifiedCount').text(emailVerifiedData.total);

        // Update ID verified
        $('#idVerifiedPercentage').text(idVerifiedData.percentage + '%');
        $('#idVerifiedCount').text(idVerifiedData.total);

        // Update progress bar (ID verified percentage of email verified)
        $('#userVerificationProgressBar').css('width', idVerifiedData.percentage + '%');
      }

      // Show content
      DashboardHelpers.hideLoader(this.$loader, this.$content);
    }

    /**
     * Clear cache
     */
    clearCache() {
      this.cache = null;
      this.cacheTimestamp = null;
    }

    /**
     * Get cache statistics
     * @returns {Object}
     */
    getCacheStats() {
      return {
        enabled: this.enableCache,
        ttl: this.cacheTTL,
        cached: this.cache !== null,
        age: this.cacheTimestamp ? Date.now() - this.cacheTimestamp : null
      };
    }

    /**
     * Refresh data by clearing cache and reloading
     */
    refresh() {
      this.clearCache();
      this.loadData();
    }

    destroy() {
      // Cancel any pending requests
      if (this.pendingRequest) {
        this.pendingRequest.abort();
        this.pendingRequest = null;
      }

      // Clear cache
      this.clearCache();
    }
  };

  /**
   * DashboardPrintManager - Manages dashboard printing functionality
   *
   * @class DashboardPrintManager
   * @param {Object} options - Configuration options
   * @param {string} options.printButtonId - Print button element ID (default: 'printDashboard')
   * @param {string} options.headerRowId - Header row element ID (default: 'printHeaderRow')
   * @param {string} options.logoUrl - Logo URL (optional)
   * @param {string} options.siteName - Site name (required)
   * @param {string} options.assetBaseUrl - Base URL for assets (default: '')
   */
  window.DashboardPrintManager = class {
    constructor(options) {
      this.options = $.extend({
        printButtonId: 'printDashboard',
        headerRowId: 'printHeaderRow',
        logoUrl: '',
        siteName: 'EasyMarket',
        assetBaseUrl: ''
      }, options);

      this.$printButton = $('#' + this.options.printButtonId);
      this.$headerRow = $('#' + this.options.headerRowId);
    }

    /**
     * Initialize print manager
     */
    init() {
      this._injectPrintStyles();
      this._attachEventHandlers();
    }

    /**
     * Inject print-specific styles
     * @private
     */
    _injectPrintStyles() {
      if ($('#dashboardPrintStyles').length) return;

      const styles = `
        <style id="dashboardPrintStyles">
          /* Hide print header on screen */
          .dashboard-print-header-hidden {
            display: none !important;
          }

          @media print {
            /* Hide site header, navbar, and unnecessary elements */
            .ezydev-sidebar, .ezydev-header, .page-header,
            .app-header, .app-navbar,
            .btn, button:not(.nav-link), .dropdown-toggle,
            .dashboard-banner-swiper-pagi,
            .alert, .swiper-pagination-congrats,
            .modal, .tooltip,
            .offcanvas, .toast,
            #toggleUserCompare, #toggleSalesCompare,
            #userNavigationButtons, #salesNavigationButtons,
            .card-header .dropdown,
            .nav-tabs, .tab-pane:not(.active),
            .col-lg-4:has(.dashboard-banner-swiper), .card:has(.dashboard-notes-swiper)  {
              display: none !important;
            }

            /* Show main content */
            .ezydev-main-content, .main-content,
            .codebay-content-wrapper {
              display: block !important;
              margin: 0 !important;
              padding: 0 !important;
            }

            /* White background */
            body, html, .main-content, .container-fluid, .card {
              background: white !important;
              background-color: white !important;
            }

            /* Full width content */
            body, .main-content {
              margin: 0 !important;
              padding: 15px !important;
              max-width: 100% !important;
              width: 100% !important;
            }

            /* Card styling for print */
            .card {
              border: none !important;
              box-shadow: none !important;
              page-break-inside: avoid;
              margin-bottom: 15px !important;
              background: white !important;
            }

            .card-header {
              display: flex !important;
              flex-direction: column !important;
              background: #f8f9fa !important;
              border-bottom: 2px solid #667eea !important;
              padding: 10px 15px !important;
              -webkit-print-color-adjust: exact;
              print-color-adjust: exact;
            }

            .card-header h5, .card-header h6,
            .card-header .card-title {
              display: block !important;
              margin: 0 !important;
              margin-bottom: 3px !important;
              font-weight: 600 !important;
              color: #333 !important;
            }

            .card-header .text-muted,
            .card-header small {
              display: block !important;
              font-size: 0.85em !important;
              color: #666 !important;
              margin-top: 2px !important;
            }

            .card-body {
              padding: 15px !important;
              background: white !important;
            }

            canvas {
              max-width: 100% !important;
              height: auto !important;
            }

            /* Row and column adjustments */
            .row {
              page-break-inside: avoid;
              margin-bottom: 15px !important;
            }

            /* Chart containers */
            .chart-wrapper {
              max-height: 300px !important;
              page-break-inside: avoid;
              position: relative !important;
            }

            /* Fix geochart container */
            #geoChartContainer {
              height: 360px !important;
              width: 100% !important;
              max-height: 360px !important;
              position: relative !important;
              page-break-inside: avoid;
            }

            #geoChartCard .chart-wrapper {
              height: 360px !important;
              max-height: 360px !important;
            }

            /* Tables */
            table {
              page-break-inside: avoid;
              font-size: 10px !important;
            }

            table thead {
              background: #667eea !important;
              color: white !important;
              -webkit-print-color-adjust: exact;
              print-color-adjust: exact;
            }

            /* Statistics metrics */
            .card-icon {
              -webkit-print-color-adjust: exact;
              print-color-adjust: exact;
            }

            /* Ensure colors print */
            * {
              -webkit-print-color-adjust: exact;
              print-color-adjust: exact;
            }

            /* Page setup */
            @page {
              size: A4 landscape;
              margin: 1cm;
            }

            /* Remove custom page header */
            body::before {
              display: none !important;
            }

            /* Print header styling */
            .dashboard-print-header {
              display: block !important;
              visibility: visible !important;
              text-align: center;
              padding: 20px;
              margin-bottom: 30px;
              background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
              color: white;
              -webkit-print-color-adjust: exact;
              print-color-adjust: exact;
              border-radius: 4px;
            }

            .dashboard-print-header img {
              max-height: 50px;
              max-width: 200px;
              margin-bottom: 10px;
              display: inline-block;
            }

            .dashboard-print-header h2 {
              margin: 0 0 10px 0;
              font-size: 24px;
              font-weight: bold;
            }

            .dashboard-print-header .header-text {
              font-size: 14px !important;
              line-height: 1.6 !important;
              font-weight: bold;
              margin-top: 10px;
            }

            .progress {
              -webkit-print-color-adjust: exact;
              print-color-adjust: exact;
            }

            body {
              font-size: 11px !important;
            }

            h1, h2, h3, h4, h5, h6 {
              page-break-after: avoid;
            }
          }
        </style>
      `;

      $('head').append(styles);
    }

    /**
     * Attach event handlers
     * @private
     */
    _attachEventHandlers() {
      const self = this;

      this.$printButton.on('click', function(e) {
        e.preventDefault();
        self.print();
      });
    }

    /**
     * Trigger print dialog
     */
    print() {
      // Build print header
      const currentPeriod = $('.stats-period-option.active').data('period') || 'lifetime';
      let periodText = $(`.stats-period-option[data-period="${currentPeriod}"]`).text().trim();

      // Format period text with actual month/year names
      const now = new Date();
      if (currentPeriod === 'this_month') {
        periodText = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
      } else if (currentPeriod === 'this_year') {
        periodText = now.getFullYear().toString();
      }

      const printDate = now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });

      // Remove existing print header if any
      $('.dashboard-print-header').remove();

      // Build header HTML
      // Properly construct logo URL - check if logoUrl already includes base path
      let logoSrc = this.options.logoUrl;
      if (logoSrc && !logoSrc.startsWith('http') && !logoSrc.startsWith('/')) {
        logoSrc = this.options.assetBaseUrl + logoSrc;
      }

      // Build header with logo or site name
      const logoHtml = logoSrc
        ? `<img src="${logoSrc}" alt="${this.options.siteName}" />`
        : `<h2>${this.options.siteName}</h2>`;

      const headerHTML = `
        <div class="dashboard-print-header dashboard-print-header-hidden">
          ${logoHtml}
          <div class="header-text">
            📊 Admin Dashboard Report<br>
            Period: ${periodText}<br>
            Generated: ${printDate}
          </div>
        </div>
      `;

      // Inject header before the main row
      this.$headerRow.before(headerHTML);

      // Handle after print event to remove header
      const afterPrint = () => {
        $('.dashboard-print-header').remove();
        window.removeEventListener('afterprint', afterPrint);
      };
      window.addEventListener('afterprint', afterPrint);

      // If logo exists, preload it before printing
      if (logoSrc) {
        const img = new Image();
        img.onload = () => {
          // Image loaded, now print
          window.print();
        };
        img.onerror = () => {
          // Image failed, print anyway
          window.print();
        };
        img.src = logoSrc;
      } else {
        // No logo, print immediately
        window.print();
      }
    }

    /**
     * Destroy instance and cleanup
     */
    destroy() {
      this.$printButton.off('click');
      $('#dashboardPrintStyles').remove();
      $('.dashboard-print-header').remove();
    }
  };

})(jQuery);
