<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Carrito;

class PedidoTiendaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre;
    public $email;
    public $direccion;
    public $metodoPago;
    public $carrito;
    public $referencia;
    public $total;

    public function __construct($nombre, $email, $direccion, $metodoPago, Carrito $carrito, $referencia, $total)
    {
        $this->nombre = $nombre;
        $this->email = $email;
        $this->direccion = $direccion;
        $this->metodoPago = $metodoPago;
        $this->carrito = $carrito;
        $this->referencia = $referencia;
        $this->total = $total;
    }

    public function build()
    {
        return $this->subject("Nuevo pedido {$this->referencia} ({$this->metodoPago})")
                    ->view('emails.pedido_tienda')
                    ->with([
                        'nombre' => $this->nombre,
                        'email' => $this->email,
                        'direccion' => $this->direccion,
                        'metodoPago' => $this->metodoPago,
                        'carrito' => $this->carrito,
                        'referencia' => $this->referencia,
                        'total' => $this->total,
                    ]);
    }
}
