<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    use VerifiesEmails;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Sobrescribimos el método original para evitar que la web explote (Error 500)
     * si el servidor de correo está caído o bloqueado.
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->wantsJson()
                        ? new JsonResponse([], 204)
                        : redirect($this->redirectPath());
        }

        try {
            // Intentamos enviar el email
            $request->user()->sendEmailVerificationNotification();

            return $request->wantsJson()
                        ? new JsonResponse([], 202)
                        : back()->with('resent', true);

        } catch (\Exception $e) {
            // Si el SMTP falla, guardamos el error en secreto y avisamos al usuario amablemente
            Log::error('Error crítico enviando email de verificación: ' . $e->getMessage());

            return back()->with('error', 'El servidor de correos está saturado en este momento. Por favor, espera un par de minutos e inténtalo de nuevo.');
        }
    }
}
