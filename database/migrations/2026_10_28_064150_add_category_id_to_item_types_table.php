<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('item_types', 'category')) {
            return;
        }

        Schema::table('item_types', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->after('name');
        });

        foreach (DB::table('item_types')->whereNotNull('category')->get(['id', 'category']) as $itemType) {
            $categoryId = DB::table('categories')->where('name', $itemType->category)->value('id');

            if ($categoryId === null) {
                $categoryId = DB::table('categories')->insertGetId([
                    'name' => $itemType->category,
                    'description' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('item_types')->where('id', $itemType->id)->update(['category_id' => $categoryId]);
        }

        Schema::table('item_types', function (Blueprint $table): void {
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('item_types', 'category_id')) {
            return;
        }

        Schema::table('item_types', function (Blueprint $table): void {
            $table->string('category')->nullable()->after('name');
        });

        foreach (DB::table('item_types')->join('categories', 'categories.id', '=', 'item_types.category_id')->get(['item_types.id', 'categories.name as category']) as $itemType) {
            DB::table('item_types')->where('id', $itemType->id)->update(['category' => $itemType->category]);
        }

        Schema::table('item_types', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
