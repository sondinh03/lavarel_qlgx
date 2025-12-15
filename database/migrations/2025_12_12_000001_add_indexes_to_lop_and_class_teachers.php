<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds indexes to speed up common filters used by LopList.
     */
    public function up(): void
    {
        Schema::table('lop', function (Blueprint $table) {
            if (!Schema::hasColumn('lop', 'schoolyear')) return;
            // single column indexes
            $table->index('schoolyear', 'idx_lop_schoolyear');
            $table->index('block', 'idx_lop_block');
            $table->index('status', 'idx_lop_status');
            // composite index for frequent combo
            $table->index(['schoolyear', 'status'], 'idx_lop_schoolyear_status');
        });

        Schema::table('class_teachers', function (Blueprint $table) {
            if (!Schema::hasColumn('class_teachers', 'namhoc_id')) return;
            $table->index('namhoc_id', 'idx_ct_namhoc');
            $table->index('status', 'idx_ct_status');
            $table->index(['class_id', 'namhoc_id'], 'idx_ct_class_namhoc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lop', function (Blueprint $table) {
            $table->dropIndex('idx_lop_schoolyear');
            $table->dropIndex('idx_lop_block');
            $table->dropIndex('idx_lop_status');
            $table->dropIndex('idx_lop_schoolyear_status');
        });

        Schema::table('class_teachers', function (Blueprint $table) {
            $table->dropIndex('idx_ct_namhoc');
            $table->dropIndex('idx_ct_status');
            $table->dropIndex('idx_ct_class_namhoc');
        });
    }
};
