<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveScoresEntryOpenFromParishes extends Migration
{
    public function up(): void
    {
        Schema::table('parishes', function (Blueprint $table) {
            $table->dropColumn('scores_entry_open');
        });
    }

    public function down(): void
    {
        Schema::table('parishes', function (Blueprint $table) {
            $table->boolean('scores_entry_open')->default(false)->after('status');
        });
    }
}
