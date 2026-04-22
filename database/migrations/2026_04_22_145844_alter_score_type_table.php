<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterScoreTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('score_types', function (Blueprint $table) {
            $table->dropUnique('uq_class_semester_type');
            $table->unique(
                ['class_id', 'semester', 'type', 'name'],
                'uq_class_semester_type_name'
            );
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
            $table->dropUnique('uq_class_semester_type_name');
            $table->unique(
                ['class_id', 'semester', 'type'],
                'uq_class_semester_type'
            );
        });
    }
}
