<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parishes', function (Blueprint $table) {
            $table->dropForeign(['parish_priest_id']);
            $table->dropColumn('parish_priest_id');
        });

        Schema::table('parishes', function (Blueprint $table) {
            $table->string('parish_priest_name', 255)
                ->nullable()
                ->after('diocese_id')
                ->comment('Cha xứ (nhập tay)');
        });
    }

    public function down(): void
    {
        Schema::table('parishes', function (Blueprint $table) {
            $table->dropColumn('parish_priest_name');
        });

        Schema::table('parishes', function (Blueprint $table) {
            $table->foreignId('parish_priest_id')
                ->nullable()
                ->after('diocese_id')
                ->constrained('sacrament_givers')
                ->nullOnDelete();
        });
    }
};
