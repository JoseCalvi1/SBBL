<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('war_news', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Ej: "Caída del Bastión Norte"
            $table->text('content'); // Ej: "El equipo X ha arrasado con..."
            // Tipo de noticia para cambiar el color (attack, conquest, defense, info)
            $table->enum('type', ['attack', 'conquest', 'defense', 'info'])->default('info');
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
        Schema::dropIfExists('war_news');
    }
}
