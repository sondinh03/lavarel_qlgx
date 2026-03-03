<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradeLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            // Thuộc giáo xứ nào
            $table->unsignedBigInteger('parish_id');

            // Tên khối
            $table->string('name');
            // Ví dụ: Thánh Thể 1, Bao Đồng, Thêm Sức

            // Viết tắt nếu cần (optional)
            $table->string('code')->nullable();
            // Ví dụ: TT1, BD, TS

            // Thứ tự hiển thị
            $table->integer('sort_order')->default(0);

            // Bật / tắt khối
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('parish_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grade_levels');
    }
}
