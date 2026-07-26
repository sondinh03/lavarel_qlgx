<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Runtime probe for why scheduled / UI backups may not appear.
 * Writes NDJSON to debug-78c47a.log (workspace + storage/logs).
 */
class DiagnoseBackupCommand extends Command
{
    protected $signature = 'qlgx:diagnose-backup {--run : Also attempt backup:run once}';

    protected $description = 'Diagnose Spatie/Backpack backup schedule + disk + mysqldump';

    public function handle(): int
    {
        $data = [
            'hypothesisId' => 'all',
            'now' => now()->toDateTimeString(),
            'timezone' => config('app.timezone'),
            'app_name' => config('backup.backup.name'),
            'php' => PHP_BINARY,
            'php_version' => PHP_VERSION,
            'cwd' => getcwd(),
            'base_path' => base_path(),
            'disks_config' => config('backup.backup.destination.disks'),
            'include_db' => config('backup.backup.source.databases'),
            'dump_config' => config('database.connections.'.config('database.default').'.dump'),
        ];

        // A: mysqldump reachable?
        $mysqldump = $this->probeMysqldump();
        $data['mysqldump'] = $mysqldump;
        $this->agentLog('A', 'mysqldump probe', $mysqldump);

        // B: backups disk writable + existing zips (what Backpack UI lists)
        $diskProbe = $this->probeBackupDisk();
        $data['disk'] = $diskProbe;
        $this->agentLog('B', 'backup disk probe', $diskProbe);

        // C: schedule due times vs now
        $schedule = $this->probeSchedule();
        $data['schedule'] = $schedule;
        $this->agentLog('C', 'schedule probe', $schedule);

        // D: recent laravel.log backup failures
        $logTail = $this->probeLaravelLog();
        $data['laravel_log_hits'] = $logTail;
        $this->agentLog('D', 'laravel.log backup hits', ['count' => count($logTail), 'lines' => $logTail]);

        // E: zip / proc_open available
        $ext = [
            'zip' => extension_loaded('zip'),
            'proc_open' => function_exists('proc_open'),
            'pcntl' => extension_loaded('pcntl'),
        ];
        $data['extensions'] = $ext;
        $this->agentLog('E', 'php extensions', $ext);

        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if ($this->option('run')) {
            $this->info('Running backup:run ...');
            $exit = Artisan::call('backup:run');
            $output = Artisan::output();
            $this->agentLog('A', 'backup:run result', [
                'exit' => $exit,
                'output_tail' => mb_substr($output, -2000),
                'failed' => str_contains($output, 'Backup failed because'),
            ]);
            $this->line($output);
            $after = $this->probeBackupDisk();
            $this->agentLog('B', 'disk after backup:run', $after);
        }

        $this->info('Wrote debug NDJSON to debug-78c47a.log (and storage/logs/debug-78c47a.log)');

        return self::SUCCESS;
    }

    private function probeMysqldump(): array
    {
        $dumpPath = config('database.connections.'.config('database.default').'.dump.dump_binary_path');
        $candidates = array_filter([
            $dumpPath ? rtrim($dumpPath, '/\\').DIRECTORY_SEPARATOR.'mysqldump' : null,
            'mysqldump',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/bin/mysqldump',
        ]);

        $found = [];
        foreach ($candidates as $bin) {
            try {
                $p = Process::fromShellCommandline(escapeshellarg($bin).' --version');
                $p->setTimeout(10);
                $p->run();
                $found[] = [
                    'bin' => $bin,
                    'ok' => $p->isSuccessful(),
                    'exit' => $p->getExitCode(),
                    'out' => trim($p->getOutput().' '.$p->getErrorOutput()),
                ];
                if ($p->isSuccessful()) {
                    break;
                }
            } catch (\Throwable $e) {
                $found[] = ['bin' => $bin, 'ok' => false, 'error' => $e->getMessage()];
            }
        }

        return ['candidates' => $found, 'any_ok' => collect($found)->contains(fn ($r) => ($r['ok'] ?? false))];
    }

    private function probeBackupDisk(): array
    {
        $result = [];
        foreach (config('backup.backup.destination.disks', []) as $diskName) {
            $disk = Storage::disk($diskName);
            $root = config("filesystems.disks.$diskName.root");
            $files = [];
            try {
                $all = $disk->allFiles();
                foreach ($all as $f) {
                    if (substr($f, -4) === '.zip') {
                        $files[] = [
                            'path' => $f,
                            'size' => $disk->size($f),
                            'mtime' => date('Y-m-d H:i:s', $disk->lastModified($f)),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                $result[$diskName] = ['error' => $e->getMessage(), 'root' => $root];
                continue;
            }

            $result[$diskName] = [
                'root' => $root,
                'root_exists' => $root ? is_dir($root) : null,
                'root_writable' => $root ? is_writable($root) : null,
                'zip_count' => count($files),
                'zips' => $files,
            ];
        }

        return $result;
    }

    private function probeSchedule(): array
    {
        $events = [];
        $schedule = new \Illuminate\Console\Scheduling\Schedule(config('app.timezone'));

        $kernel = app(\Illuminate\Contracts\Console\Kernel::class);
        $ref = new \ReflectionClass($kernel);
        $m = $ref->getMethod('schedule');
        $m->setAccessible(true);
        $m->invoke($kernel, $schedule);

        foreach ($schedule->events() as $event) {
            $cmd = $event->command ?? $event->description ?? (string) $event;
            if (! str_contains((string) $cmd, 'backup') && ! str_contains((string) $cmd, 'telescope')) {
                continue;
            }
            $tz = $event->timezone ?? config('app.timezone');
            if ($tz instanceof \DateTimeZone) {
                $tz = $tz->getName();
            } elseif (! is_string($tz)) {
                $tz = config('app.timezone');
            }
            $events[] = [
                'command' => $cmd,
                'expression' => $event->expression,
                'timezone' => $tz,
                'isDue' => $event->isDue(app()),
                'next' => method_exists($event, 'nextRunDate')
                    ? $event->nextRunDate()->toDateTimeString()
                    : null,
            ];
        }

        return $events;
    }

    private function probeLaravelLog(): array
    {
        $path = storage_path('logs/laravel.log');
        if (! is_file($path)) {
            return [];
        }

        $size = filesize($path);
        $fp = fopen($path, 'rb');
        if ($fp === false) {
            return [];
        }
        $read = min(200_000, $size);
        fseek($fp, -$read, SEEK_END);
        $chunk = stream_get_contents($fp) ?: '';
        fclose($fp);

        $hits = [];
        foreach (preg_split("/\r\n|\n|\r/", $chunk) as $line) {
            if (stripos($line, 'backup') !== false && (stripos($line, 'fail') !== false || stripos($line, 'error') !== false || stripos($line, 'mysqldump') !== false)) {
                $hits[] = mb_substr($line, 0, 400);
            }
        }

        return array_slice($hits, -15);
    }

    private function agentLog(string $hypothesisId, string $message, array $data): void
    {
        // #region agent log
        $payload = json_encode([
            'sessionId' => '78c47a',
            'timestamp' => (int) (microtime(true) * 1000),
            'location' => 'DiagnoseBackupCommand.php',
            'message' => $message,
            'hypothesisId' => $hypothesisId,
            'data' => $data,
            'runId' => 'diagnose',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        foreach ([base_path('debug-78c47a.log'), storage_path('logs/debug-78c47a.log')] as $file) {
            try {
                File::append($file, $payload.PHP_EOL);
            } catch (\Throwable $e) {
                // ignore
            }
        }
        // #endregion
    }
}
