<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlackMarketTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Definición de los objetos (Potenciadores)
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // Ej: "Inyección de Adrenalina"
            $table->string('code')->unique();// Ej: "attack_boost_1.2" (Para usar en tu lógica)
            $table->text('description');     // Descripción para el usuario
            $table->integer('cost');         // Coste en SBBL Coin
            $table->string('image')->nullable(); // Icono
            $table->boolean('is_active')->default(true); // Si está a la venta
            $table->timestamps();
        });

        // 2. Inventario del EQUIPO (Los items comprados van al equipo)
        Schema::create('team_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0); // Cantidad acumulada

            // Opcional: Si el item es de "usar y gastar" o de "activar por tiempo"
            // Podrías necesitar una columna 'active_until' si son duraderos
            $table->timestamps();
        });

        // 3. Control de Ruleta en el Usuario
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_daily_reward_at')->nullable();
        });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('black_market_tables');
    }
}
