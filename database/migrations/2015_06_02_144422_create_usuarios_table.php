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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sede_id')->unsigned()->index();
            $table->string('documento', 50)->nullable();
            $table->string('genero', 50)->default('Sin Definir');
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('telefonos')->nullable();
            $table->string('direccion')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('api_key')->unique()->nullable();
            $table->string('username')->unique();
            $table->string('password', 100);
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('sede_id')
                  ->references('id')
                  ->on('sedes')
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
        Schema::drop('usuarios');
    }
};
