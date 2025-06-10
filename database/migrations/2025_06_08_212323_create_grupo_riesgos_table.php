<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
 Schema::create('grupos_riesgo', function (Blueprint $table) {  // Cambiado a plural
    $table->id();
    $table->string('nombre');
    $table->string('tipo');
    $table->timestamps();
});
    }

    public function down()
    {
        Schema::dropIfExists('grupo_riesgo');
    }
};