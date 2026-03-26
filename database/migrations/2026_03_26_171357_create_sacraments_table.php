<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSacramentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sacraments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parishioner_id')
                ->constrained('parishioners_new')
                ->cascadeOnDelete();
            $table->enum('type', [
                'baptism',      // rửa tội
                'communion',    // rước lễ lần đầu
                'confirmation', // thêm sức
                'anointing',    // xức dầu
                'holy_orders',  // truyền chức
            ]);
            $table->date('received_date')->nullable();
            $table->string('certificate_number', 50)->nullable(); // số chứng chỉ/sổ
            $table->unsignedInteger('book_number')->nullable();   // số sách
            $table->string('giver', 100)->nullable();             // người ban bí tích
            $table->string('sponsor', 100)->nullable();           // người đỡ đầu

            // Nơi lãnh nhận — có thể là giáo xứ khác nên lưu text thay vì FK
            $table->foreignId('parish_id')
                ->nullable()
                ->constrained('parishes')
                ->nullOnDelete();
            $table->string('parish_name', 100)->nullable();       // nếu giáo xứ khác hệ thống
            $table->foreignId('deanery_id')
                ->nullable()
                ->constrained('deanerys')
                ->nullOnDelete();
            $table->foreignId('diocese_id')
                ->nullable()
                ->constrained('dioceses')
                ->nullOnDelete();

            $table->text('note')->nullable();
            $table->timestamps();

            // 1 người chỉ lãnh 1 lần mỗi bí tích (trừ xức dầu)
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
        Schema::dropIfExists('sacraments');
    }
}
