<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // color del rol (purpura, azul, verde, etc.)
            if (!Schema::hasColumn('roles', 'color')) {
                $table->string('color', 30)->default('azul')->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'color')) {
                $table->dropColumn('color');
            }
        });
    }
};
