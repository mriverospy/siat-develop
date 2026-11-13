<?php

namespace Database\Seeders;

use App\Models\GrupoDePreguntas;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Bloque24HorasSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $grupos = GrupoDePreguntas::where('nombre', 'Bloque 24 horas')->lockForUpdate()->get();
            if ($grupos->count() > 1) throw new RuntimeException('Hay más de un Bloque 24 horas.');
            $grupo = $grupos->first() ?? new GrupoDePreguntas;
            $grupo->fill(['nombre' => 'Bloque 24 horas', 'descripcion' => 'Datos iniciales del siniestro según Diccionario SIAT.',
                'master' => true, 'visible' => true, 'puede_duplicar' => false, 'orden' => 0]);
            $grupo->save();
            $orden = 0;
            foreach (config('bloque_24_horas.preguntas') as $codigo => $definicion) {
                ++$orden;
                $preguntas = Pregunta::where('codigo', $codigo)->lockForUpdate()->get();
                if ($preguntas->count() > 1 || ($preguntas->first() && $preguntas->first()->grupo_de_preguntas_id !== $grupo->id)) {
                    throw new RuntimeException("El código {$codigo} ya está en otro grupo o está duplicado.");
                }
                if ($preguntas->isNotEmpty()) {
                    // Orden estable; respetar los textos y opciones existentes.
                    $preguntas->first()->update(['orden' => $orden]);
                    continue;
                }
                $opciones = $definicion['opciones'] ?? [];
                $pregunta = Pregunta::create([
                    'grupo_de_preguntas_id' => $grupo->id, 'codigo' => $codigo,
                    'pregunta' => $definicion['pregunta'], 'tipo' => $definicion['tipo'],
                    'orden' => $orden, 'activo' => true, 'obligatorio' => true,
                    'opciones' => implode('|', array_keys($opciones)), 'mostrar_otro' => false,
                    'instrucciones' => 'Bloque 24 horas: '.$codigo,
                ]);
                foreach ($opciones as $texto => $valor) {
                    $pregunta->detalles()->where('opcion', $texto)->update(['codigo' => $valor]);
                }
            }
        });
    }
}
