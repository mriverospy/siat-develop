<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->string('codigo', 50)->nullable();
        });
        Schema::table('respuestas', function (Blueprint $table) {
            $table->string('codigo', 50)->nullable();
        });
        Schema::create('preguntas_detalles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pregunta_id');
            $table->string('codigo', 50)->nullable();
            $table->text('opcion');
            $table->unsignedInteger('orden');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->foreign('pregunta_id')->references('id')->on('preguntas')->onDelete('cascade');
            $table->unique(['pregunta_id', 'codigo']);
        });

        // No modificar textos ni inventar códigos para el catálogo histórico.
        DB::table('preguntas')->whereIn('tipo', ['radio', 'check'])->orderBy('id')->chunkById(200, function ($preguntas) {
            foreach ($preguntas as $pregunta) {
                foreach (explode('|', $pregunta->opciones ?? '') as $orden => $opcion) {
                    if ($opcion === '') continue;
                    DB::table('preguntas_detalles')->insert([
                        'pregunta_id' => $pregunta->id, 'opcion' => $opcion,
                        'codigo' => null, 'orden' => $orden + 1, 'activo' => true,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('preguntas_detalles');
        Schema::table('respuestas', function (Blueprint $table) { $table->dropColumn('codigo'); });
        Schema::table('preguntas', function (Blueprint $table) { $table->dropColumn('codigo'); });
    }
};
