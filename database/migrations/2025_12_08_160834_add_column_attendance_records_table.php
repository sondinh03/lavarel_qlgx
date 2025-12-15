<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnAttendanceRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->string('created_by')->nullable()->after('updated_at');
            $table->string('updated_by')->nullable()->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            //
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
}
