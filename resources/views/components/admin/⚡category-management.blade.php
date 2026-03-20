<?php

use Livewire\Component;
use App\Models\Category;
use Mary\Traits\Toast;
new class extends Component
{
    use Toast;
    public $search = '';
    public $categories = [];
    public $selectedCategoryId = null;
    public $isEditing = false;
    public $showInputForm = false;
    public $showConfirmDeleteModal = false;
    public $name = '';
    public $is_active = true;
    public $searchCategory = '';
    public $headers = [
        ['key' => 'name', 'label' => 'Name'],
        ['key' => 'is_active', 'label' => 'Status'],
        ['key' => 'actions', 'label' => 'Actions'],
    ];
    public function mount(){
        $this->resetForm();
    }
    public function resetForm(){
        $this->name = '';
        $this->is_active = true;
        $this->showInputForm = false;
        $this->isEditing = false;
        $this->showConfirmDeleteModal = false;
        $this->selectedCategoryId = null;
    }

    //renders the page
    public function render(){
        if($this->searchCategory){
            $this->categories = Category::where('name', 'like', '%'.$this->searchCategory.'%')->get();
        }else{
            $this->categories = Category::all();
        }
        return view('components.admin.⚡category-management');
    }
    public function create()
    {
        $this->isEditing = false;
        $this->showInputForm = true;
    }
    
    public function store(){
        //validate the form
        $this->validate([
            'name' => 'required|string|max:20|min:3|unique:categories,name',
            'is_active' => 'required|boolean',
        ]);
        
        try{
            //save the form
            $category = new Category();
            $category->name = $this->name;
            $category->is_active = $this->is_active;
            $category->save();
            $this->resetForm();
            //show success message
            $this->toast(
                type: 'success',
                css: 'bg-white text-gray-700',
                title: 'Category saved successfully!',
            );
        }catch(\Exception $e){
            //show error message
            $this->toast(
                type: 'error',
                css: 'bg-white text-gray-700',
                title: $e->getMessage(),
            );
        }
    }
    public function edit($id){
        $this->selectedCategoryId = $id;
        $this->isEditing = true;
        $this->showInputForm = true;
       
        //$category = Category::where('id','=',$id)->first();
        $category = Category::find($id);
        $this->name = $category->name;
        $this->is_active =(bool) $category->is_active;
        // if($category->is_active == 0){
        //     $this->is_active = false;
        // }else{
        //     $this->is_active = true;
        // }
    }
    public function update(){
        //validate the form
        $this->validate([
            'name' => 'required|string|max:20|min:3|unique:categories,name,' . $this->selectedCategoryId,
            'is_active' => 'required|boolean',
        ]);
        
        try{
            //save the form
            $category = Category::find($this->selectedCategoryId);
            $category->name = $this->name;
            $category->is_active = $this->is_active;
            $category->save();
            $this->resetForm();
            //show success message
            $this->toast(
                type: 'success',
                css: 'bg-white text-gray-700',
                title: 'Category updated successfully!',
            );
        }catch(\Exception $e){
            //show error message
            $this->toast(
                type: 'error',
                css: 'bg-white text-gray-700',
                title: $e->getMessage(),
            );
        }
    }
    
    public function confirmDelete($id){
        $this->selectedCategoryId = $id;
        $this->showConfirmDeleteModal = true;
    }
    public function delete(){
        try{
            Category::find($this->selectedCategoryId)->delete();
            $this->resetForm();
            //show success message
            $this->toast(
                type: 'success',
                title: 'Category deleted successfully!',
            );
        }catch(\Exception $e){
            //show error message
            $this->toast(
                type: 'error',
                css: 'bg-white text-gray-700',
                title: $e->getMessage(),
            );
        }
    }
};
?>

<div>
    <div class="flex justify-center" >
        
    </div>

    <div class="flex gap-4">
        <x-button icon="o-plus" label="Add New Category" wire:click="create" />
        <x-input wire:model.live.debounce.500ms="searchCategory" 
            placeholder="Search categories..." 
            icon="o-magnifying-glass" 
            class="w-full" />
        {{-- <x-button icon="o-arrow-path" label="Search" wire:click="getTables" /> --}}
    </div>

    <x-table :headers="$headers"  :rows="$categories">
        @scope('cell_is_active', $category)
            @if($category->is_active)
                <x-badge value="Active" class="badge-success" />
            @else
                <x-badge value="Inactive" class="badge-error" />
            @endif
        @endscope
        @scope('actions', $category)
            <div class="flex gap-4">
                <x-button icon="o-pencil" class="bg-indigo-400 text-white btn-circle" wire:click="edit({{ $category->id }})" />
                <x-button icon="o-trash" class="text-white bg-red-400 btn-circle" wire:click="confirmDelete({{ $category->id }})" />
            </div>
        @endscope
    </x-table>
    
     <x-modal wire:model="showInputForm" title="Category">
        <x-form wire:submit="{{ $isEditing == true ? 'update' : 'store' }}" 
            class=" bg-white p-4 rounded-2xl shadow-2xl">
            <x-input wire:model="name" label="Name" />
            <x-toggle label="Active" wire:model="is_active" right />
            <div class="flex justify-between">
                <x-button label="Close" @click="$wire.showInputForm = false" />
                <x-button label="{{ $isEditing == true ? 'Update' : 'Save' }}" 
                class="btn-primary" type="submit" spinner="save" />
            </div>
        </x-form>
    </x-modal>
    <x-modal wire:model="showConfirmDeleteModal" title="Delete" class="backdrop-blur">
        Are you sure you want to delete this category?

        <x-slot:actions>
            <x-button label="No" @click="$wire.showConfirmDeleteModal = false" />
            <x-button label="Yes" class="btn-error" wire:click="delete" />
        </x-slot:actions>
    </x-modal>
</div>