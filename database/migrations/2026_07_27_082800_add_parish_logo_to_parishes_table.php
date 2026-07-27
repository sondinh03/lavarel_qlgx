<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parishes', function (Blueprint $table) {
            $table->text('parish_logo')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('parishes', function (Blueprint $table) {
            $table->dropColumn('parish_logo');
        });
    }
};
