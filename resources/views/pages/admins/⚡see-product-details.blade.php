<?php

namespace App\App\Livewire\Admin;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component {
    public Product $product;

    /**
     * Initialize the component with the product model,
     * ensuring images and categories are eagerly loaded.
     */
    public function mount($id)
    {
        $this->product = Product::with(['category', 'productImages'])->findOrFail($id);
    }
};
?>

<div class="max-w-6xl mx-auto p-4 md:p-6 my-6" 
     x-data="{ 
        // Populate thumbnails directly from the eager-loaded relationship
        images: [
            @foreach($product->productImages as $img)
                '{{ asset('storage/' . $img->image_link) }}',
            @endforeach
        ],
        // Default to the first uploaded image, fallback to a placeholder if none exist
        activeImage: '{{ $product->productImages->first() ? asset('storage/' . $product->productImages->first()->image_link) : '' }}',
        fallback: 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'%239ca3af\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg>'
     }"
     x-init="if(images.length === 0) activeImage = fallback">

    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.show-all-products') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Products
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        
        <div class="lg:col-span-5 flex flex-col space-y-4">
            <div class="w-full aspect-square bg-gray-50 rounded-xl border border-gray-200 overflow-hidden flex items-center justify-center p-2">
                <img :src="activeImage" 
                     alt="{{ $product->name }}" 
                     class="max-w-full max-h-full object-contain rounded-lg shadow-sm">
            </div>

            <div class="w-full overflow-x-auto flex items-center space-x-3 py-2 scrollbar-thin scrollbar-thumb-gray-200"
                 x-show="images.length > 0">
                <template x-for="(img, idx) in images" :key="idx">
                    <button type="button" 
                            @click="activeImage = img"
                            class="w-20 h-20 rounded-lg overflow-hidden border-2 bg-gray-50 shrink-0 transition-all outline-none"
                            :class="activeImage === img ? 'border-blue-600 ring-2 ring-blue-100 scale-95' : 'border-gray-200 hover:border-gray-400'">
                        <img :src="img" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </div>

        <div class="lg:col-span-7 flex flex-col justify-between space-y-6">
            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded-md text-xs font-semibold tracking-wide uppercase">
                        {{ $product->category->name ?? 'Uncategorized' }}
                    </span>
                    
                    @if($product->is_available)
                        <span class="px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 rounded-md text-xs font-semibold inline-flex items-center">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span> Available
                        </span>
                    @else
                        <span class="px-2.5 py-1 bg-red-50 text-red-700 border border-red-200 rounded-md text-xs font-semibold inline-flex items-center">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></span> Unavailable
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-tight">
                    {{ $product->name }}
                </h1>

                <div class="py-4 border-y border-gray-100 my-2">
                    <span class="text-sm font-medium text-gray-500 block mb-1">Pricing Specification</span>
                    <div class="text-3xl font-extrabold text-gray-900 flex items-baseline">
                        @if($product->sell_by_piece)
                            ${{ number_format($product->price_per_piece, 2) }}
                            <span class="text-gray-500 text-base font-normal ml-1">/ piece</span>
                        @elseif($product->sell_by_weight)
                            ${{ number_format($product->price_per_kg, 2) }}
                            <span class="text-gray-500 text-base font-normal ml-1">/ kg</span>
                        @else
                            <span class="text-gray-400 text-xl font-medium">Pricing configuration pending</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-xs text-gray-500 grid grid-cols-2 gap-4">
                <div><span class="font-medium text-gray-700 block">System Identifier</span> #{{ $product->id }}</div>
                <div><span class="font-medium text-gray-700 block">Created On</span> {{ $product->created_at->format('M d, Y') }}</div>
            </div>
        </div>

    </div>

    <div class="mt-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
        <h3 class="text-lg font-bold text-gray-900 border-b pb-2 flex items-center">
            <i class="fa-solid fa-align-left text-gray-400 mr-2 text-sm"></i> Product Description
        </h3>
        <div class="prose max-w-none text-gray-700 text-sm md:text-base leading-relaxed tracking-normal">
            {!! $product->description !!}
        </div>
    </div>
</div>