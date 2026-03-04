<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersParishFk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_parish_id_foreign');
            $table->foreign('parish_id')
                ->references('id')
                ->on('parishes')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_parish_id_foreign');
            $table->foreign('parish_id')
                ->references('id')
                ->on('parishs')
                ->nullOnDelete();
        });
    }
}
