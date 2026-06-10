<?php

namespace Tests\Unit;

use App\Services\Scc\FuzzyChargeController;
use PHPUnit\Framework\TestCase;

class FuzzyChargeControllerTest extends TestCase
{
    public function test_low_battery_in_daylight_uses_high_bulk_duty(): void
    {
        $result = (new FuzzyChargeController)->evaluate([
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
        $result = (new FuzzyChargeController)->evaluate([
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
        $result = (new FuzzyChargeController)->evaluate([
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

    public function test_mamdani_overlap_produces_gradual_output_near_error_boundary(): void
    {
        $controller = new FuzzyChargeController;

        $beforeBoundary = $controller->evaluate([
            'vpv' => 19.0,
            'ipv' => 2.0,
            'vbat' => 13.99,
            'ibat' => 1.5,
            'soc' => 60.0,
        ], 0.41);

        $afterBoundary = $controller->evaluate([
            'vpv' => 19.0,
            'ipv' => 2.0,
            'vbat' => 14.01,
            'ibat' => 1.5,
            'soc' => 60.0,
        ], 0.39);

        $this->assertLessThan(8.0, abs($beforeBoundary['duty_cycle'] - $afterBoundary['duty_cycle']));
    }

    public function test_mamdani_explanation_exposes_memberships_rules_and_centroid(): void
    {
        $explanation = (new FuzzyChargeController)->explain([
            'vpv' => 19.0,
            'ipv' => 2.2,
            'vbat' => 12.7,
            'ibat' => 1.8,
            'soc' => 48.0,
        ], 1.5);

        $this->assertArrayHasKey('error_memberships', $explanation);
        $this->assertArrayHasKey('delta_memberships', $explanation);
        $this->assertArrayHasKey('rule_strengths', $explanation);
        $this->assertArrayHasKey('mamdani_centroid', $explanation);
        $this->assertArrayHasKey('final_duty', $explanation);
        $this->assertSame(['NB', 'NS', 'ZO', 'PS', 'PB'], array_keys($explanation['rule_strengths']));
        $this->assertGreaterThan(0.0, max($explanation['rule_strengths']));
        $this->assertGreaterThanOrEqual(0.0, $explanation['final_duty']);
        $this->assertLessThanOrEqual(96.0, $explanation['final_duty']);
    }
}
