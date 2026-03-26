<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parishioner_id')
                ->constrained('parishioners_new')
                ->cascadeOnDelete();
            $table->enum('type', ['permanent', 'temporary']);  // thường trú, tạm trú
            $table->unsignedInteger('ward_id')->nullable();    // mã phường/xã
            $table->string('province', 100)->nullable();       // tỉnh/thành
            $table->string('residence', 255)->nullable();      // địa chỉ cụ thể
            $table->timestamps();

            // 1 người chỉ có 1 thường trú, 1 tạm trú
            $table->unique(['parishioner_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
