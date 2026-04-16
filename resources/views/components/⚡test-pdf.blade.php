<?php

use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
new class extends Component
{
    public array $bill = [
        'subtotal' => 200,
        'tax' => 36,
        'grandTotal' => 236,
        'invoice_no' => 'TEST-INVOICE-1',
        'invoice_date' => '16th April-2026',
        'due_date' => '23-04-2026',
        'customer_name' => 'Gordon Kynsai Nongkynrih',
        'customer_address' => 'Shillong, Meghalaya, India',
        'customer_phone' => '+91 9876543210',
        'items' => [
            [
                'name' => 'Website Development',
                'qty' => 1,
                'price' => 15000,
            ],
            [
                'name' => 'Hosting Setup',
                'qty' => 1,
                'price' => 2500,
            ],
            [
                'name' => 'Maintenance Support',
                'qty' => 2,
                'price' => 1500,
            ],
        ],
        'notes' => 'Thank you for your business.',
    ];
   

    public function generatePdf()
    {
        $bill = $this->bill;
        return response()->streamDownload(function () use ($bill) {
            echo Pdf::loadView('pdf.invoice', $bill)->output();
        }, 'invoice.pdf', ['Content-Type' => 'application/pdf']);
    }
};
?>

<div class="min-h-screen bg-slate-100 py-10 px-4">
    <div class="mx-auto max-w-5xl rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
        <div class="border-b border-slate-200 px-8 py-6">
            <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-800">Sample Bill</h1>
                    <p class="mt-2 text-sm text-slate-500">Professional invoice generated with Laravel Livewire</p>
                </div>

                <div class="text-sm text-slate-600 space-y-1">
                    <p><span class="font-semibold text-slate-800">Invoice No:</span> {{ $bill['invoice_no'] }}</p>
                    <p><span class="font-semibold text-slate-800">Invoice Date:</span> {{ $bill['invoice_date'] }}</p>
                    <p><span class="font-semibold text-slate-800">Due Date:</span> {{ $bill['due_date'] }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 px-8 py-6 md:grid-cols-2">
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">From</h2>
                <div class="mt-3 space-y-1 text-sm text-slate-700">
                    <p class="font-semibold text-slate-800">ABC Tech Solutions</p>
                    <p>Laitumkhrah, Shillong</p>
                    <p>Meghalaya - 793003</p>
                    <p>support@abctech.com</p>
                </div>
            </div>

            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Bill To</h2>
                <div class="mt-3 space-y-1 text-sm text-slate-700">
                    <p class="font-semibold text-slate-800">{{ $bill['customer_name'] }}</p>
                    <p>{{ $bill['customer_address'] }}</p>
                    <p>{{ $bill['customer_phone'] }}</p>
                </div>
            </div>
        </div>

        <div class="px-8 pb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Item</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-600">Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Rate</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($bill['items'] as $index => $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $index + 1 }}</td>
                                <td class="px-4 py-4 text-sm font-medium text-slate-800">{{ $item['name'] }}</td>
                                <td class="px-4 py-4 text-center text-sm text-slate-600">{{ $item['qty'] }}</td>
                                <td class="px-4 py-4 text-right text-sm text-slate-600">₹ {{ number_format($item['price'], 2) }}</td>
                                <td class="px-4 py-4 text-right text-sm font-semibold text-slate-800">
                                    ₹ {{ number_format($item['qty'] * $item['price'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid gap-6 border-t border-slate-200 px-8 py-6 md:grid-cols-2">
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Notes</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    {{ $bill['notes'] }}
                </p>
            </div>

            <div class="md:ml-auto md:w-full md:max-w-sm">
                <div class="space-y-3 rounded-xl bg-slate-50 p-5">
                    <div class="flex items-center justify-between text-sm text-slate-600">
                        <span>Subtotal</span>
                        <span>₹ {{ number_format($bill['subtotal'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-slate-600">
                        <span>Tax (18%)</span>
                        <span>₹ {{ number_format($bill['tax'], 2) }}</span>
                    </div>
                    <div class="border-t border-slate-200 pt-3">
                        <div class="flex items-center justify-between text-base font-bold text-slate-800">
                            <span>Total</span>
                            <span>₹ {{ number_format($bill['grandTotal'], 2) }}</span>
                        </div>
                    </div>
                    <x-button
                        wire:click="generatePdf"
                     class="btn-primary w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
  <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1"/>
  <path d="M4.603 12.087a.8.8 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.7 7.7 0 0 1 1.482-.645 20 20 0 0 0 1.062-2.227 7.3 7.3 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.187-.012.395-.047.614-.084.51-.27 1.134-.52 1.794a11 11 0 0 0 .98 1.686 5.8 5.8 0 0 1 1.334.05c.364.065.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.86.86 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.7 5.7 0 0 1-.911-.95 11.6 11.6 0 0 0-1.997.406 11.3 11.3 0 0 1-1.021 1.51c-.29.35-.608.655-.926.787a.8.8 0 0 1-.58.029m1.379-1.901q-.25.115-.459.238c-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361q.016.032.026.044l.035-.012c.137-.056.355-.235.635-.572a8 8 0 0 0 .45-.606m1.64-1.33a13 13 0 0 1 1.01-.193 12 12 0 0 1-.51-.858 21 21 0 0 1-.5 1.05zm2.446.45q.226.244.435.41c.24.19.407.253.498.256a.1.1 0 0 0 .07-.015.3.3 0 0 0 .094-.125.44.44 0 0 0 .059-.2.1.1 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a4 4 0 0 0-.612-.053zM8.078 5.8a7 7 0 0 0 .2-.828q.046-.282.038-.465a.6.6 0 0 0-.032-.198.5.5 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822q.036.167.09.346z"/>
</svg>

Download PDF
                    </x-button>
                </div>
            </div>
        </div>
    </div>
</div>