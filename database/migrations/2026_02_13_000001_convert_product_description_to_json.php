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
        // Data migration: Convert legacy text to JSON format
        // We use raw DB to avoid Model events/casts during migration
        DB::table('products')
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->orderBy('id')
            ->chunk(100, function ($products) {
                foreach ($products as $product) {
                    $desc = $product->description;
                    
                    // Skip if already looks like JSON
                    if (str_starts_with(trim($desc), '{') || str_starts_with(trim($desc), '[')) {
                        continue;
                    }

                    // Convert to new JSON structure
                    $newDesc = json_encode([
                        'sections' => [
                            [
                                'key' => 'legacy',
                                'title' => 'Mô tả chi tiết',
                                'content' => $desc,
                                'media' => null,
                            ]
                        ]
                    ], JSON_UNESCAPED_UNICODE);

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update(['description' => $newDesc]);
                }
            });

        Schema::table('products', function (Blueprint $table) {
            // Change description column from text to json
            // This preserves backward compatibility by allowing null values
            $table->json('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert back to text
            $table->text('description')->nullable()->change();
        });
    }
};
