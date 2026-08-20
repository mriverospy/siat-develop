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
        Schema::table('derivaciones', function (Blueprint $table) {
            DB::statement('ALTER TABLE `derivaciones` MODIFY `deriva_a_sede_id` INTEGER UNSIGNED NULL;');
            DB::statement('ALTER TABLE `derivaciones` MODIFY `deriva_a_usuario_id` INTEGER UNSIGNED NULL;');
            $table->dropForeign('derivaciones_deriva_a_sede_id_foreign');
            $table->dropForeign('derivaciones_deriva_a_usuario_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('derivaciones', function (Blueprint $table) {
            // Usuario: sysadmin
            DB::statement('UPDATE `derivaciones` SET `deriva_a_sede_id` = 1 WHERE `deriva_a_sede_id` IS NULL;');
            // Sede: ANTSV/Central
            DB::statement('UPDATE `derivaciones` SET `deriva_a_usuario_id` = 59 WHERE `deriva_a_usuario_id` IS NULL;');
            DB::statement('ALTER TABLE `derivaciones` MODIFY `deriva_a_sede_id` INTEGER UNSIGNED NOT NULL;');
            DB::statement('ALTER TABLE `derivaciones` MODIFY `deriva_a_usuario_id` INTEGER UNSIGNED NOT NULL;');
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
};
