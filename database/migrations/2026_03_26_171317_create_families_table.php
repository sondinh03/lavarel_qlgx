<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFamiliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parish_id')->constrained('parishes');
            $table->foreignId('parish_group_id')
                ->nullable()
                ->constrained('parish_groups')
                ->nullOnDelete();
            $table->string('name', 100);                        // tên hộ gia đình
            $table->foreignId('head_id')                        // chủ hộ
                ->nullable()
                ->constrained('parishioners_new')
                ->nullOnDelete();
            $table->text('note')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('families');
    }
}
