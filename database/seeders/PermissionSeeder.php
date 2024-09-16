<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $data = $this->data();
        // Permission::create([
        //     'name' => 'full access',
        //     'alias' => 'Tam Yetki'
        // ]);
        foreach ($data as $value) {
            Permission::create([
                'name' => $value['name'],
                'alias' => $value['alias'],
                'guard_name' => "web"
            ]);
        }
    }

    public function data()
    {
        $data = [];
        $model = [
            'role' => 'Rol',
            'user' => 'Kullanıcı',

        ];
        $crud = [
            'view' => 'Görüntüleme',
            'edit' => 'Düzenleme',
            'create' => 'Ekleme',
            'delete' => 'Silme'
        ];

        foreach ($model as $key => $value) {
            foreach ($crud as $crudKey => $crudValue) {
                $data[] = [
                    'name' => $key . '_' . $crudKey,
                    'alias' => $value . ' ' . $crudValue,
                ];
            }
        }

        return $data;
    }
}
