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
        Schema::create('contenedores', function (Blueprint $table) {
            $table->id();
            $table->string('numero_contenedor')->unique();
            $table->string('cliente');
            $table->date('fecha_llegada');
            $table->string('proveedor');
            $table->string('naviera');
            $table->string('mercancia_recibida');
            $table->enum('estado', ['pendiente', 'en_proceso', 'entregado'])->default('pendiente');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contenedores');
    }
};