<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Sobrescribimos el método original para añadir un Try-Catch
     * y evitar que la web devuelva un Error 500 si el servidor de correo falla.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        try {
            // Intentamos enviar el correo de recuperación
            $response = $this->broker()->sendResetLink(
                $this->credentials($request)
            );

            return $response == Password::RESET_LINK_SENT
                        ? $this->sendResetLinkResponse($request, $response)
                        : $this->sendResetLinkFailedResponse($request, $response);

        } catch (\Exception $e) {
            // Guardamos el error real en los logs para que tú puedas verlo (storage/logs/laravel.log)
            Log::error('Error al enviar correo de recuperación: ' . $e->getMessage());

            // Devolvemos al usuario a la vista anterior con un mensaje de error amigable
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'En este momento hay un problema con el servidor de correos. Por favor, inténtalo de nuevo en unos minutos.']);
        }
    }
}
