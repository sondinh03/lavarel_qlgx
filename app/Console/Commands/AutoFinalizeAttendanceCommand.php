<?php

namespace App\Console\Commands;

use App\Services\AttendanceFinalizer;
use Illuminate\Console\Command;

class AutoFinalizeAttendanceCommand extends Command
{
    protected $signature = 'qlgx:auto-finalize-attendance
                            {--dry-run : Chỉ liệt kê giáo xứ đã đến giờ, không ghi DB}';

    protected $description = 'Tự động chốt điểm danh: đánh vắng không phép học sinh chưa điểm danh (buổi đã có dữ liệu), rồi khóa buổi';

    public function handle(AttendanceFinalizer $finalizer): int
    {
        $now = now();

        if ($this->option('dry-run')) {
            $this->info('Dry-run lúc ' . $now->format('Y-m-d H:i:s') . ' (' . config('app.timezone') . ')');
            $this->info('Không ghi database.');

            return self::SUCCESS;
        }

        $result = $finalizer->finalizeDueParishes($now);

        $this->info(sprintf(
            'Chốt xong: %d giáo xứ · %d buổi · %d học sinh đánh vắng không phép',
            $result['parishes'],
            $result['sessions'],
            $result['marked_absent']
        ));

        return self::SUCCESS;
    }
}
