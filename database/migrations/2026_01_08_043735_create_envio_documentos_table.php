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
        Schema::create('envio_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contenedor_id')->constrained('contenedores')->onDelete('cascade');
            $table->boolean('enviado')->default(false);
            $table->date('fecha_envio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envio_documentos');
    }
};