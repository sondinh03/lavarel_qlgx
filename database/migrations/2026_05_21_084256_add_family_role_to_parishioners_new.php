<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFamilyRoleToParishionersNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parishioners_new', function (Blueprint $table) {
            $table->enum('family_role', ['husband', 'wife', 'child', 'other'])
                ->nullable()
                ->after('family_id')
                ->comment('Vai trò trong gia đình: husband=chồng, wife=vợ, child=con, other=khác');
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
            $table->dropColumn('family_role');
        });
    }
}
