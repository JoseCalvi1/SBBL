<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Actualiza el correo electrónico del usuario autenticado.
     */
    public function updateEmail(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'current_password' => ['required', 'string'],
        ], [
            'email.required' => 'Introduce un correo electrónico.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado en otra cuenta.',
            'current_password.required' => 'Introduce tu contraseña actual para confirmar el cambio.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'emailUpdate')
                ->withInput($request->only('email'));
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'La contraseña actual no es correcta.'], 'emailUpdate')
                ->withInput($request->only('email'));
        }

        $newEmail = trim($request->email);
        if (strcasecmp($newEmail, $user->email) === 0) {
            return back()->with('account_status', 'El correo electrónico ya coincide con el actual.');
        }

        $user->email = $newEmail;
        $user->email_verified_at = null;
        $user->save();

        // Intentamos enviar el correo de verificación sin bloquear ni provocar error 500 si falla el SMTP
        try {
            if (method_exists($user, 'sendEmailVerificationNotification')) {
                $user->sendEmailVerificationNotification();
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el correo de verificación tras cambiar el email: ' . $e->getMessage());

            return back()->with(
                'account_warning',
                'Correo actualizado correctamente, pero no se pudo enviar el email de verificación en este momento.'
            );
        }

        return back()->with(
            'account_status',
            'Correo electrónico actualizado correctamente. Te hemos enviado un enlace de verificación a tu nueva dirección.'
        );
    }

    /**
     * Actualiza la contraseña del usuario autenticado.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Introduce tu contraseña actual.',
            'password.required' => 'Introduce una nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'passwordUpdate');
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(
                ['current_password' => 'La contraseña actual no es correcta.'],
                'passwordUpdate'
            );
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('account_status', 'Contraseña actualizada correctamente.');
    }
}
