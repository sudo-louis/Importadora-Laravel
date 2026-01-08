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
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contenedor_id')->constrained('contenedores')->onDelete('cascade');
            $table->string('numero_pedimento')->nullable();
            $table->string('clave_pedimento')->nullable();
            $table->string('importador')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->date('fecha_modulacion')->nullable();
            $table->enum('tipo_carga', ['terrestre', 'maritimo'])->nullable();
            $table->date('fecha_carga')->nullable();
            $table->date('reconocimiento_aduanero')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despachos');
    }
};