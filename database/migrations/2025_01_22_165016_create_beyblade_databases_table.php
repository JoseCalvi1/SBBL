<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeybladeDatabasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tabla de Blades
        Schema::create('blades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_takara')->nullable();
            $table->string('nombre_hasbro')->nullable();
            $table->boolean('marca_hasbro')->default(false);
            $table->boolean('marca_takara')->default(false);
            $table->string('tipo')->nullable(); // Ataque, defensa, Energía
            $table->string('color')->nullable();
            $table->string('giro')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('analisis')->nullable();
            $table->longText('imagen')->nullable();
            $table->longText('tarjeta')->nullable();
            $table->string('sistema')->nullable();
            $table->string('wave_hasbro')->nullable();
            $table->date('fecha_takara')->nullable();
            $table->timestamps();
        });

        // Tabla de Blades
        Schema::create('assist_blades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->boolean('marca_hasbro')->default(false);
            $table->boolean('marca_takara')->default(false);
            $table->string('tipo')->nullable();
            $table->string('color')->nullable();
            $table->string('giro')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('analisis')->nullable();
            $table->longText('imagen')->nullable();
            $table->longText('tarjeta')->nullable();
            $table->string('sistema')->nullable();
            $table->string('wave_hasbro')->nullable();
            $table->date('fecha_takara')->nullable();
            $table->timestamps();
        });

        // Tabla de Ratchets
        Schema::create('ratchets', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('altura')->nullable();
            $table->integer('num_salientes')->nullable();
            $table->boolean('marca_hasbro')->default(false);
            $table->boolean('marca_takara')->default(false);
            $table->boolean('recolor')->default(false);
            $table->string('color')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('analisis')->nullable();
            $table->longText('imagen')->nullable();
            $table->longText('tarjeta')->nullable();
            $table->string('wave_hasbro')->nullable();
            $table->date('fecha_takara')->nullable();
            $table->timestamps();
        });

        // Tabla de Bits
        Schema::create('bits', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('abreviatura')->nullable();
            $table->string('color')->nullable();
            $table->boolean('marca_hasbro')->default(false);
            $table->boolean('marca_takara')->default(false);
            $table->boolean('recolor')->default(false);
            $table->string('tipo')->nullable();
            $table->string('altura')->nullable(); // low, regular, high
            $table->text('descripcion')->nullable();
            $table->text('analisis')->nullable();
            $table->longText('imagen')->nullable();
            $table->longText('tarjeta')->nullable();
            $table->string('wave_hasbro')->nullable();
            $table->date('fecha_takara')->nullable();
            $table->timestamps();
        });

        // Tabla de Beyblades
        Schema::create('beyblades', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // Ataque, defensa, Energía
            $table->boolean('marca_hasbro')->default(false);
            $table->boolean('marca_takara')->default(false);
            $table->boolean('recolor')->default(false);
            $table->foreignId('blade_id')->constrained('blades')->onDelete('cascade');
            $table->foreignId('ratchet_id')->constrained('ratchets')->onDelete('cascade');
            $table->foreignId('bit_id')->constrained('bits')->onDelete('cascade');
            $table->text('descripcion')->nullable();
            $table->text('analisis')->nullable();
            $table->string('imagen')->nullable();
            $table->string('tarjeta')->nullable();
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
        Schema::dropIfExists('beyblades');
        Schema::dropIfExists('bits');
        Schema::dropIfExists('ratchets');
        Schema::dropIfExists('blades');
    }
}
