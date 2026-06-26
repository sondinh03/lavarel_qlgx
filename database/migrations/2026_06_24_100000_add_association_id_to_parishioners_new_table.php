<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parishioners_new', function (Blueprint $table) {
            if (! Schema::hasColumn('parishioners_new', 'association_id')) {
                $table->unsignedBigInteger('association_id')
                    ->nullable()
                    ->after('parish_area_id')
                    ->comment('Hội đoàn (FK → associations)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('parishioners_new', function (Blueprint $table) {
            if (Schema::hasColumn('parishioners_new', 'association_id')) {
                $table->dropColumn('association_id');
            }
        });
    }
};
