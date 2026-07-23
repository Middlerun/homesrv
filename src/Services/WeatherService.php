<?php

namespace HomeSrv\Services;

use HomeSrv\Models\Setting;

class WeatherService
{
    private const CACHE_FILE = __DIR__ . '/../../storage/weather_cache.json';

    private const WMO_CODES = [
        0 => 'Clear sky',
        1 => 'Mainly clear',
        2 => 'Partly cloudy',
        3 => 'Overcast',
        45 => 'Fog',
        48 => 'Rime fog',
        51 => 'Light drizzle',
        53 => 'Moderate drizzle',
        55 => 'Dense drizzle',
        56 => 'Light freezing drizzle',
        57 => 'Dense freezing drizzle',
        61 => 'Slight rain',
        63 => 'Moderate rain',
        65 => 'Heavy rain',
        66 => 'Light freezing rain',
        67 => 'Heavy freezing rain',
        71 => 'Slight snow',
        73 => 'Moderate snow',
        75 => 'Heavy snow',
        77 => 'Snow grains',
        80 => 'Slight rain showers',
        81 => 'Moderate rain showers',
        82 => 'Violent rain showers',
        85 => 'Slight snow showers',
        86 => 'Heavy snow showers',
        95 => 'Thunderstorm',
        96 => 'Thunderstorm with slight hail',
        99 => 'Thunderstorm with heavy hail',
    ];

    public static function get(): ?array
    {
        $lat = Setting::get('weather_latitude');
        $lon = Setting::get('weather_longitude');

        if (!$lat || !$lon) {
            return null;
        }

        $cacheMinutes = (int) Setting::get('weather_cache_minutes', '30');
        $cached = self::loadCache();

        if ($cached && isset($cached['fetched_at'])) {
            $age = time() - $cached['fetched_at'];
            if ($age < $cacheMinutes * 60) {
                return $cached['data'];
            }
        }

        $data = self::fetch($lat, $lon);
        if ($data) {
            self::saveCache($data);
            return $data;
        }

        // Return stale cache if fetch fails
        return $cached['data'] ?? null;
    }

    private static function fetch(string $lat, string $lon): ?array
    {
        $units = Setting::get('weather_units', 'celsius');
        $tempUnit = $units === 'fahrenheit' ? 'fahrenheit' : 'celsius';

        $url = sprintf(
            'https://api.open-meteo.com/v1/forecast?latitude=%s&longitude=%s&current=temperature_2m,weather_code,relative_humidity_2m&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max&temperature_unit=%s&timezone=auto&forecast_days=5',
            urlencode($lat),
            urlencode($lon),
            $tempUnit
        );

        $ctx = stream_context_create(['http' => ['timeout' => 10]]);
        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            return null;
        }

        $json = json_decode($response, true);
        if (!$json || !isset($json['current'])) {
            return null;
        }

        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $forecast = [];
        $daily = $json['daily'] ?? [];

        for ($i = 1; $i < min(5, count($daily['time'] ?? [])); $i++) {
            $dayOfWeek = (int) date('w', strtotime($daily['time'][$i]));
            $forecast[] = [
                'name' => $dayNames[$dayOfWeek],
                'high' => round($daily['temperature_2m_max'][$i]),
                'low' => round($daily['temperature_2m_min'][$i]),
                'code' => $daily['weather_code'][$i] ?? 0,
                'description' => self::WMO_CODES[$daily['weather_code'][$i] ?? 0] ?? 'Unknown',
            ];
        }

        return [
            'temperature' => round($json['current']['temperature_2m']),
            'code' => $json['current']['weather_code'] ?? 0,
            'description' => self::WMO_CODES[$json['current']['weather_code'] ?? 0] ?? 'Unknown',
            'humidity' => $json['current']['relative_humidity_2m'] ?? null,
            'rain_chance' => $json['daily']['precipitation_probability_max'][0] ?? null,
            'forecast' => $forecast,
        ];
    }

    private static function loadCache(): ?array
    {
        if (!file_exists(self::CACHE_FILE)) {
            return null;
        }
        $data = json_decode(file_get_contents(self::CACHE_FILE), true);
        return is_array($data) ? $data : null;
    }

    private static function saveCache(array $data): void
    {
        $dir = dirname(self::CACHE_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(self::CACHE_FILE, json_encode([
            'fetched_at' => time(),
            'data' => $data,
        ], JSON_PRETTY_PRINT));
    }
}
