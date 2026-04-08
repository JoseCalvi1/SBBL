<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('treasury_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ingreso', 'gasto']);
            $table->string('category');

            // Decimales(10,2) permite cifras de hasta millones con 2 céntimos (ej: 99999999.99)
            $table->decimal('gross_amount', 10, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->enum('status', ['pendiente', 'completado', 'fallido', 'reembolsado'])->default('completado');

            $table->string('description');
            $table->string('reference_id')->nullable(); // ID de PayPal o Nº Factura
            $table->longText('receipt_b64')->nullable();

            // Relaciones opcionales para cruzar datos
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('treasury_logs');
    }
};
