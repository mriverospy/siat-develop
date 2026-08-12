<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->dropColumn('fecha_inicio');
            $table->dropColumn('fecha_fin');
            $table->boolean('compartido')->default(false);
            $table->jsonb('instituciones')->nullable();
            $table->integer('ejecutar_como_usuario')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->dropColumn('compartido');
            $table->dropColumn('instituciones');
            $table->dropColumn('ejecutar_como_usuario');
        });
    }
};
