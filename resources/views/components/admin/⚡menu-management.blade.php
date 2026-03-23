<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\MenuItem;
use App\Models\Category;
use Mary\Traits\Toast;
new class extends Component
{
    use Toast;
    // Form properties
    public $menuItemId = null;
    public $category_id = '';
    public $name = '';
    public $description = '';
    public $price = '';
    public $is_available = true;
    
    // UI state
    public $isOpen = false;
    public $isEditing = false;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $filterCategory = '';
    
    // For delete confirmation
    public $itemToDelete = null;
    public $showDeleteModal = false;
    
    protected $rules = [
        'category_id' => 'required|exists:categories,id',
        'name' => 'required|min:2|max:50',
        'description' => 'nullable|max:500',
        'price' => 'required|numeric|min:0|max:1999.99',
        'is_available' => 'boolean',
    ];
    
    protected $messages = [
        'category_id.required' => 'Category cannot be blank.',
        'name.required' => 'Item name is required.',
        'name.min'=>'Name cannot be less than 2 characters.',
        'name.max'=>'Name cannot be more than 50 characters.',
        'price.required' => 'Price is required.',
        'price.numeric' => 'Price must be a valid number.',
    ];
    
    public function mount()
    {
        $this->resetForm();
    }
    
    public function resetForm()
    {
        $this->menuItemId = null;
        $this->category_id = '';
        $this->name = '';
        $this->description = '';
        $this->price = '';
        $this->is_available = true;
        $this->isEditing = false;
        $this->resetValidation();
    }
    
    public function openModal()
    {
        $this->resetForm();
        $this->isOpen = true;
    }
    
    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
    }
    
    public function edit($id)
    {
        $item = MenuItem::findOrFail($id);
        $this->menuItemId = $id;
        $this->category_id = $item->category_id;
        $this->name = $item->name;
        $this->description = $item->description;
        $this->price = $item->price;
        $this->is_available = $item->is_available;
        $this->isEditing = true;
        $this->isOpen = true;
    }
    
    public function save()
    {
        //validate the form
        $validated = $this->validate();
        
        if ($this->isEditing ==true) {
            $item = MenuItem::findOrFail($this->menuItemId);
            $item->update($validated);
            $this->toast(
                type:'success',
                title:'Menu item updated successfully'
            );
            // session()->flash('message', 'Menu item updated successfully!');
        } else {
            MenuItem::create($validated);
            // session()->flash('message', 'Menu item created successfully!');
            $this->toast(
                type:'success',
                title:'Menu item added successfully'
            );
        }
        
        $this->closeModal();
    }
    
    public function confirmDelete($id)
    {
        $this->itemToDelete = $id;
        $this->showDeleteModal = true;
    }
    
    public function delete()
    {
        if ($this->itemToDelete) {
            MenuItem::findOrFail($this->itemToDelete)->delete();
            $this->itemToDelete = null;
            $this->showDeleteModal = false;
            session()->flash('message', 'Menu item deleted successfully!');
            $this->dispatch('menuItemDeleted');
        }
    }
    
    public function cancelDelete()
    {
        $this->itemToDelete = null;
        $this->showDeleteModal = false;
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function toggleAvailability($id)
    {
        $item = MenuItem::findOrFail($id);
        $item->update(['is_available' => !$item->is_available]);
        session()->flash('message', 'Availability updated!');
    }
    
    //It is a hook / lifecycle method that lets you return extra data 
    // that should be automatically passed to the Blade view 
    // every time the component renders.
    public function with()
    {
        // $query = MenuItem::with('category')
        //     ->when($this->search, fn($q) => $q->where('name', 'like', '%'.$this->search.'%'))
        //     ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
        //     ->orderBy($this->sortField, $this->sortDirection);
        
            $query = MenuItem::with('category');
            //if user enter in the search text box, filter by name
            if($this->search){
                $query = $query->where('name', 'like', '%'.$this->search.'%');
            }
            //if user select a category from the filter dropdown, filter by category
            if($this->filterCategory){
                $query = $query->where('category_id', $this->filterCategory);
            }
            //sort by the selected field
            $query = $query->orderBy($this->sortField, $this->sortDirection);
        return [
            'menuItems' => $query->paginate(5),
            'categories' => Category::all(),
        ];
    }
};
?>

