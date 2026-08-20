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
        Schema::table('grupos_de_preguntas', function (Blueprint $table) {
            $table->boolean('puede_duplicar')->default(0)->after('master');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grupos_de_preguntas', function (Blueprint $table) {
            $table->dropColumn('puede_duplicar');
        });
    }
};
