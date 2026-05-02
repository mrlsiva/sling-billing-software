<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Stock;
use App\Models\StockVariation;
use App\Models\Product;

class StockVariationSeeder extends Seeder
{
    public function run()
    {
        // Optional: clear existing data
        StockVariation::truncate();

        $stocks = Stock::all();

        foreach ($stocks as $stock) {

            // Get product price
            $product = Product::find($stock->product_id);

            if (!$product) {
                continue; // skip if product missing
            }

            StockVariation::create([
                'stock_id'   => $stock->id,
                'product_id' => $stock->product_id,
                'size_id'    => null,
                'colour_id'  => null,
                'quantity'   => $stock->quantity,
                'price'      => $product->price ?? 0,
            ]);
        }
    }
}
