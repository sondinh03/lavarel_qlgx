<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnScoreTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('score_types', function (Blueprint $table) {
            // Thêm 2 trường mới
            $table->tinyInteger('order')
                ->default(0)
                ->after('name')
                ->comment('Thứ tự hiển thị (để sắp xếp)');

            $table->decimal('coefficient', 5, 2)
                ->default(1.00)
                ->after('order')
                ->comment('Hệ số điểm (tùy chỉnh theo xứ)');

            // Xóa trường weight cũ (nếu có)
            if (Schema::hasColumn('score_types', 'weight')) {
                $table->dropColumn('weight');
            }
        });

        // Thêm index cho order để tối ưu khi sort
        Schema::table('score_types', function (Blueprint $table) {
            $table->index(['class_id', 'semester', 'order'], 'idx_class_semester_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('score_types', function (Blueprint $table) {
            // Xóa index trước
            $table->dropIndex('idx_class_semester_order');

            // Xóa 2 trường mới
            $table->dropColumn(['order', 'coefficient']);

            // Khôi phục lại trường weight cũ (nếu cần)
            $table->decimal('weight', 5, 2)->default(1.00);
        });
    }
}
