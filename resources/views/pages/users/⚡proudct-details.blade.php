<?php

use App\Models\Product;
use App\Models\Cart;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Title;
use Livewire\Component;

new class () extends Component {
    public Product $product;
    public int $product_id ;
    public $activeImage;


    public function mount(Product $product)
    {
        $this->product_id = $product->id;
        $this->product = $product;
        $this->activeImage = $this->product->productImages->first()?->image_link;


        // check if the category of the product is available or not. if not then redirect to the 404 page
        if (!$this->product->category || !$this->product->category->is_available) {
            abort(404);
        }
    }


    public function addToCart()
    {
        if (!Auth::check()) {
            session()->flash('message', 'Please login to add products to your cart.');
            return redirect()->route('login');
        }

        Cart::updateOrCreate(
            [
                'user_id'    => Auth::id(),
                'product_id' => $this->product_id,
            ],
            [
                'quantity'   => 1,
            ]
        );

        $this->dispatch('cart-updated');
        session()->flash('success', 'Product added to cart!');
    }
};
?>

<x-slot:title>
    {{ $product->name }} - {{ config('app.name') }}
</x-slot>

<div class="max-w-6xl mx-auto p-6 my-8" 
     x-data="{ activeImg: '{{ $activeImage ? config('services.imagekit.url_endpoint') . $activeImage . '?tr=w-600,h-600,fo-auto' : '' }}', showSuccessOverlay: false }">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
        
        <!-- Left: Image Gallery with the success overlay placed inside -->
        <div class="space-y-4">
            <div class="w-full aspect-square bg-gray-100 rounded-2xl overflow-hidden shadow-sm relative">
                <!-- Main Image -->
                <img :src="activeImg" class="w-full h-full object-cover">

                <!-- 2. The Success Overlay -->
                <div x-show="showSuccessOverlay"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute inset-0 bg-emerald-600/90 backdrop-blur-sm flex flex-col items-center justify-center text-white z-10"
                     x-cloak>
                    <div class="bg-white text-emerald-600 rounded-full h-16 w-16 flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-check text-3xl font-black"></i>
                    </div>
                    <span class="text-sm font-bold uppercase tracking-wider mt-4">Added to Cart</span>
                </div>
            </div>

            <!-- Thumbnail Slider -->
            <div class="flex gap-3 overflow-x-auto pb-2">
                @foreach($product->productImages as $img)
                    <button @click="activeImg = '{{ config('services.imagekit.url_endpoint') . $img->image_link }}?tr=w-600,h-600,fo-auto'"
                            class="w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-blue-500 transition-all">
                        <img src="{{ config('services.imagekit.url_endpoint') . $img->image_link }}?tr=w-300,h-300,fo-auto" class="w-full h-full object-cover">
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Right: Details -->
        <div class="space-y-6">
            <h1 class="text-3xl font-black text-gray-900">{{ $product->name }}</h1>
            
            <!-- 3. Updated Add to Cart Button with temporary overlay trigger -->
            @if($product->is_available)
                <div wire:click="addToCart"
                     @click="showSuccessOverlay = true; setTimeout(() => { showSuccessOverlay = false }, 2000)"
                     class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-transform hover:scale-[1.02] flex items-center gap-2 w-max cursor-pointer">
                        <i class="fa-solid fa-cart-plus text-xs"></i>
                        <p>Add to Cart</p>
                </div>
            @else
                <button disabled class="px-8 py-3 bg-gray-300 text-gray-500 font-bold rounded-xl cursor-not-allowed">
                    Currently Unavailable
                </button>
            @endif
            
            <!-- Pricing and Description remain unchanged -->
            <div class="p-4 bg-gray-50 rounded-xl">
                @if($product->sell_by_piece)
                    <p class="text-2xl font-bold text-blue-600">৳{{ number_format($product->price_per_piece, 2) }} <span class="text-sm text-gray-500 font-normal">/ piece</span></p>
                @elseif($product->sell_by_weight)
                    <p class="text-2xl font-bold text-blue-600">৳{{ number_format($product->price_per_kg, 2) }} <span class="text-sm text-gray-500 font-normal">/ kg</span></p>
                @endif
            </div>

            <div class="text-gray-600 leading-relaxed">
                <h3 class="font-bold text-gray-900 mb-2">Description</h3>
                <div class="prose max-w-none">
                    {!! $product->description !!}
                </div>
            </div>
        </div>
    </div>
</div>