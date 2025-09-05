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
            $table->longText('image_mod')->nullable();
            $table->string('city')->nullable();
            $table->string('location')->nullable();
            $table->string('created_by')->nullable();
            $table->string('status')->nullable();
            $table->string('deck')->nullable();
            $table->string('configuration')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('region_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('beys')->nullable();
            $table->text('iframe')->nullable();
            $table->text('challonge')->nullable();
            $table->timestamps();
        });

        Schema::create('event_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('referee_id')->constrained('users')->onDelete('cascade'); // asume que los árbitros son usuarios
            $table->enum('status', ['approved', 'rejected', 'pending'])->nullable(); // o null mientras no revisen
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'referee_id']); // evita que un árbitro revise dos veces el mismo evento
        });

        Schema::create('event_judge_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('judge_id')->constrained('users')->onDelete('cascade');
            $table->enum('final_status', ['approved', 'rejected']);
            $table->text('comment')->nullable();
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
        Schema::dropIfExists('event_judge_reviews');
        Schema::dropIfExists('event_reviews');
        Schema::dropIfExists('events');
    }
}
