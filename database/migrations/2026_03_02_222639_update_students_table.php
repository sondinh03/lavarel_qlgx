<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            // giáo họ
            $table->unsignedBigInteger('parish_group_id')->nullable()->after('parish_id');
            $table->string('father_name')->nullable()->after('last_name');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('note')->nullable()->after('is_active');

            $table->index('parish_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            //
        });
    }
}
