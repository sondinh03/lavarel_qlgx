<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parishioners_new', function (Blueprint $table) {
            if (! Schema::hasColumn('parishioners_new', 'code')) {
                $table->string('code', 32)->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('parishioners_new', function (Blueprint $table) {
            if (Schema::hasColumn('parishioners_new', 'code')) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            }
        });
    }
};
