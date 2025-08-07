<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Metric;
use DB;

class MetricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Metric::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $metric = [

            ['name' => 'Pcs'],
        ];

        foreach ($metric as $data) {
            Metric::firstOrCreate(
                [
                    'name' => $data['name'],
                ],
                $data
            );
        }
    }
}
