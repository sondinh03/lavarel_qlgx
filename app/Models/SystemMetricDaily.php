<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SystemMetricDaily extends Model
{
    protected $table = 'system_metric_daily';

    protected $fillable = [
        'metric_date',
        'logins',
        'failed_logins',
        'requests',
        'slow_requests',
        'server_errors',
        'avg_duration_ms_sum',
    ];

    protected $casts = [
        'metric_date' => 'date',
    ];

    /**
     * @param  array<string, int>  $increments
     */
    public static function bump(string $date, array $increments): void
    {
        if ($increments === []) {
            return;
        }

        static::query()->firstOrCreate(
            ['metric_date' => $date],
            [
                'logins'              => 0,
                'failed_logins'       => 0,
                'requests'            => 0,
                'slow_requests'       => 0,
                'server_errors'       => 0,
                'avg_duration_ms_sum' => 0,
            ]
        );

        $parts = [];
        $bindings = [];
        foreach ($increments as $column => $value) {
            if (! in_array($column, [
                'logins',
                'failed_logins',
                'requests',
                'slow_requests',
                'server_errors',
                'avg_duration_ms_sum',
            ], true)) {
                continue;
            }
            $parts[] = "`{$column}` = `{$column}` + ?";
            $bindings[] = (int) $value;
        }

        if ($parts === []) {
            return;
        }

        $parts[] = '`updated_at` = ?';
        $bindings[] = now();
        $bindings[] = $date;

        DB::update(
            'UPDATE system_metric_daily SET ' . implode(', ', $parts) . ' WHERE metric_date = ?',
            $bindings
        );
    }
}
