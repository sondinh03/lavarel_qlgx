<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parish_admin_registration_requests', function (Blueprint $table) {
            $table->string('custom_deanery_name')->nullable()->after('deanery_id');
        });

        $this->normalizeDeaneryNames('deanerys', 'name');

        if (Schema::hasColumn('parish_admin_registration_requests', 'custom_deanery_name')) {
            $this->normalizeDeaneryNames('parish_admin_registration_requests', 'custom_deanery_name');
        }
    }

    public function down(): void
    {
        Schema::table('parish_admin_registration_requests', function (Blueprint $table) {
            $table->dropColumn('custom_deanery_name');
        });
    }

    private function normalizeDeaneryNames(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)
            ->whereNotNull($column)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $column) {
                foreach ($rows as $row) {
                    $name = trim((string) $row->{$column});

                    if ($name === '') {
                        continue;
                    }

                    $name = trim((string) preg_replace('/^(?:giáo\s*hạt\s*)+/iu', '', $name));
                    $normalized = 'Giáo hạt' . ($name !== '' ? ' ' . $name : '');

                    if ($normalized === (string) $row->{$column}) {
                        continue;
                    }

                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([$column => $normalized]);
                }
            });
    }
};
