<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <title>{{ translate(':site_name Performance Analytics', ['site_name' => getSiteName()]) }} - {{ $period }}</title>
    <style>
        @page {
            margin: 1cm;
        }

        * {
            box-sizing: border-box;
            padding: 0;
        }

        body,
        table,
        td,
        th,
        div {
            font-family: 'Hind Siliguri', 'DejaVu Sans', sans-serif !important;
        }

        body {
            font-family: 'Hind Siliguri', 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.5;
            background: #ffffff;
        }

        .header {
            background: #1e293b;
            color: white;
            padding: 30px 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 26px;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .header .subtitle {
            font-size: 13px;
            opacity: 0.8;
            font-weight: 300;
        }

        .section {
            padding: 0 30px;
            margin-bottom: 35px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            border-spacing: 10px;
        }

        .summary-row {
            display: table-row;
        }

        .summary-box {
            display: table-cell;
            width: 25%;
            padding: 20px 15px;
            text-align: center;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
        }

        .summary-box .label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .summary-box .value {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th {
            background: #f1f5f9;
            color: #475569;
            padding: 12px 10px;
            text-align: left;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 9px;
            color: #334155;
        }

        table tbody tr:nth-child(even) {
            background: #fbfcfd;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .page-break {
            page-break-after: always;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            padding: 10px;
            border-top: 1px solid #f1f5f9;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            background: #f1f5f9;
            color: #475569;
            border-radius: 20px;
            font-size: 8px;
            font-weight: 600;
        }

        .highlight-container {
            background: #f0fdfa;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #ccfbf1;
            margin-top: 10px;
        }

        .highlight-label {
            font-size: 10px;
            color: #0d9488;
            text-transform: uppercase;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <table style="width: 100%; border-bottom: 1px solid #e2e8f0; padding-bottom: 25px; margin-bottom: 30px;">
        <tr style="text-align: center; margin-bottom: 10px;">
            <td colspan="2">
                <img src="{{ getSiteLogo() }}" alt="{{ getSiteName() }}" style="height: 45px; margin-bottom: 2px;">
                <div
                    style="font-size: 18px; font-weight: 700; text-transform: uppercase; color: #0f172a; margin-bottom: 5px;">
                    {{ translate('Seller Performance Report') }}
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div style="color: #334155; font-size: 10px;">
                    <span style="font-size: 12px; font-weight: 600; color: #1e293b;">{{ $user->full_name }}</span><br>
                    <strong>{{ translate('Username') }}:</strong> {{ $user->username }}<br>
                    <strong>{{ translate('Email') }}:</strong> {{ $user->email }}<br>
                    <strong>{{ translate('Seller ID') }}:</strong> #{{ $user->id }}<br>
                </div>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right;">
                <div style="font-size: 10px; color: #334155;">
                    <strong>{{ translate('Generation Date') }}:</strong> {{ now()->format('M d, Y H:i') }}<br>
                    <strong>{{ translate('Selection Period') }}:</strong> {{ $period }}<br>
                    <strong>{{ translate('Analytics Date Range') }}:</strong><br>
                    <span style="color: #0f172a; font-weight: 600;">{{ $startDate->format('M d, Y') }} — {{
                        $endDate->format('M d, Y') }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">{{ translate('Executive Summary') }}</div>

        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-box">
                    <span class="label">{{ translate('Total Sales') }}</span>
                    <div class="value">{{ $counters['total_sales'] }}</div>
                </div>
                <div class="summary-box">
                    <span class="label">{{ translate('Sales Earnings') }}</span>
                    <div class="value">{{ getAmount($counters['sales_earnings']) }}</div>
                </div>
                <div class="summary-box">
                    <span class="label">{{ translate('Referral Earnings') }}</span>
                    <div class="value">{{ getAmount($counters['referrals_earnings']) }}</div>
                </div>
                <div class="summary-box">
                    <span class="label">{{ translate('Total Views') }}</span>
                    <div class="value">{{ $counters['total_views'] }}</div>
                </div>
            </div>
        </div>

        <div class="highlight-container" style="text-align: center;">
            <div class="highlight-label">{{ translate('Net Earnings') }}</div>
            @php
            $netEarnings = $counters['sales_earnings'] + $counters['referrals_earnings'];
            @endphp
            <h2 class="mb-0">{{ getAmount($netEarnings ?? 0) }}</h2>
        </div>
    </div>

    <div class="page-break"></div>

    @if($sales->count() > 0)
    <div class="section">
        <div class="section-title">{{ translate('Sales Performance Detail') }}</div>
        <table>
            <thead>
                <tr>
                    <th>{{ translate('Sale ID') }}</th>
                    <th>{{ translate('Product') }}</th>
                    <th>{{ translate('Buyer') }}</th>
                    <th class="text-right">{{ translate('Earnings') }}</th>
                    <th class="text-center">{{ translate('Country') }}</th>
                    <th>{{ translate('Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>#{{ $sale->id }}</td>
                    <td><strong>{{ truncateText($sale->product->name ?? 'N/A', 50) }}</strong></td>
                    <td>{{ $sale->user->username ?? 'N/A' }}</td>
                    <td class="text-right"><strong>{{ getAmount($sale->seller_earning) }}</strong></td>
                    <td class="text-center">{{ $sale->country ?? '—' }}</td>
                    <td>{{ $sale->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="page-break"></div>

    <div class="section">
        <div class="section-title">{{ translate('Analytical Distribution') }}</div>
        <div class="summary-grid">
            <div class="summary-row">
                {{-- License Distribution --}}
                <div class="summary-box" style="width: 50%; text-align: left; padding: 20px;">
                    <span class="label"
                        style="margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">{{
                        translate('License Distribution') }}</span>
                    @php $totalLicenseSales = array_sum($licenseDistribution['data']); @endphp
                    @foreach($licenseDistribution['labels'] as $index => $label)
                    @php $val = $licenseDistribution['data'][$index]; $pct = $totalLicenseSales > 0 ? ($val /
                    $totalLicenseSales) * 100 : 0; @endphp
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="font-weight: 600;">{{ $label }}</span>
                            <span style="color: #64748b;">{{ $val }} ({{ $pct }}%)</span>
                        </div>
                        <div style="background: #e2e8f0; height: 6px; border-radius: 10px; overflow: hidden;">
                            <div style="background: #3b82f6; width: {{ $pct }}%; height: 100%;"></div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Sales Health --}}
                <div class="summary-box" style="width: 50%; text-align: left; padding: 20px;">
                    <span class="label"
                        style="margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">{{
                        translate('Sales Success vs Refunds') }}</span>
                    @php $totalRefundStats = array_sum($refundsDistribution['data']); @endphp
                    @foreach($refundsDistribution['labels'] as $index => $label)
                    @php $val = $refundsDistribution['data'][$index]; $pct = $totalRefundStats > 0 ? ($val /
                    $totalRefundStats) * 100 : 0; @endphp
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="font-weight: 600;">{{ $label }}</span>
                            <span style="color: #64748b;">{{ $val }} ({{ $pct }}%)</span>
                        </div>
                        <div style="background: #e2e8f0; height: 6px; border-radius: 10px; overflow: hidden;">
                            <div
                                style="background: {{ $index == 0 ? '#10b981' : '#f43f5e' }}; width: {{ $pct }}%; height: 100%;">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if($topSellingProducts->count() > 0)
    <div class="section">
        <div class="section-title">{{ translate('Product Efficiency (Top Selling)') }}</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">{{ translate('Rank') }}</th>
                    <th>{{ translate('Product Name') }}</th>
                    <th class="text-center">{{ translate('Units Sold') }}</th>
                    <th class="text-right">{{ translate('Total Earnings') }}</th>
                    <th class="text-right">{{ translate('Avg. per Sale') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topSellingProducts as $index => $product)
                <tr>
                    <td class="text-center">
                        <span class="badge">#{{ $index + 1 }}</span>
                    </td>
                    <td><strong>{{ $product['title'] }}</strong></td>
                    <td class="text-center">{{ $product['sales'] }}</td>
                    <td class="text-right"><strong>{{ getAmount($product['earnings']) }}</strong></td>
                    <td class="text-right">{{ getAmount($product['earnings'] / ($product['sales'] ?: 1)) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="page-break"></div>

    <div class="section">
        <div class="section-title">{{ translate('Global Reach & Acquisition') }}</div>
        <div style="display: table; width: 100%;">
            <div style="display: table-row;">
                {{-- Top Purchasing Countries --}}
                <div style="display: table-cell; width: 50%; vertical-align: top; padding-right: 15px;">
                    <h4 style="font-size: 11px; margin-bottom: 10px; color: #475569;">{{ translate('Top Purchasing
                        Countries') }}</h4>
                    @if($salesByCountry->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>{{ translate('Country') }}</th>
                                <th class="text-right">{{ translate('Earnings') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesByCountry as $country)
                            <tr>
                                <td><strong>{{ countries($country->country) }}</strong></td>
                                <td class="text-right"><strong>{{ getAmount($country->total_earnings) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div
                        style="padding: 20px; background: #f8fafc; border-radius: 8px; text-align: center; color: #94a3b8;">
                        {{ translate('No geographic data available') }}
                    </div>
                    @endif
                </div>

                {{-- Traffic Sources --}}
                <div style="display: table-cell; width: 50%; vertical-align: top; padding-left: 15px;">
                    <h4 style="font-size: 11px; margin-bottom: 10px; color: #475569;">{{ translate('Top Traffic
                        Sources') }}</h4>
                    @if($referrals->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>{{ translate('Source') }}</th>
                                <th class="text-right">{{ translate('Views') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referrals as $referral)
                            <tr>
                                <td>
                                    <div
                                        style="width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $referral->referrer ?: translate('Direct / Unknown') }}</div>
                                </td>
                                <td class="text-right"><strong>{{ $referral->total_views }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div
                        style="padding: 20px; background: #f8fafc; border-radius: 8px; text-align: center; color: #94a3b8;">
                        {{ translate('No referral data available') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div
            style="margin-bottom: 5px; font-weight: 700; color: #64748b; letter-spacing: 1px; text-transform: uppercase;">
            {{ translate('Confidential - Seller Performance Report') }}</div>
        {{ translate('Document generated by') }} {{ siteName() }} | {{ now()->format('F d, Y \a\t H:i') }}
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 7;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 30;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>

</html>
