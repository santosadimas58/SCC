<?php

namespace App\Services\Scc;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class BmkgWeatherService
{
    public function forecast(): array
    {
        $adm4 = config('services.bmkg_weather.adm4', '32.73.08.1003');
        $ttl = (int) config('services.bmkg_weather.cache_seconds', 1800);
        $key = $this->cacheKey($adm4);
        $lastGoodKey = "{$key}:last-good";

        try {
            $cached = Cache::get($key);
            if (is_array($cached)) {
                return $cached;
            }
        } catch (Throwable) {
            $cached = null;
        }

        try {
            $forecast = $this->fetch($adm4);

            if ($forecast['available']) {
                try {
                    Cache::put($key, $forecast, $ttl);
                    Cache::put($lastGoodKey, $forecast, now()->addDay());
                } catch (Throwable) {
                    // Cache storage is optional for the dashboard; live data can still render.
                }
            }

            if ($forecast['available']) {
                return $forecast;
            }

            try {
                return Cache::get($lastGoodKey, $forecast);
            } catch (Throwable) {
                return $forecast;
            }
        } catch (Throwable) {
            try {
                return Cache::get($lastGoodKey, $this->unavailable());
            } catch (Throwable) {
                return $this->unavailable();
            }
        }
    }

    private function fetch(string $adm4): array
    {
        $response = Http::acceptJson()
            ->timeout(8)
            ->retry(2, 300)
            ->get('https://api.bmkg.go.id/publik/prakiraan-cuaca', [
                'adm4' => $adm4,
            ]);

        if (! $response->successful()) {
            return $this->unavailable('Data BMKG belum bisa diambil.');
        }

        $payload = $response->json();
        $forecasts = $this->flattenForecasts($payload);
        $current = $this->nearestForecast($forecasts);

        if (! $current) {
            return $this->unavailable('Data BMKG belum memuat prakiraan cuaca.');
        }

        $location = $this->locationFromPayload($payload);
        $upcoming = $this->upcomingForecasts($forecasts);

        return [
            'available' => true,
            'location_label' => config('services.bmkg_weather.display_name', 'Setiabudhi, Bandung'),
            'source_location' => $location,
            'adm4' => $adm4,
            'current' => $current,
            'upcoming' => $upcoming,
            'solar_note' => $this->solarNote($current),
            'source' => 'BMKG',
            'source_url' => 'https://data.bmkg.go.id/prakiraan-cuaca/',
            'fetched_at' => now('Asia/Jakarta')->format('d M Y H:i'),
            'message' => null,
        ];
    }

    private function flattenForecasts(?array $payload): array
    {
        $groups = data_get($payload, 'data.0.cuaca', data_get($payload, 'data.cuaca', []));
        $items = [];

        $this->collectForecastItems($groups, $items);

        return collect($items)
            ->map(fn (array $item) => $this->normalizeForecast($item))
            ->filter(fn (array $item) => $item['time'] !== null)
            ->sortBy('timestamp')
            ->values()
            ->all();
    }

    private function collectForecastItems(mixed $node, array &$items): void
    {
        if (! is_array($node)) {
            return;
        }

        if (isset($node['local_datetime']) || isset($node['datetime'])) {
            $items[] = $node;

            return;
        }

        foreach ($node as $child) {
            $this->collectForecastItems($child, $items);
        }
    }

    private function normalizeForecast(array $item): array
    {
        $time = $this->parseLocalTime($item['local_datetime'] ?? $item['datetime'] ?? null);
        $weather = $item['weather_desc'] ?? $item['weather_desc_en'] ?? '-';
        $cloudCover = $this->numberOrNull($item['tcc'] ?? null);

        return [
            'time' => $time?->format('H:i'),
            'date' => $time?->format('d M Y'),
            'timestamp' => $time?->timestamp ?? 0,
            'weather' => $weather,
            'temperature' => $this->numberOrNull($item['t'] ?? null),
            'humidity' => $this->numberOrNull($item['hu'] ?? null),
            'wind_speed' => $this->numberOrNull($item['ws'] ?? null),
            'wind_direction' => $item['wd'] ?? '-',
            'cloud_cover' => $cloudCover,
            'visibility' => $item['vs_text'] ?? '-',
            'tone' => $this->weatherTone($weather, $cloudCover),
            'analysis_time' => $this->formatAnalysisTime($item['analysis_date'] ?? null),
        ];
    }

