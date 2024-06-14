<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDuelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams_versus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id_1')->references('id')->on('teams');
            $table->foreignId('team_id_2')->references('id')->on('teams');
            $table->integer('winner')->default(0);
            $table->string('url')->nullable();
            $table->text('matchup')->nullable();
            $table->string('status')->nullable();
            $table->string('result_1')->nullable();
            $table->string('result_2')->nullable();
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
        Schema::dropIfExists('teams_versus');
    }
}
