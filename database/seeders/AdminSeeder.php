<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\User;
use DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {

            DB::beginTransaction();
            $user = User::create([
                'role_id' => 1,
                'unique_id' => '10000',
                'name' => 'Super Admin',
                'user_name' => 'super_admin',
                'email' => 'super_admin@admin.com',
                'phone' => '1234567890',
                'address' => 'Tuticorin',
                'gst' => '12345',
                'password' => \Hash::make('Admin@2025'),
                'is_active' => 1,
                'is_lock' => 0,
                'is_delete' => 0,
            ]);

            $user->update([
                'created_by' => $user->id
            ]);

            $role = Role::where('id',1)->first()->name;
            $user->assignRole($role);


            DB::commit();
        }
        catch (Exception $e) {

            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }
}
