<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FirstRole extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);
        $fullAccessPermission = Permission::firstOrCreate([
            'name' => 'full_access',
            'alias' => 'Full Access',
            'guard_name' => 'web'
        ]);
        $superadminRole->permissions()->sync($fullAccessPermission->id);
    }
}
