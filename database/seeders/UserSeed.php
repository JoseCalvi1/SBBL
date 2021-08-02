<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name' => 'JoseCalvi1',
            'email' => 'josecalvilloolmedo@gmail.com',
            'is_admin' => true,
            'password' => Hash::make('josecalvi1'),
        ]);
    }
}
