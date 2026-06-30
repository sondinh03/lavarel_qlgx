<?php

namespace App\Console\Commands;

use App\Models\Teacher;
use App\Models\User;
use App\Support\UserAccountEmailResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeCatechistLoginEmails extends Command
{
    protected $signature = 'qlgx:normalize-catechist-login-emails {--dry-run : Chỉ xem trước, không ghi DB}';

    protected $description = 'Chuẩn hóa SĐT giáo lý viên và email đăng nhập dạng @giaoly.local';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $domain = UserAccountEmailResolver::phoneLoginDomain();

        $this->info($dryRun ? 'DRY RUN — không ghi dữ liệu' : 'Đang chuẩn hóa dữ liệu...');

        $teacherUpdates = 0;
        $userUpdates    = 0;
        $conflicts      = [];

        DB::beginTransaction();

        try {
            Teacher::query()
                ->whereNotNull('phone_number')
                ->orderBy('id')
                ->chunkById(100, function ($teachers) use ($dryRun, &$teacherUpdates) {
                    foreach ($teachers as $teacher) {
                        $normalized = UserAccountEmailResolver::normalizePhone($teacher->phone_number);

                        if ($normalized === null || $normalized === $teacher->phone_number) {
                            continue;
                        }

                        if (! $dryRun) {
                            $teacher->update(['phone_number' => $normalized]);
                        }

                        $teacherUpdates++;
                    }
                });

            $seenEmails = [];

            User::query()
                ->orderBy('id')
                ->chunkById(100, function ($users) use ($dryRun, $domain, &$userUpdates, &$conflicts, &$seenEmails) {
                    foreach ($users as $user) {
                        if (! UserAccountEmailResolver::isSyntheticEmail($user->email)) {
                            continue;
                        }

                        $localPart = strstr($user->email, '@', true) ?: '';
                        $normalized = UserAccountEmailResolver::normalizePhone($localPart);

                        if ($normalized === null) {
                            $conflicts[] = "User #{$user->id}: không chuẩn hóa được SĐT từ \"{$localPart}\"";
                            continue;
                        }

                        $newEmail = $normalized . '@' . $domain;

                        if (strcasecmp($user->email, $newEmail) === 0) {
                            continue;
                        }

                        if (isset($seenEmails[$newEmail]) || User::where('email', $newEmail)->where('id', '!=', $user->id)->exists()) {
                            $conflicts[] = "User #{$user->id}: email trùng sau chuẩn hóa → {$newEmail}";
                            continue;
                        }

                        if (! $dryRun) {
                            $user->update(['email' => $newEmail]);
                        }

                        $seenEmails[$newEmail] = $user->id;
                        $userUpdates++;
                    }
                });
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        if ($dryRun) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        $this->line("Giáo lý viên cập nhật SĐT: {$teacherUpdates}");
        $this->line("User cập nhật email giả: {$userUpdates}");

        if ($conflicts) {
            $this->warn('Xung đột / bỏ qua:');
            foreach ($conflicts as $message) {
                $this->warn(" - {$message}");
            }
        }

        $this->info('Hoàn tất.');

        return self::SUCCESS;
    }
}
