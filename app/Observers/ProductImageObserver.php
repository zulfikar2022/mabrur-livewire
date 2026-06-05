<?php

namespace App\Observers;

use App\Models\ProductImage;
use Illuminate\Support\Facades\Cache;

class ProductImageObserver
{
    private function clearCache(ProductImage $productImage)
    {
        $productId = $productImage->product_id;
        Cache::forget('product_details_' . $productId);
    }
    /**
     * Handle the ProductImage "created" event.
     */
    public function created(ProductImage $productImage): void
    {

        $this->clearCache($productImage);
    }

    /**
     * Handle the ProductImage "updated" event.
     */
    public function updated(ProductImage $productImage): void
    {
        $this->clearCache($productImage);
    }

    /**
     * Handle the ProductImage "deleted" event.
     */
    public function deleted(ProductImage $productImage): void
    {
        $this->clearCache($productImage);
    }

    /**
     * Handle the ProductImage "restored" event.
     */
    public function restored(ProductImage $productImage): void
    {
        $this->clearCache($productImage);
    }

    /**
     * Handle the ProductImage "force deleted" event.
     */
    public function forceDeleted(ProductImage $productImage): void
    {
        $this->clearCache($productImage);
    }
}