<div class="min-h-screen bg-gray-100 p-6">
    <!-- Flash Messages -->
    {{-- @if (session()->has('message'))
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => show = false, 3000)" 
             class="mb-4 rounded-lg bg-green-50 p-4 text-green-800 border border-green-200 shadow-sm transition-all duration-500" 
             role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('message') }}</span>
            </div>
        </div>
    @endif --}}

    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 mb-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Menu Management</h1>
                <p class="text-gray-500 mt-1 text-sm">Manage your restaurant menu items with ease</p>
            </div>
            <button wire:click="openModal" 
                    class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add New Item
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <x-input wire:model.live.debounce.500ms="search" 
                                placeholder="Search menu items..." 
                                icon="o-magnifying-glass" 
                                class="w-full" />
            </div>
            
            <x-select wire:model.live="filterCategory" 
                :options="$categories" 
                option-value="id" 
                option-label="name"
                placeholder="All Categories"
                class="w-full md:w-48" />
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80 backdrop-blur-sm">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors" wire:click="sortBy('name')">
                            <div class="flex items-center gap-1">
                                Item
                                @if($sortField === 'name')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors" wire:click="sortBy('category_id')">
                            <div class="flex items-center gap-1">
                                Category
                                @if($sortField === 'category_id')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors" wire:click="sortBy('price')">
                            <div class="flex items-center gap-1">
                                Price
                                @if($sortField === 'price')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($menuItems as $item)
                        <tr class="hover:bg-gray-50/80 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900">{{ $item->name }}</span>
                                    <span class="text-xs text-gray-500 line-clamp-1 max-w-xs">{{ Str::limit($item->description, 50) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $item->category->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-gray-900">${{ number_format($item->price, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="toggleAvailability({{ $item->id }})" 
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $item->is_available ? 'bg-green-500' : 'bg-gray-200' }}">
                                    <span class="sr-only">Toggle availability</span>
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $item->is_available ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $item->id }})" 
                                            class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-all duration-200" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" 
                                            class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-all duration-200" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <x-icon name="o-face-frown" class="w-16 h-16 mb-4 text-gray-300" />
                                    <p class="text-lg font-medium">No menu items found</p>
                                    <p class="text-sm mt-1">Get started by adding your first menu item</p>
                                    <button wire:click="openModal" class="mt-4 text-blue-600 hover:text-blue-800 font-medium text-sm">
                                        Create your first item &rarr;
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($menuItems->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $menuItems->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
        <x-modal wire:model="isOpen" title="Add New Items" class="backdrop-blur">
            <div class="flex items-center justify-center p-4 text-center sm:block sm:p-0">
                
                <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <!-- Modal Body -->
                    <div class="px-6 py-6 space-y-5">
                        <!-- Category -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                            <select wire:model="category_id" id="category_id" 
                                    class="block w-full px-4 py-2.5 text-base border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 {{ $errors->has('category_id') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : '' }}">
                                <option value="">Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Item Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name" id="name" 
                                   class="block w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : '' }}" 
                                   placeholder="e.g., Margherita Pizza">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea wire:model="description" id="description" rows="3" 
                                      class="block w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 {{ $errors->has('description') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : '' }}" 
                                      placeholder="Describe the item... (optional)"></textarea>
                            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Price -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" step="0.01" wire:model="price" id="price" 
                                       class="block w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 {{ $errors->has('price') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : '' }}" 
                                       placeholder="0.00">
                            </div>
                            @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Availability -->
                        <div class="flex items-center">
                            <button type="button" wire:click="$set('is_available', {{ $is_available ? 'false' : 'true' }})" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $is_available ? 'bg-green-500' : 'bg-gray-200' }}">
                                <span class="sr-only">Available</span>
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $is_available ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                            <span class="ml-3 text-sm font-medium text-gray-700">Available for ordering</span>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                        <button wire:click="save" type="button" 
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm transition-all duration-200">
                            {{ $isEditing ? 'Save Changes' : 'Create Item' }}
                        </button>
                        <button wire:click="closeModal" type="button" 
                                class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-5 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:w-auto sm:text-sm transition-all duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </x-modal>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="cancelDelete"></div>
                
                <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Delete Menu Item</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Are you sure you want to delete this menu item? This action cannot be undone and the item will be permanently removed from your menu.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button wire:click="delete" type="button" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-all duration-200">
                            Delete
                        </button>
                        <button wire:click="cancelDelete" type="button" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-all duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>