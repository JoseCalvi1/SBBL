<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('tournament_results', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Para enlazar con la tabla de usuarios
        $table->integer('event_id')->nullable(); // Para enlazar con la tabla de eventos
        $table->integer('versus_id')->nullable();
        $table->string('blade');
        $table->string('ratchet');
        $table->string('bit');
        $table->integer('victorias');
        $table->integer('derrotas');
        $table->integer('puntos_ganados');
        $table->integer('puntos_perdidos');
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
        Schema::dropIfExists('tournament_results');
    }
}
