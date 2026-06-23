<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parishioner_registration_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('parishioner_registration_requests', 'marriages')) {
                $table->json('marriages')->nullable()->after('sacraments');
            }

            if (! Schema::hasColumn('parishioner_registration_requests', 'family_id')) {
                $table->foreignId('family_id')->nullable()->after('parishioner_id')
                    ->constrained('families')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('parishioner_registration_requests', function (Blueprint $table) {
            if (Schema::hasColumn('parishioner_registration_requests', 'family_id')) {
                $table->dropConstrainedForeignId('family_id');
            }

            if (Schema::hasColumn('parishioner_registration_requests', 'marriages')) {
                $table->dropColumn('marriages');
            }
        });
    }
};
