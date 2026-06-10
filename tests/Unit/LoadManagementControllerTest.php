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
            'ipv' => 5.0,
            'vbat' => 12.8,
            'soc' => 78.0,
            'fase' => 'Float',
            'label_e' => 'NB',
        ]);

        $this->assertSame('Lampu DC, Kipas DC, Pompa DC', $result['load_name']);
        $this->assertSame('ON', $result['load_status']);
        $this->assertSame(36.0, $result['load_power']);
        $this->assertGreaterThan(0.0, $result['load_current']);
        $this->assertGreaterThan(0.0, $result['net_power']);
        $this->assertCount(3, $result['load_items']);
    }

    public function test_medium_soc_limits_load(): void
    {
        $result = (new LoadManagementController())->evaluate([
            'vpv' => 17.5,
            'ipv' => 0.8,
            'vbat' => 12.4,
            'soc' => 45.0,
            'fase' => 'Bulk',
            'label_e' => 'PS',
        ]);

        $this->assertSame('Lampu DC', $result['load_name']);
        $this->assertSame('LIMITED', $result['load_status']);
        $this->assertSame(4.0, $result['load_power']);
        $this->assertSame('OFF', $result['load_items'][1]['load_status']);
    }

    public function test_low_soc_turns_load_off(): void
    {
        $result = (new LoadManagementController())->evaluate([
            'vpv' => 14.0,
            'ipv' => 0.2,
            'vbat' => 11.8,
            'soc' => 25.0,
            'fase' => 'Standby',
        ]);

        $this->assertSame('OFF', $result['load_status']);
        $this->assertSame(0.0, $result['load_power']);
        $this->assertLessThanOrEqual(3.0, $result['net_power']);
    }
}
