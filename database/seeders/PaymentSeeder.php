<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;
use DB;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Payment::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $payment = [

            ['name' => 'Cash'],
            ['name' => 'Card'],
            ['name' => 'UPI'],
            ['name' => 'Exchange'],
            ['name' => 'Finanace'],
            ['name' => 'Credit'],
            ['name' => 'Cheque'],
        ];

        foreach ($payment as $data) {
            Payment::firstOrCreate(
                [
                    'name' => $data['name'],
                ],
                $data
            );
        }
    }
}
