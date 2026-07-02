<?php

use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class () extends Component {
    public int $product_id;
    public $activeImage;

    public function mount(Product $product)
    {
        $this->product_id = $product->id;
        $this->activeImage = $product->productImages->first()?->image_link;

        // check if the category of the product is available or not. if not then redirect to the 404 page
        // if (!$product->category || !$product->category->is_available) {
        //     abort(404);
        // }
    }

    // THE FIX: Computed property prevents the "Vanishing Details" bug on Add to Cart
    #[Computed]
    public function product()
    {
        return Product::with(['category', 'productImages'])->findOrFail($this->product_id);
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

    public function orderNow()
    {
        // 1. Check Auth first to prevent crashing on Auth::id()
        if (!Auth::check()) {
            session()->flash('message', 'Please login to order products.');
            return redirect()->route('login');
        }

        // 2. Add to cart
        $this->addToCart();

        // 3. Redirect to the cart page with the wire:navigate directive for a smooth SPA-like transition
        return redirect()->route('user.cart', ['user' => Auth::id()]);
    }

    public function redirectToGoogle()
    {
        return redirect()->route('auth.google');
    }
};
?>



<x-slot:title>
    {{ $this->product->name }} - {{ config('app.name') }}
</x-slot:title>

<x-slot:metaDescription>
    {{ \Illuminate\Support\Str::limit(strip_tags($this->product->description), 160) }}
</x-slot:metaDescription>

<x-slot:metaImage>
    {{ $this->product->productImages->first() ? config('services.imagekit.url_endpoint') . $this->product->productImages->first()->image_link . '?tr=w-1200,h-630,fo-auto' : '' }}
</x-slot:metaImage>
<x-slot:imageWidth>1200</x-slot:imageWidth>
<x-slot:imageHeight>630</x-slot:imageHeight>

<x-slot:ogType>product</x-slot:ogType>


<div class="max-w-6xl mx-auto p-6 my-8" 
     x-data="{ activeImg: '{{ $activeImage ? config('services.imagekit.url_endpoint') . $activeImage . '?tr=w-600,h-600,fo-auto,f-avif,f-webp' : '' }}', showSuccessOverlay: false }">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
        
        <div class="space-y-4">
            <div class="w-full aspect-square bg-gray-100 rounded-2xl overflow-hidden shadow-sm relative">
                <img :src="activeImg" fetchpriority="high" loading="eager" class="w-full h-full object-cover">

                @if($this->product->weight_per_piece == 0)
                    <div class="absolute top-2 right-2 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg z-10">
                        Free Delivery
                    </div>
                @endif

                <div x-show="showSuccessOverlay"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute inset-0 bg-emerald-600/90 backdrop-blur-sm flex flex-col items-center justify-center text-white z-20"
                     x-cloak>
                    <div class="bg-white text-emerald-600 rounded-full h-16 w-16 flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-check text-3xl font-black"></i>
                    </div>
                    <span class="text-sm font-bold uppercase tracking-wider mt-4">Added to Cart</span>
                </div>
            </div>
            
            <div class="flex gap-3 overflow-x-auto pb-2">
                @foreach($this->product->productImages as $img)
                    <button @click="activeImg = '{{ config('services.imagekit.url_endpoint') . $img->image_link }}?tr=w-600,h-600,fo-auto,f-avif,f-webp'"
                            class="w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-blue-500 transition-all flex-shrink-0">
                        <img src="{{ config('services.imagekit.url_endpoint') . $img->image_link }}?tr=w-100,h-100,fo-auto,f-avif,f-webp" loading="lazy" class="w-full h-full object-cover">
                    </button>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <h1 class="text-3xl font-black text-gray-900">{{ $this->product->name }}</h1>
            
            @if($this->product->is_available && $this->product->category->is_available)
                <div wire:click="addToCart"
                     @click="showSuccessOverlay = true; setTimeout(() => { showSuccessOverlay = false }, 2000)"
                     class="px-8  py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-transform hover:scale-[1.02] flex items-center gap-2 w-full md:w-max cursor-pointer">
                        <i class="fa-solid fa-cart-plus text-xs"></i>
                        <p>Add to Cart</p>
                </div>
            @else
                <button disabled class="px-8 py-3 bg-gray-300 text-gray-500 font-bold rounded-xl cursor-not-allowed border border-gray-400">
                    Currently Unavailable
                </button>
            @endif
            
            <div class="p-4 bg-gray-50 rounded-xl">
                @if($this->product->sell_by_piece)
                    <p class="text-2xl font-bold text-blue-600">৳{{ number_format($this->product->price_per_piece, 2) }} <span class="text-sm text-gray-500 font-normal">/ piece</span></p>
                @elseif($this->product->sell_by_weight)
                    <p class="text-2xl font-bold text-blue-600">৳{{ number_format($this->product->price_per_kg, 2) }} <span class="text-sm text-gray-500 font-normal">/ kg</span></p>
                @endif
            </div>
            
            <div>
                @if($this->product->is_mango)
                    <div class="px-4 py-2 text-red-700 border border-yellow-200 rounded-md text-sm font-medium inline-flex items-center mb-4 bg-yellow-50">
                        <i class="fa-solid fa-info-circle mr-2"></i> 
                        <p>এই পণ্যটির হোম ডেলিভারি করা হয় না। আপনাকে এটি সংগ্রহ করতে হবে আপনার নিকটস্থ <b>স্টেডফাস্ট / সুন্দরবন / সওদাগর / এজিআর  </b> কুরিয়ারের শাখা থেকে।</p>
                    </div>
                @endif
            </div>

            @if(Auth::check() && $this->product->is_available && $this->product->category->is_available)
                <div wire:click="orderNow"
                    class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-transform hover:scale-[1.02] flex items-center gap-2 w-full md:w-max cursor-pointer">
                        <i class="fa-solid fa-shopping-bag text-xs "></i>
                        <p>Order Now</p>
                </div>
            @else 
                <div>
                    <button 
                        wire:click="redirectToGoogle"
                        class="flex items-center gap-3 px-6 py-3 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:cursor-pointer transition duration-200"
                    >
                        <img src="https://res.cloudinary.com/dq7jdy5xy/image/upload/v1781240987/google_ceygiu.jpg" loading="lazy" class="w-5 h-5" alt="Google Logo">
                        
                        <span class="font-semibold text-gray-700">পণ্য অর্ডার করতে লগইন করুন</span>
                    </button>
                </div>
            @endif
            <div class="w-full">
                <livewire:user.similar-products-slider :product="$this->product" />
            </div>

        </div>
        <div class="text-black leading-relaxed col-span-1 md:col-span-2">
                <h3 class="font-bold text-black mb-2">Description</h3>
                <div class="prose max-w-none">
                    {!! $this->product->description !!}
                </div>
        </div>
    </div>



@php
    // 1. Format the image array dynamically
    $schemaImages = $this->product->productImages->map(function($img) {
        return config('services.imagekit.url_endpoint') . $img->image_link . '?tr=w-1200,h-1200,fo-auto';
    })->toArray();

    // 2. Determine the correct price based on selling logic
    $schemaPrice = $this->product->sell_by_piece 
        ? $this->product->price_per_piece 
        : $this->product->price_per_kg;

    // 3. Build the ENTIRE Schema as a clean PHP array
    $schemaData = [
        "@context" => "https://schema.org/",
        "@type" => "Product",
        "name" => $this->product->name,
        "image" => $schemaImages,
        "description" => strip_tags($this->product->description),
        "sku" => "MABRUR-" . $this->product->id,
        "brand" => [
            "@type" => "Brand",
            "name" => "Mabrur Hut"
        ],
        "offers" => [
            "@type" => "Offer",
            "url" => request()->url(),
            "priceCurrency" => "BDT",
            "price" => number_format($schemaPrice, 2, '.', ''),
            "itemCondition" => "https://schema.org/NewCondition",
            "availability" => $this->product->is_available ? "https://schema.org/InStock" : "https://schema.org/OutOfStock"
        ]
    ];
@endphp

@push('productDetails')
        <script type="application/ld+json">
            {!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
@endpush

</div>
