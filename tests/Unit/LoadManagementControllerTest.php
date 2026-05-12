<?php

namespace Tests\Unit;

use App\Services\Scc\LoadManagementController;
use PHPUnit\Framework\TestCase;

class LoadManagementControllerTest extends TestCase
{
    public function test_high_soc_and_strong_panel_turns_load_on(): void
    {
        $result = (new LoadManagementController())->evaluate([
            'vpv' => 19.0,
            'ipv' => 2.0,
            'vbat' => 12.8,
            'soc' => 78.0,
        ]);

        $this->assertSame('Kipas DC', $result['load_name']);
        $this->assertSame('ON', $result['load_status']);
        $this->assertSame(4.8, $result['load_power']);
        $this->assertGreaterThan(0.0, $result['load_current']);
        $this->assertGreaterThan(0.0, $result['net_power']);
    }

    public function test_medium_soc_limits_load(): void
    {
        $result = (new LoadManagementController())->evaluate([
            'vpv' => 17.5,
            'ipv' => 0.8,
            'vbat' => 12.4,
            'soc' => 45.0,
        ]);

        $this->assertSame('Lampu DC', $result['load_name']);
        $this->assertSame('LIMITED', $result['load_status']);
        $this->assertSame(1.08, $result['load_power']);
    }

    public function test_low_soc_turns_load_off(): void
    {
        $result = (new LoadManagementController())->evaluate([
            'vpv' => 14.0,
            'ipv' => 0.2,
            'vbat' => 11.8,
            'soc' => 25.0,
        ]);

        $this->assertSame('OFF', $result['load_status']);
        $this->assertSame(0.0, $result['load_power']);
        $this->assertLessThanOrEqual(3.0, $result['net_power']);
    }
}
