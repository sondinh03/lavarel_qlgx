<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarriagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marriages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('husband_id')
                ->constrained('parishioners_new')
                ->cascadeOnDelete();
            $table->foreignId('wife_id')
                ->constrained('parishioners_new')
                ->cascadeOnDelete();
            $table->date('married_date')->nullable();
            $table->string('certificate_number', 50)->nullable();

            // Nơi hôn phối
            $table->foreignId('parish_id')
                ->nullable()
                ->constrained('parishes')
                ->nullOnDelete();
            $table->string('parish_name', 100)->nullable();  // nếu giáo xứ ngoài hệ thống

            $table->enum('status', ['valid', 'invalid', 'widowed', 'divorced'])
                ->default('valid');
            $table->string('witness_1', 100)->nullable();    // nhân chứng 1
            $table->string('witness_2', 100)->nullable();    // nhân chứng 2
            $table->text('note')->nullable();
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
        Schema::dropIfExists('marriages');
    }
}
