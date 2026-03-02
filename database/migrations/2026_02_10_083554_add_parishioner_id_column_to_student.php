<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParishionerIdColumnToStudent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student', function (Blueprint $table) {
            $table->unsignedBigInteger('parishioner_id')->nullable()->after('id');

            // Thêm index để tăng tốc truy vấn
            $table->index('parishioner_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student', function (Blueprint $table) {
            // Xóa index
            $table->dropIndex(['parishioner_id']);

            // Xóa cột
            $table->dropColumn('parishioner_id');
        });
    }
}
