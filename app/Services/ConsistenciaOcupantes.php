<?php

namespace App\Services;

use App\Models\Pregunta;
use App\Models\Respuesta;

class ConsistenciaOcupantes
{
    public function validarAccidente($accidenteId): array
    {
        $respuestas = [];
        foreach (Respuesta::where('accidente_id', $accidenteId)->get() as $respuesta) {
            $respuestas[$respuesta->agrupador][$respuesta->pregunta_id][] = $respuesta->respuesta_texto ?? $respuesta->respuesta_numero ?? ($respuesta->respuesta_fecha ? $respuesta->respuesta_fecha->format('Y-m-d') : '');
        }
        return $this->validar($respuestas);
    }

    /** Valida una fila por agrupador de vehículo o persona, no una por respuesta. */
    public function validar(array $respuestas): array
    {
        $codigos = ['V_1', 'VPY_01', 'P_1'];
        $preguntas = Pregunta::whereIn('codigo', $codigos)->get()->groupBy('codigo');
        foreach ($codigos as $codigo) {
            if (!isset($preguntas[$codigo]) || $preguntas[$codigo]->count() !== 1) {
                return [['pregunta_id' => null, 'grupo_pregunta_id' => null, 'mensaje' => 'No se puede validar ocupantes: falta o está duplicada la pregunta '.$codigo.' en el catálogo.']];
            }
        }
        $vehiculo = $preguntas['V_1']->first();
        $ocupantes = $preguntas['VPY_01']->first();
        $persona = $preguntas['P_1']->first();
        if ($vehiculo->grupo_de_preguntas_id !== $ocupantes->grupo_de_preguntas_id || $vehiculo->grupo_de_preguntas_id === $persona->grupo_de_preguntas_id) {
            return [['pregunta_id' => null, 'grupo_pregunta_id' => null, 'mensaje' => 'No se puede validar ocupantes: los grupos del catálogo no son válidos.']];
        }
        $idsGrupos = Pregunta::whereIn('grupo_de_preguntas_id', [$vehiculo->grupo_de_preguntas_id, $persona->grupo_de_preguntas_id])->pluck('grupo_de_preguntas_id', 'id')->all();
        $errores = [];
        $vehiculos = [];
        $personas = [];
        $error = function ($pregunta, $mensaje) use (&$errores) {
            $errores[] = ['pregunta_id' => $pregunta->id, 'grupo_pregunta_id' => $pregunta->grupo_de_preguntas_id, 'mensaje' => $mensaje];
        };
        $numero = function ($valor) {
            if (is_array($valor)) {
                if (count($valor) !== 1) return null;
                $valor = reset($valor);
            }
            if (!is_scalar($valor) || !preg_match('/^\d+(?:\.0+)?$/D', trim((string) $valor))) return null;
            $entero = explode('.', trim((string) $valor), 2)[0];
            return ltrim($entero, '0') ?: '0';
        };
        foreach ($respuestas as $grupo) {
            if (!is_array($grupo)) continue;
            $hayVehiculo = false;
            $hayPersona = false;
            foreach ($grupo as $id => $valor) {
                if (FormularioAccidente::valor($valor) === '') continue;
                if (($idsGrupos[$id] ?? null) == $vehiculo->grupo_de_preguntas_id) $hayVehiculo = true;
                if (($idsGrupos[$id] ?? null) == $persona->grupo_de_preguntas_id) $hayPersona = true;
            }
            if ($hayVehiculo) {
                $n = $numero($grupo[$vehiculo->id] ?? null);
                $cantidad = $numero($grupo[$ocupantes->id] ?? null);
                if ($n === null || $n === '0') $error($vehiculo, 'Indique un número válido para cada vehículo.');
                elseif (isset($vehiculos[$n])) $error($vehiculo, 'El número de vehículo '.$n.' está repetido.');
                elseif ($cantidad === null || $cantidad === '0') $error($ocupantes, 'Vehículo '.$n.': indique un número de ocupantes mayor que cero.');
                else $vehiculos[$n] = ['declarados' => $cantidad, 'personas' => 0];
            }
            if ($hayPersona) {
                $n = $numero($grupo[$persona->id] ?? null);
                if ($n === null) $error($persona, 'Indique el vehículo asociado a cada persona; use 0 cuando corresponda.');
                elseif ($n !== '0') $personas[] = $n;
            }
        }
        foreach ($personas as $n) {
            if (!isset($vehiculos[$n])) $error($persona, 'La persona está asociada al vehículo '.$n.', que no está registrado o no tiene una cantidad válida de ocupantes.');
            else $vehiculos[$n]['personas']++;
        }
        foreach ($vehiculos as $n => $datos) {
            if ($datos['declarados'] !== (string) $datos['personas']) {
                $error($ocupantes, 'Vehículo '.$n.': se declararon '.$datos['declarados'].' ocupantes y se registraron '.$datos['personas'].' '.($datos['personas'] === 1 ? 'persona' : 'personas').'. Las cantidades deben coincidir para guardar.');
            }
        }
        return $errores;
    }
}
