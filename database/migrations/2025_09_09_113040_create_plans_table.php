<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // bronce, plata, oro
            $table->string('name');
            $table->decimal('monthly_price', 8, 2)->nullable();
            $table->decimal('annual_price', 8, 2)->nullable();
            $table->string('paypal_plan_monthly_id')->nullable();
            $table->string('paypal_plan_annual_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
