<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, create a temporary column
        Schema::table('sales', function (Blueprint $table) {
            $table->string('delivery_status_new')->after('delivery_status');
        });

        // Copy and transform data
        DB::table('sales')->update([
            'delivery_status_new' => DB::raw('CASE 
                WHEN delivery_status = "pending" THEN "not_started"
                WHEN delivery_status = "delivered" THEN "delivered"
                ELSE "not_started"
            END')
        ]);

        // Drop the old column
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('delivery_status');
        });

        // Rename the new column
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('delivery_status_new', 'delivery_status');
        });

        // Add the enum constraint
        DB::statement("ALTER TABLE sales MODIFY COLUMN delivery_status ENUM('not_started', 'in_progress', 'completed', 'delivered') NOT NULL DEFAULT 'not_started'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, create a temporary column
        Schema::table('sales', function (Blueprint $table) {
            $table->string('delivery_status_old')->after('delivery_status');
        });

        // Copy and transform data
        DB::table('sales')->update([
            'delivery_status_old' => DB::raw('CASE 
                WHEN delivery_status IN ("not_started", "in_progress", "completed") THEN "pending"
                WHEN delivery_status = "delivered" THEN "delivered"
                ELSE "pending"
            END')
        ]);

        // Drop the old column
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('delivery_status');
        });

        // Rename the new column
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('delivery_status_old', 'delivery_status');
        });

        // Add the enum constraint
        DB::statement("ALTER TABLE sales MODIFY COLUMN delivery_status ENUM('pending', 'delivered') NOT NULL DEFAULT 'pending'");
    }
};
