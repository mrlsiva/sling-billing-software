<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Finance;
use DB;

class FinanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Finance::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $payment = [

            ['name' => 'Bajaj CD'],
            ['name' => 'Bajaj LSF'],
            ['name' => 'TVS Finance'],
            ['name' => 'IDFC CD'],
            ['name' => 'IDFC LSF'],
            ['name' => 'OFC'],
        ];

        foreach ($payment as $data) {
            Finance::firstOrCreate(
                [
                    'name' => $data['name'],
                ],
                $data
            );
        }
    }
}
