<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'failed', 'returned'])
                ->default('pending')
                ->after('quantity')
                ->index();
            $table->text('tracking_note')->nullable()->after('status');
            $table->timestamp('confirmed_at')->nullable()->after('tracking_note');
            $table->timestamp('shipped_at')->nullable()->after('confirmed_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['status', 'tracking_note', 'confirmed_at', 'shipped_at', 'delivered_at']);
        });
    }
};
