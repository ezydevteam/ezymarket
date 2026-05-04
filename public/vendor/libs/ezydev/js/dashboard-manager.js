/**
 * Dashboard Analytics Manager
 * Reusable class for analytics with Chart.js integration
 * Handles both single-year and multi-year comparison charts with caching
 *
 * @version 1.0.0
 * @author EzyDev
 * @requires jQuery, Chart.js, ChartDataLabels Plugin
 *
 * @dependencies
 * <script src="{{ asset('vendor/libs/chartjs/chart.min.js') }}"></script>
 * <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
 * <script src="{{ asset_with_version('vendor/libs/ezydev/js/chart-manager.js') }}"></script>
 * <script src="{{ asset_with_version('vendor/libs/ezydev/js/dashboard-manager.js') }}"></script>
 */

(function($) {
  'use strict';

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
    /**
     * Generate unique ID suffix
     * @private
     * @static
     */
    static _generateUniqueId() {
      return `_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

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

      this.translations = options.translations || {};
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
        console.error(`Canvas #${options.canvasId} not found`);
        return;
      }

      if (typeof Chart === 'undefined') {
        return;
      }
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
      // Unbind existing events first to prevent duplicates
      $(document).off('click', this.selectors.analyticsTab);
      $(document).off('click', this.selectors.compareTab);
      $(document).off('click', this.selectors.prevYearBtn);
      $(document).off('click', this.selectors.nextYearBtn);
      $(document).off('click', this.selectors.toggleCompareBtn);

      // Use class-based selectors only (avoid duplicate event binding)
      $(document).on('click', this.selectors.analyticsTab, (e) => {
        e.preventDefault();
        const $target = $(e.currentTarget);
        this._handleTabClick($target, false);
      });

      $(document).on('click', this.selectors.compareTab, (e) => {
        e.preventDefault();
        const $target = $(e.currentTarget);
        this._handleTabClick($target, true);
      });

      // Year/Period navigation
      $(document).on('click', this.selectors.prevYearBtn, () => {
        if (this.isPeriodBased) {
          this.periodOffset++;
          this._updatePeriodButtons();
          this.loadPeriodAnalytics(this.currentType, this.periodOffset);
        } else {
          this.currentYear--;
          this._updateYearButtons();
          this.loadAnalytics(this.currentType, this.currentYear);
        }
      });

      $(document).on('click', this.selectors.nextYearBtn, () => {
        if (this.isPeriodBased) {
          if (this.periodOffset > 0) {
            this.periodOffset--;
            this._updatePeriodButtons();
            this.loadPeriodAnalytics(this.currentType, this.periodOffset);
          }
        } else {
          this.currentYear++;
          this._updateYearButtons();
          this.loadAnalytics(this.currentType, this.currentYear);
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

      if (isComparison) {
        if (this.isPeriodBased) {
          this.loadPeriodComparison(this.currentType);
        } else {
          this.loadComparison(this.currentType);
        }
      } else {
        if (this.isPeriodBased) {
          this.periodOffset = 0; // Reset offset when switching tabs
          this.maxOffset = undefined; // Reset boundary for new period type
          this.loadPeriodAnalytics(this.currentType, this.periodOffset);
        } else {
          this.loadAnalytics(this.currentType, this.currentYear);
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

        if (this.isPeriodBased) {
          this.loadPeriodComparison(this.currentType);
        } else {
          this.loadComparison(this.currentType);
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

        if (this.isPeriodBased) {
          this.periodOffset = 0;
          this.maxOffset = undefined; // Reset boundary when toggling back from compare
          this.loadPeriodAnalytics(this.currentType, this.periodOffset);
        } else {
          this.loadAnalytics(this.currentType, this.currentYear);
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

      this.loader.removeClass('d-none');

      $.ajax({
        url: this.analyticsUrl,
        type: 'GET',
        data: { type, offset },
        dataType: 'json',
        success: (response) => {
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
        error: (xhr) => {
          this._handleAjaxError(xhr);
        },
        complete: () => {
          this.loader.addClass('d-none');
        }
      });
    }

    /**
     * Load single year analytics
     * @param {string} type - Analytics type (revenue, earnings, members)
     * @param {number} year - Year to load
     */
    loadAnalytics(type, year) {
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

      this.loader.removeClass('d-none');

      $.ajax({
        url: this.analyticsUrl,
        type: 'GET',
        data: { type, year },
        dataType: 'json',
        success: (response) => {
          if (!response.success) {
            this._showError(response.message || this.translations.loadFailed || 'Failed to load analytics');
            return;
          }

          // Cache the response
          if (this.enableCache) {
            const cacheKey = `${type}_${year}`;
            this._setCachedData('analytics', cacheKey, response);
          }

          this._renderBarChart(response, type);
          this.yearDisplay.text(response.year);
          this._updateSubtitle();
        },
        error: (xhr) => {
          this._handleAjaxError(xhr);
        },
        complete: () => {
          this.loader.addClass('d-none');
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

      this.loader.removeClass('d-none');

      $.ajax({
        url: this.comparisonUrl,
        type: 'GET',
        data: { type },
        dataType: 'json',
        success: (response) => {
          // Cache the response
          if (this.enableCache) {
            const cacheKey = `compare_${type}`;
            this._setCachedData('comparison', cacheKey, response);
          }

          this._renderLineChart(response, type);
          this._updateSubtitle();
        },
        error: (xhr) => {
          this._handleAjaxError(xhr);
        },
        complete: () => {
          this.loader.addClass('d-none');
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

      // Check cache first
      if (this.enableCache) {
        const cached = this._getCachedData('comparison', type);
        if (cached) {
          this._renderLineChart(cached, type);
          this._updateSubtitle();
          return;
        }
      }

      this.loader.removeClass('d-none');

      $.ajax({
        url: this.comparisonUrl,
        type: 'GET',
        data: { type },
        dataType: 'json',
        success: (response) => {
          if (!response.success) {
            this._showError(response.message || this.translations.loadFailed || 'Failed to load comparison');
            return;
          }

          // Cache the response
          if (this.enableCache) {
            this._setCachedData('comparison', type, response);
          }

          this._renderLineChart(response, type);
          this._updateSubtitle();
        },
        error: (xhr) => {
          this._handleAjaxError(xhr);
        },
        complete: () => {
          this.loader.addClass('d-none');
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

      // Hide no-data message if showing
      this._hideNoDataMessage();

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
                : this._hexToRgba(chartColor, 0.3);
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

      // Hide no-data message if showing
      this._hideNoDataMessage();

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
                : this._hexToRgba(chartColor, 0.3);
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

      // Hide no-data message if showing
      this._hideNoDataMessage();

      const datasets = response.datasets.map((dataset, index) => ({
        label: dataset.label,
        data: dataset.data,
        borderColor: colors[index],
        backgroundColor: this._hexToRgba(colors[index], 0.1),
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
                return label;
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
     * Convert hex color to rgba
     * @private
     */
    _hexToRgba(hex, alpha = 1) {
      const r = parseInt(hex.slice(1, 3), 16);
      const g = parseInt(hex.slice(3, 5), 16);
      const b = parseInt(hex.slice(5, 7), 16);
      return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    /**
     * Format number with K/M/B suffix
     * @private
     */
    _formatNumber(value) {
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
    }

    /**
     * Format value based on dataFormat setting
     * @private
     */
    _formatValue(value) {
      const formatted = this._formatNumber(value);
      return this.dataFormat === 'number' ? formatted : this.currencySymbol + formatted;
    }

    /**
     * Show error message
     * @private
     */
    _showError(message) {
      if (typeof toastr !== 'undefined') {
        toastr.error(message);
      }
    }

    /**
     * Handle AJAX error
     * @private
     */
    _handleAjaxError(xhr) {
      let errorMessage = this.translations.loadFailed || 'Failed to load data';
      if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
      }
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

      // Remove existing no-data message if any
      canvasParent.find('.no-data-message').remove();

      // Hide canvas
      this.canvas.addClass('d-none');

      // Create and show no-data message
      const noDataHtml = `
        <div class="no-data-message text-center text-muted py-5">
          <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
          <p class="mb-0">No data available</p>
        </div>
      `;
      canvasParent.append(noDataHtml);
    }

    /**
     * Hide "No data found" message and show canvas
     * @private
     */
    _hideNoDataMessage() {
      const canvasParent = this.canvas.parent();
      canvasParent.find('.no-data-message').remove();
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
        week: this.translations.week || 'Weekly',
        month: this.translations.month || 'Monthly',
        year: this.translations.year || 'Yearly'
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
      // Unbind all events
      if (this.selectors) {
        $(document).off('click', this.selectors.analyticsTab);
        $(document).off('click', this.selectors.compareTab);
        $(document).off('click', this.selectors.prevYearBtn);
        $(document).off('click', this.selectors.nextYearBtn);
        $(document).off('click', this.selectors.toggleCompareBtn);
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
   * @param {string} options.defaultPeriod - Default period type (default: 'week')
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
      this.currentPeriod = options.defaultPeriod || 'week';
      this.currencySymbol = options.currencySymbol || '$';

      // Cache configuration
      this.enableCache = options.enableCache !== false;
      this.cacheTTL = options.cacheTTL || 5 * 60 * 1000; // 5 minutes default
      this.cache = {}; // Format: { 'period': { data, timestamp } }

      // Translations
      this.translations = options.translations || {
        weeklyOverview: 'Weekly Sales Overview',
        monthlyOverview: 'Monthly Sales Overview',
        yearlyOverview: 'Yearly Sales Overview',
        noData: 'No data available',
        loadFailed: 'Failed to load country analytics'
      };

      // Get DOM elements
      this.container = $(`#${this.containerId}`);
      this.loader = $(`#${this.loaderId}`);
      this.card = $(`#${this.cardId}`);
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

      // Unbind first to prevent duplicates
      $(document).off('click', '.country-period-option');

      // Handle period selection
      $(document).on('click', '.country-period-option', function(e) {
        e.preventDefault();

        const period = $(this).data('period');
        self.currentPeriod = period;

        // Update active state
        $('.country-period-option').removeClass('active');
        $(this).addClass('active');

        // Load data for selected period
        self.loadAnalytics(period);
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

      // Check cache first
      const cachedData = this._getFromCache(cacheKey);
      if (cachedData) {
        this._renderData(cachedData, period);
        return;
      }

      // Show loader
      this.loader.removeClass('d-none');
      this.container.addClass('d-none');

      $.ajax({
        url: this.apiUrl,
        method: 'GET',
        data: { period: period },
        success: function(response) {
          if (response.success && response.data) {
            // Save to cache
            self._saveToCache(cacheKey, response.data);
            self._renderData(response.data, period);
          }
        },
        error: function(xhr) {
          if (typeof toastr !== 'undefined') {
            toastr.error(self.translations.loadFailed);
          }
          self.container.removeClass('d-none');
        },
        complete: function() {
          self.loader.addClass('d-none');
          self.container.removeClass('d-none');
        }
      });
    }

    /**
     * Render country data
     * @private
     */
    _renderData(countries, period) {
      // Update subtitle
      const subtitle = period === 'week' ? this.translations.weeklyOverview :
                      period === 'month' ? this.translations.monthlyOverview :
                      this.translations.yearlyOverview;

      this.card.find('.card-subtitle').text(subtitle);

      // Check if no data
      if (!countries || countries.length === 0) {
        this.container.html(`
          <div class="text-center text-muted py-4">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            <p class="mb-0">${this.translations.noData}</p>
          </div>
        `);
        return;
      }

      // Build HTML
      let html = '<div class="d-flex flex-column gap-2">';

      countries.forEach((country) => {
        const percentageClass = country.is_positive ? 'bg-text-green' : 'bg-text-red';
        const arrowIcon = country.is_positive ? 'bi-arrow-up' : 'bi-arrow-down';
        const percentageSign = country.percentage_change >= 0 ? '+' : '';

        // Format amount in compact form (K, M, B)
        const formattedAmount = this._formatAmount(country.amount);

        html += `
          <div class="d-flex align-items-center gap-3">
            <div class="country-flag-wrapper rounded-circle flex-shrink-0">
              <img src="${country.flag}" alt="${country.country_name}" class="country-flag-img">
            </div>
            <div class="flex-grow-1 min-w-0">
              <div class="fw-medium mb-0">${formattedAmount}</div>
              <small class="text-muted">${country.country_name}</small>
            </div>
            <span class="badge ${percentageClass}">
              <i class="${arrowIcon}"></i> ${percentageSign}${Math.abs(country.percentage_change)}%
            </span>
          </div>
        `;
      });

      html += '</div>';
      this.container.html(html);
    }

    /**
     * Format amount with K, M, B suffix
     * @private
     */
    _formatAmount(amount) {
      let displayAmount = amount;
      let suffix = '';

      if (displayAmount >= 1000000000) {
        displayAmount = (displayAmount / 1000000000).toFixed(1);
        suffix = 'B';
      } else if (displayAmount >= 1000000) {
        displayAmount = (displayAmount / 1000000).toFixed(1);
        suffix = 'M';
      } else if (displayAmount >= 1000) {
        displayAmount = (displayAmount / 1000).toFixed(1);
        suffix = 'K';
      }

      return this.currencySymbol + displayAmount + suffix;
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
      // Unbind events
      $(document).off('click', '.country-period-option');

      // Clear cache
      this.clearCache();
    }
  };

  /**
   * SupportTrackerManager - Manages support ticket analytics with gauge chart
   *
   * @class SupportTrackerManager
   * @param {Object} options - Configuration options
   * @param {string} options.cardId - Card container element ID (default: 'supportTrackerCard')
   * @param {string} options.contentId - Content container element ID (default: 'supportTrackerContent')
   * @param {string} options.loaderId - Loader element ID (default: 'supportTrackerLoader')
   * @param {string} options.canvasId - Chart canvas element ID (default: 'supportGaugeChart')
   * @param {string} options.apiUrl - API endpoint for support data (required)
   * @param {string} options.defaultPeriod - Default period: 'week'/'month'/'year' (default: 'week')
   * @param {boolean} options.enableCache - Enable caching (default: true)
   * @param {number} options.cacheTTL - Cache time-to-live in ms (default: 300000 = 5 min)
   * @param {Object} options.translations - Translation strings
   */
  window.SupportTrackerManager = class {
    constructor(options) {
      this.cardId = options.cardId || 'supportTrackerCard';
      this.contentId = options.contentId || 'supportTrackerContent';
      this.loaderId = options.loaderId || 'supportTrackerLoader';
      this.canvasId = options.canvasId || 'supportGaugeChart';
      this.apiUrl = options.apiUrl;
      this.currentPeriod = options.defaultPeriod || 'week';
      this.chart = null;

      // Cache configuration
      this.enableCache = options.enableCache !== false;
      this.cacheTTL = options.cacheTTL || 5 * 60 * 1000; // 5 minutes
      this.cache = {}; // Format: { 'period': { data, timestamp } }

      // Elements
      this.card = $(`#${this.cardId}`);
      this.content = $(`#${this.contentId}`);
      this.loader = $(`#${this.loaderId}`);

      // Translations
      this.translations = $.extend({
        lastSevenDays: 'Last 7 Days',
        thisMonth: 'This Month',
        thisYear: 'This Year',
        completed: 'Completed',
        pending: 'Pending',
        completedTask: 'Completed Task',
        loadFailed: 'Failed to load support tracker data',
        monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
      }, options.translations || {});

      // Chart colors
      this.chartColor = (typeof config !== 'undefined' && config.colors?.primary_color) || '#6366f1';
    }

    /**
     * Initialize the manager
     */
    init() {
      this.bindEvents();
      this.loadData(this.currentPeriod);
    }

    /**
     * Bind UI events
     * @private
     */
    bindEvents() {
      $(document).on('click', '.support-period-option', (e) => {
        e.preventDefault();
        const $btn = $(e.currentTarget);
        const period = $btn.data('period');

        $('.support-period-option').removeClass('active');
        $btn.addClass('active');

        this.loadData(period);
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
     * Load support tracker data
     * @param {string} period - Period type: 'week', 'month', 'year'
     */
    loadData(period) {
      this.currentPeriod = period;

      // Check cache first
      if (this._isCacheValid(period)) {
        this.updateUI(this.cache[period].data);
        this.renderChart(this.cache[period].data.completion);
        return;
      }

      this.loader.removeClass('d-none');
      this.content.addClass('d-none');

      $.ajax({
        url: this.apiUrl,
        method: 'GET',
        data: { period: period },
        success: (response) => {
          // Cache the response
          if (this.enableCache) {
            this.cache[period] = {
              data: response,
              timestamp: Date.now()
            };
          }

          this.updateUI(response);
          this.renderChart(response.completion);
        },
        error: (xhr) => {
          if (typeof toastr !== 'undefined') {
            toastr.error(this.translations.loadFailed);
          }
          this.loader.addClass('d-none');
        }
      });
    }

    /**
     * Update UI with data
     * @private
     */
    updateUI(data) {
      $('#totalTickets').text(data.total);
      $('#newTickets').text(data.new);
      $('#openTickets').text(data.open);
      $('#responseTime').text(data.responseTime);

      // Update subtitle
      const subtitle = {
        week: this.translations.lastSevenDays,
        month: this.translations.thisMonth,
        year: this.translations.thisYear
      };
      this.card.find('.card-subtitle').text(subtitle[this.currentPeriod]);

      this.loader.addClass('d-none');
      this.content.removeClass('d-none');
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

      const ctx = canvas.getContext('2d');
      this.chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: [this.translations.completed, this.translations.pending],
          datasets: [{
            data: [percentage, 100 - percentage],
            backgroundColor: [this.chartColor, '#e5e7eb'],
            borderWidth: 0,
            cutout: '85%',
            circumference: 180,
            rotation: 270
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
          }
        },
        plugins: [{
          afterDraw: (chart) => {
            const { width, height, ctx } = chart;
            ctx.restore();
            ctx.font = 'bold 28px Inter, sans-serif';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#111827';
            const text = percentage + '%';
            const textX = Math.round((width - ctx.measureText(text).width) / 2);
            const textY = height / 2 + 20;
            ctx.fillText(text, textX, textY);

            ctx.font = '12px Inter, sans-serif';
            ctx.fillStyle = '#6b7280';
            const label = this.translations.completedTask;
            const labelX = Math.round((width - ctx.measureText(label).width) / 2);
            ctx.fillText(label, labelX, textY + 25);
            ctx.save();
          }
        }]
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
      // Unbind events
      $(document).off('click', '.support-period-option');

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

      // Translations
      this.translations = $.extend({
        loadFailed: 'Failed to load statistics',
        loading: 'Loading...'
      }, options.translations || {});
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

      $(document).on('click', '.stats-period-option', function(e) {
        e.preventDefault();

        const period = $(this).data('period');
        const $btn = $(this);

        // Update active state
        $('.stats-period-option').removeClass('active');
        $btn.addClass('active');

        // Load data
        self.loadData(period);
      });
    }

    /**
     * Load statistics data
     * @param {string} period - Period to load
     */
    loadData(period) {
      const self = this;

      // Check cache
      if (this.enableCache && this.cache[period]) {
        const cached = this.cache[period];
        const now = Date.now();

        if (now - cached.timestamp < this.cacheTTL) {
          this.updateUI(cached.data);
          return;
        }
      }

      // Create loader element if it doesn't exist
      let $loader = this.card.find('.statistics-loader');
      if ($loader.length === 0) {
        $loader = $('<div class="statistics-loader position-absolute top-50 start-50 translate-middle d-none"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">' + this.translations.loading + '</span></div></div>');
        this.card.css('position', 'relative').append($loader);
      }

      // Show loader
      $loader.removeClass('d-none');
      $('[data-counter]').css('opacity', '0.5');

      // Make AJAX request
      $.ajax({
        url: this.apiUrl,
        type: 'GET',
        data: { period: period },
        success: function(response) {
          if (response.success) {
            // Cache the data
            if (self.enableCache) {
              self.cache[period] = {
                data: response.counters,
                timestamp: Date.now()
              };
            }

            self.updateUI(response.counters);
          }
        },
        error: function(xhr) {
          if (typeof toastr !== 'undefined') {
            toastr.error(self.translations.loadFailed);
          }
          console.error('Statistics load error:', xhr);
        },
        complete: function() {
          $loader.addClass('d-none');
          $('[data-counter]').css('opacity', '1');
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
      num = parseFloat(num);
      if (isNaN(num)) return '0';

      if (num >= 1000000000) {
        return (num / 1000000000).toFixed(2).replace(/\.0$/, '') + 'B';
      }
      if (num >= 1000000) {
        return (num / 1000000).toFixed(2).replace(/\.0$/, '') + 'M';
      }
      if (num >= 1000) {
        return (num / 1000).toFixed(2).replace(/\.0$/, '') + 'K';
      }
      return num.toFixed(0);
    }

    /**
     * Format number with thousand separators
     * @param {number} num - Number to format
     * @returns {string} Formatted number
     */
    formatNumber(num) {
      num = parseFloat(num);
      if (isNaN(num)) return '0';

      return num.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
      // Unbind events
      $(document).off('click', '.stats-period-option');

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
      this.translations = options.translations || {
        lastWeek: 'Last Week',
        thisMonth: 'This Month',
        thisYear: 'This Year',
        lifetime: 'Lifetime',
        revenue: 'Revenue',
        expense: 'Expense',
        noData: 'No data available',
        completed: 'Completed',
        remaining: 'Remaining',
        loadFailed: 'Failed to load profit & expenses data'
      };

      // Elements
      this.card = $(`#${this.cardId}`);
      this.content = $(`#${this.contentId}`);
      this.loader = $(`#${this.loaderId}`);
    }

    /**
     * Initialize the manager
     */
    init() {
      // Ensure proper initial state
      this.loader.removeClass('d-none');
      this.content.addClass('d-none');

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
      $('.revenue-period-option').on('click', function() {
        const period = $(this).data('period');
        $('.revenue-period-option').removeClass('active');
        $(this).addClass('active');
        self.loadData(period);
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

      // Restore spinner HTML if it was replaced by no-data message
      if (this.loader.find('.spinner-border').length === 0) {
        this.loader.html(`
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        `);
      }

      this.loader.removeClass('d-none');
      this.content.addClass('d-none');

      $.ajax({
        url: this.apiUrl,
        type: 'GET',
        data: { period: period },
        success: (response) => {
          if (response.success) {
            // Cache the response
            if (this.enableCache) {
              this.cache[period] = {
                data: response.data,
                timestamp: Date.now()
              };
            }
            this.updateUI(response.data, period);
          }
        },
        error: (xhr) => {
          toastr.error(this.translations.loadFailed);
          this.loader.addClass('d-none');
        }
      });
    }

    /**
     * Update UI with new data
     * @param {Object} data - Response data
     * @param {string} period - Current period
     */
    updateUI(data, period) {
      // Update subtitle with month/year names
      const now = new Date();
      const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                          'July', 'August', 'September', 'October', 'November', 'December'];
      const currentMonth = monthNames[now.getMonth()];
      const currentYear = now.getFullYear();

      const subtitles = {
        'last_week': this.translations.lastWeek,
        'this_month': `${this.translations.thisMonth} (${currentMonth})`,
        'this_year': `${this.translations.thisYear} (${currentYear})`,
        'lifetime': this.translations.lifetime
      };
      this.card.find('.card-subtitle').text(subtitles[period]);

      // Check if data is empty
      if (data.revenue === 0 && data.expense === 0) {
        // Hide content and show no data message
        this.content.addClass('d-none');
        this.loader.removeClass('d-none').html(`
          <div class="no-data-message text-center py-5 text-muted">
            <i class="bi bi-bar-chart fs-2 d-block mb-2 opacity-50"></i>
            <p class="mb-0">${this.translations.noData}</p>
          </div>
        `);
      } else {
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
        this.loader.addClass('d-none');
        this.content.removeClass('d-none');
      }
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
        case 'last_week':
          labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
          dataPoints = 7;
          variance = [0.9, 1.0, 1.1, 1.15, 1.2, 0.8, 0.85];
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
      // Convert to compact format (K, M)
      let formattedAmount;
      const absAmount = Math.abs(amount);

      if (absAmount >= 1000000) {
        formattedAmount = (amount / 1000000).toFixed(2) + 'M';
      } else if (absAmount >= 1000) {
        formattedAmount = (amount / 1000).toFixed(2) + 'K';
      } else {
        formattedAmount = amount.toFixed(2);
      }

      return this.currencyPosition == 1
        ? this.currencySymbol + formattedAmount
        : formattedAmount + this.currencySymbol;
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
      // Destroy chart
      if (this.revenueChartInstance) {
        this.revenueChartInstance.destroy();
        this.revenueChartInstance = null;
      }

      // Unbind events
      $('.revenue-period-option').off('click');

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
      this.translations = options.translations || {
        visitors: 'Visitors',
        today: 'Today',
        last28Days: 'Last 28 Days',
        thisMonth: 'This Month',
        thisYear: 'This Year',
        lifetime: 'Lifetime',
        loadFailed: 'Failed to load traffic source data'
      };

      // DOM elements
      this.$card = $(`#${this.cardId}`);
      this.$content = $(`#${this.contentId}`);
      this.$subtitle = this.$card.find('.card-subtitle');
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

        // Load data for selected period
        self.loadData(period);
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

      // Show loading state
      this.showLoading();

      // Fetch data
      $.ajax({
        url: this.apiUrl,
        method: 'GET',
        data: { period: period },
        success: (response) => {
          if (response.success) {
            // Cache the data
            if (this.enableCache) {
              this.cache[period] = {
                data: response.data,
                timestamp: Date.now()
              };
            }

            this.renderData(response.data);
          } else {
            this.showError();
          }
        },
        error: () => {
          this.showError();
        }
      });
    }

    /**
     * Show loading state
     * @private
     */
    showLoading() {
      this.$content.html(`
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      `);
    }

    /**
     * Show error message
     * @private
     */
    showError() {
      this.$content.html(`
        <div class="alert alert-danger">
          ${this.translations.loadFailed}
        </div>
      `);
    }

    /**
     * Render traffic source data
     * @private
     * @param {Object} data - Data object containing total_visitors and sources
     */
    renderData(data) {
      const now = new Date();
      const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                          'July', 'August', 'September', 'October', 'November', 'December'];
      const currentMonth = monthNames[now.getMonth()];
      const currentYear = now.getFullYear();

      const periodLabels = {
        'today': this.translations.today,
        'last_28_days': this.translations.last28Days,
        'this_month': `${this.translations.thisMonth} (${currentMonth})`,
        'last_1_year': `${this.translations.last1Year} (${currentYear})`,
        'lifetime': this.translations.lifetime
      };

      const periodLabel = periodLabels[this.currentPeriod] || 'This Month';

      // Update subtitle with total visitors and period
      this.$subtitle.text(`${data.total_visitors} ${this.translations.visitors} · ${periodLabel}`);

      // Build HTML for sources
      let html = '';
      data.sources.forEach((source) => {
        const arrowIcon = source.is_positive ? 'up' : 'down';
        const changeColor = source.is_positive ? 'success' : 'danger';
        const changeSign = source.is_positive ? '+' : '';

        html += `
          <div class="d-flex align-items-center gap-3">
            <div class="flex-shrink-0">
              <div class="card-icon card-icon-md rounded-circle bg-light">
                <i class="bi ${source.icon} text-muted"></i>
              </div>
            </div>
            <div class="flex-grow-1">
              <div class="fw-medium">${source.name}</div>
              <small class="text-muted">${source.description}</small>
            </div>
            <div class="text-end">
              <div class="fw-medium text-muted">${source.formatted_count}</div>
              <small class="text-${changeColor}">
                <i class="bi bi-arrow-${arrowIcon}"></i> ${changeSign}${source.percentage_change}%
              </small>
            </div>
          </div>
        `;
      });

      this.$content.html(html);
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
      this.chart = null;

      // Display mode: 'percentage' or 'total'
      this.displayMode = options.displayMode || 'percentage';

      // Currency configuration
      this.currencySymbol = options.currencySymbol || '';
      this.currencyPosition = options.currencyPosition || 1; // 1 = before, 2 = after

      // Cache configuration
      this.enableCache = options.enableCache !== false;
      this.cacheTTL = options.cacheTTL || 5 * 60 * 1000; // 5 minutes
      this.cache = {};

      // Translations
      this.translations = options.translations || {
        segment1: 'Segment 1',
        segment2: 'Segment 2',
        segment3: 'Segment 3',
        segment4: 'Segment 4',
        title: 'Chart',
        noData: 'No data available!',
        loadFailed: 'Failed to load chart data',
        last7Days: 'Last 7 Days',
        last28Days: 'Last 28 Days',
        thisMonth: 'This Month',
        thisYear: 'This Year',
        lifetime: 'Lifetime'
      };

      // Colors
      this.colors = options.colors || {
        segment1: '#10b981',
        segment2: '#f59e0b',
        segment3: '#ef4444',
        segment4: '#8b5cf6'
      };

      // Option class for click events (customizable for multiple instances)
      this.optionClass = options.optionClass || 'product-status-option';
    }

    /**
     * Initialize the manager
     */
    init() {
      this.showLoader();
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
      this.loadData(period);
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

      this.showLoader();

      $.ajax({
        url: this.apiUrl,
        method: 'GET',
        data: { period: period },
        success: (response) => {
          if (response.success) {
            // Cache the data
            if (this.enableCache) {
              this.cache[period] = {
                data: response.data,
                timestamp: Date.now()
              };
            }

            this.renderChart(response.data);
          } else {
            toastr.error(this.translations.loadFailed);
            this.hideLoader();
          }
        },
        error: (xhr) => {
          toastr.error(this.translations.loadFailed);
          this.hideLoader();
        }
      });
    }

    /**
     * Show loader
     */
    showLoader() {
      // Restore spinner HTML if it was replaced by no-data message
      if (this.$loader.find('.spinner-border').length === 0) {
        this.$loader.html(`
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        `);
      }
      this.$loader.removeClass('d-none');
      this.$content.addClass('d-none');
    }

    /**
     * Hide loader
     */
    hideLoader() {
      this.$loader.addClass('d-none');
      this.$content.removeClass('d-none');
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
        // Hide content and show no data message
        this.$content.addClass('d-none');
        this.$loader.removeClass('d-none').html(`
          <div class="no-data-message text-center py-5 text-muted">
            <i class="bi bi-pie-chart fs-2 d-block mb-2 opacity-50"></i>
            <p class="mb-0">${this.translations.noData}</p>
          </div>
        `);
        return;
      }

      // Show content if hidden
      this.$content.removeClass('d-none');
      this.$loader.addClass('d-none');

      const ctx = document.getElementById(this.canvasId).getContext('2d');

      // Destroy existing chart
      if (this.chart) {
        this.chart.destroy();
      }

      const display = data.display || data.segment1 || 0;
      const percentage = total > 0 ? Math.round((display / total) * 100) : 0;

      // Update center text based on display mode
      if (this.displayMode === 'total') {
        // Format total with compact notation (1.1k, 2.5M, etc)
        const formatCompact = (num) => {
          if (num >= 1000000) {
            return (num / 1000000).toFixed(2).replace(/\.0$/, '') + 'M';
          }
          if (num >= 1000) {
            return (num / 1000).toFixed(2).replace(/\.0$/, '') + 'k';
          }
          return num.toString();
        };
        this.$percentage.text(formatCompact(total));
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
            cutout: '75%'
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

      // Period filtering
      this.cardId = options.cardId || null;
      this.$card = this.cardId ? $(`#${this.cardId}`) : null;
      this.currentPeriod = options.defaultPeriod || 'this_month';

      // Cache configuration
      this.enableCache = options.enableCache !== false;
      this.cacheTTL = options.cacheTTL || 10 * 60 * 1000; // 10 minutes
      this.cachedData = {};  // Cache per period
      this.cacheTimestamp = {};  // Timestamp per period

      // Translations
      this.translations = options.translations || {
        sales: 'Sales',
        loadFailed: 'Failed to load geographic data',
        noData: 'No data available',
        loading: 'Loading...',
        last7Days: 'Last 7 Days',
        thisMonth: 'This Month',
        thisYear: 'This Year'
      };
    }

    /**
     * Initialize the manager and load Google Charts library
     */
    init() {
      this.showLoading();
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
        toastr.error(this.translations.loadFailed);
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

      // Show loading indicator
      this.showLoading();

      $.ajax({
        url: this.apiUrl,
        method: 'GET',
        data: { period: period },
        success: (response) => {
          if (response.success && response.data) {
            // Check if data has actual countries (more than just header row)
            const hasData = response.data.length > 1;

            if (hasData) {
              // Cache the data
              if (this.enableCache) {
                this.cachedData[period] = response.data;
                this.cacheTimestamp[period] = Date.now();
              }

              this.renderChart(response.data);
            } else {
              // Show no data message
              this.$container.html(`
                <div class="no-data-message text-center py-5 text-muted">
                  <i class="bi bi-globe fs-2 d-block mb-2 opacity-50"></i>
                  <p class="mb-0">${this.translations.noData}</p>
                </div>
              `);
            }
          } else {
            toastr.error(this.translations.loadFailed);
          }
        },
        error: () => {
          toastr.error(this.translations.loadFailed);
        }
      });
    }

    /**
     * Show loading indicator
     * @private
     */
    showLoading() {
      this.$container.html(`
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">${this.translations.loading}</span>
          </div>
        </div>
      `);
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
        self.loadData(period);
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

})(jQuery);
