<?php

use App\Models\Product;
use App\Models\ProductVector;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

new class () extends Component {
    public $search = '';

    public function getProductsProperty()
    {
        if (!empty(trim($this->search))) {
            try {
                $response = Http::post('http://127.0.0.1:5000/embed', [
                    'content' => $this->search,
                ]);

                if ($response->successful() && $response->json('success')) {
                    $queryEmbedding = json_encode($response->json('embedding'));

                    $similarProductIds = ProductVector::query()
                        ->select('product_id')
                        ->orderByRaw('embedding <=> ?::vector', [$queryEmbedding])
                        ->limit(20)
                        ->pluck('product_id')
                        ->unique();

                    if ($similarProductIds->isNotEmpty()) {
                        return Product::with(['productImages', 'category'])
                            ->whereIn('id', $similarProductIds)
                            ->where('is_available', true)
                            ->whereHas('category', function ($query) {
                                $query->where('is_available', true);
                            })
                            ->orderBy('id', 'desc')
                            ->get();
                    }

                    return collect();
                }
            } catch (\Exception $e) {
                Log::error("Vector Search Failed: " . $e->getMessage());
            }
        }

        $data = Cache::remember('home_products', null, function () {
            return Product::with(['productImages', 'category'])
                ->where('is_available', true)
                ->whereHas('category', function ($query) {
                    $query->where('is_available', true);
                })
                ->orderBy('id', 'desc')
                ->get()->toJson();
        });

        return Product::hydrate(json_decode($data, true));
    }
};
?>

<x-slot:title>
    Welcome to {{ config('app.name') }} - Best Products Online
</x-slot:title>

<x-slot:metaDescription>
    Browse our extensive collection of high-quality products. Use our AI-powered search to find exactly what you are looking for at the best prices.
</x-slot:metaDescription>

<x-slot:ogType>website</x-slot:ogType>

<x-slot:metaImage>
    {{ "https://ik.imagekit.io/mabrurhut/logos/mabrur-banner.jpg" }}
</x-slot:metaImage>


<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    
    <h1 class="sr-only">Browse all products at {{ config('app.name') }}</h1>

    <div class="max-w-xl mx-auto relative mb-4">
        <input type="text" 
               wire:model.live.debounce.500ms="search" 
               placeholder="Search Products..." 
               aria-label="Search through our products"
               class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-200 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-shadow text-gray-700 ">
        <div class="absolute left-4 top-3.5 text-gray-400">
             <i class="fa-solid fa-search"></i>
        </div>
        
        <div wire:loading wire:target="search" class="absolute right-4 top-3.5">
            <i class="fa-solid fa-circle-notch fa-spin text-blue-500"></i>
        </div>
    </div>

    <main class="grid {{ count($this->products) > 0 ? 'grid-cols-2' : 'grid-cols-1' }} sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
        @forelse($this->products as $product)
            <livewire:user.product-card :product="$product" :key="'prod-grid-'.$product->id" />
        @empty
            <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-5 p-12 text-center bg-white rounded-2xl border border-dashed border-gray-200 text-gray-400">
                <i class="fa-solid fa-magnifying-glass text-4xl mb-3 block text-gray-300"></i>
                <p class="font-semibold text-gray-600 text-base">
                    {{ !empty($search) ? 'No matches found for your description.' : 'No Products Found' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    {{ !empty($search) ? 'Try adjusting your search query.' : 'We are restocking our shelves at the moment. Please check back shortly!' }}
                </p>
            </div>
        @endforelse
    </main>
</div>