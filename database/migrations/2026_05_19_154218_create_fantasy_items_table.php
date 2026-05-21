<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFantasyItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fantasy_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fantasy_manager_id');
            $table->string('name');
            // Las 4 ranuras que me has pedido:
            $table->enum('slot_type', ['lanzador', 'equipacion', 'guantes', 'mascara']);
            $table->enum('rarity', ['COMÚN', 'RARO', 'ÉPICO'])->default('COMÚN');
            $table->string('rarity_color', 7)->default('#c5c6c7');
            // Multiplicadores bajos y controlados (ej: 1.01, 1.03, 1.05)
            $table->decimal('stat_multiplier', 4, 2)->default(1.01);
            // Vínculo con el jugador del roster (NULL si está guardado en el inventario)
            $table->unsignedBigInteger('fantasy_roster_id')->nullable()->default(null);
            $table->timestamps();

            // Índices / Claves foráneas (Opcional pero recomendado para que vaya rápido)
            $table->foreign('fantasy_manager_id')->references('id')->on('fantasy_managers')->onDelete('cascade');
            $table->foreign('fantasy_roster_id')->references('id')->on('fantasy_rosters')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fantasy_items');
    }
}
