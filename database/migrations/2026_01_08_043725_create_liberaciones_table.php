<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liberaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contenedor_id')->constrained('contenedores')->onDelete('cascade');
            $table->string('naviera')->nullable();
            $table->integer('dias_libres')->nullable();
            $table->boolean('revalidacion')->default(false);
            $table->date('fecha_revalidacion')->nullable();
            $table->decimal('costo_liberacion', 10, 2)->nullable();
            $table->date('fecha_liberacion')->nullable();
            $table->decimal('garantia', 10, 2)->nullable();
            $table->date('fecha_garantia')->nullable();
            $table->enum('devolucion_garantia', ['pendiente', 'entregado'])->default('pendiente');
            $table->decimal('costos_demora', 10, 2)->nullable();
            $table->date('fecha_demora')->nullable();
            $table->decimal('flete_maritimo', 10, 2)->nullable();
            $table->date('fecha_flete')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liberaciones');
    }
};