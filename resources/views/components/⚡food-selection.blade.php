<?php

use App\Models\MenuItem;
use App\Models\Category;
use Livewire\Component;
use App\Models\Order;
use App\Models\OrderItem;
use Mary\Traits\Toast;

new class extends Component
{
    use Toast;
    public $tableSession = [];
    public $menus = [];
    public $originalMenus = [];
    public $categories = [];
    public $search = '';
    public $selectedCategoryId = null;
    public $itemsOrdered = [];
    public $notes='';
    public function mount(){
        //get the table session from the session
        $this->tableSession = session('table_selection');
        //if the table is not selected then go back
        if(!$this->tableSession){
            return redirect()->route('pos.table-selection');
        }
        //get the menus
        $this->menus = MenuItem::where('is_available', true)
            ->orderBy('name','asc')
            ->get();
        $this->originalMenus = $this->menus;
        //get the categories
        $this->categories = Category::where('is_active', true)->get();
    }

    public function updatedSearch(){
        
        $this->menus = $this->originalMenus->filter(function($menu) {
            return stripos($menu->name, $this->search) !== false;
        });
    }
    public function selectCategory($categoryId){
        $this->selectedCategoryId = $categoryId;
        if(!$categoryId) {
            $this->menus = $this->originalMenus;
            return;
        }
        // Filter menus by category
        $this->menus = $this->originalMenus->filter(function($menu) use ($categoryId) {
            return $menu->category_id == $categoryId;
        });
    }

    public function addItem($menuId){
        //check if the item is already in the list
        if(isset($this->itemsOrdered[$menuId])){
            $this->itemsOrdered[$menuId]['quantity']++;
            $this->itemsOrdered[$menuId]['line_total'] = $this->itemsOrdered[$menuId]['line_total'] + $this->itemsOrdered[$menuId]['unit_price'];
        }else{
            $menu = MenuItem::find($menuId);
            $this->itemsOrdered[$menuId] = [
                'name' => $menu->name,
                'menu_item_id' => $menuId,
                'quantity' => 1,
                'unit_price' => $menu->price,
                'line_total' => $menu->price,
            ];
        }
    }
    public function removeItem($menuId){
        if(isset($this->itemsOrdered[$menuId])){
            $this->itemsOrdered[$menuId]['quantity']--;
            $this->itemsOrdered[$menuId]['line_total'] = $this->itemsOrdered[$menuId]['line_total'] - $this->itemsOrdered[$menuId]['unit_price'];
            if($this->itemsOrdered[$menuId]['quantity'] == 0){
                unset($this->itemsOrdered[$menuId]);
            }
        }
    }
    
    public function updatedItemsOrdered($value, $key)
    {
        // Extract menu ID from the key (e.g., "123.quantity" -> "123")
        $menuId = explode('.', $key)[0];
        
        if (!isset($this->itemsOrdered[$menuId])) {
            // Initialize if doesn't exist
            $menu = MenuItem::find($menuId);
            if ($menu && $value > 0) {
                $this->itemsOrdered[$menuId] = [
                    'name' => $menu->name,
                    'menu_item_id' => $menuId,
                    'quantity' => (int)$value,
                    'unit_price' => $menu->price,
                    'line_total' => $menu->price * (int)$value,
                ];
            }
        } else {
            // Update existing item
            $quantity = (int)($this->itemsOrdered[$menuId]['quantity'] ?? 0);
            
            if ($quantity <= 0) {
                unset($this->itemsOrdered[$menuId]);
            } else {
                $this->itemsOrdered[$menuId]['line_total'] = $this->itemsOrdered[$menuId]['unit_price'] * $quantity;
            }
        }
    }

    public function submitOrder(){
        try{
            \DB::beginTransaction();
            //create or update the orders table
            $order = Order::where('table_session_id', $this->tableSession['table_session_id'])
                ->where('status','pending')
                ->first();
            if($order){
                $order->update([
                    'sub_total' => array_sum(array_column($this->itemsOrdered, 'line_total')),
                    'notes' => $this->notes,
                ]);
            }else{
                $order = Order::create([
                    'table_session_id' => $this->tableSession['table_session_id'],
                    'sub_total' => array_sum(array_column($this->itemsOrdered, 'line_total')),
                    'notes' => $this->notes,
                    'user_id' => auth()->id(),
                ]);
            }

            //create the order items
            foreach($this->itemsOrdered as $item){
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                ]);
            }
            \DB::commit();
            $this->toast(
                title: 'Order submitted successfully',
                type: 'success'
            );
            return redirect()->route('pos.table-selection', $this->tableSession['table_session_id']);
        }catch(\Exception $e){
            \DB::rollBack();
            \Log::error($e->getMessage());
            $this->toast(
                title: $e->getMessage(),
                type: 'error'
            );
        }

    }
};
?>

