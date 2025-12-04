<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarriageAnnouncementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marriage_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('name');            
            $table->integer('priest')->nullable();            
            $table->string('announcements_one');            
            $table->string('announcements_two')->nullable();            
            $table->string('announcements_three')->nullable();
            $table->integer('status');
            $table->timestamps();
        });
        
        Schema::create('marriage_announcements_parishioners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idannouncement')->index('marriage_announcements_idannouncement_foreign');
            //$table->unsignedBigInteger('idgiaodan')->index('marriage_announcements_idgiaodan_foreign')->after('idannouncement');
            $table->unsignedBigInteger('idgiaodan')->index('marriage_announcements_idgiaodan_foreign');            
            $table->string('dioceses_old');
            $table->string('deanerys_old');
            $table->string('parish_managements_old');
            $table->string('parishs_old');
            
            $table->string('dioceses');
            $table->string('deanerys');
            $table->string('parish_managements');
            $table->string('parishs');
            
            $table->string('dioceses_before')->nullable();
            $table->string('deanerys_before')->nullable();
            $table->string('parish_managements_before')->nullable();
            $table->string('parishs_before')->nullable();
            $table->integer('status');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marriage_announcements');
        Schema::dropIfExists('marriage_announcements_parishioners');
    }
}
