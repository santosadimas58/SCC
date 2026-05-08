<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SccDataApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_computes_fuzzy_control_fields_from_sensor_payload(): void
    {
        config(['services.scc.api_token' => 'test-token']);

        $response = $this->withHeader('X-SCC-Token', 'test-token')->postJson('/api/scc/data', [
            'vpv' => 19.0,
            'ipv' => 2.4,
            'vbat' => 12.0,
            'ibat' => 2.1,
            'soc' => 32.0,
            'duty_cycle' => 1.0,
            'fase' => 'Float',
            'label_e' => 'NB',
            'label_de' => 'NB',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.fase', 'Bulk')
            ->assertJsonPath('data.label_e', 'PB');

        $this->assertGreaterThan(65.0, $response->json('data.duty_cycle'));
        $this->assertDatabaseHas('scc_data', [
            'fase' => 'Bulk',
            'label_e' => 'PB',
        ]);
    }

    public function test_api_rejects_invalid_scc_token(): void
    {
        config(['services.scc.api_token' => 'test-token']);

        $response = $this->postJson('/api/scc/data', [
            'vpv' => 19.0,
            'ipv' => 2.4,
            'vbat' => 12.0,
            'ibat' => 2.1,
            'soc' => 32.0,
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false);
    }
}
