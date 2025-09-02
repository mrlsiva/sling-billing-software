<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(FinanceSeeder::class);
        $this->call(MetricSeeder::class);
        $this->call(PaymentSeeder::class);
        $this->call(TaxSeeder::class);
    }
}
