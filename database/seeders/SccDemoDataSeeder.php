<?php

namespace Database\Seeders;

use App\Services\Scc\SccDemoData;
use Illuminate\Database\Seeder;

class SccDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        app(SccDemoData::class)->reset();
    }
}
