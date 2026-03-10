<?php

use Livewire\Component;
use App\Models\RestaurantTable;
use Mary\Traits\Toast;

new class extends Component
{
    use Toast;
    public $selectedId = null;
    public $name = '';
    public $status = 'available';
    public $tableStatus = true;
    public $tables = [];
    public $headers = [
        ['key' => 'name', 'label' => 'Table Name'],
        ['key' => 'status', 'label' => 'Status'],
    ];

    public $showInputForm;
    public $showConfirmDeleteModal;
    //mount gets executed when the component is loaded
    public function mount(){
        $this->showInputForm = false;
        $this->showConfirmDeleteModal = false;
        $this->getTables();
    }
    public function getTables()
    {
        //select * from restaurant_tables
        $this->tables = RestaurantTable::all();
        //select * from restaurant_tables limit 3
        // $this->tables = RestaurantTable::paginate(3);
    }
    public function store()
    {
        try{
            //check the status
            // $this->status = $this->tableStatus ? 'available' : 'occupied';
            if($this->tableStatus == true) {
                $this->status = 'available';
            } else {
                $this->status = 'occupied';
            }
            //insert into restaurant_tables (name,status)
            //values ($this->name, $this->status)
            RestaurantTable::create([
                'name' => $this->name,
                'status' => $this->status,
            ]);
            $this->getTables(); //get the records
             $this->toast(
                type: 'success',
                css: 'bg-white text-gray-700',
                title: 'Record saved successfully!',
             );
        }catch(\Exception $e){
            $this->toast(
                type: 'error',
                css: 'bg-red-200 text-gray-700',
                title: 'Error saving record!',
                description: $e->getMessage(),
            );
        }
    }
    public function confirmDelete($id)
    {
        $this->showConfirmDeleteModal = true;
    }

    public function edit($id)
    {
        $this->showInputForm = true;
        $this->selectedId = $id;

        //select * from restaurant_tables where id = $id
        $table = RestaurantTable::find($id); //use find if searching by primary key
        $this->name = $table->name;
        $this->status = $table->status;
        $this->tableStatus = $table->status == 'available' ? true : false;
    }
};
?>

<div>
    <div class="flex justify-center" >
        
    </div>

    <x-modal wire:model="showInputForm" title="Table">
        <x-form wire:submit="store" 
            class=" bg-white p-4 rounded-2xl shadow-2xl">
            <x-input wire:model="name" label="Name" />
            <x-toggle label="Available" wire:model="tableStatus" right />
            <div class="flex justify-between">
                <x-button label="Close" @click="$wire.showInputForm = false" />
                <x-button label="Save" class="btn-primary" type="submit" spinner="save" />
            </div>
        </x-form>
    </x-modal>

    <x-button icon="o-plus" label="Add New Table" @click="$wire.showInputForm = true" />

    <x-table :headers="$headers"  :rows="$tables">
        @scope('cell_status', $table)
            @if($table->status == 'available')
                <x-badge value="Available" class="badge-success" />
            @else
                <x-badge value="Occupied" class="badge-error" />
            @endif
        @endscope

       
        @scope('actions', $table)
            <div class="flex gap-4">
                <x-button icon="o-pencil" class="text-blue-500" wire:click="edit({{ $table->id }})" />
                <x-button icon="o-trash" class="text-red-500" wire:click="confirmDelete({{ $table->id }})" />
            </div>
        @endscope
    </x-table>
    

    <x-modal wire:model="showConfirmDeleteModal" title="Delete" class="backdrop-blur">
    Are you sure you want to delete this table?

    <x-slot:actions>
        <x-button label="No" @click="$wire.showConfirmDeleteModal = false" />
        <x-button label="Yes" class="btn-error" @click="$wire.showConfirmDeleteModal = false" />
    </x-slot:actions>
</x-modal>
</div>