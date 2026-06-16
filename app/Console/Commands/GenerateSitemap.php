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
        $sitemap->add(Url::create('/guest')
            ->setPriority(1.0)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        // 2. Add Dynamic Categories (under /guest/category/...)
        $categories = Category::where('is_available', true)->get();
        foreach ($categories as $category) {
            // Note: The Spatie package automatically handles the URL encoding
            // for Bengali characters (like খেজুর or বাদাম) in the final XML.
            $sitemap->add(
                Url::create("/guest/category/{$category->name}")
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        }

        // 3. Add Dynamic Products (under /guest/product/...)
        $products = Product::where('is_available', true)->get();
        foreach ($products as $product) {
            $productUrl = "/guest/product/{$product->id}/{$product->nameModifier()}";

            $sitemap->add(
                Url::create($productUrl)
                    ->setLastModificationDate($product->updated_at)
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            );
        }

        // Write the final XML file directly to the public directory
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully at public/sitemap.xml!');

        return Command::SUCCESS;
    }
}
