<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    private function clearCache(Product $product)
    {
        // clear the entire cache
        // Cache::flush();

        // 1. Always clear the homepage cache
        Cache::forget('home_products');

        // 2. Clear the specific product detail cache
        // Cache::forget('product_details_' . $product->id);

        // 3. Clear the category cache if the product belongs to a category
        // We load the category relation to get the name
        $product->load('category');

        if ($product->category) {
            $catName = strtolower(str_replace(' ', '_', $product->category->name));
            Cache::forget('products_category_'.$catName);
        }
        if ($product->isDirty('category_id')) {
            $oldCat = Category::find($product->getOriginal('category_id'));
            if ($oldCat) {
                $oldCatName = strtolower(str_replace(' ', '_', $oldCat->name));
                Cache::forget('products_category_'.$oldCatName);
            }
        }
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->clearCache($product);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->clearCache($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->clearCache($product);
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        $this->clearCache($product);
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        $this->clearCache($product);
    }
}
