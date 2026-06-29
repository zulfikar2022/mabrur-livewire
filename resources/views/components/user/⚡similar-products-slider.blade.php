<?php

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class () extends Component {
    public Product $product;

    #[Computed]
    public function similarProducts()
    {
        // Fetch products in the same category, excluding the current product
        return Product::with('productImages')
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->where('is_available', true)
            ->get();
    }
};
?>

<div>
    @if($this->similarProducts->count() > 0)
        <style>
            .hide-scrollbar::-webkit-scrollbar {
                display: none;
            }
            .hide-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>

        <div class="mt-3 border-t border-gray-100 pt-3">
            <h3 class="text-xl font-bold text-gray-900 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-basket-shopping text-blue-600"></i>
                Similar Products
            </h3>

            <div class="flex overflow-x-auto gap-4 md:gap-6 pb-4 snap-x snap-mandatory hide-scrollbar scroll-smooth">
                @foreach($this->similarProducts as $similar)
                    @php
                        $route = route(Auth::check() ? 'user.product.details' : 'guest.product.details', [
                            'product' => $similar->id, 
                            'productName' => $similar->nameModifier()
                        ]);
                    @endphp

                    <div class="snap-start shrink-0 w-40 md:w-48 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group flex flex-col">
                        
                        <a href="{{ $route }}" wire:navigate class="block aspect-square w-full bg-gray-50 overflow-hidden relative">
                            @php
                                $firstImage = $similar->productImages->first();
                            @endphp

                            @if($firstImage)
                                <img src="{{ config('services.imagekit.url_endpoint') . $firstImage->image_link }}?tr=w-300,h-300,fo-auto,f-avif,f-webp" 
                                alt="{{ $similar->name }}" 
                                width="300"
                                height="300"
                                loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-300 gap-2">
                                    <i class="fa-solid fa-image text-3xl"></i>
                                </div>
                            @endif
                        </a>

                        <div class="p-3 flex flex-col grow justify-between">
                            <a href="{{ $route }}" wire:navigate class="block mb-2">
                                <h4 class="font-semibold text-gray-800 text-sm leading-tight hover:text-blue-600 transition-colors line-clamp-2">
                                    {{ $similar->name }}
                                </h4>
                            </a>

                            <div class="mt-auto">
                                @if($similar->sell_by_piece)
                                    <span class="font-bold text-blue-600">৳{{ number_format($similar->price_per_piece, 2) }}</span>
                                @elseif($similar->sell_by_weight)
                                    <span class="font-bold text-blue-600">৳{{ number_format($similar->price_per_kg, 2) }}</span>
                                @endif
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>