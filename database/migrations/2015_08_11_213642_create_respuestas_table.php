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
        Schema::create('respuestas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('accidente_id')->unsigned()->index();
            $table->integer('pregunta_id')->unsigned()->index();
            $table->integer('usuario_id')->unsigned()->index();
            $table->string('pregunta_texto')->nullable();
            $table->string('pregunta_tipo')->nullable();
            $table->string('respuesta_texto')->nullable();
            $table->double('respuesta_numero', 14, 2)->nullable();
            $table->dateTime('respuesta_fecha')->nullable();
            $table->timestamps();

            $table->foreign('accidente_id')
                  ->references('id')
                  ->on('accidentes')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('pregunta_id')
                  ->references('id')
                  ->on('preguntas')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('usuario_id')
                  ->references('id')
                  ->on('usuarios')
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
        Schema::drop('respuestas');
    }
};
