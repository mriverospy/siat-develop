<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        // Create table for storing roles
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create table for associating roles to users (Many-to-Many)
        Schema::create('rol_usuario', function (Blueprint $table) {
            $table->integer('usuario_id')->unsigned();
            $table->integer('rol_id')->unsigned();

            $table->foreign('usuario_id')->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('rol_id')->references('id')->on('roles')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['usuario_id', 'rol_id']);
        });

        // Create table for storing permissions
        Schema::create('permisos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create table for associating permissions to roles (Many-to-Many)
        Schema::create('permiso_rol', function (Blueprint $table) {
            $table->integer('permiso_id')->unsigned();
            $table->integer('rol_id')->unsigned();

            $table->foreign('permiso_id')->references('id')->on('permisos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('rol_id')->references('id')->on('roles')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['permiso_id', 'rol_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::drop('permiso_rol');
        Schema::drop('permisos');
        Schema::drop('rol_usuario');
        Schema::drop('roles');
    }
};
