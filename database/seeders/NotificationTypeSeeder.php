<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NotificationType;
use DB;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        NotificationType::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $notification_types = [

            ['name' => 'Category', 'order_by' => 1],
            ['name' => 'Sub Category', 'order_by' => 2],
            ['name' => 'Tax', 'order_by' => 3],
            ['name' => 'Metric', 'order_by' => 4],
            ['name' => 'Product', 'order_by' => 5],
            ['name' => 'Vendor', 'order_by' => 6],
            ['name' => 'Purchase Order', 'order_by' => 7],
            ['name' => 'Product Tranfer', 'order_by' => 8],
            ['name' => 'Finance', 'order_by' => 9],
            ['name' => 'Payment Method', 'order_by' => 10],
            ['name' => 'Bill Setup', 'order_by' => 11],
            ['name' => 'Customer', 'order_by' => 12],
            ['name' => 'Staff', 'order_by' => 13],
            ['name' => 'Order', 'order_by' => 14],
            ['name' => 'Report', 'order_by' => 15],
        ];

        foreach ($notification_types as $data) {
            NotificationType::firstOrCreate(
                [
                    'name'     => $data['name'],
                    'order_by' => $data['order_by'],
                ],
            );
        }
    }
}
