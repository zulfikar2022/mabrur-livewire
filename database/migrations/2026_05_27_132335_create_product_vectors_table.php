<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Enable the pgvector extension if it's not already enabled
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector;');

        Schema::create('product_vectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->text('content');

            // 2. Define the embedding column using the 'vector' type.
            // Replace 1536 with the dimension count matching your embedding model (e.g., OpenAI text-embedding-3-small)
            $table->vector('embedding', 1536);

            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement('CREATE INDEX product_vectors_embedding_hnsw ON product_vectors USING hnsw (embedding vector_cosine_ops);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_vectors');

        // Optional: Drop the extension if you want to completely clean up
        // DB::statement('DROP EXTENSION IF EXISTS vector;');
    }
};
