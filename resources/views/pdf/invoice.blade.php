<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Bill</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f5f9;
            color: #334155;
        }

        .page-wrapper {
            min-height: 100vh;
            padding: 40px 16px;
            background: #f1f5f9;
        }

        .bill-card {
            max-width: 1024px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 25px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .section {
            padding: 24px 32px;
        }

        .border-bottom {
            border-bottom: 1px solid #e2e8f0;
        }

        .border-top {
            border-top: 1px solid #e2e8f0;
        }

        .header-flex,
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
        }

        .header-flex.stack-mobile,
        .two-column.stack-mobile {
            flex-wrap: wrap;
        }

        .title {
            margin: 0;
            font-size: 30px;
            line-height: 1.2;
            font-weight: 700;
            letter-spacing: -0.025em;
            color: #1e293b;
        }

        .subtitle {
            margin-top: 8px;
            margin-bottom: 0;
            font-size: 14px;
            color: #64748b;
        }

        .meta {
            font-size: 14px;
            color: #475569;
        }

        .meta p {
            margin: 0 0 4px;
        }

        .label-strong {
            font-weight: 600;
            color: #1e293b;
        }

        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .section-label {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }

        .address-block {
            margin-top: 12px;
            font-size: 14px;
            color: #334155;
        }

        .address-block p {
            margin: 0 0 4px;
        }

        .address-block .name {
            font-weight: 600;
            color: #1e293b;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 100%;
        }

        thead {
            background: #f8fafc;
        }

        thead th {
            padding: 12px 16px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }

        tbody td {
            padding: 16px;
            font-size: 14px;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .item-name {
            font-weight: 500;
            color: #1e293b;
        }

        .amount-strong {
            font-weight: 600;
            color: #1e293b;
        }

        .notes-text {
            margin-top: 12px;
            margin-bottom: 0;
            font-size: 14px;
            line-height: 1.7;
            color: #475569;
        }

        .totals-box {
            margin-left: auto;
            max-width: 380px;
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
        }

        .totals-box .summary-row {
            align-items: center;
            font-size: 14px;
            color: #475569;
            margin-bottom: 12px;
        }

        .totals-box .summary-row:last-child {
            margin-bottom: 0;
        }

        .total-divider {
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            margin-top: 8px;
        }

        .grand-total {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
        }

        @media (max-width: 768px) {
            .section {
                padding: 20px;
            }

            .header-flex,
            .summary-row {
                flex-direction: column;
                align-items: stretch;
            }

            .two-column {
                grid-template-columns: 1fr;
            }

            .totals-box {
                margin-left: 0;
                max-width: 100%;
            }

            .title {
                font-size: 26px;
            }
        }

        @media print {
            body {
                background: #ffffff;
            }

            .page-wrapper {
                padding: 0;
                min-height: auto;
                background: #ffffff;
            }

            .bill-card {
                box-shadow: none;
                border: 1px solid #d1d5db;
                border-radius: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="bill-card">
            <div class="section border-bottom">
                <div class="header-flex stack-mobile">
                    <div>
                        <h1 class="title">Sample Bill</h1>
                        <p class="subtitle">Professional invoice generated with Laravel Livewire</p>
                    </div>

                    <div class="meta">
                        <p><span class="label-strong">Invoice No:</span> {{ $invoice_no }}</p>
                        <p><span class="label-strong">Invoice Date:</span> {{ $invoice_date }}</p>
                        <p><span class="label-strong">Due Date:</span> {{ $due_date }}</p>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="two-column stack-mobile">
                    <div>
                        <h2 class="section-label">From</h2>
                        <div class="address-block">
                            <p class="name">ABC Tech Solutions</p>
                            <p>Laitumkhrah, Shillong</p>
                            <p>Meghalaya - 793003</p>
                            <p>support@abctech.com</p>
                        </div>
                    </div>

                    <div>
                        <h2 class="section-label">Bill To</h2>
                        <div class="address-block">
                            <p class="name">{{ $customer_name }}</p>
                            <p>{{ $customer_address }}</p>
                            <p>{{ $customer_phone }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section" style="padding-top: 0;">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="text-left">#</th>
                                <th class="text-left">Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Rate</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $index => $item)
                                <tr>
                                    <td class="text-left">{{ $index + 1 }}</td>
                                    <td class="text-left item-name">{{ $item['name'] }}</td>
                                    <td class="text-center">{{ $item['qty'] }}</td>
                                    <td class="text-right">Rs {{ number_format($item['price'], 2) }}</td>
                                    <td class="text-right amount-strong">
                                        Rs {{ number_format($item['qty'] * $item['price'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="section border-top">
                <div class="two-column stack-mobile">
                    <div>
                        <h2 class="section-label">Notes</h2>
                        <p class="notes-text">{{ $notes }}</p>
                    </div>

                    <div>
                        <div class="totals-box">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>Rs {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="summary-row">
                                <span>Tax (18%)</span>
                                <span>Rs {{ number_format($tax, 2) }}</span>
                            </div>
                            <div class="total-divider">
                                <div class="summary-row grand-total">
                                    <span>Total</span>
                                    <span>Rs {{ number_format($grandTotal, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>