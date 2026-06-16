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
     * Handles adding the product to the database cart table
     */
    public function addToCart()
    {
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
                'user_id'    => Auth::id(),
                'product_id' => $this->product->id,
            ],
            [
                'quantity'   => 1,
            ]
        );



        $this->dispatch('cart-updated');
    }
};
?>



<!-- 1. UPDATED: Added global x-data state tracker to manage the temporary success overlay context -->
<div x-data="{ showSuccessOverlay: false }" 
     class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col h-full group">
    
    <!-- PRODUCT IMAGE ZONE -->
    <div class="block aspect-square w-full bg-gray-50 overflow-hidden relative">
        @php
            // Grab the first image from the relationship collection
            $firstImage = $this->product->productImages->first();
        @endphp

        @if($firstImage)
            <a wire:navigate href="{{ Auth::check() ? route('user.product.details', ['product' => $this->product->id, 'productName' => $this->product->nameModifier()]) : route('guest.product.details', ['product' => $this->product->id, 'productName' => $this->product->nameModifier()]) }}" class="w-full h-full block">
                <!-- <img src="{{ asset('storage/' . $firstImage->image_link) }}"  -->
                 <img src="{{ config('services.imagekit.url_endpoint') . $firstImage->image_link }}"
                 alt="{{ $this->product->name }}" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </a>
        @else
            <a wire:navigate href="{{ Auth::check() ? route('user.product.details', ['product' => $this->product->id, 'productName' => $this->product->nameModifier()]) : route('guest.product.details', ['product' => $this->product->id, 'productName' => $this->product->nameModifier()]) }}" class="w-full h-full flex flex-col items-center justify-center text-gray-300 gap-2">
                <i class="fa-solid fa-image text-4xl"></i>
                <span class="text-xs font-medium text-gray-400">No Image Available</span>
            </a >
        @endif

        <div x-show="showSuccessOverlay"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute inset-0 bg-emerald-600/80 backdrop-blur-[2px] flex flex-col items-center justify-center text-white z-10"
             x-cloak>
            
            <div x-show="showSuccessOverlay"
                 x-transition:enter="transition ease-out delay-75 duration-300 transform"
                 x-transition:enter-start="scale-50 opacity-0"
                 x-transition:enter-end="scale-100 opacity-100"
                 class="bg-white text-emerald-600 rounded-full h-14 w-14 flex items-center justify-center shadow-lg border border-emerald-500/20">
                <i class="fa-solid fa-check text-2xl font-black"></i>
            </div>
            
            <span class="text-xs font-bold uppercase tracking-wider mt-2.5 drop-shadow-sm bg-emerald-700/40 px-2 py-0.5 rounded-md">
                Added to Cart
            </span>
        </div>

        <!-- Availability Badge Indicator -->
        @if(!$this->product->is_available)
            <div class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center z-20">
                <span class="bg-gray-800 text-white text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md shadow-sm">
                    Out of Stock
                </span>
            </div>
        @endif
    </div>

    <!-- CONTENT SPACE -->
    <div class="p-2 flex flex-col grow justify-between">
        <div class="space-y-1">
            <a wire:navigate href="{{ Auth::check() ? route('user.product.details', ['product' => $this->product->id, 'productName' => $this->product->nameModifier()]) : route('guest.product.details', ['product' => $this->product->id, 'productName' => $this->product->nameModifier()]) }}" class="block">
                <h3 class="font-semibold text-gray-800 text-base leading-tight hover:text-blue-600 transition-colors line-clamp-2">
                    {{ $this->product->name }}
                </h3>
            </a>
        </div>

        <!-- DYNAMIC PRICING STRATEGY DISPLAY -->
        <div class="mt-1 border-t border-gray-50 flex items-baseline justify-between">
            <div>
                @if($this->product->sell_by_piece)
                    <span class="font-bold text-gray-900">৳{{ number_format($this->product->price_per_piece, 2) }}</span>
                    <!-- <span class="text-xs text-gray-500 font-medium">/ piece</span> -->
                @else
                    <span class="font-bold text-gray-900">৳{{ number_format($this->product->price_per_kg, 2) }}</span>
                    <!-- <span class="text-xs text-gray-500 font-medium">/ kg</span> -->
                @endif
            </div>
        </div>
    </div>

    <!-- ACTION CONTROLS FOOTER -->
    <div class="p-4 pt-0">
        <button type="button"
                @click="if({{ Auth::check() ? 'true' : 'false' }} && {{ $this->product->is_available ? 'true' : 'false' }}) { 
                            showSuccessOverlay = true; 
                            setTimeout(() => { showSuccessOverlay = false }, 2000); 
                        }"
                wire:click="addToCart"
                {{ !$this->product->is_available ? 'disabled' : '' }}
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed text-white font-semibold text-sm py-2.5 px-1 rounded-xl transition-colors cursor-pointer shadow-sm flex items-center justify-center gap-1 group-hover:bg-blue-700">
            <i class="fa-solid fa-cart-plus text-xs"></i>
            <span>Add to Cart</span>
        </button>
    </div>

</div>