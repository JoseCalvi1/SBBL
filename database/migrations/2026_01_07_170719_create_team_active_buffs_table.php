<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamActiveBuffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_active_buffs', function (Blueprint $table) {
            $table->id();

            // El equipo que tiene el buff activo
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');

            // El código del item para saber qué hace (ej: 'buff_attack_1.2')
            $table->string('item_code');

            // El valor numérico para facilitar cálculos (ej: 1.20)
            $table->decimal('multiplier', 5, 2)->default(1.00);

            // Cuándo deja de funcionar (normalmente el próximo Domingo de resolución)
            $table->timestamp('expires_at')->nullable();

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
        Schema::dropIfExists('team_active_buffs');
    }
}
