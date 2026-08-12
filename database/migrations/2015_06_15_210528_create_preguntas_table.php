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
        Schema::create('preguntas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('grupo_de_preguntas_id')->unsigned()->index();
            $table->string('pregunta');
            $table->integer('orden');
            $table->string('tipo');
            $table->text('opciones')->nullable();
            $table->string('instrucciones')->default('');
            $table->boolean('mostrar_otro')->default(0);
            $table->string('titulo_otro')->nullable();
            $table->timestamps();

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
        Schema::drop('preguntas');
    }
};
