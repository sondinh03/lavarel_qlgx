<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parish_admin_registration_requests', function (Blueprint $table) {
            $table->foreignId('diocese_id')->nullable()->after('parish_id')
                ->constrained('dioceses')->nullOnDelete();
            $table->string('custom_parish_name')->nullable()->after('diocese_id');
            $table->json('requested_roles')->nullable()->after('note');

            $table->dropForeign(['parish_id']);
        });

        Schema::table('parish_admin_registration_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('parish_id')->nullable()->change();
            $table->foreign('parish_id')->references('id')->on('parishes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('parish_admin_registration_requests', function (Blueprint $table) {
            $table->dropForeign(['diocese_id']);
            $table->dropColumn(['diocese_id', 'custom_parish_name', 'requested_roles']);

            $table->dropForeign(['parish_id']);
        });

        Schema::table('parish_admin_registration_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('parish_id')->nullable(false)->change();
            $table->foreign('parish_id')->references('id')->on('parishes')->cascadeOnDelete();
        });
    }
};
