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
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contenedor_id')->constrained('contenedores')->onDelete('cascade');
            $table->date('fecha_pago')->nullable();
            $table->decimal('impuestos', 10, 2)->default(0);
            $table->decimal('honorarios', 10, 2)->default(0);
            $table->decimal('maniobras', 10, 2)->default(0);
            $table->decimal('almacenaje', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->storedAs('impuestos + honorarios + maniobras + almacenaje');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};