<div>
    <x-card 
        title="{{$tableSession['table_name']}}" 
        subtitle="Number of Guests: {{$tableSession['guest_count']}}" 
        shadow 
        separator>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Search -->
                <div class="md:w-72">
                    <x-input 
                        wire:model.live.debounce.500ms="search" 
                        placeholder="Search menu..." 
                        icon="o-magnifying-glass" />
                </div>
                
                <!-- Category Pills -->
                <div class="flex-1 flex gap-2 overflow-x-auto pb-1 scrollbar-none">
                    <button 
                        wire:click="selectCategory(null)"
                        class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all duration-200
                            {{ !$selectedCategoryId 
                                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' 
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        All
                    </button>
                    @foreach($categories as $category)
                        <button 
                            wire:click="selectCategory({{ $category->id }})"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-500
                                {{ $selectedCategoryId == $category->id 
                                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' 
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="col-span-2 border-r-2 border-gray-200">
                @foreach($menus as $menu)
                    <x-card title="{{$menu->name}}" shadow separator>
                        <div  class="flex items-center gap-2">
                            <span class="text-lg font-semibold flex-1">{{$menu->price}}</span>
                            <div class="flex items-center gap-2">
                                <x-button 
                                    wire:click="removeItem({{$menu->id}})"
                                    icon="o-minus" 
                                    class="btn-circle btn-outline
                                    bg-rose-400 text-white" />
                                <x-input 
                                    wire:model.live="itemsOrdered.{{ $menu->id }}.quantity"
                                    type="number" 
                                    min="0" 
                                    value="{{ $itemsOrdered[$menu->id]['quantity'] ?? 0 }}" 
                                    class="w-16" />
                                <x-button 
                                    wire:click="addItem({{$menu->id}})"
                                    icon="o-plus" 
                                    class="btn-circle btn-outline
                                    bg-emerald-400 text-white" />
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>
            <div class="col-span-1">
                Summary 
                @foreach($itemsOrdered as $item)
                    <div class="flex flex-col gap-2 py-2 border-b border-gray-200">
                        <span class="text-md font-semibold flex-1">{{ $item['name'] }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold flex-1">{{ $item['quantity'] }} x {{ $item['unit_price'] }}</span>
                            <span class="text-sm font-semibold">{{ $item['line_total'] }}</span>
                        </div>
                    </div>
                @endforeach
                @if(count($itemsOrdered) > 0)
                    <div class="flex items-center gap-2 mt-4">
                        <span class="text-lg font-semibold flex-1">Total</span>
                        <span class="text-lg font-semibold">{{ number_format(array_sum(array_column($itemsOrdered, 'line_total')),2) }}</span>
                    </div>
                    <x-textarea label="Instructions (if any)" wire:model="notes" />
                    <x-button label="Submit Order" wire:click="submitOrder" class="btn-primary w-full mt-2" />
                @endif
            </div>
        </div>
    </x-card>
</div>
    </x-card>
</div>