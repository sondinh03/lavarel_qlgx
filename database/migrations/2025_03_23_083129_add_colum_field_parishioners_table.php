<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumFieldParishionersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parishioners', function (Blueprint $table) {
            $table->string('origin')->nullable()->after('email');
            $table->string('residence')->after('province');
            $table->integer('resi_ward')->after('residence');
            $table->integer('resi_district')->after('resi_ward');
            $table->string('resi_province')->after('resi_district');
            $table->string('professional_level')->nullable()->after('resi_province');
            $table->integer('study')->after('professional_level');
            $table->integer('new_convert')->nullable()->after('study');
            $table->integer('married')->nullable()->after('new_convert');
            $table->integer('statistical')->nullable()->after('married');
            $table->text('note')->nullable()->after('statistical');
            $table->date('baptism_date')->after('note');
            $table->integer('baptism_number')->after('baptism_date');
            $table->integer('baptism_giver')->after('baptism_number');
            $table->integer('baptism_sponsor')->after('baptism_giver');
            $table->date('more_power_date')->after('baptism_address');
            $table->integer('more_power_number')->after('more_power_date');
            $table->integer('more_power_giver')->after('more_power_number');
            $table->integer('more_power_sponsor')->after('more_power_giver');
            $table->integer('more_power_address')->after('more_power_sponsor');
            $table->date('communion_date')->after('more_power_address');
            $table->integer('communion_number')->after('communion_date');
            $table->integer('communion_giver')->after('communion_number');
            $table->integer('communion_address')->after('communion_giver');
            $table->date('anoint_date')->after('communion_address');
            $table->integer('anoint_status')->after('anoint_date');
            $table->integer('anoint_giver')->after('anoint_status');
            $table->text('anoint_note')->after('anoint_giver');
            $table->integer('die_status')->nullable()->after('anoint_note');
            $table->date('die_time')->nullable()->after('die_status');
            $table->integer('die_lottery')->nullable()->after('die_time');
            $table->string('die_death')->nullable()->after('die_lottery');
            $table->string('die_burial')->nullable()->after('die_death');
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
