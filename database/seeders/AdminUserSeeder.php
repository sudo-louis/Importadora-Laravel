<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Role;
use App\Models\Permiso;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $admin = User::query()->updateOrCreate(
                ['email' => 'admin@logistic.mx'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('JSDFH6758__!!'), // 👈 cámbiala si quieres
                    'is_active' => true,
                ]
            );

            $role = Role::query()->firstOrCreate(
                ['name' => 'SUPERADMIN'],
                [
                    'color' => 'Rojo',
                    'description' => 'Rol con acceso total al sistema',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $admin->roles()->syncWithoutDetaching([$role->id]);

            $permisoIds = Permiso::query()->pluck('id')->all();

            $role->permisos()->syncWithoutDetaching($permisoIds);

            $admin->is_active = true;
            $admin->save();
        });
    }
}
