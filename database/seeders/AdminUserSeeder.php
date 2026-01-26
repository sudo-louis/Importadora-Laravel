<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permiso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $adminEmail = 'admin@logistic.mx';

            $user = User::query()->firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'SuperAdmin',
                    'password' => Hash::make('admin321'), // <-- CAMBIA ESTO
                    'is_active' => true,
                ]
            );

            $user->update([
                'name' => $user->name ?: 'Super Admin',
                'is_active' => true,
            ]);

            $role = Role::query()->firstOrCreate(
                ['name' => 'SUPERADMIN'],
                [
                    'color' => 'Rojo',
                    'description' => 'Acceso total al sistema',
                ]
            );

            $allPermisoIds = Permiso::query()->pluck('id')->all();

            if (method_exists($role, 'permisos')) {
                $role->permisos()->syncWithoutDetaching($allPermisoIds);
            } else {
                $existing = DB::table('role_permiso')
                    ->where('role_id', $role->id)
                    ->pluck('permiso_id')
                    ->all();

                $toInsert = array_values(array_diff($allPermisoIds, $existing));

                $now = now();
                foreach ($toInsert as $pid) {
                    DB::table('role_permiso')->insert([
                        'role_id' => $role->id,
                        'permiso_id' => $pid,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            if (method_exists($user, 'roles')) {
                $user->roles()->sync([$role->id]);
            } else {
                DB::table('user_role')->where('user_id', $user->id)->delete();

                DB::table('user_role')->insert([
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
