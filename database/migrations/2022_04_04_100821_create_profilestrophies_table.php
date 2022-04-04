<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilestrophiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profilestrophies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profiles_id');
            $table->unsignedBigInteger('trophies_id');
            $table->foreign('profiles_id')->references('id')->on('profiles')->constrained();
            $table->foreign('trophies_id')->references('id')->on('trophies')->constrained();
            $table->string('count')->default(0);
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
        Schema::dropIfExists('profilestrophies');
    }
}
