<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParishionersNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parishioners_new', function (Blueprint $table) {
            $table->id();

            // ==================== THÔNG TIN CÁ NHÂN ====================
            $table->string('last_name', 100)->comment('Họ');
            $table->string('first_name', 100)->comment('Tên');
            $table->enum('gender', ['male', 'female'])->nullable()->comment('Giới tính');
            $table->date('birthday')->nullable()->comment('Ngày sinh');
            $table->unsignedBigInteger('saint_id')->nullable()->comment('Thánh bổn mạng');
            $table->string('phone', 20)->nullable()->comment('Số điện thoại');
            $table->string('email', 255)->nullable()->comment('Email');
            $table->string('cccd', 20)->nullable()->comment('Căn cước công dân');
            $table->string('avatar_path', 255)->nullable()->comment('Ảnh đại diện');
            $table->text('note')->nullable()->comment('Ghi chú');

            // ==================== PHÂN LOẠI ====================
            $table->unsignedBigInteger('parish_id')->nullable()->comment('Giáo xứ');
            $table->unsignedBigInteger('deanery_id')->nullable()->comment('Giáo hạt');
            $table->unsignedBigInteger('diocese_id')->nullable()->comment('Giáo phận');
            $table->unsignedBigInteger('parish_area_id')->nullable()->comment('Khu/Họ đạo');
            $table->tinyInteger('ethnic')->nullable()->comment('Dân tộc');
            $table->tinyInteger('career')->nullable()->comment('Nghề nghiệp');
            $table->tinyInteger('education_level')->nullable()->comment('Trình độ học vấn');
            $table->tinyInteger('catechism_level')->nullable()->comment('Trình độ giáo lý');
            $table->tinyInteger('position')->nullable()->comment('Chức vụ trong giáo xứ');
            $table->tinyInteger('language')->nullable()->comment('Ngôn ngữ');
            $table->tinyInteger('holy_order_status')->nullable()->comment('Chức thánh (nếu có)');
            $table->boolean('is_new_convert')->default(false)->comment('Tòng giáo (người mới theo đạo)');
            $table->boolean('is_included_in_stats')->default(true)->comment('Có tính vào thống kê không');
            $table->tinyInteger('married')->nullable()->comment('Tình trạng hôn nhân');
            $table->tinyInteger('level')->nullable()->comment('Cấp bậc');
            $table->boolean('status')->default(true)->comment('Trạng thái hoạt động');

            // ==================== ĐỊA CHỈ THƯỜNG TRÚ ====================
            $table->unsignedInteger('permanent_ward_id')->nullable()->comment('Phường/Xã thường trú');
            $table->string('permanent_province', 255)->nullable()->comment('Tỉnh/Thành phố thường trú');
            $table->string('permanent_residence', 255)->nullable()->comment('Địa chỉ thường trú chi tiết');

            // ==================== ĐỊA CHỈ TẠM TRÚ ====================
            $table->unsignedInteger('temporary_ward_id')->nullable()->comment('Phường/Xã tạm trú');
            $table->string('temporary_province', 255)->nullable()->comment('Tỉnh/Thành phố tạm trú');
            $table->string('temporary_residence', 255)->nullable()->comment('Địa chỉ tạm trú chi tiết');

            // ==================== QUÊ QUÁN ====================
            $table->string('origin', 255)->nullable()->comment('Quê quán');

            // ==================== GIA ĐÌNH ====================
            $table->string('father_name', 255)->nullable()->comment('Tên cha');
            $table->string('mother_name', 255)->nullable()->comment('Tên mẹ');

            // ==================== GIÁO XỨ ====================
            $table->date('joined_date')->nullable()->comment('Ngày gia nhập giáo xứ');
            $table->unsignedBigInteger('transferred_from')->nullable()->comment('Chuyển đến từ giáo xứ nào');
            $table->date('transferred_date')->nullable()->comment('Ngày chuyển đến');
            $table->boolean('is_active')->default(true)->comment('Đang sinh hoạt tại giáo xứ');
            $table->string('left_reason', 255)->nullable()->comment('Lý do rời giáo xứ');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parishioners_new');
    }
}
