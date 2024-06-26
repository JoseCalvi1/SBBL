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

        $user = User::create([
            'name' => 'Test1',
            'email' => 'test1@test1.com',
            'is_admin' => false,
            'password' => Hash::make('josecalvi1'),
        ]);

        $user = User::create([
            'name' => 'Test2',
            'email' => 'test2@test2.com',
            'is_admin' => false,
            'password' => Hash::make('josecalvi1'),
        ]);

        $user = User::create([
            'name' => 'Test3',
            'email' => 'test3@test3.com',
            'is_admin' => false,
            'password' => Hash::make('josecalvi1'),
        ]);

        $user = User::create([
            'name' => 'Test4',
            'email' => 'test4@test4.com',
            'is_admin' => false,
            'password' => Hash::make('josecalvi1'),
        ]);

        $user = User::create([
            'name' => 'Test5',
            'email' => 'test5@test5.com',
            'is_admin' => false,
            'password' => Hash::make('josecalvi1'),
        ]);
    }
}
