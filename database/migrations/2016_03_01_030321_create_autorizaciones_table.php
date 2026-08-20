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
        Schema::create('autorizaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('accidente_id')->unsigned()->index();
            $table->integer('otorga_usuario_id')->unsigned()->index();
            $table->integer('recibe_usuario_id')->unsigned()->index();
            $table->longText('descripcion');
            $table->longText('campos_autorizados');
            $table->dateTime('fecha_autorizacion');
            $table->date('fecha_inicio_autorizacion');
            $table->date('fecha_fin_autorizacion');
            $table->timestamps();

            $table->foreign('accidente_id')
                  ->references('id')
                  ->on('accidentes')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('otorga_usuario_id')
                  ->references('id')
                  ->on('usuarios')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('recibe_usuario_id')
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
        Schema::drop('autorizaciones');
    }
};
