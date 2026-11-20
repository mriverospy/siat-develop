<?php

namespace App\Services;

use App\Models\Accidente;
use App\Models\Localidad;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class Bloque24Horas
{
    public const NOMBRE = 'Bloque 24 horas';

    public static function esPregunta($pregunta): bool
    {
        return $pregunta && array_key_exists($pregunta->codigo ?? '', config('bloque_24_horas.preguntas'))
            && optional($pregunta->grupoDePreguntas)->nombre === self::NOMBRE;
    }

    /** Admite clientes anteriores; nunca acepta dos fechas contradictorias. */
    public function normalizar(array $datos): array
    {
        if (array_key_exists('fecha_siniestro', $datos) || array_key_exists('hora_siniestro', $datos)) {
            $validador = validator($datos, [
                'fecha_siniestro' => 'required|date_format:Y-m-d',
                'hora_siniestro' => 'required|date_format:H:i',
            ]);
            $validador->validate();
            $fecha = $datos['fecha_siniestro'].' '.$datos['hora_siniestro'].':00';
            if (!empty($datos['fecha_accidente']) && $datos['fecha_accidente'] !== $fecha) {
                throw ValidationException::withMessages(['fecha_accidente' => 'La fecha y hora del bloque contradicen la cabecera.']);
            }
            $datos['fecha_accidente'] = $fecha;
        }
        unset($datos['fecha_siniestro'], $datos['hora_siniestro'], $datos['codigo_siat']);
        return $datos;
    }

    /** Se invoca dentro de la transacción de guardado del accidente. */
    public function sincronizar(Accidente $accidente): void
    {
        $preguntas = Pregunta::whereHas('grupoDePreguntas', fn ($q) => $q->where('nombre', self::NOMBRE))
            ->whereIn('codigo', array_keys(config('bloque_24_horas.preguntas')))->get()->keyBy('codigo');
        if ($preguntas->isEmpty()) return; // Instalaciones que todavía no cargaron el bloque.
        if ($preguntas->count() !== count(config('bloque_24_horas.preguntas'))) {
            throw new RuntimeException('El catálogo del Bloque 24 horas está incompleto. Ejecute Bloque24HorasSeeder.');
        }
        $registro = DB::table('accidentes')->where('id', $accidente->id)->lockForUpdate()->first();
        // Leer el registro bloqueado evita proyectar atributos viejos de un modelo concurrente.
        $actual = new Accidente;
        $actual->setRawAttributes((array) $registro, true);
        if (!$registro->codigo_siat) {
            $anio = (int) $actual->fecha_accidente->format('Y');
            DB::table('secuencias_accidentes_siat')->insertOrIgnore(['anio' => $anio, 'ultimo' => 0]);
            $secuencia = DB::table('secuencias_accidentes_siat')->where('anio', $anio)->lockForUpdate()->first();
            if ($secuencia->ultimo >= 999999) throw new RuntimeException('Se agotó la secuencia SIAT del año.');
            $codigo = sprintf('PY%04d%06d', $anio, $secuencia->ultimo + 1);
            DB::table('secuencias_accidentes_siat')->where('anio', $anio)->update(['ultimo' => $secuencia->ultimo + 1]);
            DB::table('accidentes')->where('id', $accidente->id)->update(['codigo_siat' => $codigo]);
            $accidente->setAttribute('codigo_siat', $codigo);
            $accidente->syncOriginalAttribute('codigo_siat');
        } else {
            $accidente->setAttribute('codigo_siat', $registro->codigo_siat);
        }
        $actual->setAttribute('codigo_siat', $accidente->codigo_siat);
        $accidente = $actual;
        $localidad = Localidad::with('distrito.departamento')->find($accidente->localidad_id);
        $distrito = $localidad ? $localidad->distrito : null;
        $departamento = $distrito ? $distrito->departamento : null;
        $codigoDepartamento = $departamento
            ? config('bloque_24_horas.departamentos.'.Str::upper(Str::ascii(trim($departamento->nombre)))) : null;
        $area = config('formulario_accidente.areas.'.$accidente->area);
        $via = config('formulario_accidente.vias.'.$accidente->tipo_camino);
        $valores = [
            'CA' => $accidente->caratula,
            'A_1' => $accidente->codigo_siat,
            'A_2' => $accidente->fecha_accidente->format('Y-m-d'),
            'A_3' => $accidente->fecha_accidente->format('H:i'),
            'APY_01' => $departamento ? $departamento->nombre : null,
            'APY_02' => $distrito ? $distrito->nombre : null,
            'APY_03' => $localidad ? $localidad->nombre : null,
            'APY_04' => $accidente->lugar,
            // Texto conserva los decimales originales, también para históricos.
            'A_8' => $accidente->gps_latitud, 'A_9' => $accidente->gps_longitud,
            'A_10' => $area ?? $accidente->area,
            'A_11' => $via ?? $accidente->tipo_camino,
        ];
        foreach ($valores as $codigo => $valor) {
            $pregunta = $preguntas[$codigo];
            $respuestas = $accidente->respuestas()->where('pregunta_id', $pregunta->id)->lockForUpdate()->get();
            if ($respuestas->count() > 1) throw new RuntimeException("Respuesta duplicada para {$codigo}.");
            $respuesta = $respuestas->first() ?? new Respuesta;
            $datos = [
                'pregunta_id' => $pregunta->id, 'accidente_id' => $accidente->id,
                'usuario_id' => $respuesta->exists ? $respuesta->usuario_id : (auth()->id() ?? $accidente->usuario_id), 'agrupador' => 0,
                'pregunta_texto' => $pregunta->pregunta, 'pregunta_tipo' => $pregunta->tipo,
                'respuesta_texto' => $codigo === 'A_2' ? null : $valor,
                'respuesta_fecha' => $codigo === 'A_2' ? $valor : null,
                'respuesta_numero' => null,
                'codigo' => $codigo === 'APY_01' ? $codigoDepartamento : Respuesta::codigoPara($pregunta->id, $valor),
            ];
            // Las claves locales se conservan como referencia, no como códigos INE.
            if ($codigo === 'APY_01') $datos['respuesta_numero'] = $departamento->id ?? null;
            if ($codigo === 'APY_02') $datos['respuesta_numero'] = $distrito->id ?? null;
            if ($codigo === 'APY_03') $datos['respuesta_numero'] = $localidad->id ?? null;
            $respuesta->fill($datos);
            if ($respuesta->isDirty()) {
                if ($respuesta->exists) $respuesta->usuario_id = auth()->id() ?? $accidente->usuario_id;
                $respuesta->save();
            }
        }
    }
}
