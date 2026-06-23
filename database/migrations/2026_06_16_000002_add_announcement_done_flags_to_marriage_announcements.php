<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marriage_announcements', function (Blueprint $table) {
            $table->boolean('announcements_one_done')->default(false)->after('announcements_one');
            $table->boolean('announcements_two_done')->default(false)->after('announcements_two');
            $table->boolean('announcements_three_done')->default(false)->after('announcements_three');
        });
    }

    public function down(): void
    {
        Schema::table('marriage_announcements', function (Blueprint $table) {
            $table->dropColumn([
                'announcements_one_done',
                'announcements_two_done',
                'announcements_three_done',
            ]);
        });
    }
};
