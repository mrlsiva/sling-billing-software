<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Gender;
use DB;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Gender::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $gender = [

            ['name' => 'Male'],
            ['name' => 'Female'],
        ];

        foreach ($gender as $data) {
            Gender::firstOrCreate(
                [
                    'name' => $data['name'],
                ],
            );
        }
    }
}
