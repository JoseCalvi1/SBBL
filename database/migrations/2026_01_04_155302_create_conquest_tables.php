<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConquestTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tabla de ZONAS (El mapa)
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: "Madrid Norte"
            $table->string('slug')->unique(); // Ej: "madrid-norte" (Para vincularlo con el SVG)
            $table->string('color')->default('#cccccc'); // Color actual de la zona

            // El equipo que la posee actualmente (puede ser nulo si es neutral)
            // Asumo que tu tabla de equipos se llama 'teams'. Si es otra, cámbialo aquí.
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null');

            // Multiplicador de defensa (x1, x2) que se genera aleatoriamente
            $table->integer('defense_bonus')->default(1);

            $table->timestamps();
        });

        // 2. Tabla de VOTOS (Las intenciones de ataque de la semana)
        Schema::create('conquest_votes', function (Blueprint $table) {
            $table->id();

            // Quién vota
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // A qué zona quiere atacar/defender
            $table->foreignId('zone_id')->constrained('zones')->onDelete('cascade');

            // Guardamos el equipo del usuario al momento de votar
            // (Por si se cambia de equipo a mitad de semana, que el voto cuente para el original)
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');

            $table->timestamps();

            // Restricción: Un usuario solo puede tener un voto activo por semana
            // (Esto lo gestionaremos mejor en la lógica, pero aquí ayuda)
        });

        // 3. Tabla de HISTORIAL (Opcional, pero recomendada para ver "quien ganó la semana pasada")
        Schema::create('conquest_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('zones');
            $table->foreignId('winner_team_id')->constrained('teams');
            $table->date('date_won'); // Fecha de la conquista
            $table->integer('points_spent'); // Cuántos puntos usaron para ganar
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
        Schema::dropIfExists('conquest_tables');
    }
}
