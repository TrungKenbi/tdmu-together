<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLichthiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lichthi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user', 20);
            $table->string('MaMH', 100);
            $table->string('TenMH', 100);
            $table->string('Nhom', 100);
            $table->string('To', 100);
            $table->string('SiSo', 100);
            $table->string('NgayThi', 15);
            $table->string('TGThi', 10);
            $table->string('SoPhut', 10);
            $table->string('PhongThi', 10);
            $table->string('HinhThuc', 50);
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
        Schema::dropIfExists('lichthi');
    }
}
