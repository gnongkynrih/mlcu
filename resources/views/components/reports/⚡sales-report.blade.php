<?php

use Livewire\Component;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

new class extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';

    public $orders = [];
    public $totalRevenue = 0;
    public $totalDiscount = 0;
    public $totalOrders = 0;
    public $topItems = [];

    public function mount()
    {
        $this->dateFrom = Carbon::today()->toDateString();
        $this->dateTo = Carbon::today()->toDateString();
        $this->loadReport();
    }

    public function loadReport()
    {
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();

        $query = Order::with(['tableSession.restaurantTable', 'orderItems.menuItem'])
            ->where('status', 'paid')
            ->whereBetween('updated_at', [$from, $to])
            ->orderByDesc('updated_at');

        $this->orders = $query->get();
        $this->totalOrders = $this->orders->count();
        $this->totalRevenue = $this->orders->sum('total');
        $this->totalDiscount = $this->orders->sum('discount');

        $this->topItems = OrderItem::with('menuItem')
            ->whereIn('order_id', $this->orders->pluck('id'))
            ->get()
            ->groupBy('menu_item_id')
            ->map(fn($items) => [
                'name' => $items->first()->menuItem->name ?? 'Unknown',
                'qty' => $items->sum('quantity'),
                'total' => $items->sum('line_total'),
            ])
            ->sortByDesc('qty')
            ->take(5)
            ->values();
    }

    public function exportPdf()
    {
        $data = [
            'orders' => $this->orders,
            'totalRevenue' => $this->totalRevenue,
            'totalDiscount' => $this->totalDiscount,
            'totalOrders' => $this->totalOrders,
            'topItems' => $this->topItems,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ];

        return response()->streamDownload(function () use ($data) {
            echo Pdf::loadView('pdf.sales-report', $data)->output();
        }, 'sales-report-' . $this->dateFrom . '-to-' . $this->dateTo . '.pdf', ['Content-Type' => 'application/pdf']);
    }
};
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Sales Report</h1>
            <p class="text-gray-500 mt-1">View paid orders within a date range</p>
        </div>
        @if(count($orders) > 0)
            <x-button 
                label="Export PDF" 
                icon="o-arrow-down-tray" 
                wire:click="exportPdf"
                class="btn-primary"
                spinner="exportPdf"
            />
        @endif
    </div>

    <!-- Date Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <x-input 
                type="date" 
                label="From" 
                wire:model="dateFrom"
                class="flex-1"
            />
            <x-input 
                type="date" 
                label="To" 
                wire:model="dateTo"
                class="flex-1"
            />
            <x-button 
                label="Generate Report" 
                icon="o-magnifying-glass"
                wire:click="loadReport" 
                class="btn-primary mb-1"
                spinner="loadReport"
            />
        </div>
    </div>

    @if(count($orders) > 0)
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                        <x-icon name="o-shopping-bag" class="w-6 h-6 text-indigo-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalOrders }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <x-icon name="o-banknotes" class="w-6 h-6 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">₹{{ number_format($totalRevenue, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-100 flex items-center justify-center">
                        <x-icon name="o-tag" class="w-6 h-6 text-rose-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Discounts</p>
                        <p class="text-2xl font-bold text-gray-900">₹{{ number_format($totalDiscount, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Orders Table -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Orders</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-6 py-3 text-left">Table</th>
                                <th class="px-6 py-3 text-left">Date & Time</th>
                                <th class="px-6 py-3 text-right">Sub Total</th>
                                <th class="px-6 py-3 text-right">Discount</th>
                                <th class="px-6 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $order->tableSession?->restaurantTable?->name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($order->updated_at)->format('d M Y, h:i A') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-700">
                                        ₹{{ number_format($order->sub_total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-rose-600">
                                        @if($order->discount > 0) -₹{{ number_format($order->discount, 2) }} @else — @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                        ₹{{ number_format($order->total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                            <tr>
                                <td colspan="4" class="px-6 py-4 font-bold text-gray-900 text-right">Grand Total</td>
                                <td class="px-6 py-4 text-right font-bold text-indigo-600 text-lg">₹{{ number_format($totalRevenue, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Top Selling Items -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Top Selling Items</h2>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($topItems as $i => $item)
                        <div class="px-6 py-4 flex items-center gap-4">
                            <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold flex items-center justify-center flex-shrink-0">
                                {{ $i + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 truncate">{{ $item['name'] }}</p>
                                <p class="text-sm text-gray-500">{{ $item['qty'] }} sold</p>
                            </div>
                            <span class="font-semibold text-gray-900 text-sm">₹{{ number_format($item['total'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    @else
        <!-- Empty State -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gray-100 mb-4">
                <x-icon name="o-chart-bar" class="w-8 h-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900">No sales found</h3>
            <p class="text-gray-500 mt-1">No paid orders found for the selected date range.</p>
        </div>
    @endif
</div>