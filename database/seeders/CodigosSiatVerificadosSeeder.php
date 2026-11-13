<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CodigosSiatVerificadosSeeder extends Seeder
{
    /** Correspondencias verificadas contra Diccionario_SIAT.pdf (noviembre de 2026). */
    public function run()
    {
        $filas = json_decode(file_get_contents(database_path('data/diccionario_siat_catalogo.json')), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($filas) {
            foreach ($filas as $fila) {
                ['grupo' => $grupo, 'pregunta' => $texto, 'codigo' => $codigo] = $fila;
                // No depender de IDs locales ni asignar códigos al catálogo histórico.
                $preguntas = DB::table('preguntas')
                    ->join('grupos_de_preguntas as g', 'g.id', '=', 'preguntas.grupo_de_preguntas_id')
                    ->where('g.nombre', $grupo)->where('preguntas.pregunta', $texto)
                    ->select('preguntas.id', 'preguntas.codigo', 'preguntas.opciones')->lockForUpdate()->get();
                if ($preguntas->count() !== 1) {
                    throw new RuntimeException("Se esperaba una única pregunta: {$grupo} / {$texto}.");
                }
                $pregunta = $preguntas->first();
                if ($pregunta->codigo !== null && $pregunta->codigo !== '' && $pregunta->codigo !== $codigo) {
                    throw new RuntimeException("La pregunta {$pregunta->id} ya tiene otro código.");
                }
                if (DB::table('preguntas')->where('codigo', $codigo)->where('id', '<>', $pregunta->id)->exists()) {
                    throw new RuntimeException("El código {$codigo} ya está asignado a otra pregunta.");
                }
                $opciones = $fila['opciones'];
                $textosActuales = $pregunta->opciones ? explode('|', $pregunta->opciones) : [];
                $textosEsperados = array_keys($opciones);
                sort($textosActuales);
                sort($textosEsperados);
                if ($textosActuales !== $textosEsperados) {
                    throw new RuntimeException("Las opciones de {$codigo} difieren del catálogo verificado.");
                }
                foreach ($opciones as $opcion => $valor) {
                    $detalles = DB::table('preguntas_detalles')->where('pregunta_id', $pregunta->id)
                        ->lockForUpdate()->get()->filter(fn ($d) => $d->opcion === $opcion && $d->activo);
                    if ($detalles->count() !== 1) {
                        throw new RuntimeException("Se esperaba una única opción activa: {$codigo} / {$opcion}.");
                    }
                    $detalle = $detalles->first();
                    if ($detalle->codigo !== null && $detalle->codigo !== '' && $detalle->codigo !== $valor) {
                        throw new RuntimeException("La opción {$detalle->id} ya tiene otro código.");
                    }
                    if (DB::table('preguntas_detalles')->where('pregunta_id', $pregunta->id)
                        ->where('codigo', $valor)->where('id', '<>', $detalle->id)->exists()) {
                        throw new RuntimeException("El valor {$valor} ya está asignado en {$codigo}.");
                    }
                    DB::table('preguntas_detalles')->where('id', $detalle->id)->update(['codigo' => $valor]);
                }
                DB::table('preguntas')->where('id', $pregunta->id)->update(['codigo' => $codigo]);
            }
        });
    }
}
