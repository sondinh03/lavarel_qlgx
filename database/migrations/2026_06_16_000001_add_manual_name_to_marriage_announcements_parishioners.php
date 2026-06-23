<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marriage_announcements_parishioners', function (Blueprint $table) {
            $table->string('manual_name')->nullable()->after('idgiaodan');
        });
    }

    public function down(): void
    {
        Schema::table('marriage_announcements_parishioners', function (Blueprint $table) {
            $table->dropColumn('manual_name');
        });
    }
};
