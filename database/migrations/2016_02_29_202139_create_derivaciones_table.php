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
        Schema::create('derivaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('accidente_id')->unsigned()->index();
            $table->integer('institucion_id')->unsigned()->index();
            $table->integer('sede_id')->unsigned()->index();
            $table->integer('usuario_id')->unsigned()->index();
            $table->integer('deriva_a_institucion_id')->unsigned()->index();
            $table->integer('deriva_a_sede_id')->unsigned()->index();
            $table->integer('deriva_a_usuario_id')->unsigned()->index();
            $table->date('fecha_derivacion');
            $table->date('fecha_aceptacion')->nullable();
            $table->enum('estado', ['pendiente', 'recibido'])->default('pendiente')->index();
            $table->timestamps();

            $table->foreign('accidente_id')
                  ->references('id')
                  ->on('accidentes')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('institucion_id')
                  ->references('id')
                  ->on('instituciones')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('sede_id')
                  ->references('id')
                  ->on('sedes')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('usuario_id')
                  ->references('id')
                  ->on('usuarios')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('deriva_a_institucion_id')
                  ->references('id')
                  ->on('instituciones')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('deriva_a_sede_id')
                  ->references('id')
                  ->on('sedes')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('deriva_a_usuario_id')
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
        Schema::drop('derivaciones');
    }
};
