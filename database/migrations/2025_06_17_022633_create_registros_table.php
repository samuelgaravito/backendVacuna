<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registros', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('representado_id');
            $table->foreign('representado_id')->references('id')->on('representados')->onDelete('cascade');
            $table->unsignedBigInteger('personal_salud_id');
            $table->foreign('personal_salud_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('vacuna_id');
            $table->foreign('vacuna_id')->references('id')->on('vacunas')->onDelete('cascade');
            $table->string('dosis');

            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros');
    }
};