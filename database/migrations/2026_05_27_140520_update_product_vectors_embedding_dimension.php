<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter the column to the specific vector dimension (384)
        // We use USING embedding::vector to cast existing data if necessary
        DB::statement('ALTER TABLE product_vectors ALTER COLUMN embedding TYPE vector(384);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // If you need to revert, you can change it back to the original type (e.g., text or a different vector dimension)
        DB::statement('ALTER TABLE product_vectors ALTER COLUMN embedding TYPE vector(1536);');
    }
};
