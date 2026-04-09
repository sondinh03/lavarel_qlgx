<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterParishionersNewTableSecond extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parishioners_new', function (Blueprint $table) {
            // Con thứ trong gia đình (từ sổ gia đình)
            $table->tinyInteger('birth_order')
                ->nullable()
                ->after('birthday')
                ->comment('Con thứ mấy trong gia đình');

            // Trình độ chuyên môn (mục 27)
            $table->tinyInteger('specialist_level')
                ->nullable()
                ->after('education_level')
                ->comment('Trình độ chuyên môn');

            // Chuyên ngành giáo lý / giáo dục (mục 28)
            $table->string('catechism_major', 100)
                ->nullable()
                ->after('catechism_level')
                ->comment('Chuyên ngành giáo lý / giáo dục');

            // Thông tin tử vong (mục 58–61)
            $table->date('death_date')
                ->nullable()
                ->after('status')
                ->comment('Ngày mất (mục 58)');

            $table->string('death_book_number', 20)
                ->nullable()
                ->after('death_date')
                ->comment('Số sổ mất (mục 59)');

            $table->string('death_place', 255)
                ->nullable()
                ->after('death_book_number')
                ->comment('Nơi qua đời (mục 60)');

            $table->string('burial_place', 255)
                ->nullable()
                ->after('death_place')
                ->comment('Nơi an táng (mục 61)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parishioners_new', function (Blueprint $table) {
            $table->dropColumn([
                'birth_order',
                'specialist_level',
                'catechism_major',
                'death_date',
                'death_book_number',
                'death_place',
                'burial_place',
            ]);
        });
    }
}
