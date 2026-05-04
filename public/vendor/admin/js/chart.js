(function($) {
    "use strict";

    // Chart Manager Class
    class ChartManager {
        constructor() {
            this.primaryColor = config.colors?.primary_color || '#6366f1';
            this.colors = this.generateColorPalette();
            this.pieColors = [
                this.colors.primary,
                this.colors.success,
                this.colors.info,
                this.colors.warning,
                this.colors.danger,
                this.colors.pink,
                this.colors.orange,
                this.colors.secondary
            ];
            this.rtl = config.direction === "rtl";
            this.init();
        }

        getChartsConfig() {
            const provider = document.getElementById('chart-data-provider');
            if (provider && provider.dataset.chartData) {
                try {
                    return JSON.parse(provider.dataset.chartData);
                } catch (e) {
                    console.error('Failed to parse chart data from provider:', e);
                }
            }
            return typeof chartsConfig !== 'undefined' ? chartsConfig : null;
        }

        generateColorPalette() {
            return {
                primary: this.primaryColor,
                secondary: '#8b5cf6',
                success: '#10b981',
                info: '#06b6d4',
                warning: '#f59e0b',
                danger: '#ef4444',
                pink: '#ec4899',
                orange: '#f97316'
            };
        }

        hexToRgba(hex, alpha = 1) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        createGradient(ctx, colorStart, colorEnd, vertical = true) {
            const gradient = vertical
                ? ctx.createLinearGradient(0, 0, 0, 400)
                : ctx.createLinearGradient(0, 0, 400, 0);
            gradient.addColorStop(0, colorStart);
            gradient.addColorStop(1, colorEnd);
            return gradient;
        }

        createAreaGradient(ctx, color, startOpacity = 0.4, endOpacity = 0.01) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, this.hexToRgba(color, startOpacity));
            gradient.addColorStop(1, this.hexToRgba(color, endOpacity));
            return gradient;
        }

        getDefaultTooltipOptions() {
            return {
                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                titleColor: '#fff',
                bodyColor: '#e5e7eb',
                padding: 12,
                cornerRadius: 8,
                displayColors: false
            };
        }

        getDefaultGridOptions() {
            return {
                color: 'rgba(156, 163, 175, 0.1)',
                drawBorder: false
            };
        }

        getDefaultScaleOptions(maxValue = null) {
            return {
                x: {
                    grid: { display: false },
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 12,
                        color: '#9ca3af'
                    }
                },
                y: {
                    suggestedMax: maxValue,
                    grid: this.getDefaultGridOptions(),
                    ticks: { color: '#9ca3af' }
                }
            };
        }

        getDefaultLegendOptions(position = 'bottom') {
            return {
                position: position,
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    color: '#6b7280',
                    font: { size: 12 }
                }
            };
        }

        init() {
            this.initLineCharts();
            this.initBarCharts();
            this.initPieCharts();
            this.initDoughnutCharts();
            this.initHorizontalBarCharts();
            this.initPolarCharts();
            this.initRadarCharts();
            this.initGeoCharts();
        }

        // Line Charts - uses class .chart-line on canvas elements
        initLineCharts() {
            const self = this;
            const configData = this.getChartsConfig();
            if (!configData) return;

            $('canvas.chart-line').each(function() {
                const canvas = $(this);
                const chartKey = canvas.data('chart');

                if (!chartKey || !configData[chartKey]) return;

                const chartData = configData[chartKey];
                const ctx = canvas[0].getContext('2d');
                const lineColor = canvas.data('color') || self.colors.primary;

                new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: chartData.title || '',
                            data: chartData.data,
                            fill: true,
                            backgroundColor: self.createAreaGradient(ctx, lineColor),
                            pointBackgroundColor: lineColor,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderColor: lineColor,
                            borderWidth: 3,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { display: false },
                            tooltip: self.getDefaultTooltipOptions()
                        },
                        scales: self.getDefaultScaleOptions(chartData.max)
                    }
                });
            });
        }

        // Bar Charts - uses class .chart-bar on canvas elements
        initBarCharts() {
            const self = this;
            const configData = this.getChartsConfig();
            if (!configData) return;

            $('canvas.chart-bar').each(function() {
                const canvas = $(this);
                const chartKey = canvas.data('chart');

                if (!chartKey || !configData[chartKey]) return;

                const chartData = configData[chartKey];
                const ctx = canvas[0].getContext('2d');
                const barColor = canvas.data('color') || self.colors.success;
                const barGradient = self.createGradient(ctx, barColor, self.hexToRgba(barColor, 0.7));
                const highlightMax = canvas.data('highlight-max') === true;
                const highlightCurrent = canvas.data('highlight-current');

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: chartData.title || '',
                            data: chartData.data,
                            backgroundColor: function(context) {
                                const index = context.dataIndex;

                                // Highlight current month/period if specified
                                if (highlightCurrent !== undefined && index === highlightCurrent) {
                                    return self.colors.primary;
                                }

                                // Highlight max value if specified
                                if (highlightMax) {
                                    const value = context.dataset.data[index];
                                    const max = Math.max(...context.dataset.data);
                                    return value === max ? self.colors.primary : self.hexToRgba(self.colors.primary, 0.2);
                                }

                                return self.hexToRgba(barColor, 0.7);
                            },
                            borderColor: 'transparent',
                            borderWidth: 0,
                            borderRadius: 6,
                            borderSkipped: false,
                            barPercentage: 0.85,
                            categoryPercentage: 0.9,
                            hoverBackgroundColor: self.colors.primary
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { display: false },
                            tooltip: self.getDefaultTooltipOptions()
                        },
                        scales: self.getDefaultScaleOptions(chartData.max)
                    }
                });
            });
        }

        // Pie Charts - uses class .chart-pie on canvas elements
        initPieCharts() {
            const self = this;
            const configData = this.getChartsConfig();
            if (!configData) return;

            $('canvas.chart-pie').each(function() {
                const canvas = $(this);
                const chartKey = canvas.data('chart');

                if (!chartKey || !configData[chartKey]) return;

                const chartData = configData[chartKey];
                if (!chartData.data || chartData.data.length === 0) return;

                new Chart(canvas, {
                    type: 'pie',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.data,
                            backgroundColor: self.pieColors,
                            borderColor: '#fff',
                            borderWidth: 2,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: self.getDefaultLegendOptions(),
                            tooltip: { ...self.getDefaultTooltipOptions(), displayColors: true }
                        }
                    }
                });
            });
        }

        // Doughnut Charts - uses class .chart-doughnut on canvas elements
        initDoughnutCharts() {
            const self = this;
            const configData = this.getChartsConfig();
            if (!configData) return;

            $('canvas.chart-doughnut').each(function() {
                const canvas = $(this);
                const chartKey = canvas.data('chart');

                if (!chartKey || !configData[chartKey]) return;

                const chartData = configData[chartKey];
                if (!chartData.data || chartData.data.length === 0) return;

                const cutout = canvas.data('cutout') || '65%';

                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.data,
                            backgroundColor: self.pieColors,
                            borderColor: '#fff',
                            borderWidth: 2,
                            hoverOffset: 8,
                            cutout: cutout
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: self.getDefaultLegendOptions(),
                            tooltip: { ...self.getDefaultTooltipOptions(), displayColors: true }
                        }
                    }
                });
            });
        }

        // Horizontal Bar Charts - uses class .chart-bar-horizontal on canvas elements
        initHorizontalBarCharts() {
            const self = this;
            const configData = this.getChartsConfig();
            if (!configData) return;

            $('canvas.chart-bar-horizontal').each(function() {
                const canvas = $(this);
                const chartKey = canvas.data('chart');

                if (!chartKey || !configData[chartKey]) return;

                const chartData = configData[chartKey];
                if (!chartData.data || chartData.data.length === 0) return;

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: chartData.title || '',
                            data: chartData.data,
                            backgroundColor: self.pieColors,
                            borderColor: 'transparent',
                            borderWidth: 0,
                            borderRadius: 4,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: self.getDefaultTooltipOptions()
                        },
                        scales: {
                            x: {
                                grid: self.getDefaultGridOptions(),
                                ticks: { color: '#9ca3af' }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: '#6b7280' }
                            }
                        }
                    }
                });
            });
        }

        // Polar Area Charts - uses class .chart-polar on canvas elements
        initPolarCharts() {
            const self = this;
            const configData = this.getChartsConfig();
            if (!configData) return;

            $('canvas.chart-polar').each(function() {
                const canvas = $(this);
                const chartKey = canvas.data('chart');

                if (!chartKey || !configData[chartKey]) return;

                const chartData = configData[chartKey];
                if (!chartData.data || chartData.data.length === 0) return;

                new Chart(canvas, {
                    type: 'polarArea',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.data,
                            backgroundColor: self.pieColors.map(c => self.hexToRgba(c, 0.8)),
                            borderColor: self.pieColors,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: self.getDefaultLegendOptions(),
                            tooltip: self.getDefaultTooltipOptions()
                        },
                        scales: {
                            r: {
                                grid: { color: 'rgba(156, 163, 175, 0.2)' },
                                ticks: { color: '#9ca3af', backdropColor: 'transparent' }
                            }
                        }
                    }
                });
            });
        }

        // Radar Charts - uses class .chart-radar on canvas elements
        initRadarCharts() {
            const self = this;
            const configData = this.getChartsConfig();
            if (!configData) return;

            $('canvas.chart-radar').each(function() {
                const canvas = $(this);
                const chartKey = canvas.data('chart');

                if (!chartKey || !configData[chartKey]) return;

                const chartData = configData[chartKey];
                if (!chartData.data || chartData.data.length === 0) return;

                new Chart(canvas, {
                    type: 'radar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: chartData.title || 'Performance',
                            data: chartData.data,
                            fill: true,
                            backgroundColor: self.hexToRgba(self.colors.primary, 0.2),
                            borderColor: self.colors.primary,
                            pointBackgroundColor: self.colors.primary,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: self.getDefaultTooltipOptions()
                        },
                        scales: {
                            r: {
                                angleLines: { color: 'rgba(156, 163, 175, 0.2)' },
                                grid: { color: 'rgba(156, 163, 175, 0.2)' },
                                pointLabels: { color: '#6b7280', font: { size: 12 } },
                                ticks: { color: '#9ca3af', backdropColor: 'transparent' }
                            }
                        }
                    }
                });
            });
        }

        // Geo Charts - uses class .chart-geo
        initGeoCharts() {
            const self = this;
            const $geoElements = $('.chart-geo');
            if ($geoElements.length === 0) return;

            const configData = self.getChartsConfig();
            if (!configData) return;

            const drawCharts = () => {
                $geoElements.each(function() {
                    const element = this;
                    const $element = $(this);
                    const chartKey = $element.data('chart') || 'geo';

                    if (!configData[chartKey] || !configData[chartKey].data) return;

                    const containerWidth = $element.parent().width() || 800;
                    const containerHeight = $element.parent().height() || 400;

                    const data = google.visualization.arrayToDataTable(configData[chartKey].data);

                    const options = {
                        width: containerWidth,
                        height: containerHeight,
                        region: 'world',
                        displayMode: 'regions',
                        resolution: 'countries',
                        colorAxis: {
                            colors: ['#e0e0ff', '#b3b3f5', '#8080eb', '#5555e5', self.colors.primary],
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
                            textStyle: { color: '#1f2937', fontSize: 13 },
                            showColorCode: true,
                            trigger: 'focus'
                        },
                        keepAspectRatio: true,
                        enableRegionInteractivity: true
                    };

                    const geoChart = new google.visualization.GeoChart(element);
                    geoChart.draw(data, options);

                    // Handle window resize
                    let resizeTimeout;
                    $(window).off('resize.geoChart').on('resize.geoChart', function() {
                        clearTimeout(resizeTimeout);
                        resizeTimeout = setTimeout(function() {
                            const newWidth = $element.parent().width();
                            if (newWidth) {
                                options.width = newWidth;
                                geoChart.draw(data, options);
                            }
                        }, 250);
                    });
                });
            };

            if (typeof google !== 'undefined' && google.charts) {
                google.charts.load('current', { 'packages': ['geochart'] });
                google.charts.setOnLoadCallback(drawCharts);
            }
        }
    }

    // Expose globally for re-initialization (e.g., after AJAX tab load)
    window.initProductCharts = function() {
        if (window.chartManager) {
            window.chartManager.init();
        } else {
            window.chartManager = new ChartManager();
        }
    };

    // Initialize Chart Manager when document is ready
    $(document).ready(function() {
        window.initProductCharts();

        // Handle Sync Statistics button
        $(document).on('click', '.btn-sync-statistics', function() {
            const btn = $(this);
            const productId = btn.data('id');
            const syncUrl = btn.data('url');
            const originalHtml = btn.html();

            if (btn.hasClass('loading')) return;

            btn.addClass('loading').prop('disabled', true);
            btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + 'Syncing...');

            $.ajax({
                url: syncUrl || `/admin/products/${productId}/statistics/recalculate`,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        // Reload the current tab to refresh data
                        const activeTab = $('.nav-link.active').data('bs-target')?.replace('#', '') || 'statistics';
                        window.location.reload();
                    } else {
                        toastr.error(response.message);
                        btn.removeClass('loading').prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'An error occurred while synchronizing statistics';
                    toastr.error(error);
                    btn.removeClass('loading').prop('disabled', false).html(originalHtml);
                }
            });
        });
    });

})(jQuery);
