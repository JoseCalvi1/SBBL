<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarritosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carritos', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable(); // para invitados
            $table->string('nombre')->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();
            $table->boolean('enviado')->default(false);
            $table->string('referencia')->nullable(); // identificador único del pedido
            $table->enum('metodo_pago', ['paypal', 'coins'])->nullable();
            $table->enum('estado_pago', ['pendiente', 'pagado', 'cancelado'])->default('pendiente');
            $table->enum('estado_envio', ['pendiente', 'preparando', 'enviado', 'entregado'])->default('pendiente');
            $table->decimal('total', 10, 2)->default(0); // total en €
            $table->integer('total_lagartos')->default(0); // total en 🦎
            $table->boolean('solicitado')->default(false);
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
        Schema::dropIfExists('carritos');
    }
}
