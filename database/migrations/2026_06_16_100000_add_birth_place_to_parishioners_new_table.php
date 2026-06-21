<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parishioners_new', function (Blueprint $table) {
            $table->string('birth_place', 255)->nullable()->after('birthday');
        });
    }

    public function down(): void
    {
        Schema::table('parishioners_new', function (Blueprint $table) {
            $table->dropColumn('birth_place');
        });
    }
};
