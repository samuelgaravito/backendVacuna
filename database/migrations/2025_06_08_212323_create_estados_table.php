<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('estados', function (Blueprint $table) {
            $table->id(); // Laravel usa bigIncrements por defecto
            $table->string('nombre', 100);
            $table->timestamps(); // Opcional, pero recomendado para control
        });
    }

    public function down()
    {
        Schema::dropIfExists('estados');
    }
};