<?php

namespace Modules\Weather\App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Weather\App\Models\WeatherFetchRun;
use Modules\Weather\App\Models\WeatherForecastSnapshot;
use Modules\Weather\App\Models\WeatherForecastValue;
use Modules\Weather\App\Models\WeatherPoint;
use Throwable;

/**
 * Server-side forecast fetch (MET Norway primary, Open-Meteo fallback).
 * Keeps the last successful snapshot when a fetch fails (stale-ok).
 */
class WeatherFetchService
{
    /**
     * @return array{run: WeatherFetchRun, snapshot: ?WeatherForecastSnapshot, kept_previous: bool}
     */
    public function fetch(WeatherPoint $point): array
    {
        $started = microtime(true);
        $fetchedAt = now();
        $conn = config('tenancy.connection_name');

        try {
            $payload = $this->fetchFromProviders($point);
            $durationMs = (int) round((microtime(true) - $started) * 1000);

            return DB::connection($conn)->transaction(function () use ($point, $payload, $fetchedAt, $durationMs, $conn) {
                $run = WeatherFetchRun::on($conn)->create([
                    'weather_point_id' => $point->getKey(),
                    'fetched_at' => $fetchedAt,
                    'status' => WeatherFetchRun::STATUS_SUCCESS,
                    'http_status' => $payload['http_status'],
                    'error_message' => null,
                    'duration_ms' => $durationMs,
                ]);

                WeatherForecastSnapshot::on($conn)
                    ->where('weather_point_id', $point->getKey())
                    ->where('is_current', true)
                    ->update(['is_current' => false]);

                $snapshot = WeatherForecastSnapshot::on($conn)->create([
                    'weather_point_id' => $point->getKey(),
                    'fetch_run_id' => $run->getKey(),
                    'fetched_at' => $fetchedAt,
                    'period_start' => $payload['period_start'],
                    'period_end' => $payload['period_end'],
                    'raw' => $payload['raw'],
                    'is_current' => true,
                ]);

                foreach ($payload['values'] as $row) {
                    WeatherForecastValue::on($conn)->create([
                        'snapshot_id' => $snapshot->getKey(),
                        'valid_at' => $row['valid_at'],
                        'variable' => $row['variable'],
                        'unit' => $row['unit'],
                        'value' => $row['value'],
                    ]);
                }

                return [
                    'run' => $run->fresh(),
                    'snapshot' => $snapshot->fresh(),
                    'kept_previous' => false,
                    'provider' => $payload['provider'],
                    'attribution' => config('weather.attribution'),
                ];
            });
        } catch (Throwable $e) {
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            $httpStatus = property_exists($e, 'httpStatus') ? (int) $e->httpStatus : null;

            $run = WeatherFetchRun::on($conn)->create([
                'weather_point_id' => $point->getKey(),
                'fetched_at' => $fetchedAt,
                'status' => WeatherFetchRun::STATUS_FAILED,
                'http_status' => $httpStatus,
                'error_message' => mb_substr($e->getMessage(), 0, 255),
                'duration_ms' => $durationMs,
            ]);

            $previous = WeatherForecastSnapshot::on($conn)
                ->where('weather_point_id', $point->getKey())
                ->where('is_current', true)
                ->first();

            return [
                'run' => $run,
                'snapshot' => $previous,
                'kept_previous' => $previous !== null,
                'provider' => null,
                'attribution' => config('weather.attribution'),
            ];
        }
    }

