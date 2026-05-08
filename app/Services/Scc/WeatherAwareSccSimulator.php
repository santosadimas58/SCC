<?php

namespace App\Services\Scc;

use App\Models\SccData;

class WeatherAwareSccSimulator
{
    public function __construct(
        private readonly FuzzyChargeController $controller,
    ) {
    }

    public function createFromWeather(array $weather): ?SccData
    {
        if (! ($weather['available'] ?? false) || ! is_array($weather['current'] ?? null)) {
            return null;
        }

        $latest = SccData::latest()->first();
        $payload = $this->payload($weather['current'], $latest);
        $previousError = $latest
            ? $this->targetForPhase($latest->fase, $latest->vbat) - $latest->vbat
            : null;

        return SccData::create($this->controller->evaluate($payload, $previousError));
    }

    private function payload(array $forecast, ?SccData $latest): array
    {
        $demoMode = (bool) config('services.bmkg_weather.demo_mode', false);
        $factor = $this->demoAdjustedSolarFactor($this->solarFactor($forecast), $latest, $demoMode);
        $isDaylight = $demoMode || $this->isDaylight($forecast['time'] ?? null);
        $effectiveFactor = $isDaylight ? $factor : 0.0;
        $previousSoc = $latest?->soc ?? $this->initialSoc($effectiveFactor);
        $socDelta = $isDaylight
            ? (-0.08 + ($effectiveFactor * 0.95))
            : -0.22;
        $socDelta *= $demoMode
            ? (float) config('services.bmkg_weather.demo_soc_delta_multiplier', 5.0)
            : 1.0;
        $soc = $this->clamp($previousSoc + $socDelta, 18.0, 99.0);
        $targetVbat = 11.75 + ($soc / 100.0 * 2.7);
        $vbat = $this->clamp(($latest?->vbat ?? $targetVbat) + (($targetVbat - ($latest?->vbat ?? $targetVbat)) * 0.35), 11.4, 14.45);
        $cloudPenalty = (float) (($forecast['cloud_cover'] ?? 45.0) / 100.0);
        $temperature = (float) ($forecast['temperature'] ?? 24.0);

        $vpv = $isDaylight
            ? 15.1 + ($effectiveFactor * 6.2) - ($cloudPenalty * 0.9) + (($temperature - 24.0) * 0.025)
            : 0.6 + ($effectiveFactor * 2.0);

        $ipv = $isDaylight
            ? max(0.05, 0.25 + ($effectiveFactor * 4.4) - ($cloudPenalty * 0.55))
            : 0.0;

        $ibat = $isDaylight
            ? max(0.0, 0.18 + ($effectiveFactor * 3.2) - max(0.0, ($soc - 82.0) * 0.045))
            : 0.0;

        return [
            'vpv' => round($this->clamp($vpv, 0.0, 23.5), 2),
            'ipv' => round($this->clamp($ipv, 0.0, 6.0), 2),
            'vbat' => round($vbat, 2),
            'ibat' => round($this->clamp($ibat, 0.0, 5.5), 2),
            'soc' => round($soc, 2),
        ];
    }

    private function demoAdjustedSolarFactor(float $factor, ?SccData $latest, bool $demoMode): float
    {
        if (! $demoMode) {
            return $factor;
        }

        $minimum = (float) config('services.bmkg_weather.demo_min_solar_factor', 0.62);
        $step = (($latest?->id ?? 0) % 8) / 7.0;
        $pulse = sin($step * M_PI * 2.0) * 0.12;

        return $this->clamp(max($factor, $minimum) + $pulse, 0.35, 1.0);
    }

    private function solarFactor(array $forecast): float
    {
        $weather = mb_strtolower((string) ($forecast['weather'] ?? ''));
        $cloudCover = $forecast['cloud_cover'] ?? null;
        $factor = $cloudCover === null
            ? 0.72
            : 1.0 - ($this->clamp((float) $cloudCover, 0.0, 100.0) / 100.0 * 0.72);

        if (str_contains($weather, 'cerah')) {
            $factor += 0.18;
        }

        if (str_contains($weather, 'berawan')) {
            $factor -= 0.18;
        }

        if (str_contains($weather, 'hujan')) {
            $factor -= 0.5;
        }

        if (str_contains($weather, 'petir') || str_contains($weather, 'lebat')) {
            $factor -= 0.2;
        }

        return $this->clamp($factor, 0.05, 1.0);
    }

    private function isDaylight(?string $time): bool
    {
        if (! $time) {
            $hour = (int) now('Asia/Jakarta')->format('G');
        } else {
            $hour = (int) substr($time, 0, 2);
        }

        return $hour >= 6 && $hour <= 17;
    }

    private function initialSoc(float $factor): float
    {
        return $this->clamp(42.0 + ($factor * 18.0), 30.0, 72.0);
    }

    private function targetForPhase(string $phase, float $vbat): float
    {
        return match ($phase) {
            'Bulk', 'Absorption' => FuzzyChargeController::BULK_TARGET,
            'Float' => FuzzyChargeController::FLOAT_TARGET,
            default => $vbat,
        };
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return min($max, max($min, $value));
    }
}
