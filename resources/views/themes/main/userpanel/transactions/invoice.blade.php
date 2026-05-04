<!DOCTYPE html>
<html lang="{{ getLocale() }}" dir="{{ getDirection() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ translate('Invoice #:number', ['number' => $trx->id]) }}</title>
    <link rel="icon" href="{{ asset($themeSettings->general->favicon) }}">
    @include('themes.main.includes.styles')
    <style>
        :root {
            --invoice-modern-bg: #ffffff;
            --invoice-modern-border: #e2e8f0;
            --invoice-modern-accent: var(--primary_color, #3b82f6);
            --invoice-modern-text: #1e293b;
            --invoice-modern-muted: #64748b;
        }

        body.invoice-modern-page {
            font-family: 'Outfit', 'Inter', sans-serif;
            background-color: #f8fafc;
            color: var(--invoice-modern-text);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice-modern-wrapper {
            padding: 3rem 1rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .invoice-modern-card {
            background: var(--invoice-modern-bg);
            width: 100%;
            max-width: 850px;
            position: relative;
            padding: 4rem;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        /* Decorative Double Border */
        .invoice-modern-card::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 2px solid var(--invoice-modern-border);
            border-radius: 8px;
            pointer-events: none;
        }

        .invoice-modern-card::after {
            content: '';
            position: absolute;
            top: 22px;
            left: 22px;
            right: 22px;
            bottom: 22px;
            border: 1px solid var(--invoice-modern-border);
            border-radius: 6px;
            pointer-events: none;
        }

        /* Watermarks */
        .invoice-modern-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            opacity: 0.03;
            z-index: 0;
            width: 60%;
            pointer-events: none;
        }

        .invoice-modern-paid-stamp {
            border: 3px dashed #10b981;
            color: #10b981;
            padding: 8px 15px;
            font-size: 2.5rem;
            font-weight: 900;
            text-transform: uppercase;
            border-radius: 10px;
            opacity: 0.3;
            user-select: none;
            pointer-events: none;
            letter-spacing: 2px;
            display: inline-block;
            transform: rotate(-10deg);
        }

        .invoice-modern-content {
            position: relative;
            z-index: 1;
        }

        .invoice-modern-header {
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px dashed var(--invoice-modern-border);
        }

        .table-invoice-modern thead th {
            background-color: #f8fafc;
            border-bottom: 2px solid var(--invoice-modern-border);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: var(--invoice-modern-muted);
            padding: 1rem;
        }

        .table-invoice-modern tbody td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .invoice-modern-total-box {
            background: #f8fafc;
            padding: 2rem;
            border-radius: 8px;
            border: 1px solid var(--invoice-modern-border);
        }

        .invoice-modern-print-btn-wrapper {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 100;
        }

        .invoice-modern-btn-print {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        /* Single Page Print Fix */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body.invoice-modern-page {
                background: white;
                padding: 0 !important;
            }

            .invoice-modern-wrapper {
                padding: 0 !important;
                display: block !important;
            }

            .invoice-modern-card {
                box-shadow: none !important;
                max-width: 100% !important;
                border-radius: 0 !important;
                padding: 2.5rem !important;
                /* Reduced padding for single page */
                margin: 0 !important;
                height: 100vh;
            }

            .invoice-modern-header {
                margin-bottom: 2rem !important;
                padding-bottom: 1.5rem !important;
            }

            .invoice-modern-card::before,
            .invoice-modern-card::after {
                display: none !important;
            }

            .invoice-modern-print-hidden {
                display: none !important;
            }

            .table-invoice-modern tbody td {
                padding: 0.75rem 1rem !important;
                /* Tighter rows for print */
            }

            .invoice-modern-total-box {
                padding: 1.5rem !important;
            }
        }
    </style>
</head>

<body class="invoice-modern-page">
    <div class="invoice-modern-wrapper">
        <div class="invoice-modern-card">
            <!-- Watermark Logo -->
            <img src="{{ asset($themeSettings->general->logo_light) }}" class="invoice-modern-watermark"
                alt="watermark">

            <div class="invoice-modern-content">
                <!-- Header Section -->
                <div class="invoice-modern-header">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-sm-6">
                            <img src="{{ asset($themeSettings->general->logo_light) }}"
                                alt="{{ @$settings->general->site_name }}" style="max-height: 40px;"
                                class="mb-3 flex-shrink-0">
                        </div>
                        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                            <h1 class="fw-bold ls-1 mb-1 text-uppercase" style="color: #0f172a; font-size: 2.5rem;">{{
                                translate('Invoice') }}</h1>
                            <div class="text-gray-700 small">
                                <span class="fw-bold text-dark">{{ translate('Invoice No') }}:</span> #{{ $trx->id
                                }}<br>
                                <span class="fw-bold text-dark">{{ translate('Date') }}:</span> {{
                                dateFormat($trx->created_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Grid -->
                <div class="row mb-5 g-4">
                    <div class="col-sm-6">
                        <h6 class="text-uppercase small fw-bold text-muted mb-3 tracking-wider">{{ translate('Bill To')
                            }}</h6>
                        @php $user = $trx->user; @endphp
                        <div>
                            <h5 class="fw-bold mb-1">{{ $user->full_name }}</h5>
                            <p class="text-gray-700 mb-0 small">{{ $user->address['line_1'] ?? '' }}</p>
                            @if ($user->address['line_2'] ?? null)
                            <p class="text-gray-700 mb-0 small">{{ $user->address['line_2'] }}</p>
                            @endif
                            <p class="text-gray-700 mb-0 small">
                                {{ $user->address['city'] ?? '' }}{{ isset($user->address['state']) ? ', ' .
                                $user->address['state'] : '' }} {{ $user->address['zip'] ?? '' }}
                            </p>
                            <p class="text-gray-700 mb-2 small">{{ $user->country_name }}</p>
                            <span class="badge bg-light text-gray-700 border fw-normal">{{ $user->email }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h6 class="text-uppercase small fw-bold text-muted mb-3 tracking-wider">{{ translate('Billed
                            From') }}</h6>
                        <div>
                            <h5 class="fw-bold mb-1">{{ @$settings->general->site_name }}</h5>
                            <p class="text-gray-700 mb-0 small">{{ @$settings->general->address }}</p>
                            <p class="text-gray-700 mb-2 small">{{ @$settings->general->contact_email }}</p>
                            <div class="text-xs fst-italic opacity-75 mt-2 fs-13">
                                {{ translate('Payment processed via :gateway', ['gateway' => $trx->paymentGateway ?
                                $trx->paymentGateway->name : 'System']) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-5">
                    <table class="table table-invoice-modern mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50%;">{{ translate('Description') }}</th>
                                <th class="text-center">{{ translate('Qty') }}</th>
                                <th class="text-center">{{ translate('Price') }}</th>
                                <th class="text-end">{{ translate('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($trx->isTypePurchase())
                            @foreach ($trx->trxProducts as $trxProduct)
                            @php
                            $product = $trxProduct->product;
                            $licenseType = $trxProduct->isRegularLicense() ? translate('Regular License') :
                            translate('Extended License');
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold mb-1" style="color: var(--invoice-modern-text);">{{
                                        $product->name }}</div>
                                    <span class="text-muted small">{{ $licenseType }}</span>
                                </td>
                                <td class="text-center">{{ $trxProduct->quantity }}</td>
                                <td class="text-center">{{ getAmount($trxProduct->price) }}</td>
                                <td class="text-end fw-bold" style="color: var(--invoice-modern-text);">{{
                                    getAmount($trxProduct->total) }}</td>
                            </tr>
                            @if ($trxProduct->support)
                            <tr>
                                <td class="ps-4">
                                    <div class="text-gray-700 small fw-medium">
                                        <i class="bi bi-arrow-return-right me-1 text-primary"></i>
                                        {{ translate('Product Support') }}: {{ $trxProduct->support->name }}
                                    </div>
                                </td>
                                <td class="text-center small text-gray-700">{{ $trxProduct->support->quantity }}</td>
                                <td class="text-center small text-gray-700">{{ getAmount($trxProduct->support->price) }}
                                </td>
                                <td class="text-end small fw-bold text-gray-700">{{
                                    getAmount($trxProduct->support->total) }}</td>
                            </tr>
                            @endif
                            @endforeach
                            @elseif($trx->isTypeSupportPurchase() || $trx->isTypeSupportExtend())
                            <tr>
                                <td>
                                    <div class="fw-bold mb-1" style="color: var(--invoice-modern-text);">
                                        {{ translate('Support :label', ['label' => $trx->isTypeSupportPurchase() ?
                                        'Purchase' : 'Extend']) }}
                                    </div>
                                    <span class="text-muted small">{{ $trx->support->name }}</span>
                                </td>
                                <td class="text-center">1</td>
                                <td class="text-center">{{ getAmount($trx->support->price) }}</td>
                                <td class="text-end fw-bold" style="color: var(--invoice-modern-text);">{{
                                    getAmount($trx->support->total) }}</td>
                            </tr>
                            @elseif($trx->isTypeDeposit())
                            <tr>
                                <td>
                                    <div class="fw-bold mb-1" style="color: var(--invoice-modern-text);">{{
                                        translate('Deposit Refill') }}</div>
                                    <span class="text-muted small">{{ translate('Direct wallet deposit') }}</span>
                                </td>
                                <td class="text-center">1</td>
                                <td class="text-center">{{ getAmount($trx->amount) }}</td>
                                <td class="text-end fw-bold" style="color: var(--invoice-modern-text);">{{
                                    getAmount($trx->amount) }}</td>
                            </tr>
                            @elseif($trx->isTypeSubscription())
                            <tr>
                                <td>
                                    <div class="fw-bold mb-1" style="color: var(--invoice-modern-text);">
                                        {{ translate('Premium Membership') }}
                                    </div>
                                    <span class="text-muted small">{{ $trx->package->name }} ({{
                                        $trx->package->getIntervalName() }})</span>
                                </td>
                                <td class="text-center">1</td>
                                <td class="text-center">{{ getAmount($trx->amount) }}</td>
                                <td class="text-end fw-bold" style="color: var(--invoice-modern-text);">{{
                                    getAmount($trx->amount) }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Totals Section -->
                <div class="row justify-content-end align-items-center">
                    <div class="col-sm-6 text-center text-sm-end pe-sm-4">
                        @if ($trx->isPaid())
                        <div class="invoice-modern-paid-stamp">{{ translate('PAID') }}</div>
                        @endif
                    </div>
                    <div class="col-sm-6 col-md-5">
                        <div class="invoice-modern-total-box">
                            @if ($trx->hasFees() || $trx->hasTax())
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-gray-700 small">{{ translate('Subtotal') }}</span>
                                <span class="fw-medium small">{{ getAmount($trx->amount) }}</span>
                            </div>
                            @if ($trx->hasTax())
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-gray-700 small">{{ $trx->tax->name }} ({{ $trx->tax->rate }}%)</span>
                                <span class="fw-medium small">{{ getAmount($trx->tax->amount) }}</span>
                            </div>
                            @endif
                            @if ($trx->hasFees())
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom dashed">
                                <span class="text-gray-700 small">{{ translate('Processing Fees') }}</span>
                                <span class="fw-medium small">{{ getAmount($trx->fees) }}</span>
                            </div>
                            @endif
                            @endif
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">{{ translate('Total Paid') }}</h5>
                                <h4 class="mb-0 fw-bold text-success">{{ getAmount($trx->total) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="mt-5 pt-4 border-top text-center opacity-75">
                    <p class="text-xs fst-italic mb-0" style="font-size: 0.75rem;">
                        {{ translate('This is a system generated official invoice for your transaction.') }}<br>
                        {{ translate('Thank you for choosing :site_name.', ['site_name' =>
                        @$settings->general->site_name]) }}
                    </p>
                </footer>
            </div>
        </div>

        <div class="text-center mt-4 invoice-modern-print-hidden">
            <a href="{{ route('user.transaction.index') }}" class="text-muted small hover-underline">
                <i class="bi bi-arrow-left me-1"></i> {{ translate('Back to Dashboard') }}
            </a>
        </div>
    </div>

    {{-- Floating Print Button --}}
    <div class="invoice-modern-print-btn-wrapper invoice-modern-print-hidden">
        <button class="btn invoice-modern-btn-print shadow-sm d-flex align-items-center" onclick="window.print()">
            <i class="bi bi-download me-2"></i>
            {{ translate('Save / Print Invoice') }}
        </button>
    </div>

    @include('themes.main.includes.scripts')
</body>

</html>
