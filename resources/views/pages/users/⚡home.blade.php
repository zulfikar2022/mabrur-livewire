<?php

use App\Models\Product;
use Livewire\Component;

new class () extends Component {
    /**
     * Computed Property: Fetches available products along with their images
     * in a single batch to keep database requests highly optimized.
     */
    public function getProductsProperty()
    {
        return Product::with(['productImages', 'category'])
            ->where('is_available', true)
            ->whereHas('category', function ($query) {
                // Only include products whose category is also marked as available
                $query->where('is_available', true);
            })
            ->orderBy('name', 'asc')
            ->get();
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-8 space-y-6">
   
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
        @forelse($this->products as $product)
            <livewire:user.product-card :product="$product" :key="'prod-grid-'.$product->id" />
        @empty
            <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-4 p-12 text-center bg-white rounded-2xl border border-dashed border-gray-200 text-gray-400">
                <i class="fa-solid fa-basket-shopping text-4xl mb-3 block text-gray-300"></i>
                <p class="font-semibold text-gray-600 text-base">No Products Found</p>
                <p class="text-xs text-gray-400 mt-1">We are restocking our shelves at the moment. Please check back shortly!</p>
            </div>
        @endforelse
    </div>
    
</div>