<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     */
    protected $description = 'Automatically generate the sitemap for the store based on active database records.';

    public function handle()
    {
        $this->info('Generating sitemap...');

        // Initialize the sitemap
        $sitemap = Sitemap::create();

        // 1. Add the Guest Homepage
        // Using now() since static pages are technically "current" at generation time
        $sitemap->add(Url::create('/')
            ->setLastModificationDate(now()));

        // Add the guest faq page
        $sitemap->add(Url::create('/faq')
            ->setLastModificationDate(now()));

        // 2. Add Dynamic Categories (under /guest/category/...)
        $categories = Category::where('is_available', true)->get();
        foreach ($categories as $category) {
            // FIX: Trim trailing spaces from the database and strictly URL-encode the Bengali characters
            $cleanCategoryName = rawurlencode($category->name);

            $sitemap->add(
                Url::create("/category/{$cleanCategoryName}")
                    ->setLastModificationDate($category->updated_at)
            );
        }

        // 3. Add Dynamic Products (under /guest/product/...)
        $products = Product::where('is_available', true)
            ->whereHas('category', function ($query) {
                $query->where('is_available', true);
            })
            ->get();

        foreach ($products as $product) {
            // FIX: Trim the slug to prevent any trailing spaces from creeping into the XML
            $cleanSlug = trim($product->nameModifier());
            $productUrl = "/product/{$product->id}/{$cleanSlug}";

            $sitemap->add(
                Url::create($productUrl)
                    ->setLastModificationDate($product->updated_at)
            );
        }

        // Write the final XML file directly to the public directory
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully at public/sitemap.xml!');

        return Command::SUCCESS;
    }
}
