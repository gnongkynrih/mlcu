<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #334155; background: #fff; padding: 32px; }
        h1 { font-size: 24px; font-weight: 700; color: #1e293b; }
        .subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 24px; }
        .meta { font-size: 12px; color: #475569; text-align: right; }
        .meta p { margin-bottom: 2px; }
        .summary { display: flex; gap: 16px; margin-bottom: 24px; }
        .card { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; }
        .card .label { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
        .card .value { font-size: 20px; font-weight: 700; color: #1e293b; margin-top: 4px; }
        .section-title { font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead th { background: #f1f5f9; padding: 9px 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; border-bottom: 1px solid #e2e8f0; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        tbody td { padding: 10px 12px; font-size: 12px; color: #475569; border-bottom: 1px solid #f1f5f9; }
        tfoot td { padding: 10px 12px; font-size: 13px; font-weight: 700; color: #1e293b; border-top: 2px solid #e2e8f0; background: #f8fafc; }
        .grand { color: #4f46e5; font-size: 15px; }
        .top-items table thead th, .top-items table tbody td { }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Sales Report</h1>
            <p class="subtitle">Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</p>
        </div>
        <div class="meta">
            <p><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary">
        <div class="card">
            <div class="label">Total Orders</div>
            <div class="value">{{ $totalOrders }}</div>
        </div>
        <div class="card">
            <div class="label">Total Revenue</div>
            <div class="value">&#8377;{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="card">
            <div class="label">Total Discounts</div>
            <div class="value">&#8377;{{ number_format($totalDiscount, 2) }}</div>
        </div>
    </div>

    <!-- Orders Table -->
    <p class="section-title">Orders</p>
    <table>
        <thead>
            <tr>
                <th class="text-left">#</th>
                <th class="text-left">Table</th>
                <th class="text-left">Date & Time</th>
                <th class="text-right">Sub Total</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $i => $order)
                <tr>
                    <td class="text-left">{{ $i + 1 }}</td>
                    <td class="text-left">{{ $order->tableSession?->restaurantTable?->name ?? '—' }}</td>
                    <td class="text-left">{{ \Carbon\Carbon::parse($order->updated_at)->format('d M Y, h:i A') }}</td>
                    <td class="text-right">&#8377;{{ number_format($order->sub_total, 2) }}</td>
                    <td class="text-right">{{ $order->discount > 0 ? '-&#8377;' . number_format($order->discount, 2) : '—' }}</td>
                    <td class="text-right">&#8377;{{ number_format($order->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">Grand Total</td>
                <td class="text-right grand">&#8377;{{ number_format($totalRevenue, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Top Selling Items -->
    @if(count($topItems) > 0)
        <p class="section-title">Top Selling Items</p>
        <table class="top-items">
            <thead>
                <tr>
                    <th class="text-left">#</th>
                    <th class="text-left">Item Name</th>
                    <th class="text-center">Qty Sold</th>
                    <th class="text-right">Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topItems as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td class="text-center">{{ $item['qty'] }}</td>
                        <td class="text-right">&#8377;{{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
