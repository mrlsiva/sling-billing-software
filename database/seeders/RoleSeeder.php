<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [

            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['name' => 'HO', 'guard_name' => 'web'],
            ['name' => 'Branch', 'guard_name' => 'web'],
        ];

        foreach ($roles as $key => $value) {

            Role::create($value);

        }
    }
}
