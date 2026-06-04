<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class DiscordController extends Controller
{
    /**
     * 1. Redirige al usuario a la pantalla de autorización de Discord.
     */
    public function redirectToDiscord()
    {
        return Socialite::driver('discord')->scopes(['identify'])->redirect();
    }

    /**
     * 2. Recibe los datos de vuelta cuando el usuario acepta.
     */
    public function handleDiscordCallback()
    {
        try {
            // Obtenemos los datos del usuario desde Discord
            $discordUser = Socialite::driver('discord')->user();
            $user = Auth::user();

            // Guardamos el ID de Discord en tu base de datos
            $user->discord_id = $discordUser->getId();
            $user->save();

            // 3. ¡Magia! Le damos el rol en el servidor
            $this->assignDiscordRole($user->discord_id);

            // Redirigimos pasando su ID como parámetro
            return redirect()->route('profiles.show', ['profile' => $user->id])
                ->with('success', '¡Cuenta de Discord vinculada! Si estabas en el servidor, se te ha asignado el rol VIP.');
        } catch (\Exception $e) {
            Log::error('Error vinculando Discord: ' . $e->getMessage());
            redirect()->route('profiles.show', ['profile' => $user->id])
            ->with('error', 'Se canceló la vinculación o hubo un error con Discord.');
        }
    }

    /**
     * 3. Conexión a la API de Discord mediante el Bot
     */
    private function assignDiscordRole($discordId)
    {
        $guildId = config('services.discord.guild_id');
        $roleId = config('services.discord.role_id');
        $botToken = config('services.discord.bot_token');

        if (!$guildId || !$roleId || !$botToken) {
            Log::warning('Faltan credenciales de Discord Bot en el .env');
            return;
        }

        // Endpoint oficial de Discord para añadir un rol a un usuario
        $url = "https://discord.com/api/v10/guilds/{$guildId}/members/{$discordId}/roles/{$roleId}";

        // Hacemos la petición PUT autenticados como el Bot
        $response = Http::withHeaders([
            'Authorization' => "Bot {$botToken}",
        ])->put($url);

        if (!$response->successful()) {
            Log::error("Fallo al asignar rol de Discord al usuario {$discordId}. Código: " . $response->status() . " Body: " . $response->body());
        }
    }
}
