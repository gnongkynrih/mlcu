<?php
use App\Models\RestaurantTable;
use App\Models\TableSession;
use Livewire\Component;
use Mary\Traits\Toast;

new class extends Component
{
    use Toast;
    
    public $tables = [];
    public $selectedTableId = null;
    public $selectedTable = null;
    public $showNumberOfGuests = false;
    public $numberOfGuest = 1;
    public $isExistingSession = false;
    
    public function mount()
    {
        $this->loadTables();
    }
    
    public function loadTables()
    {
        $this->tables = RestaurantTable::withCount(['tableSessions' => function($q) {
            $q->where('status', 'occupied');
        }])->get();
    }
    
    public function selectTable($tableId)
    {
        $this->selectedTableId = $tableId;
        $this->selectedTable = RestaurantTable::find($tableId);
        
        $existingSession = TableSession::where('restaurant_table_id', $tableId)
            ->where('status', 'occupied')
            ->first();
        
        if ($existingSession) {
            $this->numberOfGuest = $existingSession->guest_count;
            $this->isExistingSession = true;
        } else {
            $this->numberOfGuest = 1;
            $this->isExistingSession = false;
        }
        
        $this->showNumberOfGuests = true;
    }
    
    public function closeModal()
    {
        $this->showNumberOfGuests = false;
        $this->selectedTableId = null;
        $this->selectedTable = null;
        $this->isExistingSession = false;
    }
    
    public function confirmNumberOfGuests()
    {
        $this->validate([
            'numberOfGuest' => 'required|integer|min:1|max:20',
        ]);
        
        $session = TableSession::where('restaurant_table_id', $this->selectedTableId)
            ->where('status', 'occupied')
            ->first();
        
        if ($session) {
            $session->update(['guest_count' => $this->numberOfGuest]);
        } else {
            $session = TableSession::create([
                'restaurant_table_id' => $this->selectedTableId,
                'guest_count' => $this->numberOfGuest,
                'user_id' => auth()->id(),
                'status' => 'occupied',
            ]);
            
            $this->selectedTable->update(['status' => 'occupied']);
        }

        session()->put('table_selection', [
            'table_session_id' => $session->id,
            'table_name' => $this->selectedTable->name,
            'guest_count' => $this->numberOfGuest,
        ]);
        
        $this->toast(
            type: 'success',
            title: 'Table ' . $this->selectedTable->name . ' selected',
        );
        
        return redirect()->route('pos.food-selection');
    }
};
?>

<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Select a Table</h1>
        <p class="text-gray-500 mt-1">Choose a table to start taking orders</p>
    </div>
    
    <!-- Legend -->
    <div class="flex items-center gap-6 mb-6">
        <div class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-emerald-500"></span>
            <span class="text-sm text-gray-600">Available</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-rose-500"></span>
            <span class="text-sm text-gray-600">Occupied</span>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @foreach($tables as $table)
            @php
                $isOccupied = $table->status === 'occupied';
            @endphp
            <div 
                wire:click="selectTable({{ $table->id }})"
                class="group relative cursor-pointer rounded-2xl p-6 transition-all duration-200 hover:scale-105 hover:shadow-lg
                    {{ $isOccupied 
                        ? 'bg-gradient-to-br from-purple-50 to-rose-100 border-2 border-rose-200 hover:border-rose-300' 
                        : 'bg-gradient-to-br from-emerald-50 to-emerald-100 border-2 border-emerald-200 hover:border-emerald-300' 
                    }}"
            >
                <!-- Status Indicator -->
                <div class="absolute top-3 right-3">
                    <span class="flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $isOccupied ? 'bg-rose-400' : 'bg-emerald-400' }}"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 {{ $isOccupied ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                    </span>
                </div>
                
                <!-- Table Icon -->
                <div class="flex justify-center mb-3">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $isOccupied ? 'bg-rose-200 text-rose-600' : 'bg-emerald-200 text-emerald-600' }}">
                        <x-icon name="o-table-cells" class="w-6 h-6" />
                    </div>
                </div>
                
                <!-- Table Info -->
                <div class="text-center">
                    <h3 class="font-semibold text-gray-900">{{ $table->name }}</h3>
                    <p class="text-xs mt-1 {{ $isOccupied ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $isOccupied ? 'Occupied' : 'Available' }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Guest Count Modal -->
    <x-modal wire:model="showNumberOfGuests" class="backdrop-blur">
        <div class="p-2">
            <!-- Modal Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-100 to-indigo-600 mb-4 shadow-lg">
                    <x-icon name="o-users" class="w-7 h-7 text-white" />
                </div>
                <h3 class="text-xl font-bold text-gray-900">
                    {{ $isExistingSession ? 'Update Guests' : 'Number of Guests' }}
                </h3>
                @if($selectedTable)
                    <p class="text-gray-500 mt-1">{{ $selectedTable->name }}</p>
                @endif
            </div>
            
            <x-form wire:submit="confirmNumberOfGuests" class="space-y-6">
                <x-input 
                    type="number" 
                    min="1" 
                    max="20"
                    wire:model="numberOfGuest"
                    placeholder="Enter number of guests"
                    icon="o-user-group"
                    hint="Maximum 20 guests per table"
                />
                
                <x-slot:actions>
                    <x-button label="Cancel" wire:click="closeModal" />
                    <x-button 
                        type="submit" 
                        label="{{ $isExistingSession ? 'Update and Continue' : 'Continue' }}" 
                        class="btn-primary" 
                        icon="o-arrow-right"
                        spinner="confirmNumberOfGuests"
                    />
                </x-slot:actions>
            </x-form>
        </div>
    </x-modal>
</div>
