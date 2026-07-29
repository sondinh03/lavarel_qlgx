<?php

/**
 * Đồng bộ lại attendance_sessions.semester theo khoảng ngày năm học.
 * semester nullable (đã ALTER trước đó).
 */

use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\NamHoc;
use App\Services\SchoolYearResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Đảm bảo nullable (idempotent)
        $col = collect(DB::select("SHOW COLUMNS FROM attendance_sessions LIKE 'semester'"))->first();
        if ($col && strtoupper((string) $col->Null) !== 'YES') {
            DB::statement('ALTER TABLE `attendance_sessions` MODIFY `semester` TINYINT NULL');
        }

        $resolver = app(SchoolYearResolver::class);

        AttendanceSession::query()
            ->orderBy('id')
            ->chunkById(200, function ($sessions) use ($resolver) {
                $classIds = $sessions->pluck('class_id')->unique()->filter()->all();
                $classes = CatechismClass::query()
                    ->whereIn('id', $classIds)
                    ->get(['id', 'school_year_id'])
                    ->keyBy('id');
                $years = NamHoc::query()
                    ->whereIn('id', $classes->pluck('school_year_id')->unique()->filter()->all())
                    ->get()
                    ->keyBy('id');

                foreach ($sessions as $session) {
                    $class = $classes->get($session->class_id);
                    if (! $class) {
                        continue;
                    }
                    $namHoc = $years->get($class->school_year_id);
                    if (! $namHoc) {
                        continue;
                    }

                    $correct = $resolver->semesterForDate($namHoc, $session->date);
                    $current = $session->semester !== null ? (int) $session->semester : null;

                    if ($current === $correct) {
                        continue;
                    }

                    AttendanceSession::query()
                        ->where('id', $session->id)
                        ->update(['semester' => $correct]);
                }
            });
    }

    public function down(): void
    {
        // no-op
    }
};
