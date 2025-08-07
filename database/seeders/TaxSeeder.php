<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tax;
use DB;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Tax::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $tax = [

            ['name' => 'GST 0%'],
            ['name' => 'GST 5%'],
            ['name' => 'GST 12%'],
            ['name' => 'GST 18%'],
            ['name' => 'GST 28%'],
        ];

        foreach ($tax as $data) {
            Tax::firstOrCreate(
                [
                    'name' => $data['name'],
                ],
                $data
            );
        }
    }
}
