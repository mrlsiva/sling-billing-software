<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PrinterType;
use DB;

class PrinterTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PrinterType::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        $types = [

            ['name' => 'A4 Vasantham', 'blade' => 'bill', 'is_active' => 1],
            ['name' => 'Thermal', 'blade' => 'thermal_bill', 'is_active' => 1],
            ['name' => 'Liya Fashion', 'blade' => 'liya_fashion_bill', 'is_active' => 1],
            ['name' => 'A4 AR Printers', 'blade' => 'ar_printer_bill', 'is_active' => 1],
        ];

        foreach ($types as $key => $value) {

            PrinterType::create($value);

        }
    }
}
