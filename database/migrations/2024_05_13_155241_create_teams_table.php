<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('points_x1')->default(0);
            $table->integer('points_x2')->default(0);
            $table->longText('image')->nullable();
            $table->longText('logo')->nullable();
            $table->string('color', 7)->default('#333333');
            $table->foreignId('captain_id')->references('id')->on('users');
            $table->text('pinned_message')->nullable(); // El mensaje fijado
            $table->timestamp('pinned_message_updated_at')->nullable();
            $table->enum('status', ['pending', 'accepted', 'updated'])->default('pending');
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
        Schema::dropIfExists('teams');
    }
}
