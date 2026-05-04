<!DOCTYPE html>
<html lang="{{ getLocale() }}" dir="{{ getDirection() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ translate('License Certificate - :product_name', ['product_name' => $purchase->product->name]) }}</title>
    <link rel="icon" href="{{ asset($themeSettings->general->favicon) }}">

    @themeInclude('includes.styles')

    <style>
        :root {
            --cert-bg: #ffffff;
            --cert-border: #e2e8f0;
            --cert-accent: #3b82f6;
            --cert-text: #1e293b;
            --cert-muted: #64748b;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: var(--cert-text);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .certificate-wrapper {
            padding: 3rem 1rem;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .certificate-card {
            background: var(--cert-bg);
            width: 100%;
            max-width: 850px;
            position: relative;
            padding: 4rem;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        /* Decorative Double Border */
        .certificate-card::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 2px solid var(--cert-border);
            border-radius: 8px;
            pointer-events: none;
        }

        .certificate-card::after {
            content: '';
            position: absolute;
            top: 22px;
            left: 22px;
            right: 22px;
            bottom: 22px;
            border: 1px solid var(--cert-border);
            border-radius: 6px;
            pointer-events: none;
        }

        /* Watermark */
        .cert-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            opacity: 0.03;
            z-index: 0;
            width: 60%;
            pointer-events: none;
        }

        .cert-content {
            position: relative;
            z-index: 1;
        }

        .cert-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .cert-logo {
            height: 40px;
            margin-bottom: 1.5rem;
        }

        .cert-title {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
            color: #0f172a;
            text-transform: uppercase;
        }

        .cert-subtitle {
            color: var(--cert-muted);
            font-size: 1rem;
            font-weight: 400;
        }

        .cert-badge {
            display: inline-block;
            padding: 0.4rem 1.2rem;
            background: var(--cert-accent);
            color: white;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .info-group {
            margin-bottom: 1.5rem;
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--cert-muted);
            margin-bottom: 0.4rem;
            font-weight: 600;
            display: block;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: var(--cert-text);
        }

        .info-value a {
            color: var(--cert-accent);
            text-decoration: none;
        }

        .purchase-code-box {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: 8px;
            font-family: monospace;
            font-size: 1.1rem;
            color: #334155;
            border: 1px dashed #cbd5e1;
            margin-top: 0.5rem;
            text-align: center;
        }

        .cert-footer {
            border-top: 1px solid #f1f5f9;
            padding-top: 2rem;
        }

        .company-info p {
            margin-bottom: 2px;
            font-size: 0.85rem;
            color: var(--cert-muted);
        }

        .print-btn-wrapper {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 100;
        }

        .btn-print {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-print:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            background: #fff;
        }

        @media print {
            body {
                background: white;
                padding: 0 !important;
            }

            .certificate-wrapper {
                padding: 0 !important;
            }

            .certificate-card {
                box-shadow: none !important;
                max-width: 100% !important;
                border-radius: 0 !important;
                padding: 2.5rem !important;
            }

            .print-btn-wrapper {
                display: none !important;
            }

            @page {
                size: portrait;
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="certificate-wrapper">
        <div class="certificate-card">
            <img src="{{ asset($themeSettings->general->logo_light) }}" class="cert-watermark" alt="watermark">

            <div class="cert-content">
                <header class="cert-header">
                    <img src="{{ asset($themeSettings->general->logo_light) }}"
                        alt="{{ @$settings->general->site_name }}" class="cert-logo">
                    <h1 class="cert-title">{{ translate('License Certificate') }}</h1>
                    <p class="cert-subtitle">{{ translate('This document certifies the legal purchase of the following license:') }}</p>
                    <div class="cert-badge">
                        {{ $purchase->isRegularLicense() ? translate('Regular License') : translate('Extended License') }}
                    </div>
                </header>

                <main class="info-grid">
                    <div class="left-col">
                        <div class="info-group">
                            <span class="info-label">{{ translate("Licensor's Username") }}</span>
                            <span class="info-value">{{ $purchase->seller ? ucfirst($purchase->seller->username) : translate('Verified Seller') }}</span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">{{ translate('Licensee Name') }}</span>
                            <span class="info-value font-bold fs-5">{{ $purchase->user->full_name }}</span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">{{ translate('Purchase Date') }}</span>
                            <span class="info-value">{{ dateFormat($purchase->created_at) }}</span>
                        </div>
                    </div>

                    <div class="right-col">
                        <div class="info-group">
                            <span class="info-label">{{ translate('Product Name') }}</span>
                            <span class="info-value">{{ $purchase->product->name }}</span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">{{ translate('Product ID') }}</span>
                            <span class="info-value">#{{ $purchase->product->id }}</span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">{{ translate('Purchase ID') }}</span>
                            <span class="info-value">
                                #{{ $purchase->id }}
                            </span>
                        </div>
                    </div>
                </main>

                <div class="purchase-code-section mb-5">
                    <span class="info-label text-center">{{ translate('Product Purchase Code') }}</span>
                    <div class="purchase-code-box">
                        {{ $purchase->code }}
                    </div>
                </div>

                <footer class="cert-footer">
                    <div class="company-info text-center">
                        <p class="fw-bold text-dark">{{ @$settings->general->site_name }}</p>
                        <p class="mb-0">{{ @$settings->general->contact_email }}</p>
                        <p>{{ translate('Official digital license document issued by platform authority.') }}</p>
                        <p class="text-xs fst-italic opacity-75 mb-0">{{ translate('This is a system generated file, no signature needed.') }}</p>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    {{-- Floating Print Button --}}
    <div class="print-btn-wrapper">
        <button class="btn btn-print shadow-sm" onclick="window.print()">
            <i class="bi bi-printer me-2"></i>
            {{ translate('Download / Print') }}
        </button>
    </div>

    @themeInclude('includes.scripts')
</body>

</html>
