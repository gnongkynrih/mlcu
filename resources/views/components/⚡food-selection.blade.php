<?php

use App\Models\MenuItem;
use App\Models\Category;
use Livewire\Component;

new class extends Component
{
    public $tableSession = [];
    public $menus = [];
    public $originalMenus = [];
    public $categories = [];
    public $search = '';
    public $selectedCategoryId = null;

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
                                <x-button icon="o-minus" class="btn-circle btn-outline bg-rose-400 text-white" />
                                <x-input type="number" min="0" value="0" class="w-16" />
                                <x-button icon="o-plus" class="btn-circle btn-outline bg-emerald-400 text-white" />
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>
            <div class="col-span-1">
                Summary 
            </div>
        </div>
    </x-card>
</div>