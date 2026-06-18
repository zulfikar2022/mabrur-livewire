<?php

use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class () extends Component {
    #[Computed]
    public function categories()
    {
        // Fetch only available categories and eager load their associated image
        return Category::where('is_available', true)
            ->with('categoryImage')
            ->orderBy('id', 'asc') // You can change this to 'created_at', 'desc', etc.
            ->get();
    }
};
?>

<div>
    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>

    <div class="max-w-7xl mx-auto py-6 px-4"
         x-data="{
             interval: null,
             startAutoScroll() {
                 // Auto-scroll every 3 seconds
                 this.interval = setInterval(() => {
                     let slider = this.$refs.slider;
                     if (!slider) return;

                     // If reached the end, smoothly scroll back to the start
                     if (slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 10) {
                         slider.scrollTo({ left: 0, behavior: 'smooth' });
                     } else {
                         // Scroll right by the width of approximately one and a half items
                         slider.scrollBy({ left: 160, behavior: 'smooth' });
                     }
                 }, 3000);
             },
             pauseAutoScroll() {
                 clearInterval(this.interval);
             }
         }"
         x-init="startAutoScroll()"
    >
        <!-- <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg md:text-xl font-black text-gray-800 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-blue-600"></i>
                <span>Shop by Category</span>
            </h2>
        </div> -->

        <div x-ref="slider" 
             @mouseenter="pauseAutoScroll" 
             @mouseleave="startAutoScroll"
             @touchstart="pauseAutoScroll"
             @touchend="startAutoScroll"
             class="flex overflow-x-auto gap-4 md:gap-6 pb-4 snap-x snap-mandatory hide-scrollbar scroll-smooth">
            
            @foreach($this->categories as $category)
                <a href="{{ route(Auth::check() ? 'user.category.products' : 'guest.category.products', ['categoryName' => $category->name]) }}" 
                   wire:navigate
                   class="snap-start shrink-0 flex flex-col items-center group w-24 md:w-32 cursor-pointer transition-transform hover:-translate-y-1">
                    
                    <div class="w-20 h-20 md:w-28 md:h-28 rounded-full bg-blue-50 border-2 border-transparent group-hover:border-blue-500 shadow-sm overflow-hidden relative flex items-center justify-center transition-all duration-300">
                        @if($category->categoryImage)
                            <img src="{{ config('services.imagekit.url_endpoint') . $category->categoryImage->image_link }}?tr=w-200,h-200,fo-auto" 
                                 alt="{{ $category->name }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <i class="fa-solid fa-image text-3xl text-blue-200"></i>
                        @endif
                    </div>
                    
                    <h3 class="mt-3 text-xs md:text-sm font-bold text-gray-700 text-center line-clamp-2 group-hover:text-blue-600 transition-colors">
                        {{ $category->name }}
                    </h3>
                </a>
            @endforeach

        </div>
    </div>
</div>