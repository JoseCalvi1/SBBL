<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mode')->nullable();
            $table->string('imagen')->nullable();
            $table->string('location')->nullable();
            $table->string('created_by')->nullable();
            $table->string('status')->nullable();
            $table->string('deck')->nullable();
            $table->string('configuration')->nullable();
            $table->foreignId('region_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('beys')->nullable();
            $table->text('iframe')->nullable();
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
        Schema::dropIfExists('events');
    }
}
