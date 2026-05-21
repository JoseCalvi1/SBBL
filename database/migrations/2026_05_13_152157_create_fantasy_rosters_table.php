<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFantasyRostersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fantasy_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_manager_id')->constrained()->onDelete('cascade');
            $table->foreignId('fantasy_real_player_id')->constrained()->onDelete('cascade');

            // LA REGLA DE ORO: ¿Es de los 3 que puntúan o de los 3 que descansan?
            $table->enum('status', ['starter', 'bench'])->default('bench');

            $table->integer('buy_price'); // A cuánto lo compró (para calcular beneficios si lo vende)
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
        Schema::dropIfExists('fantasy_rosters');
    }
}
