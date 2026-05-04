(function ($) {
    "use strict";

    const config = window.config || {};

    /**
     * Checks if a dataset (array of numbers) is empty or contains only zeros.
     */
    function isDataEmpty(data) {
        if (!data || data.length === 0) return true;
        return data.every(v => parseFloat(v) === 0);
    }

    /**
     * Replaces the chart element with a "No data available" placeholder.
     */
    function showNoDataPlaceholder(el, message = null) {
        if (!el) return;
        message = message || config?.translates?.noDataAvailable || 'No data available';

        const parent = el.parentElement;
        el.classList.add('d-none');

        // Check if placeholder already exists
        if (parent.querySelector('.no-data-placeholder')) return;

        const placeholder = document.createElement('div');
        placeholder.className = 'no-data-placeholder d-flex flex-column align-items-center justify-content-center w-100 min-h-200 h-100';
        placeholder.innerHTML = `
            <div class="text-center opacity-75">
                <i class="bi bi-bar-chart-line fs-1 mb-2 d-block"></i>
                <p class="fw-medium">${message}</p>
            </div>
        `;
        parent.appendChild(placeholder);
    }

    window.initProductCharts = function () {
        var contentEl = document.getElementById('chart-data-provider');
        var currentChartsConfig = null;

        if (contentEl && contentEl.dataset.chartData) {
            try {
                currentChartsConfig = JSON.parse(contentEl.dataset.chartData);
            } catch (e) {
                console.error("Failed to parse chart data", e);
            }
        }

        if (!currentChartsConfig) return;

        // Shared Tooltip Config
        const sharedTooltipOptions = {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            padding: 12,
            boxPadding: 8,
            titleFont: { size: 13, weight: 'bold' },
            bodyFont: { size: 12 },
            cornerRadius: 8,
            intersect: false,
            mode: 'index'
        };

        // Helper for percentage labels
        const pieTooltipCallbacks = {
            label: function (context) {
                let label = context.label || '';
                if (label) label += ': ';
                let value = context.raw;
                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                let percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                return ' ' + label + value + ' (' + percentage + ')';
            }
        };

        // 1. Sales Chart (Line)
        let salesChartEl = document.getElementById('sales-chart');
        if (salesChartEl && currentChartsConfig.sales) {
            if (isDataEmpty(currentChartsConfig.sales.data)) {
                showNoDataPlaceholder(salesChartEl);
            } else {
                let existingChart = Chart.getChart(salesChartEl);
                if (existingChart) existingChart.destroy();

                new Chart(salesChartEl, {
                    type: 'line',
                    data: {
                        labels: currentChartsConfig.sales.labels,
                        datasets: [{
                            label: ' ' + currentChartsConfig.sales.title,
                            data: currentChartsConfig.sales.data,
                            fill: true,
                            backgroundColor: 'rgba(79, 70, 229, 0.05)',
                            borderColor: config.colors.primary_color,
                            borderWidth: 3,
                            pointBackgroundColor: config.colors.primary_color,
                            pointBorderColor: '#fff',
                            pointHoverRadius: 5,
                            lineTension: 0.4,
                            rtl: config.direction == "rtl" ? true : false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: sharedTooltipOptions
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { autoSkip: true, maxTicksLimit: 10 } },
                            y: { suggestedMax: currentChartsConfig.sales.max, grid: { borderDash: [5, 5] } }
                        }
                    }
                });
            }
        }

        // 2. License Distribution (Pie)
        let licenseChartEl = document.getElementById('license-pie-chart');
        if (licenseChartEl && currentChartsConfig.license) {
            if (isDataEmpty(currentChartsConfig.license.data)) {
                showNoDataPlaceholder(licenseChartEl);
            } else {
                let existingChart = Chart.getChart(licenseChartEl);
                if (existingChart) existingChart.destroy();

                new Chart(licenseChartEl, {
                    type: 'doughnut',
                    data: {
                        labels: currentChartsConfig.license.labels,
                        datasets: [{
                            data: currentChartsConfig.license.data,
                            backgroundColor: [
                                config.colors.primary_color,
                                '#10b981',
                                '#f59e0b',
                                '#ef4444',
                                '#8b5cf6'
                            ],
                            borderWidth: 0,
                            hoverOffset: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, padding: 20 }
                            },
                            tooltip: {
                                ...sharedTooltipOptions,
                                callbacks: pieTooltipCallbacks
                            }
                        }
                    }
                });
            }
        }

        // 3. Views Chart (Bar) - Optional if standalone views chart exists
        let viewsChartEl = document.getElementById('views-chart');
        if (viewsChartEl && currentChartsConfig.views) {
            if (isDataEmpty(currentChartsConfig.views.data)) {
                showNoDataPlaceholder(viewsChartEl);
            } else {
                let existingChart = Chart.getChart(viewsChartEl);
                if (existingChart) existingChart.destroy();

                new Chart(viewsChartEl, {
                    type: 'bar',
                    data: {
                        labels: currentChartsConfig.views.labels,
                        datasets: [{
                            label: ' ' + currentChartsConfig.views.title,
                            data: currentChartsConfig.views.data,
                            backgroundColor: 'rgba(79, 70, 229, 0.8)',
                            borderRadius: 6,
                            rtl: config.direction == "rtl" ? true : false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: sharedTooltipOptions
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { suggestedMax: currentChartsConfig.views.max, grid: { borderDash: [5, 5] } }
                        }
                    }
                });
            }
        }

        // 4. Geo Chart
        let countriesChartEl = document.getElementById('countries-chart');
        if (countriesChartEl && currentChartsConfig.geo) {
            // Data table usually has [ ['Country', 'Sales'], ... ]
            if (currentChartsConfig.geo.data.length <= 1) {
                showNoDataPlaceholder(countriesChartEl);
            } else {
                if (typeof google !== 'undefined' && google.charts) {
                    google.charts.load('current', { 'packages': ['geochart'] });
                    google.charts.setOnLoadCallback(function () {
                        var data = google.visualization.arrayToDataTable(currentChartsConfig.geo.data);
                        var options = {
                            colorAxis: { colors: [config.colors.primary_color] },
                            backgroundColor: 'transparent',
                            datalessRegionColor: '#f8fafc',
                            defaultColor: '#f1f5f9'
                        };
                        var geoChart = new google.visualization.GeoChart(countriesChartEl);
                        geoChart.draw(data, options);
                    });
                }
            }
        }

        // 5. Comparison Chart (Dual Line)
        let comparisonChartEl = document.getElementById('comparison-chart');
        if (comparisonChartEl && currentChartsConfig.sales && currentChartsConfig.views) {
            if (isDataEmpty(currentChartsConfig.sales.data) && isDataEmpty(currentChartsConfig.views.data)) {
                showNoDataPlaceholder(comparisonChartEl);
            } else {
                let existingChart = Chart.getChart(comparisonChartEl);
                if (existingChart) existingChart.destroy();

                new Chart(comparisonChartEl, {
                    type: 'line',
                    data: {
                        labels: currentChartsConfig.sales.labels,
                        datasets: [
                            {
                                label: currentChartsConfig.sales.title,
                                data: currentChartsConfig.sales.data,
                                borderColor: config.colors.primary_color,
                                backgroundColor: config.colors.primary_color,
                                borderWidth: 2,
                                fill: false,
                                tension: 0.3,
                                yAxisID: 'y'
                            },
                            {
                                label: ' ' + currentChartsConfig.views.title,
                                data: currentChartsConfig.views.data,
                                borderColor: '#10b981',
                                backgroundColor: '#10b981',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.3,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true } },
                            tooltip: sharedTooltipOptions
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: { borderDash: [5, 5] },
                                title: { display: true, text: currentChartsConfig.sales.title }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: { drawOnChartArea: false },
                                title: { display: true, text: currentChartsConfig.views.title }
                            }
                        }
                    }
                });
            }
        }

        // 6. Refunds Distribution (Pie)
        let refundsChartEl = document.getElementById('refunds-pie-chart');
        if (refundsChartEl && currentChartsConfig.refunds) {
            if (isDataEmpty(currentChartsConfig.refunds.data)) {
                showNoDataPlaceholder(refundsChartEl);
            } else {
                let existingChart = Chart.getChart(refundsChartEl);
                if (existingChart) existingChart.destroy();

                new Chart(refundsChartEl, {
                    type: 'pie',
                    data: {
                        labels: currentChartsConfig.refunds.labels,
                        datasets: [{
                            data: currentChartsConfig.refunds.data,
                            backgroundColor: [
                                '#10b981',
                                '#ef4444',
                                '#f59e0b',
                                '#8b5cf6'
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, padding: 20 }
                            },
                            tooltip: {
                                ...sharedTooltipOptions,
                                callbacks: pieTooltipCallbacks
                            }
                        }
                    }
                });
            }
        }
    };

    // Sync Statistics Button Handler
    $(document).on('click', '.btn-sync-statistics', function (e) {
        e.preventDefault();
        const btn = $(this);
        const productId = btn.data('id');
        const syncUrl = btn.data('url');
        const originalHtml = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>' + originalHtml);

        $.ajax({
            url: syncUrl,
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                    // Refresh the page or the tab content to show updated values
                    if (window.loadAjaxTab) {
                        window.loadAjaxTab('statistics');
                    } else {
                        location.reload();
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function () {
                toastr.error('An error occurred during synchronization');
            },
            complete: function () {
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Initialize charts on direct page load if they exist
    $(document).ready(function() {
        if (typeof window.initProductCharts === 'function') {
            window.initProductCharts();
        }
    });

})(jQuery);