    /**
     * @return array{
     *   provider: string,
     *   http_status: int,
     *   period_start: Carbon,
     *   period_end: Carbon,
     *   raw: array<string, mixed>,
     *   values: list<array{valid_at: Carbon, variable: string, unit: string, value: float}>
     * }
     */
    private function fetchFromProviders(WeatherPoint $point): array
    {
        $timeout = (int) config('weather.http_timeout_seconds', 12);

        try {
            return $this->fetchMetNorway($point, $timeout);
        } catch (Throwable $metError) {
            try {
                return $this->fetchOpenMeteo($point, $timeout);
            } catch (Throwable $openError) {
                $e = new \RuntimeException(
                    'MET Norway and Open-Meteo failed: '.$metError->getMessage().' | '.$openError->getMessage(),
                    0,
                    $openError
                );
                $e->httpStatus = null;

                throw $e;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchMetNorway(WeatherPoint $point, int $timeout): array
    {
        $url = (string) config('weather.met_base_url');
        $response = Http::timeout($timeout)
            ->withHeaders([
                'User-Agent' => (string) config('weather.met_user_agent'),
                'Accept' => 'application/json',
            ])
            ->get($url, [
                'lat' => $point->getAttribute('latitude'),
                'lon' => $point->getAttribute('longitude'),
            ]);

        if (! $response->successful()) {
            $e = new \RuntimeException('MET Norway HTTP '.$response->status());
            $e->httpStatus = $response->status();

            throw $e;
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new \RuntimeException('MET Norway returned invalid JSON.');
        }

        $timeseries = data_get($json, 'properties.timeseries', []);
        if (! is_array($timeseries) || $timeseries === []) {
            throw new \RuntimeException('MET Norway returned no timeseries.');
        }

        $values = [];
        $periodStart = null;
        $periodEnd = null;

        foreach ($timeseries as $entry) {
            if (! is_array($entry) || empty($entry['time'])) {
                continue;
            }
            $validAt = Carbon::parse($entry['time'])->utc();
            $periodStart = $periodStart === null || $validAt->lt($periodStart) ? $validAt->copy() : $periodStart;
            $periodEnd = $periodEnd === null || $validAt->gt($periodEnd) ? $validAt->copy() : $periodEnd;

            $details = data_get($entry, 'data.instant.details', []);
            $next1h = data_get($entry, 'data.next_1_hours.details', []);
            $symbol = data_get($entry, 'data.next_1_hours.summary.symbol_code')
                ?? data_get($entry, 'data.next_6_hours.summary.symbol_code');

            if (isset($details['air_temperature'])) {
                $values[] = $this->valueRow($validAt, WeatherForecastValue::VAR_TEMPERATURE_C, 'c', (float) $details['air_temperature']);
            }
            if (isset($details['wind_speed'])) {
                $values[] = $this->valueRow($validAt, WeatherForecastValue::VAR_WIND_MS, 'm/s', (float) $details['wind_speed']);
            }
            if (isset($details['wind_speed_of_gust'])) {
                $values[] = $this->valueRow($validAt, WeatherForecastValue::VAR_GUST_MS, 'm/s', (float) $details['wind_speed_of_gust']);
            }
            $precip = $next1h['precipitation_amount'] ?? null;
            if ($precip !== null) {
                $values[] = $this->valueRow($validAt, WeatherForecastValue::VAR_PRECIPITATION_MM, 'mm', (float) $precip);
            }
            if (is_string($symbol) && $symbol !== '') {
                // Store a stable numeric stand-in for symbol codes (hash bucket).
                $code = (float) (crc32($symbol) % 10000);
                $values[] = $this->valueRow($validAt, WeatherForecastValue::VAR_SYMBOL, 'code', $code);
            }
        }

        if ($values === [] || $periodStart === null || $periodEnd === null) {
            throw new \RuntimeException('MET Norway payload had no usable forecast values.');
        }

        return [
            'provider' => 'met_norway',
            'http_status' => $response->status(),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'raw' => [
                'provider' => 'met_norway',
                'attribution' => config('weather.attribution'),
                'payload' => $json,
            ],
            'values' => $values,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchOpenMeteo(WeatherPoint $point, int $timeout): array
    {
        $url = (string) config('weather.open_meteo_base_url');
        $response = Http::timeout($timeout)->get($url, [
            'latitude' => $point->getAttribute('latitude'),
            'longitude' => $point->getAttribute('longitude'),
            'hourly' => 'temperature_2m,wind_speed_10m,wind_gusts_10m,precipitation,weather_code',
            'wind_speed_unit' => 'ms',
            'timezone' => 'UTC',
            'forecast_days' => 2,
        ]);

        if (! $response->successful()) {
            $e = new \RuntimeException('Open-Meteo HTTP '.$response->status());
            $e->httpStatus = $response->status();

            throw $e;
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new \RuntimeException('Open-Meteo returned invalid JSON.');
        }

        $times = data_get($json, 'hourly.time', []);
        if (! is_array($times) || $times === []) {
            throw new \RuntimeException('Open-Meteo returned no hourly times.');
        }

        $temps = data_get($json, 'hourly.temperature_2m', []);
        $winds = data_get($json, 'hourly.wind_speed_10m', []);
        $gusts = data_get($json, 'hourly.wind_gusts_10m', []);
        $precips = data_get($json, 'hourly.precipitation', []);
        $codes = data_get($json, 'hourly.weather_code', []);

        $values = [];
        $periodStart = null;
        $periodEnd = null;

        foreach ($times as $i => $time) {
            $validAt = Carbon::parse((string) $time)->utc();
            $periodStart = $periodStart === null || $validAt->lt($periodStart) ? $validAt->copy() : $periodStart;
            $periodEnd = $periodEnd === null || $validAt->gt($periodEnd) ? $validAt->copy() : $periodEnd;

            if (isset($temps[$i]) && $temps[$i] !== null) {
                $values[] = $this->valueRow($validAt, WeatherForecastValue::VAR_TEMPERATURE_C, 'c', (float) $temps[$i]);
            }
            if (isset($winds[$i]) && $winds[$i] !== null) {
                $values[] = $this->valueRow($validAt, WeatherForecastValue::VAR_WIND_MS, 'm/s', (float) $winds[$i]);
            }
            if (isset($gusts[$i]) && $gusts[$i] !== null) {
                $values[] = $this->valueRow($validAt, WeatherForecastValue::VAR_GUST_MS, 'm/s', (float) $gusts[$i]);
            }
            if (isset($precips[$i]) && $precips[$i] !== null) {
                $values[] = $this->valueRow($validAt, WeatherForecastValue::VAR_PRECIPITATION_MM, 'mm', (float) $precips[$i]);
            }
            if (isset($codes[$i]) && $codes[$i] !== null) {
                $values[] = $this->valueRow($validAt, WeatherForecastValue::VAR_SYMBOL, 'code', (float) $codes[$i]);
            }
        }

        if ($values === [] || $periodStart === null || $periodEnd === null) {
            throw new \RuntimeException('Open-Meteo payload had no usable forecast values.');
        }

        return [
            'provider' => 'open_meteo',
            'http_status' => $response->status(),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'raw' => [
                'provider' => 'open_meteo',
                'attribution' => 'Weather data from Open-Meteo',
                'payload' => $json,
            ],
            'values' => $values,
        ];
    }

    /**
     * @return array{valid_at: Carbon, variable: string, unit: string, value: float}
     */
    private function valueRow(Carbon $validAt, string $variable, string $unit, float $value): array
    {
        return [
            'valid_at' => $validAt,
            'variable' => $variable,
            'unit' => $unit,
            'value' => $value,
        ];
    }
}
