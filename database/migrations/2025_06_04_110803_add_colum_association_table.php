<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumAssociationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('associations', function (Blueprint $table) {
            $table->date('ngaybonmang')->nullable()->after('name');
            $table->date('ngaythanhlap')->nullable()->after('ngaybonmang');
            $table->text('thanhbonmang')->nullable()->after('ngaythanhlap');
            $table->text('note')->nullable()->after('thanhbonmang');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
