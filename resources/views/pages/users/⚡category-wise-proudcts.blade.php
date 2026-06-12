<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

new class () extends Component {
    public $categoryName;
    public $search = ''; // 1. Add search property

    public function mount($categoryName)
    {
        $this->categoryName = $categoryName;

        // check that if the product category is unavailable then redirect to 404 page
        $categoryExists = Category::where('name', $this->categoryName)
            ->where('is_available', true)
            ->exists();

        if (!$categoryExists) {
            // return redirect()->route('404');
            abort(404);
        }
    }

    public function getProductsProperty()
    {
        // 2. Vector Search Logic for this specific category
        if (!empty(trim($this->search))) {
            try {
                $response = Http::post('http://127.0.0.1:5000/embed', [
                    'content' => $this->search,
                ]);

                if ($response->successful() && $response->json('success')) {
                    $queryEmbedding = json_encode($response->json('embedding'));

                    // Perform Vector Search restricted by category
                    $similarProductIds = ProductVector::query()
                        ->whereHas('categories', function ($q) {
                            $q->where('name', $this->categoryName)
                              ->where('is_available', true);
                        })
                        ->select('product_id')
                        ->orderByRaw('embedding <=> ?::vector', [$queryEmbedding])
                        ->limit(20)
                        ->pluck('product_id')
                        ->unique();

                    if ($similarProductIds->isNotEmpty()) {
                        return Product::with(['productImages', 'category'])
                            ->whereIn('id', $similarProductIds)
                            ->where('is_available', true)
                            // Double check category and availability
                            ->whereHas('category', function ($query) {
                                $query->where('is_available', true)
                                      ->where('name', $this->categoryName);
                            })
                            ->get();
                    }

                    return collect();
                }
            } catch (\Exception $e) {
                Log::error("Category Vector Search Failed: " . $e->getMessage());
            }
        }

        // 3. Your untouched original caching logic
        $data = Cache::remember('products_category_'.str_replace(' ', '_', $this->categoryName), null, function () {
            return Product::with(['productImages', 'category'])
                ->where('is_available', true)
                ->whereHas('category', function ($query) {
                    $query->where('is_available', true)
                          ->where('name', $this->categoryName);
                })
                ->orderBy('name', 'asc')
                ->get()->toJson();
        });

        return Product::hydrate(json_decode($data, true));
    }
};
?>

<x-slot:title>
    {{ $this->categoryName }} - {{ config('app.name') }}
</x-slot>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8  space-y-6">
   
    <div class="max-w-xl mx-auto relative">
        <input type="text" 
               wire:model.live.debounce.500ms="search" 
               placeholder="Search products..." 
               class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-200 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-shadow text-gray-700">
        <div class="absolute left-4 top-3.5 text-gray-400">
            <!-- <i class="fa-solid fa-wand-magic-sparkles"></i> -->
             <i class="fa-solid fa-search"></i>
        </div>
        
        <div wire:loading wire:target="search" class="absolute right-4 top-3.5">
            <i class="fa-solid fa-circle-notch fa-spin text-blue-500"></i>
        </div>
    </div>

    <div class="grid {{ count($this->products) > 0 ? 'grid-cols-2' : 'grid-cols-1' }} sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
        @forelse($this->products as $product)
            <livewire:user.product-card :product="$product" :key="'prod-grid-'.$product->id" />
        @empty
            <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-5 p-12 text-center bg-white rounded-2xl border border-dashed border-gray-200 text-gray-400">
                <i class="fa-solid fa-magnifying-glass text-4xl mb-3 block text-gray-300"></i>
                <p class="font-semibold text-gray-600 text-base">
                    {{ !empty($search) ? 'No matches found in this category.' : 'No Products Found' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    {{ !empty($search) ? 'Try adjusting your search query.' : 'We are restocking our shelves at the moment. Please check back shortly!' }}
                </p>
            </div>
        @endforelse
    </div>
    
</div>