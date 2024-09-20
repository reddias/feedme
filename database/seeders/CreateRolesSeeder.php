<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CreateRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'admin',
            'user',
            'guest'
        ];

        foreach ($roles as $role) {
            Role::query()->firstOrCreate(['name' => $role]);
        }
    }
}
