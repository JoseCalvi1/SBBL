<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('difficulty');
            $table->timestamps();
        });

        Schema::create('challenges_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profiles_id');
            $table->unsignedBigInteger('challenges_id');
            $table->foreign('profiles_id')->references('id')->on('profiles')->constrained();
            $table->foreign('challenges_id')->references('id')->on('challenges')->constrained();
            $table->string('done')->default(0);
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
        Schema::dropIfExists('challenges_profiles');
        Schema::dropIfExists('challenges');
    }
}
