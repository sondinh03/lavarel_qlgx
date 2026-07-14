<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parishioners_new', function (Blueprint $table) {
            $table->string('death_time', 20)->nullable()->after('death_date')
                ->comment('Giờ từ trần (vd 14:30)');
            $table->dateTime('embalm_at')->nullable()->after('burial_place')
                ->comment('Nghi thức tẩm liệm');
            $table->dateTime('farewell_mass_at')->nullable()->after('embalm_at')
                ->comment('Thánh lễ đưa chân');
            $table->dateTime('burial_mass_at')->nullable()->after('farewell_mass_at')
                ->comment('Thánh lễ an táng');
        });
    }

    public function down(): void
    {
        Schema::table('parishioners_new', function (Blueprint $table) {
            $table->dropColumn([
                'death_time',
                'embalm_at',
                'farewell_mass_at',
                'burial_mass_at',
            ]);
        });
    }
};
