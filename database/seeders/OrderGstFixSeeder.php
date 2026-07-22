<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BillingAddress;
use App\Models\Order;

class OrderGstFixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::with('customer')->get();

        foreach ($orders as $order) {

            if ($order->customer && !empty($order->customer->gst)) {
                BillingAddress::where('order_id', $order->id)
                    ->update([
                        'gst' => $order->customer->gst,
                    ]);
            }

        }

        $this->command->info('Order GST updated successfully.');
    }
}
