<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Version;
use DB;

class VersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Version::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $version = [

            ['component' => '1', 'release_date' => \Carbon\Carbon::parse('2025-01-09'), 'version' => '1.0', 'release_type' => '1', 'change_log' => 'First release', 'status' => '1'],
        ];

        foreach ($version as $key => $value) {

            Version::create($value);

        }
    }
}
