<?php

use App\Models\Family;
use App\Support\FamilyCodeGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->string('code', 32)->nullable()->unique()->after('id');
        });

        Family::query()->whereNull('code')->orderBy('id')->each(function (Family $family) {
            $family->update(['code' => FamilyCodeGenerator::generate()]);
        });
    }

    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
