<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use ImageKit\ImageKit;

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
    protected $description = 'Deletes orphaned or soft-deleted images from ImageKit servers.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting ImageKit storage optimization cleanup...');

        // 1. Initialize ImageKit SDK
        $imageKit = new ImageKit(
            config('services.imagekit.public_key'),
            config('services.imagekit.private_key'),
            config('services.imagekit.url_endpoint')
        );

        // 2. Fetch all physical files present in the '/products' directory on ImageKit
        // Note: By default, this fetches up to 1000 files.
        $response = $imageKit->listFiles([
            'path' => '/products',
            'limit' => 1000
        ]);

        // THE FIX: Check if error is NOT null
        if ($response->error !== null) {
            $this->error('ImageKit API request failed: ' . json_encode($response->error));
            return Command::FAILURE;
        }

        if (empty($response->result)) {
            $this->info('The ImageKit products directory is completely empty. Nothing to clean.');
            return Command::SUCCESS;
        }

        $remoteFiles = $response->result;
        $this->info('Found ' . count($remoteFiles) . ' files in ImageKit /products directory.');

        // 3. Build a high-performance lookup collection from the database
        // We include tracing for soft-deleted entries using withTrashed()
        $dbImageRecords = ProductImage::withTrashed()
            ->select('id', 'image_link', 'deleted_at')
            ->get()
            ->mapWithKeys(function ($record) {
                // ImageKit returns filePaths with a leading slash (e.g., "/products/image.jpg")
                // We ensure our DB path matches this exact format for accurate comparison.
                $standardizedPath = Str::start($record->image_link, '/');

                return [$standardizedPath => [
                    'is_soft_deleted' => !is_null($record->deleted_at)
                ]];
            })->toArray();

        $deletedCount = 0;

        // 4. Compare remote ImageKit files against our database registry map
        foreach ($remoteFiles as $remoteFile) {
            $filePath = $remoteFile->filePath; // e.g., "/products/abc.jpg"
            $fileId = $remoteFile->fileId;     // e.g., "64a2b1..." required for deletion

            // Case A: Image does not exist in the database table at all
            if (!array_key_exists($filePath, $dbImageRecords)) {
                $imageKit->deleteFile($fileId);
                $deletedCount++;
                $this->line("Deleted Orphaned File: {$filePath} (Reason: Missing DB record)");
                continue;
            }

            // Case B: Image exists but is marked as soft-deleted
            if ($dbImageRecords[$filePath]['is_soft_deleted']) {
                $imageKit->deleteFile($fileId);
                $deletedCount++;
                $this->line("Deleted Soft-Deleted File: {$filePath} (Reason: Model soft-deleted)");
            }
        }

        $this->info("Cleanup task completed successfully! Total disk space freed: {$deletedCount} images removed from ImageKit.");

        return Command::SUCCESS;
    }
}
