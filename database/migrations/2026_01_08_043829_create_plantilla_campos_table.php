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
        Schema::create('plantilla_campos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_id')->constrained()->onDelete('cascade');
            $table->string('campo'); // nombre del campo del contenedor
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantilla_campos');
    }
};