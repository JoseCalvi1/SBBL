<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class RoleTransferSeeder extends Seeder
{
    public function run()
    {
        // 1. Creamos los roles base de la SBBL
        $roles = ['admin', 'juez', 'arbitro', 'editor', 'revisor'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // 2. Escaneamos a todos los usuarios y los mudamos al nuevo sistema
        $users = User::all();
        foreach ($users as $user) {
            if ($user->is_admin)    $user->assignRole('admin');
            if ($user->is_jury)     $user->assignRole('juez');
            if ($user->is_referee)  $user->assignRole('arbitro');
            if ($user->is_editor)   $user->assignRole('editor');
            if ($user->is_reviewer) $user->assignRole('revisor');
        }

        $this->command->info('¡Traspaso de roles completado con éxito, Comandante!');
    }
}
