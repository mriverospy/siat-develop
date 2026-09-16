<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FormularioAccidente
{
    public static function valor($value): string
    {
        return is_scalar($value) ? trim((string) $value) : (is_array($value) ? self::valor(reset($value)) : '');
    }

    public static function aplica(int $id, array $answers, array $vehicles): bool
    {
        $rule = config("formulario_accidente.condiciones.$id", []);
        $value = fn ($key) => self::valor($answers[$key] ?? '');
        if (isset($rule['sin']) && $value($rule['sin']) !== '') return false;
        if (isset($rule['roles']) && !in_array($value(135), $rule['roles'], true)) return false;
        if (isset($rule['pregunta']) && !in_array($value($rule['pregunta']), $rule['valores'], true)) return false;
        $vehicle = $vehicles[$value(125)] ?? '';
        // El catálogo existente permite 0 para ciclistas sin vehículo asociado.
        if ($value(135) === 'Ciclista' && $value(125) === '0') $vehicle = 'Bicicleta';
        return !isset($rule['vehiculos']) || in_array($vehicle, $rule['vehiculos'], true);
    }

    public function cabecera(array $data, bool $editing = false)
    {
        $vias = array_keys(config('formulario_accidente.vias'));
        if ($editing) $vias = array_merge($vias, ['calle', 'ruta', 'avenida', 'ramal', 'camino_vecinal', 'interseccion']);
        $rules = [
            'caratula' => 'required|string|max:255',
            'fecha_accidente' => 'required|date_format:Y-m-d H:i:s',
            'localidad_id' => 'required|exists:localidades,id',
            'lugar' => 'required|string|max:255',
            'tipo_camino' => ['required', Rule::in($vias)],
            'area' => ['required', Rule::in(array_keys(config('formulario_accidente.areas')))],
            'gps_latitud' => 'required|numeric|between:-90,90',
            'gps_longitud' => 'required|numeric|between:-180,180',
        ];
        if (!$editing) {
            $rules['departamento_id'] = 'required|exists:departamentos,id';
            $rules['distrito_id'] = ['required', Rule::exists('distritos', 'id')->where('departamento_id', $data['departamento_id'] ?? null)];
            $rules['localidad_id'] = ['required', Rule::exists('localidades', 'id')->where('distrito_id', $data['distrito_id'] ?? null)];
        }
        return Validator::make($data, $rules);
    }

    // Valida todos los grupos recibidos, conservando las exigencias del catálogo.
    public function respuestas(array &$answers, $questions): array
    {
        $errors = [];
        $questions = collect($questions)->keyBy('id');
        $vehicles = [];
        $error = function ($id, $message) use (&$errors, $questions) {
            $errors[] = ['pregunta_id' => $id, 'grupo_pregunta_id' => $questions[$id]->grupo_de_preguntas_id ?? null, 'mensaje' => $message];
        };
        foreach ($answers as $group) {
            if (!is_array($group)) { $error(null, 'Formato de respuestas inválido.'); continue; }
            if (array_key_exists(115, $group)) {
                $number = self::valor($group[115]);
                if ($number !== '' && isset($vehicles[$number])) $error(115, 'El número de vehículo no puede repetirse.');
                if ($number !== '') $vehicles[$number] = self::valor($group[118] ?? '');
            }
        }
        foreach ($answers as $key => &$group) {
            if (!is_array($group)) continue;
            $groupIds = [];
            foreach (array_keys($group) as $id) {
                if (!$questions->has($id)) { $error($id, 'Pregunta no permitida.'); continue; }
                $groupIds[] = $questions[$id]->grupo_de_preguntas_id;
            }
            foreach ($questions as $q) {
                if (!in_array($q->grupo_de_preguntas_id, $groupIds)) continue;
                $id = (int) $q->id;
                if (!self::aplica($id, $group, $vehicles)) { unset($group[$id]); continue; }
                $value = self::valor($group[$id] ?? '');
                $required = $q->obligatorio || in_array($id, config('formulario_accidente.obligatorias'));
                if ($id === 131 && self::valor($group[130] ?? '') === '') $required = true;
                if ($required && $value === '') $error($id, "Grupo $key: {$q->pregunta} es obligatorio.");
                if ($value === '') continue;
                $values = is_array($group[$id]) ? $group[$id] : [$group[$id]];
                if (count(array_filter($values, fn ($v) => !is_scalar($v))) || ($q->tipo !== 'check' && count($values) > 1)) {
                    $error($id, "{$q->pregunta}: formato de respuesta inválido.");
                    continue;
                }
                foreach ($values as $v) {
                    $v = self::valor($v);
                    if (mb_strlen($v) > 255) $error($id, "{$q->pregunta}: máximo 255 caracteres.");
                    if (in_array($q->tipo, ['radio','check']) && !$q->mostrar_otro && !in_array($v, explode('|', $q->opciones ?? ''), true)) $error($id, "{$q->pregunta}: opción inválida.");
                    if ($q->tipo === 'number' && !is_numeric($v)) $error($id, "{$q->pregunta}: ingrese un número.");
                    if ($q->tipo === 'date' && Validator::make(['date' => $v], ['date' => 'date_format:Y-m-d'])->fails()) $error($id, "{$q->pregunta}: fecha inválida.");
                }
                if (in_array($id, [115,116,123,125,131]) && (!ctype_digit($value) || ($id === 115 && (int)$value < 1))) $error($id, "{$q->pregunta}: ingrese un entero válido.");
                if ($id === 125) {
                    $role = self::valor($group[135] ?? '');
                    $withoutVehicle = $value === '0' && in_array($role, ['Peatón', 'Ciclista'], true);
                    if (!$withoutVehicle && !isset($vehicles[$value])) {
                        $available = implode(', ', array_keys($vehicles)) ?: 'ninguno';
                        $error($id, "Grupo $key: vehículo '$value' inválido para el rol '$role'. Vehículos registrados: $available. Seleccione un número registrado; 0 solo se permite para Peatón o Ciclista.");
                    }
                }
            }
        }
        unset($group);
        // Evita omitir por completo un capítulo obligatorio.
        foreach ($questions->groupBy('grupo_de_preguntas_id') as $groupQuestions) {
            if (!$groupQuestions->contains(fn ($q) => (bool)$q->obligatorio)) continue;
            $ids = $groupQuestions->pluck('id')->all();
            $present = false;
            foreach ($answers as $group) if (is_array($group) && array_intersect(array_keys($group), $ids)) $present = true;
            if (!$present) $error($ids[0], 'Falta completar el grupo: '.$groupQuestions->first()->grupo_de_preguntas_id);
        }
        return $errors;
    }
}
