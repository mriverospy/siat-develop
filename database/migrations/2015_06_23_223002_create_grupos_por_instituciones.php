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
        Schema::create('grupos_por_instituciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('institucion_id')->unsigned()->index();
            $table->integer('grupo_de_preguntas_id')->unsigned()->index();
            $table->integer('orden');
            $table->timestamps();

            $table->foreign('institucion_id')
                  ->references('id')
                  ->on('instituciones')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('grupo_de_preguntas_id')
                  ->references('id')
                  ->on('grupos_de_preguntas')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('grupos_por_instituciones');
    }
};
