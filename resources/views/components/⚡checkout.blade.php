<?php
use App\Models\TableSession;
use Livewire\Component;
use App\Models\Order;
new class extends Component
{
    public $tables = [];
    public $selectedTableSessionId = null;
    public $selectedOrderId = null;
    public $orderedItems = [];
    public $showOrders = false;
    public $tableName = '';
    public $subTotal = 0;
    public $discount = 0;
    public $totalAmount = 0;
    public $isPayNow = false;
    public $paymentMethod = '';
    public function mount(){
        $this->loadTable();
    }

    public function loadTable(){
        $this->tables = TableSession::where('status','occupied')->get();
    }
    public function updatedDiscount(){
        if(!$this->discount){
            $this->discount = 0;
        }
        $this->totalAmount = $this->subTotal - $this->discount;
    }
    
    public function loadOrders($tableSessionId){
        // Load orders for the table
        $this->selectedTableSessionId = $tableSessionId;
        $data = Order::where('table_session_id', $tableSessionId)
            ->where('status','pending')
            ->first();
        foreach($data->orderItems as $orderItem){
            $this->orderedItems[$orderItem->menu_item_id] = [
                'name' => $orderItem->menuItem->name,
                'menu_item_id' => $orderItem->menu_item_id,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->unit_price,
                'line_total' => $orderItem->line_total,
            ];
        }
        $this->tableName = $data?->tableSession?->restaurantTable?->name ?? '';
        $this->showOrders = true;
        $this->subTotal = $data->sub_total;
        $this->totalAmount = $data->sub_total;
        $this->selectedOrderId = $data->id;
    }
    
    public function cancelPayment(){
        $this->isPayNow = false;
        $this->showOrders = false;
        $this->totalAmount = 0;
        $this->paymentMethod = '';
    }
    
    public function payNow(){
        $this->isPayNow = true;
    }
    
    public function confirmPayment(){
        // Validate payment method is selected
        if (!$this->paymentMethod) {
            return;
        }
        
        // Process payment logic here
        try{
            // Update order status, table session, etc.
            \DB::beginTransaction();
            $order = Order::find($this->selectedOrderId);
            $order->status = 'paid';
            $order->discount = $this->discount;
            $order->total = $this->totalAmount;
            // $order->payment_method = $this->paymentMethod;
            $order->save();

            //update the order items
            foreach($order->orderItems as $orderItem){
                $orderItem->status='paid';
                $orderItem->save();
            }

            //update the table session
            $order->tableSession->status = 'closed';
            $order->tableSession->save();

            //update the restaurant table
            $order->tableSession->restaurantTable->status = 'available';
            $order->tableSession->restaurantTable->save();
            \DB::commit();
            $this->loadTable();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
        
        $this->cancelPayment();
    }
};
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
        <p class="text-gray-500 mt-1">Select a table to process payment</p>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @foreach($tables as $table)
            <div 
                wire:click="loadOrders({{ $table->id }})"
                class="group relative cursor-pointer rounded-2xl p-6 transition-all duration-200 hover:scale-105 hover:shadow-lg bg-gradient-to-br from-indigo-500 to-purple-600 border-2 border-indigo-400"
            >
                <!-- Table Icon -->
                <div class="flex justify-center mb-3">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <x-icon name="o-table-cells" class="w-6 h-6 text-white" />
                    </div>
                </div>
                
                <!-- Table Info -->
                <div class="text-center">
                    <h3 class="font-semibold text-white text-lg">{{ $table->restaurantTable->name }}</h3>
                    <p class="text-xs mt-1 text-indigo-100">Click to checkout</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Order Details Modal -->
    <x-modal wire:model="showOrders" class="backdrop-blur" persistent>
        @if(false == $isPayNow)
            <div class="p-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $tableName }}</h2>
                        <p class="text-sm text-gray-500 mt-1">Review order and apply discount</p>
                    </div>
                    <button wire:click="cancelPayment" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <x-icon name="o-x-mark" class="w-6 h-6" />
                    </button>
                </div>

                <!-- Order Items -->
                <div class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                    @foreach($orderedItems as $item)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $item['name'] }}</p>
                                <p class="text-sm text-gray-500">{{ $item['quantity'] }} × ${{ number_format($item['unit_price'], 2) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">${{ number_format($item['line_total'], 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Summary Section -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-6 space-y-4">
                    <!-- Subtotal -->
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-semibold text-gray-900">${{ number_format($subTotal, 2) }}</span>
                    </div>

                    <!-- Discount Input -->
                    <div class="flex items-center gap-4">
                        <label class="text-gray-600 flex-shrink-0">Discount</label>
                        <x-input 
                            type="number" 
                            wire:model.live="discount" 
                            placeholder="0.00"
                            prefix="$"
                            class="flex-1"
                        />
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-300 pt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-semibold text-gray-900">Total Amount</span>
                            <span class="text-2xl font-bold text-indigo-600">${{ number_format($totalAmount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mt-6">
                    <x-button 
                        label="Cancel" 
                        wire:click="cancelPayment" 
                        class="flex-1 btn-outline"
                    />
                    <x-button 
                        icon="o-credit-card" 
                        class="flex-1 btn-primary bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border-0" 
                        label="Pay Now" 
                        wire:click="payNow" 
                    />
                </div>
            </div>
        @else
            <!-- Payment Method Selection -->
            <div class="p-6">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 mb-4 shadow-lg">
                        <x-icon name="o-credit-card" class="w-8 h-8 text-white" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Select Payment Method</h3>
                    <p class="text-gray-500 mt-1">Total Amount: <span class="font-semibold text-gray-900">${{ number_format($totalAmount, 2) }}</span></p>
                </div>

                <!-- Payment Options -->
                <div class="space-y-3 mb-8">
                    <!-- Cash Payment -->
                    <label class="relative flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all duration-200
                        {{ $paymentMethod === 'cash' 
                            ? 'border-green-500 bg-green-50 shadow-md' 
                            : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                        <input 
                            type="radio" 
                            wire:model.live="paymentMethod" 
                            value="cash" 
                            class="sr-only"
                        />
                        <div class="flex items-center flex-1">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl {{ $paymentMethod === 'cash' ? 'bg-green-100' : 'bg-gray-100' }}">
                                <x-icon name="o-banknotes" class="w-6 h-6 {{ $paymentMethod === 'cash' ? 'text-green-600' : 'text-gray-600' }}" />
                            </div>
                            <div class="ml-4">
                                <p class="font-semibold text-gray-900">Cash Payment</p>
                                <p class="text-sm text-gray-500">Pay with physical cash</p>
                            </div>
                        </div>
                        @if($paymentMethod === 'cash')
                            <div class="flex items-center justify-center w-6 h-6 rounded-full bg-green-500">
                                <x-icon name="o-check" class="w-4 h-4 text-white" />
                            </div>
                        @endif
                    </label>

                    <!-- Online Payment -->
                    <label class="relative flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all duration-200
                        {{ $paymentMethod === 'online' 
                            ? 'border-blue-500 bg-blue-50 shadow-md' 
                            : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                        <input 
                            type="radio" 
                            wire:model.live="paymentMethod" 
                            value="online" 
                            class="sr-only"
                        />
                        <div class="flex items-center flex-1">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl {{ $paymentMethod === 'online' ? 'bg-blue-100' : 'bg-gray-100' }}">
                                <x-icon name="o-device-phone-mobile" class="w-6 h-6 {{ $paymentMethod === 'online' ? 'text-blue-600' : 'text-gray-600' }}" />
                            </div>
                            <div class="ml-4">
                                <p class="font-semibold text-gray-900">Online Payment</p>
                                <p class="text-sm text-gray-500">Pay via UPI, Card, or Wallet</p>
                            </div>
                        </div>
                        @if($paymentMethod === 'online')
                            <div class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-500">
                                <x-icon name="o-check" class="w-4 h-4 text-white" />
                            </div>
                        @endif
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <x-button 
                        label="Cancel" 
                        wire:click="cancelPayment" 
                        class="flex-1 btn-outline"
                    />
                    <x-button 
                        label="Continue" 
                        wire:click="confirmPayment" 
                        class="flex-1 btn-primary {{ !$paymentMethod ? 'opacity-50 cursor-not-allowed' : '' }}"
                        icon="o-arrow-right"
                        :disabled="!$paymentMethod"
                        spinner="confirmPayment"
                    />
                </div>
            </div>
        @endif
    </x-modal>
</div>
