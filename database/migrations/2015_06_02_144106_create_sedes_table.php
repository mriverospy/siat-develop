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
        Schema::create('sedes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('institucion_id')->unsigned()->index();
            $table->string('nombre');
            $table->string('descripcion');
            $table->timestamps();

            $table->foreign('institucion_id')
                  ->references('id')
                  ->on('instituciones')
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
        Schema::drop('sedes');
    }
};
