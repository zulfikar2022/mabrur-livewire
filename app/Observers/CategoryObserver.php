<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    private function clearCache(Category $category)
    {
        Cache::forget('nav_categories');
        Cache::forget('home_products');
        Cache::forget('products_category_'.strtolower(str_replace(' ', '_', $category->name)));
        // invalidate all the individual product caches that belong to this category

        $category->load('products');
        // foreach ($category->products as $product) {
        //     Cache::forget('product_details_' . $product->id);
        // }

    }

    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        $this->clearCache($category);
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        $this->clearCache($category);
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->clearCache($category);
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        $this->clearCache($category);
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        $this->clearCache($category);
    }
}
