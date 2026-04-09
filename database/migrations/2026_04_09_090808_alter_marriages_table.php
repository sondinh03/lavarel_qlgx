<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMarriagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marriages', function (Blueprint $table) {
            // Địa chỉ nơi hôn phối (mục 26–27)
            $table->unsignedInteger('place_ward_id')
                ->nullable()
                ->after('parish_name')
                ->comment('Xã/Phường nơi hôn phối (mục 26)');

            $table->string('place_province', 100)
                ->nullable()
                ->after('place_ward_id')
                ->comment('Tỉnh/TP nơi hôn phối (mục 27)');

            // Linh mục chứng hôn (mục 28)
            $table->string('priest_witness', 100)
                ->nullable()
                ->after('witness_2')
                ->comment('Linh mục chứng hôn (mục 28)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marriages', function (Blueprint $table) {
            $table->dropColumn([
                'place_ward_id',
                'place_province',
                'priest_witness',
            ]);
        });
    }
}
