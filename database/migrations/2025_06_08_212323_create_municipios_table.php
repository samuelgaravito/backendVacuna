<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estado_id'); // Relación con estados
            $table->string('nombre', 100);
            $table->timestamps();
            
            // Definición de la clave foránea
            $table->foreign('estado_id')
                  ->references('id')
                  ->on('estados')
                  ->onDelete('cascade'); // Opcional: define el comportamiento al eliminar
        });
    }

    public function down()
    {
        Schema::dropIfExists('municipios');
    }
};