<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductVector;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreateEmbedding implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 60; // Increase timeout since we are processing multiple chunks

    public function __construct(public Product $product) {}

    public function handle(): void
    {
        $contentString = 'Category: '.($this->product->category->name ?? 'Uncategorized').' | '.
                         'Product: '.$this->product->name.' | '.
                         'Description: '.$this->product->description;

        $chunks = $this->chunkTextWithOverlap($contentString, 500, 100);

        $this->product->productVectors()->delete();

        foreach ($chunks as $chunkText) {
            try {
                $response = Http::timeout(15)->post('http://127.0.0.1:5000/embed', [
                    'content' => $chunkText,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // 4. Create a fresh row for each chunk
                    ProductVector::create([
                        'product_id' => $this->product->id,
                        'category_id' => $this->product->category_id, // Store category_id for better search
                        'content' => $data['content'],
                        'embedding' => $data['embedding'],
                    ]);
                } else {
                    Log::error("Node service failed on a chunk for product ID {$this->product->id}");
                }
            } catch (\Exception $e) {
                Log::error('Failed chunk embedding processing: '.$e->getMessage());
                // We let the loop continue or fail based on your architecture preferences
            }
        }
    }

    private function chunkTextWithOverlap(string $text, int $chunkSize = 500, int $overlap = 100): array
    {
        $chunks = [];
        $textLength = mb_strlen($text);
        $position = 0;

        if ($textLength <= $chunkSize) {
            return [$text];
        }

        while ($position < $textLength) {
            // Take a slice of the text
            $chunk = mb_substr($text, $position, $chunkSize);

            $chunks[] = $chunk;

            // Shift forward by the chunk size minus the overlap
            $position += ($chunkSize - $overlap);

            // Guard against infinite loops if bad configuration occurs
            if (($chunkSize - $overlap) <= 0) {
                break;
            }
        }

        return $chunks;
    }
}
