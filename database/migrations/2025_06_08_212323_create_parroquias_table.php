<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parroquias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('municipio_id'); // Relación con municipios
            $table->string('nombre', 100);
            $table->timestamps();
            
            // Definición de la clave foránea
            $table->foreign('municipio_id')
                  ->references('id')
                  ->on('municipios')
                  ->onDelete('cascade'); // Opcional: define el comportamiento al eliminar
        });
    }

    public function down()
    {
        Schema::dropIfExists('parroquias');
    }
};