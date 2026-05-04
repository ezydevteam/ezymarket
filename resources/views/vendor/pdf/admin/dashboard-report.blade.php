<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard Report - {{ $period }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 11px;
            opacity: 0.9;
        }

        .info-bar {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            font-size: 10px;
        }

        .metrics-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .metrics-row {
            display: table-row;
        }

        .metric-box {
            display: table-cell;
            width: 24%;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 12px;
            margin-right: 1%;
            text-align: center;
        }

        .metric-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .metric-value {
            font-size: 15px;
            font-weight: bold;
            color: #667eea;
        }

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #667eea;
            padding: 8px 0;
            margin-bottom: 12px;
            border-bottom: 2px solid #667eea;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table thead {
            background: #667eea;
            color: white;
        }

        table th {
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }

        table td {
            padding: 6px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .stats-box {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 3px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }

        .stats-item {
            display: inline-block;
            width: 48%;
            margin-bottom: 8px;
        }

        .stats-label {
            font-size: 9px;
            color: #666;
            display: block;
            margin-bottom: 3px;
        }

        .stats-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
            padding: 8px 0;
            border-top: 1px solid #dee2e6;
            background: white;
        }

        .page-break {
            page-break-after: always;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
            font-size: 10px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .two-column {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            margin-right: 2%;
        }

        .two-column:last-child {
            margin-right: 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📊 Admin Dashboard Report</h1>
        <p>Comprehensive Platform Performance Overview</p>
        <p><strong>{{ ucfirst(str_replace('_', ' ', $period)) }}</strong> | Generated: {{ $exportDate }}</p>
        @if($startDate && $endDate)
            <p>{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        @endif
    </div>

    <!-- Key Metrics Summary -->
    <div class="section">
        <div class="section-title">📈 Key Performance Indicators</div>

        <div class="metrics-grid">
            <div class="metrics-row">
                <div class="metric-box">
                    <div class="metric-label">Total Sales</div>
                    <div class="metric-value">{{ number_format($counters['total_sales']) }}</div>
                </div>
                <div class="metric-box">
                    <div class="metric-label">Sales Amount</div>
                    <div class="metric-value">${{ number_format($counters['sellers_sales'], 0) }}</div>
                </div>
                <div class="metric-box">
                    <div class="metric-label">Platform Revenue</div>
                    <div class="metric-value">${{ number_format($counters['platform_total_revenues'], 0) }}</div>
                </div>
                <div class="metric-box">
                    <div class="metric-label">Net Profit</div>
                    <div class="metric-value">${{ number_format($revenueExpense['profit'], 0) }}</div>
                </div>
            </div>
        </div>

        <div class="metrics-grid">
            <div class="metrics-row">
                <div class="metric-box">
                    <div class="metric-label">Products</div>
                    <div class="metric-value">{{ number_format($counters['total_products']) }}</div>
                </div>
                <div class="metric-box">
                    <div class="metric-label">Users</div>
                    <div class="metric-value">{{ number_format($counters['total_users']) }}</div>
                </div>
                <div class="metric-box">
                    <div class="metric-label">Sellers</div>
                    <div class="metric-value">{{ number_format($counters['total_sellers']) }}</div>
                </div>
                <div class="metric-box">
                    <div class="metric-label">Refunds</div>
                    <div class="metric-value">{{ number_format($counters['total_refunds']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue & Expense Analysis -->
    <div class="section">
        <div class="section-title">💰 Financial Overview</div>

        <div class="stats-box">
            <div class="stats-item">
                <span class="stats-label">Total Revenue</span>
                <span class="stats-value">${{ number_format($revenueExpense['revenue'], 2) }}</span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Total Expenses</span>
                <span class="stats-value">${{ number_format($revenueExpense['expense'], 2) }}</span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Buyer Fees</span>
                <span class="stats-value">${{ number_format($counters['buyer_fees'], 2) }}</span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Seller Fees</span>
                <span class="stats-value">${{ number_format($counters['seller_fees'], 2) }}</span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Total Payouts</span>
                <span class="stats-value">${{ number_format($counters['payout_amount'], 2) }}</span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Seller Earnings</span>
                <span class="stats-value">${{ number_format($counters['sellers_earnings'], 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Support & Refunds -->
    <div class="section">
        <div class="section-title">🎫 Support Tickets & Refunds</div>

        <div class="two-column">
            <div class="stats-box">
                <h4 style="margin-bottom: 10px; font-size: 11px;">Support Tickets</h4>
                <div class="stats-item" style="width: 100%;">
                    <span class="stats-label">Total Tickets</span>
                    <span class="stats-value">{{ number_format($supportStats['total']) }}</span>
                </div>
                <div class="stats-item" style="width: 100%;">
                    <span class="stats-label">Open Tickets</span>
                    <span class="stats-value">{{ number_format($supportStats['open']) }}</span>
                </div>
                <div class="stats-item" style="width: 100%;">
                    <span class="stats-label">Closed Tickets</span>
                    <span class="stats-value">{{ number_format($supportStats['closed']) }}</span>
                </div>
                <div class="stats-item" style="width: 100%;">
                    <span class="stats-label">Completion Rate</span>
                    <span class="stats-value">{{ $supportStats['completion'] }}%</span>
                </div>
            </div>
        </div>

        <div class="two-column">
            <div class="stats-box">
                <h4 style="margin-bottom: 10px; font-size: 11px;">Refund Statistics</h4>
                <div class="stats-item" style="width: 100%;">
                    <span class="stats-label">Total Refunds</span>
                    <span class="stats-value">{{ number_format($refundStats['total']) }}</span>
                </div>
                <div class="stats-item" style="width: 100%;">
                    <span class="stats-label">Accepted</span>
                    <span class="stats-value">{{ number_format($refundStats['accepted']) }}</span>
                </div>
                <div class="stats-item" style="width: 100%;">
                    <span class="stats-label">Declined</span>
                    <span class="stats-value">{{ number_format($refundStats['declined']) }}</span>
                </div>
                <div class="stats-item" style="width: 100%;">
                    <span class="stats-label">Acceptance Rate</span>
                    <span class="stats-value">{{ $refundStats['acceptance'] }}%</span>
                </div>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Top Selling Products -->
    <div class="section">
        <div class="section-title">🏆 Top Selling Products</div>

        @if($topProducts->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th style="text-align: center;">Total Sales</th>
                        <th style="text-align: right;">Total Revenue</th>
                        <th style="text-align: right;">Avg per Sale</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topProducts as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product['title'] }}</td>
                            <td style="text-align: center;">{{ number_format($product['sales']) }}</td>
                            <td style="text-align: right;">${{ number_format($product['revenue'], 2) }}</td>
                            <td style="text-align: right;">${{ $product['sales'] > 0 ? number_format($product['revenue'] / $product['sales'], 2) : '0.00' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">No product sales data available for this period</div>
        @endif
    </div>

    <!-- Sales by Country -->
    <div class="section">
        <div class="section-title">🌍 Sales by Country</div>

        @if($topCountries->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Country</th>
                        <th style="text-align: center;">Total Sales</th>
                        <th style="text-align: right;">Total Revenue</th>
                        <th style="text-align: right;">Avg per Sale</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topCountries as $index => $country)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $country['country'] }}</td>
                            <td style="text-align: center;">{{ number_format($country['total_sales']) }}</td>
                            <td style="text-align: right;">${{ number_format($country['total_revenue'], 2) }}</td>
                            <td style="text-align: right;">${{ $country['total_sales'] > 0 ? number_format($country['total_revenue'] / $country['total_sales'], 2) : '0.00' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">No geographic sales data available for this period</div>
        @endif
    </div>

    <!-- Traffic Sources -->
    <div class="section">
        <div class="section-title">🚦 Traffic Sources</div>

        @if($trafficSources && isset($trafficSources['sources']) && count($trafficSources['sources']) > 0)
            @php
                $totalVisitors = collect($trafficSources['sources'])->sum('count');
            @endphp
            <table>
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Description</th>
                        <th style="text-align: center;">Visitors</th>
                        <th style="text-align: right;">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trafficSources['sources'] as $source)
                        @php
                            $percentage = $totalVisitors > 0 ? ($source['count'] / $totalVisitors) * 100 : 0;
                        @endphp
                        <tr>
                            <td><strong>{{ $source['name'] }}</strong></td>
                            <td>{{ $source['description'] }}</td>
                            <td style="text-align: center;">{{ number_format($source['count']) }}</td>
                            <td style="text-align: right;">{{ number_format($percentage, 1) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">No traffic source data available for this period</div>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- Recent Sales Transactions -->
    <div class="section">
        <div class="section-title">💳 Recent Sales (Last 50)</div>

        @if($sales->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Seller</th>
                        <th>Buyer</th>
                        <th style="text-align: right;">Price</th>
                        <th>Country</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                        <tr>
                            <td>#{{ $sale->id }}</td>
                            <td>{{ $sale->product->name ?? 'N/A' }}</td>
                            <td>{{ $sale->seller->username ?? 'N/A' }}</td>
                            <td>{{ $sale->user->username ?? 'N/A' }}</td>
                            <td style="text-align: right;">${{ number_format($sale->price, 2) }}</td>
                            <td>{{ $sale->country ?? '-' }}</td>
                            <td>{{ $sale->created_at->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">No sales transactions in this period</div>
        @endif
    </div>

    <!-- Recent Users -->
    <div class="section">
        <div class="section-title">👥 Recent Users</div>

        @if($recentUsers->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th style="text-align: center;">Role</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentUsers as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td style="text-align: center;">
                                @if($user->is_seller)
                                    <span class="badge badge-info">Seller</span>
                                @else
                                    <span class="badge badge-success">User</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">No new users registered in this period</div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>© {{ date('Y') }} {{ config('app.name') }} - Admin Dashboard Report</p>
        <p>This is a confidential document. Generated on {{ $exportDate }}</p>
        <script type="text/php">
            if (isset($pdf)) {
                $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
                $size = 8;
                $font = $fontMetrics->getFont("DejaVu Sans");
                $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                $x = ($pdf->get_width() - $width) / 2;
                $y = $pdf->get_height() - 35;
                $pdf->page_text($x, $y, $text, $font, $size);
            }
        </script>
    </div>
</body>
</html>
