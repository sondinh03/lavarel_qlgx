<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (! Schema::hasColumn('teachers', 'teacher_code')) {
                $table->string('teacher_code', 32)->nullable()->after('parish_id');
            }
            if (! Schema::hasColumn('teachers', 'qr_token')) {
                $table->uuid('qr_token')->nullable()->after('teacher_code');
            }
        });

        $parishCodes = DB::table('parishes')->pluck('code', 'id');
        $year = substr((string) now()->year, -2);
        $sequences = [];

        DB::table('teachers')
            ->orderBy('id')
            ->select(['id', 'parish_id', 'teacher_code', 'qr_token'])
            ->chunkById(200, function ($rows) use ($parishCodes, $year, &$sequences) {
                foreach ($rows as $row) {
                    $updates = [];

                    if (empty($row->qr_token)) {
                        $updates['qr_token'] = (string) Str::uuid();
                    }

                    if (empty($row->teacher_code)) {
                        $parishId = (int) $row->parish_id;
                        $parishCode = $parishCodes[$parishId] ?? 'GXU';
                        $prefix = "{$parishCode}-GV-{$year}-";

                        if (! isset($sequences[$parishId])) {
                            $last = DB::table('teachers')
                                ->where('parish_id', $parishId)
                                ->where('teacher_code', 'like', "{$prefix}%")
                                ->max('teacher_code');

                            $sequences[$parishId] = $last
                                ? (int) substr($last, strlen($prefix))
                                : 0;
                        }

                        $sequences[$parishId]++;
                        $updates['teacher_code'] = $prefix . str_pad((string) $sequences[$parishId], 4, '0', STR_PAD_LEFT);
                    }

                    if ($updates !== []) {
                        DB::table('teachers')->where('id', $row->id)->update($updates);
                    }
                }
            });

        Schema::table('teachers', function (Blueprint $table) {
            $table->unique('teacher_code');
            $table->unique('qr_token');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (Schema::hasColumn('teachers', 'qr_token')) {
                $table->dropUnique(['qr_token']);
                $table->dropColumn('qr_token');
            }
            if (Schema::hasColumn('teachers', 'teacher_code')) {
                $table->dropUnique(['teacher_code']);
                $table->dropColumn('teacher_code');
            }
        });
    }
};
