<?php

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component {
    #[Url(as: 'q')]
    public $search = '';

    #[Url(as: 'cat')]
    public $category = '';

    public function toggleProductAvailability($id)
    {
        $product = Product::findOrFail($id);
        $product->is_available = !$product->is_available;
        $product->save();
    }
};
?>


<div class="p-6 space-y-6">
    @php
        $categories = Category::orderBy('name', 'asc')->get();

        $products = Product::query()
        ->with(['category', 'productImages'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    // Using ILIKE for PostgreSQL case-insensitive search
                    $subQuery->where('name', 'ILIKE', "%{$search}%")
                             ->orWhere('description', 'ILIKE', "%{$search}%");
                });
            })
            ->when($category, function ($query) use ($category) {
                $query->where('category_id', $category);
            })
            ->orderBy('id', 'desc')
            ->get();

        $numberOfAvailableProducts = $products->where('is_available', true)->count();
        $numberOfInAvailableProucts = $products->where('is_available', false)->count();

    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Manage Products</h1>
        <a href="{{ route('admin.add-product') }}" wire:navigate class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition-colors cursor-pointer shadow-sm">
            <i class="fa-solid fa-plus mr-1"></i> Add Product
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div class="md:col-span-3 relative">
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   placeholder="Search products by name or description..." 
                   class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all">
            <div class="absolute left-3 top-3 text-gray-400">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
        </div>

        <div>
            <select wire:model.live="category" 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all cursor-pointer">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 font-bold text-gray-700">
            <p>Product List</p>
            <p class="text-sm text-gray-500 font-normal mt-1">
               Total: {{ $products->count() }}, Available: {{ $numberOfAvailableProducts }}, Unavailable: {{ $numberOfInAvailableProucts }} 
            </p>
        </div>
        
        <div class="divide-y divide-gray-100 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 font-bold text-gray-700 hidden md:grid md:grid-cols-12 gap-4 items-center">
        <div class="col-span-4">Product Details</div>
        <div class="col-span-2">Quantity</div>
        <div class="col-span-2 text-center">Price</div>
        <div class="col-span-2 text-center">Status</div>
        <div class="col-span-2 text-right">Actions</div>
    </div>
    
    <div class="divide-y divide-gray-100">
        @forelse($products as $product)
            <div wire:key="product-row-{{ $product->id }}" class="px-6 py-4 grid grid-cols-1 md:grid-cols-12 gap-4 items-center hover:bg-gray-50 transition-colors">
        
        <div class="col-span-1 md:col-span-4 flex items-center space-x-4">
            <div class="w-14 h-14 rounded-lg bg-gray-100 overflow-hidden shrink-0 border border-gray-200">
                @if($product->productImages && $product->productImages->first())
                    <!-- <img src="{{ asset('storage/' . $product->productImages->first()->image_link) }}" class="w-full h-full object-cover"> -->
                    <img src="{{ config('services.imagekit.url_endpoint') . $product->productImages->first()->image_link }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400"><i class="fa-regular fa-image text-xl"></i></div>
                @endif
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 text-base leading-tight">{{ $product->name }}</h4>
                <span class="text-xs text-gray-500 font-medium block mt-0.5">{{ $product->category->name ?? 'Uncategorized' }}</span>
            </div>
        </div>

        <div class="col-span-1 md:col-span-2 flex items-center">
            <span class="md:hidden text-sm font-medium text-gray-500 mr-2">Quantity:</span>
            <div class="text-sm font-semibold text-gray-800">
                {{ $product->available_quantity }} {{ $product->sell_by_piece ? 'pcs' : ($product->sell_by_weight ? 'kg' : '') }}
            </div>
        </div>

        <div class="col-span-1 md:col-span-2 flex md:justify-center items-center">
            <span class="md:hidden text-sm font-medium text-gray-500 mr-2">Price:</span>
            <div class="text-sm font-semibold text-gray-800">
                @if($product->sell_by_piece)
                    ${{ number_format($product->price_per_piece, 2) }}<span class="text-gray-500 text-xs">/pc</span>
                @elseif($product->sell_by_weight)
                    ${{ number_format($product->price_per_kg, 2) }}<span class="text-gray-500 text-xs">/kg</span>
                @endif
            </div>
        </div>

        <div class="col-span-1 md:col-span-2 flex md:justify-center items-center">
            <button type="button" wire:click="toggleProductAvailability({{ $product->id }})" class="relative inline-flex h-5 w-11 shrink-0 cursor-pointer rounded-full transition-colors {{ $product->is_available ? 'bg-green-600' : 'bg-gray-300' }}">
                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-gray-200 transition {{ $product->is_available ? 'translate-x-5' : 'translate-x-0' }}"></span>
            </button>
        </div>

        <div class="col-span-1 md:col-span-2 flex justify-start md:justify-end items-center space-x-3">
             <a href="{{ route('admin.see-product-details', $product->id) }}" wire:navigate class="p-2 text-gray-500 hover:text-blue-600"><i class="fa-solid fa-eye"></i></a>
             <a href="{{ route('admin.update-product', $product->id)}}" wire:navigate class="p-2 text-gray-500 hover:text-amber-600"><i class="fa-solid fa-pen-to-square"></i></a>
        </div>
    </div>
        @empty
            <div class="p-12 text-center text-gray-500">
                <div class="text-4xl mb-2">📦</div>
                <p class="font-medium">No products found matching your search criteria.</p>
            </div>
        @endforelse
    </div>
</div>
    </div>
</div>