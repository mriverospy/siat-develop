<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accidentes', function (Blueprint $table) {
            $table->string('codigo_siat', 12)->nullable()->unique();
        });
        Schema::create('secuencias_accidentes_siat', function (Blueprint $table) {
            $table->unsignedSmallInteger('anio')->primary();
            $table->unsignedInteger('ultimo')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('secuencias_accidentes_siat');
        Schema::table('accidentes', function (Blueprint $table) {
            $table->dropUnique(['codigo_siat']);
            $table->dropColumn('codigo_siat');
        });
    }
};
