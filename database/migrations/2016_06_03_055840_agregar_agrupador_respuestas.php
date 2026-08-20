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
        Schema::table('respuestas', function (Blueprint $table) {
            // $table->integer('grupo_id')->index()->after('accidente_id');
            $table->integer('agrupador')->after('usuario_id');

            // $table->foreign('grupo_id')
            //       ->references('id')
            //       ->on('grupos')
            //       ->onUpdate('cascade')
            //       ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('respuestas', function (Blueprint $table) {
            // $table->dropColumn('grupo_id');
            $table->dropColumn('agrupador');
        });
    }
};
