<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parishioner_registration_requests', function (Blueprint $table) {
            $table->json('marriages')->nullable()->after('sacraments');
            $table->foreignId('family_id')->nullable()->after('parishioner_id')
                ->constrained('families')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('parishioner_registration_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('family_id');
            $table->dropColumn('marriages');
        });
    }
};
