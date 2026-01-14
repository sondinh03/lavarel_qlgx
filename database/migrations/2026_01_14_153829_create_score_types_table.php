<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScoreTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('score_types', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('class_id');
            $table->tinyInteger('semester')
                ->comment('1 = HK1, 2 = HK2');

            $table->tinyInteger('type')
                ->comment('1: Khảo kinh, 2: 15p, 3: 45p, 4: Giữa kỳ, 5: Cuối kỳ');

            $table->string('name', 100);
            $table->decimal('weight', 5, 2)->default(1.00)
                ->comment('Hệ số điểm (tùy chỉnh theo xứ)');
            $table->decimal('max_score', 5, 2)->default(10.00)
                ->comment('Điểm tối đa');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Index
            $table->index('class_id');
            $table->index(['class_id', 'semester']);
            $table->index(['class_id', 'type']);

            // Unique: Mỗi lớp + học kỳ + loại điểm chỉ có 1 cấu hình
            $table->unique(['class_id', 'semester', 'type'], 'uq_class_semester_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('score_types');
    }
}
