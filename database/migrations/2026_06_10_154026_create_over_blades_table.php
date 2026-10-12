<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('over_blades', function (Blueprint $table) {
            $table->id(); // BIGINT(20) unsigned auto_increment
            $table->string('nombre');
            $table->tinyInteger('marca_hasbro')->default(0);
            $table->tinyInteger('marca_takara')->default(0);
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
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('over_blades');
    }
};
