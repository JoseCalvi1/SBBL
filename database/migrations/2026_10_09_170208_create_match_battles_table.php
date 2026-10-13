<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchBattlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_battles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_match_id')->constrained('tournament_matches')->onDelete('cascade');
            
            // Referenciamos directamente a la fila del combo guardado en tournament_results
            $table->foreignId('player1_combo_id')->nullable()->constrained('tournament_results')->onDelete('set null');
            $table->foreignId('player2_combo_id')->nullable()->constrained('tournament_results')->onDelete('set null');
            
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null'); // Null si es un empate (Draw)
            
            // Tipos de finales oficiales de Beyblade X
            $table->string('finish_type')->nullable(); // 'xtreme', 'burst', 'over', 'spin', 'draw'
            $table->integer('points_awarded')->default(0);
            
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
        Schema::dropIfExists('match_battles');
    }
}
