<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('basic_salary', 10, 2);
            $table->integer('month');
            $table->integer('year');
            $table->integer('total_days');
            $table->integer('present_days');
            $table->integer('absent_days');
            $table->integer('late_days');
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('total_earnings', 10, 2);
            $table->decimal('total_deductions', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->enum('status', ['pending', 'approved', 'paid'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate salary records
            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('salaries');
    }
}; 