<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('featured_sort_order')->default(0)->after('is_featured');
            $table->unsignedInteger('sale_sort_order')->default(0)->after('is_on_sale');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['featured_sort_order', 'sale_sort_order']);
        });
    }
};
