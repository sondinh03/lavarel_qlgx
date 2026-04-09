<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFamiliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('families', function (Blueprint $table) {
            // Số người trong gia đình (mục 8)
            $table->tinyInteger('member_count')
                ->nullable()
                ->after('name')
                ->comment('Số người trong gia đình (mục 8)');

            // Địa chỉ gia đình (mục 12–14)
            $table->string('address', 255)
                ->nullable()
                ->after('member_count')
                ->comment('Địa chỉ gia đình (mục 12)');

            $table->unsignedInteger('ward_id')
                ->nullable()
                ->after('address')
                ->comment('Xã/Phường gia đình (mục 13)');

            $table->string('province', 100)
                ->nullable()
                ->after('ward_id')
                ->comment('Tỉnh/TP gia đình (mục 14)');

            // Đã chuyển đi xứ khác (mục 19)
            $table->tinyInteger('is_transferred')
                ->default(0)
                ->after('province')
                ->comment('Đã chuyển đi xứ khác (mục 19)');

            // Diện gia đình: khó khăn / bình thường / khá giả... (mục 20)
            $table->tinyInteger('level')
                ->nullable()
                ->after('is_transferred')
                ->comment('Diện gia đình (mục 20)');

            // Gia đình không được thống kê (mục 21)
            $table->tinyInteger('is_included_in_stats')
                ->default(1)
                ->after('level')
                ->comment('Gia đình được thống kê hay không (mục 21)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('families', function (Blueprint $table) {
            $table->dropColumn([
                'member_count',
                'address',
                'ward_id',
                'province',
                'is_transferred',
                'level',
                'is_included_in_stats',
            ]);
        });
    }
}
