<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parish_admin_registration_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('deanery_id')->nullable()->after('diocese_id');
            $table->foreign('deanery_id')->references('id')->on('deanerys')->nullOnDelete();
            $table->string('name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('parish_admin_registration_requests', function (Blueprint $table) {
            $table->dropForeign(['deanery_id']);
            $table->dropColumn('deanery_id');
            $table->string('name')->nullable(false)->change();
        });
    }
};
