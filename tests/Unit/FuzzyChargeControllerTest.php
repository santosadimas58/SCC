<?php

namespace Tests\Unit;

use App\Services\Scc\FuzzyChargeController;
use PHPUnit\Framework\TestCase;

class FuzzyChargeControllerTest extends TestCase
{
    public function test_low_battery_in_daylight_uses_high_bulk_duty(): void
    {
        $result = (new FuzzyChargeController())->evaluate([
            'vpv' => 19.2,
            'ipv' => 2.1,
            'vbat' => 12.1,
            'ibat' => 1.9,
            'soc' => 35.0,
        ]);

        $this->assertSame('Bulk', $result['fase']);
        $this->assertSame('PB', $result['label_e']);
        $this->assertGreaterThanOrEqual(65.0, $result['duty_cycle']);
    }

    public function test_low_panel_voltage_enters_standby_and_disables_pwm(): void
    {
        $result = (new FuzzyChargeController())->evaluate([
            'vpv' => 8.0,
            'ipv' => 0.0,
            'vbat' => 12.6,
            'ibat' => 0.0,
            'soc' => 60.0,
        ]);

        $this->assertSame('Standby', $result['fase']);
        $this->assertSame(0.0, $result['duty_cycle']);
        $this->assertSame('ZO', $result['label_e']);
    }

    public function test_full_battery_uses_float_with_limited_duty(): void
    {
        $result = (new FuzzyChargeController())->evaluate([
            'vpv' => 18.4,
            'ipv' => 0.8,
            'vbat' => 14.4,
            'ibat' => 0.5,
            'soc' => 96.0,
        ]);

        $this->assertSame('Float', $result['fase']);
        $this->assertSame('NB', $result['label_e']);
        $this->assertLessThanOrEqual(10.0, $result['duty_cycle']);
    }
}
