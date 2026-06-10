<?php

namespace Tests\Feature;

use App\Models\SccData;
use App\Services\Scc\SccDemoData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SccDemoDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_reset_only_inserts_persistent_database_fields(): void
    {
        $count = app(SccDemoData::class)->reset(30);

        $this->assertSame(30, $count);
        $this->assertSame(30, SccData::count());

        $latest = SccData::latest()->firstOrFail();

        $this->assertNotNull($latest->load_status);
        $this->assertNotNull($latest->load_power);
        $this->assertArrayNotHasKey('load_items', $latest->getAttributes());
        $this->assertArrayNotHasKey('load_score', $latest->getAttributes());
    }
}
