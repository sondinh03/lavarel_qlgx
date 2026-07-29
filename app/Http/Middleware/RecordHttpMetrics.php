<?php

namespace App\Http\Middleware;

use App\Models\SystemMetricDaily;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecordHttpMetrics
{
    public const SLOW_MS = 1000;

    public function handle(Request $request, Closure $next)
    {
        $started = microtime(true);

        $response = $next($request);

        if ($this->shouldSkip($request)) {
            return $response;
        }

        try {
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            $status = method_exists($response, 'getStatusCode')
                ? (int) $response->getStatusCode()
                : 200;

            $increments = [
                'requests'            => 1,
                'avg_duration_ms_sum' => max(0, $durationMs),
            ];

            if ($durationMs >= self::SLOW_MS) {
                $increments['slow_requests'] = 1;
            }

            if ($status >= 500) {
                $increments['server_errors'] = 1;
            }

            SystemMetricDaily::bump(now()->toDateString(), $increments);
        } catch (\Throwable $e) {
            // Không để metric làm hỏng request người dùng.
            report($e);
        }

        return $response;
    }

    protected function shouldSkip(Request $request): bool
    {
        if ($request->isMethod('OPTIONS')) {
            return true;
        }

        $path = trim($request->path(), '/');

        foreach ([
            'telescope',
            '_debugbar',
            'livewire',
            'vendor',
            'storage',
            'css',
            'js',
            'images',
            'favicon.ico',
        ] as $prefix) {
            if ($path === $prefix || Str::startsWith($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }
}
