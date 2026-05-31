<?php

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class () extends Component {
    // Declare the public parameter to receive the product instance
    public Product $product;

    public function mount(Product $product)
    {
        // Eager load images to prevent N+1 query issues if used in a loop
        $this->product = $product->relationLoaded('productImages')
            ? $product
            : $product->load('productImages');
    }

    /**
     * Placeholder method for your future cart implementation
     */
    public function addToCart()
    {
        // first check if the user is logeed in or not. If not then redirect to login page with a message to login first
        if (!Auth::check()) {
            session()->flash('message', 'Please login to add products to your cart.');
            return redirect()->route('login');
        }
        if (!$this->product->is_available) {
            session()->flash('message', 'Sorry, this product is currently out of stock.');
            return;
        }

        Cart::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $this->product->id,
            ],
            [
                'quantity' => 1,
            ]
        );

    }
};
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col h-full group">
    
    <!-- 1. PRODUCT IMAGE ZONE (Clickable link to details) -->
    <a href="#" class="block aspect-square w-full bg-gray-50 overflow-hidden relative">
        @php
            // Grab the first image from the relationship collection
            $firstImage = $this->product->productImages->first();
        @endphp

        @if($firstImage)
            <img src="{{ asset('storage/' . $firstImage->image_link) }}" 
                 alt="{{ $this->product->name }}" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
            <!-- Placeholder if product has no media assets attached -->
            <div class="w-full h-full flex flex-col items-center justify-center text-gray-300 gap-2">
                <i class="fa-solid fa-image text-4xl"></i>
                <span class="text-xs font-medium text-gray-400">No Image Available</span>
            </div>
        @endif

        <!-- Availability Badge Indicator -->
        @if(!$this->product->is_available)
            <div class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center">
                <span class="bg-gray-800 text-white text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md shadow-sm">
                    Out of Stock
                </span>
            </div>
        @endif
    </a>

    <!-- 2. CONTENT SPACE (Grows to push button uniformly to the bottom) -->
    <div class="p-4 flex flex-col grow justify-between">
        
        <div class="space-y-1">
            <!-- PRODUCT NAME (Clickable link to details) -->
            <a href="#" class="block">
                <h3 class="font-semibold text-gray-800 text-base leading-tight hover:text-blue-600 transition-colors line-clamp-2">
                    {{ $this->product->name }}
                </h3>
            </a>
        </div>

        <!-- DYNAMIC PRICING STRATEGY DISPLAY -->
        <div class="mt-3 pt-2 border-t border-gray-50 flex items-baseline justify-between">
            <div>
                @if($this->product->sell_by_piece)
                    <span class="text-xl font-bold text-gray-900">৳{{ number_format($this->product->price_per_piece, 2) }}</span>
                    <span class="text-xs text-gray-500 font-medium">/ piece</span>
                @else
                    <span class="text-xl font-bold text-gray-900">৳{{ number_format($this->product->price_per_kg, 2) }}</span>
                    <span class="text-xs text-gray-500 font-medium">/ kg</span>
                @endif
            </div>
        </div>

    </div>

    <!-- 3. ACTION CONTROLS FOOTER -->
    <div class="p-4 pt-0">
        <button type="button"
                wire:click="addToCart"
                {{ !$this->product->is_available ? 'disabled' : '' }}
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed text-white font-semibold text-sm py-2.5 px-4 rounded-xl transition-colors cursor-pointer shadow-sm flex items-center justify-center gap-2 group-hover:bg-blue-700">
            <i class="fa-solid fa-cart-plus text-xs"></i>
            <span>Add to Cart</span>
        </button>
    </div>

</div>