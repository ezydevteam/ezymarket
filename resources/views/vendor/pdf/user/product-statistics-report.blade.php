<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <title>{{ translate('Product Statistics Report') }} - {{ $product->name }}</title>
    <style>
        @page {
            margin: 1cm;
        }

        * {
            box-sizing: border-box;
            padding: 0;
        }

        body, table, td, th, div {
            font-family: 'Hind Siliguri', 'DejaVu Sans', sans-serif !important;
        }

        body {
            font-family: 'Hind Siliguri', 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.5;
            background: #ffffff;
        }

        /* Header Styles */
        .report-header {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 25px;
            margin-bottom: 35px;
        }

        .header-table {
            width: 100%;
            display: table;
        }

        .header-cell {
            display: table-cell;
            vertical-align: bottom;
        }

        .report-title {
            font-size: 22px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .product-name {
            font-size: 14px;
            color: #4f46e5;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .product-id {
            font-size: 10px;
            color: #64748b;
            font-weight: 400;
        }

        .meta-info {
            text-align: right;
            font-size: 10px;
            color: #475569;
        }

        /* Summary Grid */
        .metrics-grid {
            width: 100%;
            display: table;
            margin-bottom: 45px;
            border-spacing: 12px 0;
            margin-left: -12px;
            margin-right: -12px;
        }

        .metric-card {
            display: table-cell;
            width: 25%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px 15px;
            text-align: left;
        }

        .metric-label {
            font-size: 9px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 10px;
        }

        .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
        }

        /* Section Titles */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
        }

        .data-table th {
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            padding: 12px 10px;
            background: #f1f5f9;
            border-bottom: 2px solid #e2e8f0;
        }

        .data-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
            color: #334155;
            vertical-align: middle;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table .row-highlight {
            font-weight: 600;
            color: #0f172a;
        }

        /* Utilities */
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .page-break { page-break-after: always; }

        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 10px;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
        }

        .no-data {
            padding: 50px;
            text-align: center;
            background: #f8fafc;
            border-radius: 8px;
            color: #475569;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="report-wrapper">
        {{-- Header --}}
        <div class="report-header">
            <table class="header-table">
                <tr>
                    <td class="header-cell">
                        <div class="report-title">{{ translate('Product Statistics Report') }}</div>
                        <div class="product-name">{{ $product->name }}</div>
                        <div class="product-id">#{{ $product->id }}</div>
                    </td>
                    <td class="header-cell meta-info">
                        <div>{{ translate('Date Range') }}: <strong>{{ $period }}</strong></div>
                        <div>{{ $startDate->format('M d, Y') }} — {{ $endDate->format('M d, Y') }}</div>
                        <div style="margin-top: 4px;">{{ translate('Generated') }}: {{ now()->format('M d, Y H:i') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Summary Metrics --}}
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">{{ translate('Total Sales') }}</div>
                <div class="metric-value">{{ number_format($counters['total_sales']) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">{{ translate('Sales Earnings') }}</div>
                <div class="metric-value">{{ getAmount($counters['total_sales_amount']) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">{{ translate('Net Revenue') }}</div>
                <div class="metric-value">{{ getAmount($counters['total_earnings']) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">{{ translate('Total Views') }}</div>
                <div class="metric-value">{{ number_format($counters['total_views']) }}</div>
            </div>
        </div>

        {{-- Geographic Statistics --}}
        <div class="section">
            <div class="section-title">{{ translate('Sales by Location') }}</div>
            @if($salesByCountry->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">{{ translate('Country') }}</th>
                            <th class="text-center" style="width: 20%;">{{ translate('Sales') }}</th>
                            <th class="text-right" style="width: 20%;">{{ translate('Total Earnings') }}</th>
                            <th class="text-right" style="width: 20%;">{{ translate('% of Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalSales = $salesByCountry->sum('total_sales'); @endphp
                        @foreach($salesByCountry as $country)
                            <tr>
                                <td class="row-highlight">{{ $country->country }}</td>
                                <td class="text-center">{{ $country->total_sales }}</td>
                                <td class="text-right">{{ getAmount($country->total_earnings) }}</td>
                                <td class="text-right">{{ number_format(($country->total_sales / $totalSales) * 100, 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">{{ translate('No geographic sales data recorded in this period.') }}</div>
            @endif
        </div>

        {{-- Traffic Sources --}}
        <div class="section">
            <div class="section-title">{{ translate('Top Traffic Sources') }}</div>
            @if($referrals->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 60%;">{{ translate('Referrer Source') }}</th>
                            <th class="text-center" style="width: 20%;">{{ translate('Views') }}</th>
                            <th class="text-right" style="width: 20%;">{{ translate('% of Views') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($referrals as $referral)
                            <tr>
                                <td>{{ $referral->referrer ?: translate('Direct / Unknown') }}</td>
                                <td class="text-center">{{ number_format($referral->total_views) }}</td>
                                <td class="text-right">{{ $totalViews > 0 ? number_format(($referral->total_views / $totalViews) * 100, 1) : 0 }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">{{ translate('No referral data available for this period.') }}</div>
            @endif
        </div>

        <div class="page-break"></div>

        {{-- Recent Sales Table --}}
        <div class="section">
            <div class="section-title">{{ translate('Recent Sales Transactions') }}</div>
            @if($sales->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">{{ translate('ID') }}</th>
                            <th style="width: 40%;">{{ translate('Buyer') }}</th>
                            <th class="text-right" style="width: 15%;">{{ translate('Price') }}</th>
                            <th class="text-right" style="width: 15%;">{{ translate('Earnings') }}</th>
                            <th class="text-center" style="width: 15%;">{{ translate('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            <tr>
                                <td>#{{ $sale->id }}</td>
                                <td class="row-highlight">{{ $sale->user->username ?? translate('Guest') }}</td>
                                <td class="text-right">{{ getAmount($sale->price) }}</td>
                                <td class="text-right" style="color: #166534; font-weight: 600;">{{ getAmount($sale->seller_earning) }}</td>
                                <td class="text-center">{{ $sale->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">{{ translate('No sales transactions found for this period.') }}</div>
            @endif
        </div>
    </div>

    <div class="footer">
        {{ translate('Generated by') }} {{ settings('general')->site_name }} — {{ translate('Confidential Statistics Report') }}
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 30;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>
