<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->date('delivery_date')->nullable()->after('sale_date');
            $table->enum('delivery_status', ['not_started', 'in_progress', 'completed', 'delivered'])->default('not_started')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('delivery_date');
            $table->enum('delivery_status', ['pending', 'delivered'])->default('pending')->change();
        });
    }
};
