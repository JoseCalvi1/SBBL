<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('subtitulo')->nullable();
            $table->string('imagen')->nullable();
            $table->string('marco')->nullable();
            $table->string('fondo')->nullable();
            $table->integer('points')->default(0);
            $table->integer('points_s2')->default(0);
            $table->integer('points_s3')->default(0);
            $table->integer('points_g1')->default(0);
            $table->integer('points_x1')->default(0);
            $table->integer('points_x2')->default(0);
            $table->foreignId('region_id')->nullable()->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('profiles');
        Schema::dropIfExists('regions');
    }
}
