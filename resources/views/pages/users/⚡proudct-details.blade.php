<?php

use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class () extends Component {
    public Product $product;
    public $activeImage;

    public function mount(Product $product)
    {
        // Eager load images to avoid N+1 queries
        $this->product = $product->load('productImages');
        // Set initial main image to the first one available
        $this->activeImage = $this->product->productImages->first()?->image_link;
    }

    public function addToCart()
    {
        if (!Auth::check()) {
            session()->flash('message', 'Please login to add products to your cart.');
            return redirect()->route('login');
        }

        Cart::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $this->product->id],
            ['quantity' => 1]
        );

        $this->dispatch('cart-updated');
        session()->flash('success', 'Product added to cart!');
    }
};
?>

<div class="max-w-6xl mx-auto p-6 my-8" x-data="{ activeImg: '{{ asset('storage/' . $activeImage) }}' }">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
        
        <div class="space-y-4">
            <div class="w-full aspect-square bg-gray-100 rounded-2xl overflow-hidden shadow-sm">
                <img :src="activeImg" class="w-full h-full object-cover">
            </div>

            <div class="flex gap-3 overflow-x-auto pb-2">
                @foreach($product->productImages as $img)
                    <button @click="activeImg = '{{ asset('storage/' . $img->image_link) }}'"
                            class="w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-blue-500 transition-all">
                        <img src="{{ asset('storage/' . $img->image_link) }}" class="w-full h-full object-cover">
                    </button>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <h1 class="text-3xl font-black text-gray-900">{{ $product->name }}</h1>
            
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

            @if($product->is_available)
                <button wire:click="addToCart"
                        class="w-full md:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-transform hover:scale-[1.02]">
                    Add to Cart
                </button>
            @else
                <button disabled class="w-full md:w-auto px-8 py-3 bg-gray-300 text-gray-500 font-bold rounded-xl cursor-not-allowed">
                    Currently Unavailable
                </button>
            @endif

            @if (session()->has('success'))
                <p class="text-emerald-600 font-medium text-sm mt-2">{{ session('success') }}</p>
            @endif
        </div>
    </div>
</div>