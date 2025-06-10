<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('representados', function (Blueprint $table) {
            $table->id();
            
            // Datos básicos
            $table->string('cedula', 20)->unique();
            $table->string('nombre_completo');
            $table->date('fecha_nacimiento');
            $table->enum('sexo', ['M', 'F']);
            $table->enum('nacionalidad', ['venezolano', 'extranjero']);
            $table->text('direccion');
            
            // Relaciones - CORRECTED ORDER: nullable() BEFORE constrained()
            $table->foreignId('parroquia_id')->nullable()->constrained('parroquias')->onDelete('set null');
            $table->foreignId('grupo_riesgo_id')->nullable()->constrained('grupos_riesgo')->onDelete('set null');
            $table->foreignId('indigena_id')->nullable()->constrained('indigenas')->onDelete('set null');
            
            // This one is correctly defined as NOT NULL by default (no nullable())
            // and cascade delete is good here for user_id.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
           
            $table->timestamps();
            
            // Índices
            $table->index('cedula');
            $table->index('nombre_completo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('representados');
    }
};