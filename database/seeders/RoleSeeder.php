<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Role::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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
