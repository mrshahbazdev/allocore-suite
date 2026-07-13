<?php

namespace Modules\FinancialPlatform\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(KpiThresholdsSeeder::class);
    }
}
