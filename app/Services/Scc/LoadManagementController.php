<?php

namespace App\Services\Scc;

class LoadManagementController
{
    public function evaluate(array $input): array
    {
        $vpv = (float) ($input['vpv'] ?? 0.0);
        $ipv = max(0.0, (float) ($input['ipv'] ?? 0.0));
        $vbat = max(0.0, (float) ($input['vbat'] ?? 0.0));
        $soc = max(0.0, min(100.0, (float) ($input['soc'] ?? 0.0)));
        $panelPower = $vpv * $ipv;
        $panelStrong = $panelPower >= 18.0 && $vpv >= 17.0;
        $loadName = $this->loadName($soc, $panelStrong);
        $basePower = $this->basePower($loadName);

        if ($soc < 30.0 || ($soc < 45.0 && ! $panelStrong)) {
            $status = 'OFF';
            $loadPower = 0.0;
            $reason = $panelStrong
                ? 'Baterai rendah, beban dimatikan agar energi panel diprioritaskan untuk charging.'
                : 'Baterai rendah dan panel lemah, beban dimatikan untuk proteksi low battery.';
        } elseif ($soc <= 60.0) {
            $status = 'LIMITED';
            $loadPower = $basePower * 0.45;
            $reason = $panelStrong
                ? 'SoC menengah dan panel cukup kuat, beban dibatasi sambil charging tetap berjalan.'
                : 'SoC menengah, beban dibatasi agar baterai tidak turun terlalu cepat.';
        } else {
            $status = 'ON';
            $loadPower = $panelStrong ? $basePower : $basePower * 0.75;
            $reason = $panelStrong
                ? 'Panel kuat dan baterai cukup, beban boleh ON sambil charging.'
                : 'Baterai cukup, beban ON dengan daya konservatif karena panel tidak dominan.';
        }

        $loadCurrent = $vbat > 0.0 ? $loadPower / $vbat : 0.0;
        $netPower = $panelPower - $loadPower;

        return [
            'load_name' => $loadName,
            'load_status' => $status,
            'load_power' => round($loadPower, 2),
            'load_current' => round($loadCurrent, 3),
            'net_power' => round($netPower, 2),
            'load_reason' => $reason,
        ];
    }

    private function loadName(float $soc, bool $panelStrong): string
    {
        if ($soc >= 75.0 && $panelStrong) {
            return 'Kipas DC';
        }

        return 'Lampu DC';
    }

    private function basePower(string $loadName): float
    {
        return $loadName === 'Kipas DC' ? 4.8 : 2.4;
    }
}
