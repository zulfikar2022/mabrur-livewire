<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CleanupOrphanedImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes files from storage that do not exist or are soft-deleted in the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting product image storage optimization cleanup...');

        // 1. Fetch all physical files present in the 'public/products' directory
        // This maps exactly to your physical path: storage/app/public/products
        $disk = Storage::disk('public');
        $files = $disk->files('products');

        if (empty($files)) {
            $this->info('The products directory is completely empty. Nothing to clean.');
            return Command::SUCCESS;
        }

        $this->info('Found ' . count($files) . ' physical files in storage directory.');

        // 2. Build a high-performance lookup collection from the database
        // We include tracing for soft-deleted entries using withTrashed()
        $dbImageRecords = ProductImage::withTrashed()
            ->select('id', 'image_link', 'deleted_at')
            ->get()
            ->mapWithKeys(function ($record) {
                // Deduct the 'products/' prefix to get the raw file name
                $rawName = Str::after($record->image_link, 'products/');
                return [$rawName => [
                    'is_soft_deleted' => !is_null($record->deleted_at)
                ]];
            })->toArray();

        $deletedCount = 0;

        // 3. Compare disk files against our database registry map
        foreach ($files as $fileRelativePath) {
            // Get just the raw filename from the disk path (e.g., "abc.png")
            $rawFileName = basename($fileRelativePath);

            // Case A: Image does not exist in the database table at all
            if (!array_key_exists($rawFileName, $dbImageRecords)) {
                $disk->delete($fileRelativePath);
                $deletedCount++;
                $this->line("Deleted Orphaned File: {$rawFileName} (Reason: Missing DB record)");
                continue;
            }

            // Case B: Image exists but is marked as soft-deleted (deleted_at is NOT null)
            if ($dbImageRecords[$rawFileName]['is_soft_deleted']) {
                $disk->delete($fileRelativePath);
                $deletedCount++;
                $this->line("Deleted Soft-Deleted File: {$rawFileName} (Reason: Model soft-deleted)");
            }
        }

        $this->info("Cleanup task completed successfully! Total disk space freed: {$deletedCount} images removed.");

        return Command::SUCCESS;
    }
}
