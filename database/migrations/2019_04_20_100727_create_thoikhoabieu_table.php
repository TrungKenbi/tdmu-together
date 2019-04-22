<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThoikhoabieuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thoikhoabieu', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user', 20);
            $table->string('MaMH', 10);
            $table->string('TenMH', 100);
            $table->string('Phong', 10);
            $table->integer('Thu');
            $table->integer('TietBatDau');
            $table->integer('SoTiet');
            $table->string('GiangVien', 50);
            $table->string('Lop', 20);
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
        Schema::dropIfExists('thoikhoabieu');
    }
}
