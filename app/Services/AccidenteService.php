<?php

namespace App\Services;

use App\Models\Accidente;
use App\Models\GrupoDePreguntas;
use App\Models\Respuesta;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Settings;

class AccidenteService
{
    private $user = null;

    /**
     * Service Constructor
     */
    public function __construct()
    {
        //
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function calcularFechaCierre($fechaAccidente)
    {
        $fechaInicio = Carbon::parse($fechaAccidente);

        $fechaFin = $fechaInicio->copy();

        $dias_edicion = Settings::get('dias_edicion');

        $institucion = $this->user->sede->institucion;

        if (! is_null($institucion->dias_edicion)) {
            $dias_edicion = $institucion->dias_edicion;
        }

        $diasAgregados = 0;
        while ($diasAgregados < $dias_edicion) {
            $fechaFin->addDays(1);
            if (! $fechaFin->isWeekend()) {
                $diasAgregados++;
            }
        }

        return $fechaFin->format('Y-m-d H:i:s');
    }

    public function puedeEditar($accidenteId)
    {
        try {
            $accidente = Accidente::findOrFail($accidenteId);
        } catch (ModelNotFoundException $e) {
            return [
                'puedeEditar' => false,
                'camposAutorizados' => [],
                'mensaje' => 'El ID de Accidente no existe',
            ];
        }

        // si el accidente está abierto
        if ($accidente->estado == 'abierto') {
            // verificar que accidente corresponde a la institución
            if ($accidente->institucion_id == $this->user->sede->institucion_id) {
                return [
                    'puedeEditar' => true,
                    'mensaje' => '',
                ];
            } else {
                // si no corresponde a la institución, denegar edición
                return [
                    'puedeEditar' => false,
                    'mensaje' => 'El accidente no puede ser editado porque pertenece a otra Institución',
                ];
            }
        } else {
            // si el accidente está cerrado, no se puede editar
            return [
                'puedeEditar' => false,
                'mensaje' => 'El Accidente no se puede editar porque está cerrado, solicite una autorización de cambio para hacer ediciones',
            ];
        }
    }

    // actualizar los datos de un accidente
    public function actualizarAccidente($accidenteId, $accidente, $ediciones)
    {
        // todas las operaciones se hacen dentro de una transaccion grande
        // si salta un error todo se cancela
        DB::transaction(function () use ($accidenteId, $accidente, $ediciones) {
            // Actualizar campos del accidente
            Accidente::where('id', $accidenteId)
                ->update($accidente);

            // iterar sobre todas las ediciones
            if($ediciones != null){
                foreach ($ediciones as $edicion) {
                    // agregar respuesta
                    if ($edicion['modoEdicion'] == 'agregar') {
                        if (is_array($edicion['respuestaNueva'])) {
                            // agregar las nuevas respuestas
                            foreach ($edicion['respuestaNueva'] as $nuevaRespuesta) {
                                Respuesta::create([
                                    'accidente_id' => $accidenteId,
                                    'pregunta_id' => $edicion['preguntaId'],
                                    'usuario_id' => $this->user->id,
                                    'agrupador' => $edicion['agrupador'],
                                    'pregunta_texto' => $edicion['pregunta'],
                                    'pregunta_tipo' => $edicion['tipoPregunta'],
                                    'respuesta_texto' => $nuevaRespuesta,
                                ]);
                            }
                        } else {
                            $campo = 'respuesta_texto';

                            if ($edicion['tipoPregunta'] == 'number') {
                                $campo = 'respuesta_numero';
                            }

                            if ($edicion['tipoPregunta'] == 'date') {
                                $campo = 'respuesta_fecha';
                            }

                            Respuesta::create([
                                'accidente_id' => $accidenteId,
                                'pregunta_id' => $edicion['preguntaId'],
                                'usuario_id' => $this->user->id,
                                'agrupador' => $edicion['agrupador'],
                                'pregunta_texto' => $edicion['pregunta'],
                                'pregunta_tipo' => $edicion['tipoPregunta'],
                                $campo => $edicion['respuestaNueva'],
                            ]);
                        }
                    }

                    // modificar respuesta
                    if ($edicion['modoEdicion'] == 'modificar') {
                        if (is_array($edicion['respuestaNueva'])) {
                            // si son varias respuestas, borrar las existentes y agregar las nuevas
                            Respuesta::where('accidente_id', $accidenteId)
                                ->where('pregunta_id', $edicion['preguntaId'])
                                ->delete();

                            // agregar las nuevas respuestas
                            foreach ($edicion['respuestaNueva'] as $nuevaRespuesta) {
                                Respuesta::create([
                                    'accidente_id' => $accidenteId,
                                    'pregunta_id' => $edicion['preguntaId'],
                                    'usuario_id' => $this->user->id,
                                    'agrupador' => $edicion['agrupador'],
                                    'pregunta_texto' => $edicion['pregunta'],
                                    'pregunta_tipo' => $edicion['tipoPregunta'],
                                    'respuesta_texto' => $nuevaRespuesta,
                                ]);
                            }
                        } else {
                            $campo = 'respuesta_texto';

                            if ($edicion['tipoPregunta'] == 'number') {
                                $campo = 'respuesta_numero';
                            }

                            if ($edicion['tipoPregunta'] == 'date') {
                                $campo = 'respuesta_fecha';
                            }

                            Respuesta::where('accidente_id', $accidenteId)
                                ->where('pregunta_id', $edicion['preguntaId'])
                                ->where('agrupador', $edicion['agrupador'])
                                ->update([
                                    $campo => $edicion['respuestaNueva'],
                                ]);
                        }
                    }

                    // borrar respuesta
                    if ($edicion['modoEdicion'] == 'borrar') {
                        Respuesta::where('accidente_id', $accidenteId)
                            ->where('pregunta_id', $edicion['preguntaId'])
                            ->where('agrupador', $edicion['agrupador'])
                            ->delete();
                    }
                }
            }

        });

        return [
            'mensaje' => 'Los datos del accidente se han guardado correctamente',
        ];
    }

    public function obtenerGruposPreguntas()
    {
        // Obtener grupos y preguntas masters
        $masters = GrupoDePreguntas::where('master', '=', 1)
                    ->with([
                        'preguntas' => function ($query) {
                            $query->orderBy('orden', 'asc');
                        },
                    ])
                    ->get();

        // Obtener grupos y preguntas de la institución del usuario
        $this->user->load('sede.institucion');
        $institucionales = $this->user->sede->institucion->grupoDePreguntas()
                            ->with([
                                'preguntas' => function ($query) {
                                    $query->orderBy('orden', 'asc');
                                },
                            ])
                            ->where('master', '=', 0)
                            ->orderBy('orden')
                            ->get();

        // Fusionar grupos master con grupos de la institución
        $grupoPreguntas = $masters->merge($institucionales);

        // Preparar los grupos de preguntas con sus preguntas
        $grupos = [];
        $agrupador = 0;
        foreach ($grupoPreguntas as $grupo) {
            // Preparamos las preguntas que van dentro de cada grupo de pregunta
            $preguntas = [];
            foreach ($grupo->preguntas as $pregunta) {
                $preguntas[] = [
                    // 'index' => $index,
                    'grupo_id' => $grupo->id,
                    'grupo_nombre' => $grupo->nombre,
                    'grupo_descripcion' => $grupo->descripcion,
                    'grupo_master' => ((int) $grupo->master == 1),
                    'grupo_duplicable' => ((int) $grupo->puede_duplicar == 1),
                    'grupo_orden' => $grupo->orden,
                    'pregunta_id' => $pregunta->id,
                    'pregunta' => $pregunta->pregunta,
                    'pregunta_orden' => (int) $pregunta->orden,
                    'tipo' => $pregunta->tipo,
                    'opciones' => explode('|', $pregunta->opciones),
                    'instrucciones' => $pregunta->instrucciones,
                    'mostrar_otro' => ((int) $pregunta->mostrar_otro == 1),
                    'titulo_otro' => $pregunta->titulo_otro,
                ];
            }

            // Asignamos las preguntos y el grupo
            $grupos[$grupo->id] = [
                'id' => $grupo->id,
                'agrupador' => (((int) $grupo->puede_duplicar == 1) ? $agrupador++ : 0),
                'grupo_nombre' => $grupo->nombre,
                'tipo' => 'grupo',
                'duplicable' => ((int) $grupo->puede_duplicar == 1),
                'preguntas' => $preguntas,
            ];
        }

        return $grupos;
    }

    public function obtenerAgrupadores($accidenteId)
    {
        $agrupadores = [];

        $rows = DB::query()
            ->select(['grupo_de_preguntas_id', 'grupos_de_preguntas.nombre', 'agrupador'])
            ->distinct()
            ->from('respuestas')
            ->join('preguntas', 'preguntas.id', '=', 'respuestas.pregunta_id', 'left')
            ->join('grupos_de_preguntas', 'grupos_de_preguntas.id', '=', 'preguntas.grupo_de_preguntas_id', 'left')
            ->where('accidente_id', $accidenteId)
            ->where('puede_duplicar', true)
            ->orderBy('grupo_de_preguntas_id')
            ->orderBy('agrupador')
            ->get();

        $index = 0;
        $last = null;
        foreach ($rows as $row) {
            if ($last != $row->grupo_de_preguntas_id) {
                $index = 0;
            }

            $index++;

            $agrupadores[$row->grupo_de_preguntas_id][] = [
                'titulo' => $row->nombre.' - '.$index,
                'agrupador' => $row->agrupador,
            ];

            $last = $row->grupo_de_preguntas_id;
        }

        return $agrupadores;
    }

    public function obtenerRespuestas($accidenteId)
    {
        $respuestas = [];

        $rows = DB::query()
            ->select(['pregunta_id', 'agrupador', 'pregunta_tipo', 'respuesta_texto', 'respuesta_numero', 'respuesta_fecha'])
            ->from('respuestas')
            ->where('accidente_id', $accidenteId)
            ->orderBy('pregunta_id')
            ->orderBy('agrupador')
            ->get();

        foreach ($rows as $row) {
            $key = $row->pregunta_id.'-'.$row->agrupador;

            if (! isset($respuestas[$key])) {
                $respuestas[$key] = [
                    'pregunta_id' => $row->pregunta_id,
                    'agrupador' => $row->agrupador,
                    'pregunta_tipo' => $row->pregunta_tipo,
                    'respuestas' => [],
                ];
            }

            switch ($row->pregunta_tipo) {
                case 'number':
                    $respuestas[$key]['respuestas'] = $row->respuesta_numero;
                    break;

                case 'date':
                    $respuestas[$key]['respuestas'] = Carbon::parse($row->respuesta_fecha)->format('Y-m-d');
                    break;

                case 'check':
                    $respuestas[$key]['respuestas'][] = $row->respuesta_texto;
                    break;

                case 'radio':
                case 'text':
                default:
                    $respuestas[$key]['respuestas'] = $row->respuesta_texto;
                    break;
            }
        }

        return $respuestas;
    }
}
