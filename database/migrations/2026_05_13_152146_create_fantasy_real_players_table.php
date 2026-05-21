<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFantasyRealPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fantasy_real_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // El competidor real

            $table->integer('base_price')->default(100);    // Precio de salida
            $table->integer('current_price')->default(100); // Precio que subirá/bajará
            $table->boolean('is_active')->default(true);    // Por si un jugador se retira

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fantasy_real_players');
    }
}
