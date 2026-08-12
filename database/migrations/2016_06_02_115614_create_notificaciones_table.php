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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('usuario_id')->unsigned()->index();
            $table->dateTime('fecha');
            $table->string('asunto');
            $table->longText('mensaje');
            $table->boolean('leido')->default(false)->index();
            $table->timestamps();

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
        Schema::drop('notificaciones');
    }
};
