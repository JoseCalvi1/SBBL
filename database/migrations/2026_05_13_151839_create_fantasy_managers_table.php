<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFantasyManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fantasy_managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Enlace a tu tabla users

            $table->integer('coins_balance')->default(10000); // 10.000 Monedas iniciales
            $table->integer('gems_balance')->default(0);      // Moneda premium
            $table->integer('total_points')->default(0);      // Puntos totales en la liga Fantasy

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
        Schema::dropIfExists('fantasy_managers');
    }
}
