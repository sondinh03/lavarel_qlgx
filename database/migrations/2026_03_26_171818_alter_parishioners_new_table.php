<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterParishionersNewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parishioners_new', function (Blueprint $table) {
            $table->foreignId('father_id')->nullable()->constrained('parishioners_new')->nullOnDelete();
            $table->foreignId('mother_id')->nullable()->constrained('parishioners_new')->nullOnDelete();
            $table->foreignId('family_id')->nullable()->constrained('families')->nullOnDelete();
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
            // Drop foreign keys
            $table->dropForeign(['father_id']);
            $table->dropForeign(['mother_id']);
            $table->dropForeign(['family_id']);

            // Drop columns
            $table->dropColumn(['father_id', 'mother_id', 'family_id']);
        });
    }
}
