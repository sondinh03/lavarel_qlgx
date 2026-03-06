<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodeToParishs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parishes', function (Blueprint $table) {
            $table->string('code', 10)
                ->nullable()
                ->unique()
                ->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parishes', function (Blueprint $table) {
            Schema::table('parishes', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        });
    }
}
