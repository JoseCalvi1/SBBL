<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVersusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('versus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id_1')->references('id')->on('users');
            $table->foreignId('user_id_2')->references('id')->on('users');
            $table->integer('winner')->default(0);
            $table->foreignId('event_id')->references('id')->on('events');
            $table->string('url')->nullable();
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
        Schema::dropIfExists('versus');
    }
}
