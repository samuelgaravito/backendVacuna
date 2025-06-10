<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vacunas', function (Blueprint $table) {
            $table->id(); // bigint autoincremental
            $table->string('nombre', 255); // varchar
            $table->text('descripcion')->nullable();
            $table->integer('cantidad'); // texto más largo, nullable
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vacunas');
    }
};