    private function nearestForecast(array $forecasts): ?array
    {
        if ($forecasts === []) {
            return null;
        }

        $now = now('Asia/Jakarta')->timestamp;

        return collect($forecasts)->first(fn ($item) => $item['timestamp'] >= $now)
            ?? collect($forecasts)->last();
    }

    private function upcomingForecasts(array $forecasts): array
    {
        $now = now('Asia/Jakarta')->timestamp;

        return collect($forecasts)
            ->filter(fn ($item) => $item['timestamp'] >= $now)
            ->take(4)
            ->values()
            ->all();
    }

    private function locationFromPayload(?array $payload): string
    {
        $location = data_get($payload, 'lokasi', data_get($payload, 'data.0.lokasi', []));

        $parts = array_filter([
            data_get($location, 'desa'),
            data_get($location, 'kecamatan'),
            data_get($location, 'kotkab'),
        ]);

        return $parts !== []
            ? implode(', ', $parts)
            : config('services.bmkg_weather.source_name', 'Ledeng, Cidadap, Kota Bandung');
    }

    private function parseLocalTime(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value, 'Asia/Jakarta');
        } catch (Throwable) {
            return null;
        }
    }

    private function formatAnalysisTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->setTimezone('Asia/Jakarta')->format('d M Y H:i');
        } catch (Throwable) {
            return null;
        }
    }

    private function numberOrNull(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function weatherTone(string $weather, ?float $cloudCover): string
    {
        $weather = mb_strtolower($weather);

        if (str_contains($weather, 'petir') || str_contains($weather, 'lebat')) {
            return 'critical';
        }

        if (str_contains($weather, 'hujan')) {
            return 'warning';
        }

        if (str_contains($weather, 'berawan') || ($cloudCover !== null && $cloudCover >= 70.0)) {
            return 'info';
        }

        return 'normal';
    }

    private function solarNote(array $forecast): string
    {
        $weather = mb_strtolower($forecast['weather'] ?? '');
        $cloudCover = $forecast['cloud_cover'] ?? null;

        if (str_contains($weather, 'hujan') || str_contains($weather, 'petir')) {
            return 'Potensi produksi panel turun karena prakiraan hujan di sekitar lokasi.';
        }

        if ($cloudCover !== null && $cloudCover >= 70.0) {
            return 'Tutupan awan tinggi, produksi panel bisa lebih rendah dari kondisi cerah.';
        }

        if (str_contains($weather, 'berawan')) {
            return 'Kondisi berawan dapat membuat daya panel naik turun.';
        }

        return 'Cuaca mendukung pembacaan panel yang lebih stabil.';
    }

    private function unavailable(string $message = 'Data cuaca BMKG belum tersedia.'): array
    {
        return [
            'available' => false,
            'location_label' => config('services.bmkg_weather.display_name', 'Setiabudhi, Bandung'),
            'source_location' => config('services.bmkg_weather.source_name', 'Ledeng, Cidadap, Kota Bandung'),
            'adm4' => config('services.bmkg_weather.adm4', '32.73.08.1003'),
            'current' => null,
            'upcoming' => [],
            'solar_note' => '-',
            'source' => 'BMKG',
            'source_url' => 'https://data.bmkg.go.id/prakiraan-cuaca/',
            'fetched_at' => null,
            'message' => $message,
        ];
    }

    private function cacheKey(string $adm4): string
    {
        return "bmkg-weather:{$adm4}";
    }
}
