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
    public $search='';
    public $tableStatus = true;
    public $tables = [];
    public $headers = [
        ['key' => 'name', 'label' => 'Table Name'],
        ['key' => 'status', 'label' => 'Status'],
    ];
    public $isEditing;
    public $showInputForm;
    public $showConfirmDeleteModal;
    //mount gets executed when the component is loaded
    public function mount(){
        $this->showInputForm = false;
        $this->showConfirmDeleteModal = false;
        $this->isEditing = false;
        $this->search = '';
        $this->getTables();
    }
   
    //this function is called when the search input is updated
    public function updatedSearch()
    {
        $this->getTables();
    }
    public function getTables()
    {
        //select * from restaurant_tables
        // $this->tables = RestaurantTable::all();
        //select * from restaurant_tables limit 3
        // $this->tables = RestaurantTable::paginate(3);
        
        if(!empty($this->search)) {
            //select * from restaurant_tables where name like '%search%'
            $this->tables = RestaurantTable::where('name', 'like', '%'.$this->search.'%')->get();
        } else {
            $this->tables = RestaurantTable::all();
        }
    }
    public function create()
    {
        $this->showInputForm = true;
        $this->isEditing = false;
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
        $this->selectedId = $id; //store the id of the record to be deleted
    }

    public function edit($id)
    {
        $this->showInputForm = true;
        $this->selectedId = $id;
        $this->isEditing = true;
        //select * from restaurant_tables where id = $id
        $table = RestaurantTable::find($id); //use find if searching by primary key
        if(!$table) { //if the table does not exist show error
            $this->toast(
                type: 'error',
                css: 'bg-red-200 text-gray-700',
                title: 'Error updating record!',
                description: 'Table not found!',
            );
            $this->isEditing = false;
            $this->showInputForm = false;
            return;
        }
        $this->name = $table->name;
        $this->status = $table->status;
        $this->tableStatus = $table->status == 'available' ? true : false;
        $this->isEditing = true;
    }

    public function update()
    {
        // RestaurantTable::where('id', $this->selectedId)->update([
        //     'name' => $this->name,
        //     'status' => $this->status,
        // ]);
        //select * from restaurant_tables where id = $this->selectedId limit 1
        $table = RestaurantTable::where('id', $this->selectedId)->first();
        if(!$table) { //if the table does not exist show error
            $this->toast(
                type: 'error',
                css: 'bg-red-200 text-gray-700',
                title: 'Error updating record!',
                description: 'Table not found!',
            );
            return;
        }
        $table->name = $this->name;
        $table->status = $this->status;
        $table->save();

        $this->getTables();
        $this->showInputForm = false;
        $this->isEditing = false;
        $this->toast(
            type: 'success',
            css: 'bg-white text-gray-700',
            title: 'Record updated successfully!',
        );
    }
    public function delete()
    {
        RestaurantTable::where('id', $this->selectedId)->delete();
        $this->getTables();
        $this->showConfirmDeleteModal = false;
        $this->toast(
            type: 'success',
            css: 'bg-white text-gray-700',
            title: 'Record deleted successfully!',
        );
    }
};
?>

<div>
    <div class="flex justify-center" >
        
    </div>

    <div class="flex gap-4">
        <x-button icon="o-plus" label="Add New Table" wire:click="create" />
        <x-input wire:model.live.debounce.300ms="search" placeholder="Search tables..." icon="o-magnifying-glass" />
        {{-- <x-button icon="o-arrow-path" label="Search" wire:click="getTables" /> --}}
    </div>

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
                <x-button icon="o-pencil" class="bg-indigo-400 text-white btn-circle" wire:click="edit({{ $table->id }})" />
                <x-button icon="o-trash" class="text-white bg-red-400 btn-circle" wire:click="confirmDelete({{ $table->id }})" />
            </div>
        @endscope
    </x-table>
    
     <x-modal wire:model="showInputForm" title="Table">
        <x-form wire:submit="{{ $isEditing == true ? 'update' : 'store' }}" 
            class=" bg-white p-4 rounded-2xl shadow-2xl">
            <x-input wire:model="name" label="Name" />
            <x-toggle label="Available" wire:model="tableStatus" right />
            <div class="flex justify-between">
                <x-button label="Close" @click="$wire.showInputForm = false" />
                <x-button label="{{ $isEditing == true ? 'Update' : 'Save' }}" class="btn-primary" type="submit" spinner="save" />
            </div>
        </x-form>
    </x-modal>
    <x-modal wire:model="showConfirmDeleteModal" title="Delete" class="backdrop-blur">
    Are you sure you want to delete this table?

    <x-slot:actions>
        <x-button label="No" @click="$wire.showConfirmDeleteModal = false" />
        <x-button label="Yes" class="btn-error" wire:click="delete" />
    </x-slot:actions>
</x-modal>
</div>