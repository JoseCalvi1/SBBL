<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Verificar si el usuario está autenticado y es un administrador
        if (Auth::check() && Auth::user()->is_admin == 1) {
            return $next($request);
        }

        // Si el usuario no es un administrador, redirigir al home
        return redirect('/home');
    }
}
