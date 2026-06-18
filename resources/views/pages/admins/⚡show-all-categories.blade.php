<?php

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component {
    public $name;
    public $editingCategoryId = null;
    public $showModal = false;
    public $categories;

    public function mount()
    {
        $this->refreshCategories();
    }

    public function refreshCategories()
    {

        $this->categories = Category::withCount(['products' => function ($query) {
            $query->where('is_available', true);
        }])
        ->orderBy('id', 'asc')
        ->get();
    }

    protected $rules = ['name' => 'required|min:2|max:255'];

    public function toggleAvailability(Category $category)
    {
        $category->update(['is_available' => !$category->is_available]);
        $this->refreshCategories(); // Refresh data after update
    }

    public function openModal($categoryId = null)
    {
        $this->editingCategoryId = $categoryId;
        $this->name = $categoryId ? Category::find($categoryId)->name : '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();
        Category::updateOrCreate(
            ['id' => $this->editingCategoryId],
            ['name' => $this->name, 'is_available' => true]
        );
        $this->showModal = false;
        $this->reset('name', 'editingCategoryId');
        $this->refreshCategories(); // Refresh data after save
    }
};
?>
<div class="p-6" x-data="{ open: @entangle('showModal') }">
    <div class="flex justify-between mb-6">
        <h1 class="text-2xl font-bold">Manage Categories</h1>
        <a href="{{ route('admin.create-category') }}" wire:navigate class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:cursor-pointer font-bold">Add Category</a>
    </div>

    <div class="space-y-4">
        <div class="hidden md:grid grid-cols-4 gap-4 px-6 py-3 bg-gray-50 text-gray-700 font-bold rounded-lg shadow-sm">
            <div>Name</div>
            <div>Products</div>
            <div>Status</div>
            <div>Action</div>
        </div>

        @foreach($categories as $category)
            <div wire:key="category-{{ $category->id }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                
                <div class="font-semibold text-lg md:text-base">{{ $category->name }}</div>

                <div class="flex md:block items-center">
                    <span class="md:hidden text-gray-500 mr-2">Products:</span>
                    {{ $category->products_count }}
                </div>

                <div class="flex md:block items-center">
                    <span class="md:hidden text-gray-500 mr-2">Status:</span>
                    <button wire:click="toggleAvailability({{ $category->id }})" 
                            type="button"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $category->is_available ? 'bg-green-600' : 'bg-gray-200' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $category->is_available ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                <div class="flex justify-start md:block">
                    <a href="{{ route('admin.update-category', $category->id) }}" wire:navigate class="text-blue-500 hover:text-blue-700 transition-colors">
                        <i class="fa-solid fa-pencil"></i> Edit
                    </a >
                </div>
            </div>
        @endforeach
    </div>

    <div x-show="open" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95"
     class="fixed inset-0 bg-black/50 flex items-center justify-center">
    
    <div @click.away="open = false" class="bg-white p-6 rounded-lg w-96 shadow-xl">
        <h2 class="text-lg font-bold mb-4">{{ $editingCategoryId ? 'Edit' : 'New' }} Category</h2>
        <input wire:model="name" class="w-full border p-2 mb-4 rounded" placeholder="Category Name">
        <div class="flex justify-end space-x-2">
            <button @click="open = false" class="px-4 py-2 bg-gray-200 rounded hover:cursor-pointer">Cancel</button>
            <button wire:click="save" class="px-4 py-2 bg-blue-600 text-white rounded hover:cursor-pointer">Save</button>
        </div>
    </div>
    
</div>

</div>