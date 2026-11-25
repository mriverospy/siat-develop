<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AlinearOpcionesDiccionarioSiatSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            foreach (['APY_07', 'P_4', 'P_5'] as $codigo) {
                $preguntas = DB::table('preguntas')->where('codigo', $codigo)->lockForUpdate()->get();
                if ($preguntas->count() !== 1) throw new RuntimeException("Se esperaba una pregunta única {$codigo}.");
                $pregunta = $preguntas->first();
                $detalles = DB::table('preguntas_detalles')->where('pregunta_id', $pregunta->id)->lockForUpdate()->get();
                $desconocidas = $detalles->filter(fn ($d) => $d->opcion === 'Se desconoce' || $d->codigo === '99');
                if ($desconocidas->count() > 1 || ($desconocidas->count() === 1 && ($desconocidas->first()->opcion !== 'Se desconoce' || !in_array($desconocidas->first()->codigo, [null, '', '99'], true)))) {
                    throw new RuntimeException("Conflicto en la opción 99 de {$codigo}.");
                }
                if ($desconocidas->isEmpty()) {
                    DB::table('preguntas_detalles')->insert(['pregunta_id' => $pregunta->id, 'opcion' => 'Se desconoce', 'codigo' => '99', 'activo' => true, 'orden' => ($detalles->max('orden') ?? 0) + 1, 'created_at' => now(), 'updated_at' => now()]);
                } else {
                    DB::table('preguntas_detalles')->where('id', $desconocidas->first()->id)->update(['codigo' => '99', 'activo' => true]);
                }
                $opciones = DB::table('preguntas_detalles')->where('pregunta_id', $pregunta->id)->where('activo', true)->orderBy('orden')->orderBy('id')->pluck('opcion')->all();
                $datos = ['opciones' => implode('|', $opciones)];
                if ($codigo === 'P_5') $datos['obligatorio'] = false;
                DB::table('preguntas')->where('id', $pregunta->id)->update($datos);
            }
            $preguntas = DB::table('preguntas')->where('codigo', 'V_2')->lockForUpdate()->get();
            if ($preguntas->count() !== 1) throw new RuntimeException('Se esperaba una pregunta única V_2.');
            $pregunta = $preguntas->first();
            $detalles = DB::table('preguntas_detalles')->where('pregunta_id', $pregunta->id)->where('codigo', '6')->lockForUpdate()->get();
            $anterior = 'Vehículo para transporte de mercancías pesado (>3,5 tn)';
            $nuevo = 'Vehículo para transporte de mercancías pesado (≥3,5 tn)';
            if ($detalles->count() !== 1 || !in_array($detalles->first()->opcion, [$anterior, $nuevo], true)) throw new RuntimeException('Conflicto en V_2 opción 6.');
            DB::table('preguntas_detalles')->where('id', $detalles->first()->id)->update(['opcion' => $nuevo]);
            $opciones = DB::table('preguntas_detalles')->where('pregunta_id', $pregunta->id)->where('activo', true)->orderBy('orden')->orderBy('id')->pluck('opcion')->all();
            DB::table('preguntas')->where('id', $pregunta->id)->update(['opciones' => implode('|', $opciones)]);
        });
    }
}
