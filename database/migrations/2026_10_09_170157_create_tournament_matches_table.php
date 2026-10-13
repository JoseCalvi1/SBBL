<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('tournament_matches', function (Blueprint $table) {
        $table->id();
        $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
        $table->integer('round_number'); 
        $table->string('bracket_type')->default('swiss'); // 'swiss', 'winners', 'losers', 'finals'
        
        $table->foreignId('player1_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('player2_id')->nullable()->constrained('users')->onDelete('cascade'); // Null si le toca Bye (descanso)
        $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
        
        $table->integer('player1_score')->default(0);
        $table->integer('player2_score')->default(0);
        
        $table->string('status')->default('pending'); // 'pending', 'playing', 'completed'
        
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
        Schema::dropIfExists('tournament_matches');
    }
}
