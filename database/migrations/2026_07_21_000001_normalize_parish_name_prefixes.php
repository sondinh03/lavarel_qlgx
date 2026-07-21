<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeColumn('parishes', 'name');

        if (Schema::hasTable('parish_admin_registration_requests')
            && Schema::hasColumn('parish_admin_registration_requests', 'custom_parish_name')) {
            $this->normalizeColumn('parish_admin_registration_requests', 'custom_parish_name');
        }
    }

    public function down(): void
    {
        $this->stripPrefix('parishes', 'name');

        if (Schema::hasTable('parish_admin_registration_requests')
            && Schema::hasColumn('parish_admin_registration_requests', 'custom_parish_name')) {
            $this->stripPrefix('parish_admin_registration_requests', 'custom_parish_name');
        }
    }

    private function normalizeColumn(string $table, string $column): void
    {
        DB::table($table)
            ->whereNotNull($column)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $column) {
                foreach ($rows as $row) {
                    $name = trim((string) $row->{$column});

                    if ($name === '') {
                        continue;
                    }

                    $name = trim((string) preg_replace('/^(?:giáo\s*xứ\s*)+/iu', '', $name));
                    $normalized = 'Giáo xứ' . ($name !== '' ? ' ' . $name : '');

                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([$column => $normalized]);
                }
            });
    }

    private function stripPrefix(string $table, string $column): void
    {
        DB::table($table)
            ->whereNotNull($column)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $column) {
                foreach ($rows as $row) {
                    $name = trim((string) preg_replace(
                        '/^giáo\s*xứ\s*/iu',
                        '',
                        trim((string) $row->{$column})
                    ));

                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([$column => $name]);
                }
            });
    }
};
