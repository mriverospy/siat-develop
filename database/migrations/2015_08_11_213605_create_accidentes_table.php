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
        Schema::create('accidentes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('usuario_id')->unsigned()->index();
            $table->integer('sede_id')->unsigned()->index();
            $table->integer('institucion_id')->unsigned()->index();
            $table->integer('localidad_id')->unsigned()->index();
            $table->integer('estado_id')->unsigned()->index();
            $table->string('caratula')->nullable();
            $table->dateTime('fecha_accidente');
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable()->default(null);
            $table->string('expediente_judicial')->nullable();
            $table->string('sumario_policial')->nullable();
            $table->string('otro')->nullable();
            $table->string('dependencia')->nullable();
            $table->string('lugar')->nullable();
            $table->string('tipo_camino')->nullable();
            $table->integer('altura')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->string('gps_latitud');
            $table->string('gps_longitud');
            $table->integer('heridos')->default(0);
            $table->integer('fallecidos')->default(0);
            $table->integer('ilesos')->default(0);
            $table->integer('participantes_siniestro')->default(0);
            $table->timestamps();

            $table->foreign('usuario_id')
                  ->references('id')
                  ->on('usuarios')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('sede_id')
                  ->references('id')
                  ->on('sedes')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('institucion_id')
                  ->references('id')
                  ->on('instituciones')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('localidad_id')
                  ->references('id')
                  ->on('localidades')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('estado_id')
                  ->references('id')
                  ->on('estados')
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
        Schema::drop('accidentes');
    }
};
