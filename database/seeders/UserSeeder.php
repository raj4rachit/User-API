<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usersData = [
            [
                'name' => 'Beytullah YAŞAR',
                'phone' => '+99999999999',
                'email' => 'beytullahyasar06@gmail.com',
                'password' => Hash::make('demo'),
            ],
        ];

        foreach ($usersData as $data) {
            $user = User::create(array_merge($data, ['email_verified_at' => now()]));
            $role = Role::where('name', 'Super Admin')->first();
            if ($role) {
                $user->assignRole($role->name);
            }
        }
    }
}
