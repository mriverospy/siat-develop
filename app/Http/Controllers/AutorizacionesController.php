<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestUtils;
use App\Models\Autorizacion;
use App\Models\Pregunta;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AutorizacionesController extends Controller
{
    private $route_name = 'autorizaciones';

    private $params = [];

    public function __construct()
    {
        $this->params = [
            'title' => 'AUTORIZACIONES',
            'subtitle' => 'LISTADO DE AUTORIZACIONES',
            'panel_color' => 'panel-midnightblue',
            'search' => 'NO',
            'filter' => '',
            'items_per_page' => 10,
            'route_name' => $this->route_name,
            'route' => '/'.$this->route_name,
            'data' => null,
        ];
        parent::__construct();
    }

    /**
     * Lista derivaciones enviadas
     *
     * @return \Illuminate\Http\Response
     */
    public function sent()
    {
        // Verifica permiso
        if (! RequestUtils::havePermission('accidentes', 'send-authorization')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'Disculpe, no cuenta con el permiso de emitir autorizaciones.']);
        }

        $this->params['title'] = 'AUTORIZACIONES ENVIADAS';
        $this->params['subtitle'] = 'LISTADO DE AUTORIZACIONES ENVIADAS';
        $this->params['data']['items'] = Auth::user()->autorizaciones_enviadas()->filteredPaginate($this->params['filter'], $this->params['items_per_page']);

        return view($this->route_name.'.sent')->with('params', $this->params);
    }

    /**
     * Lista derivaciones recibidas
     *
     * @return \Illuminate\Http\Response
     */
    public function received()
    {
        // Verifica permiso
        if (! RequestUtils::havePermission('accidentes', 'receive-authorization')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'Disculpe, no cuenta con el permiso de recibir autorizaciones.']);
        }

        $this->params['title'] = 'AUTORIZACIONES RECIBIDAS';
        $this->params['subtitle'] = 'LISTADO DE AUTORIZACIONES RECIBIDAS';
        $this->params['data']['items'] = Auth::user()->autorizaciones_recibidas()->filteredPaginate($this->params['filter'], $this->params['items_per_page']);

        return view($this->route_name.'.received')->with('params', $this->params);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        try {
            $autorizacion = Autorizacion::with([
                'accidente',
                'accidente.localidad',
                'accidente.localidad.distrito',
                'accidente.localidad.distrito.departamento',
                'accidente.respuestas.pregunta',
                'usuario_emisor',
                'usuario_emisor.sede',
                'usuario_emisor.sede.institucion',
                'usuario_receptor',
                'usuario_receptor.sede',
                'usuario_receptor.sede.institucion',
            ])
                ->where('id', $id)
                ->where(function ($q) {
                    $q->where('otorga_usuario_id', Auth::user()->id)
                        ->orWhere('recibe_usuario_id', Auth::user()->id);
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('autorizaciones.received')
                ->withMessages(['type' => 'error', 'text' => 'No se encontró la Autorización seleccionada.']);
        }

        $camposCabecera = [
            'caratula' => 'Carátula',
            'expediente_judicial' => 'Expediente Judicial',
            'sumario_policial' => 'Sumario Policial',
            'otro' => 'Otro',
            'dependencia' => 'Dependencia',
            'fecha_accidente' => 'Fecha del Accidente',
            'lugar' => 'Lugar',
            'altura' => 'Altura',
            'tipo_camino' => 'Tipo de Camino',
            'localidad_id' => 'Localidad',
            'codigo_postal' => 'Código Postal',
            'ubicacion' => 'Ubicación GPS',
            'heridos' => 'Cantidad de Heridos',
            'fallecidos' => 'Cantidad de Fallecidos',
            'ilesos' => 'Cantidad de Ilesos',
        ];

        // Obtener IDs de Preguntas y Campos Cabecera
        $preguntaIds = $camposEditables = [];
        foreach ($autorizacion->campos_autorizados as $campo) {
            if (stristr($campo, 'pregunta_') !== false) {
                $preguntaIds[] = (int) str_replace('pregunta_', '', $campo);
            }

            if (in_array($campo, array_keys($camposCabecera))) {
                $camposEditables[$campo] = $camposCabecera[$campo];
            }
        }

        // Obtener preguntas
        $camposPreguntas = [];
        if (count($preguntaIds) > 0) {
            $preguntas = Pregunta::with('grupoDePreguntas')->whereIn('id', $preguntaIds)->get();
            foreach ($preguntas as $pregunta) {
                $camposPreguntas[] = [
                    'id' => $pregunta->id,
                    'titulo' => $pregunta->grupoDePreguntas->nombre.' / '.$pregunta->pregunta,
                ];
            }
        }

        return view($this->route_name.'.show', compact('autorizacion', 'camposEditables', 'camposPreguntas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // do nothing for now
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $autorizacion = Autorizacion::where('id', $id)
                ->where(function ($q) {
                    $q->where('otorga_usuario_id', Auth::user()->id)
                        ->orWhere('recibe_usuario_id', Auth::user()->id);
                })
                ->firstOrFail();
            $autorizacion->delete();
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('accidentes.index')
                ->withMessages(['type' => 'error', 'text' => 'Error al borrar la autorización seleccionada.']);
        }

        if ($autorizacion->estado == 'recibido') {
            return redirect()
                ->route('accidentes.index')
                ->withMessages(['type' => 'error', 'text' => 'La autorización seleccionada ya se ha recibido y no se puede borrar.']);
        }

        try {
            $autorizacion->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('accidentes.index')
                ->withMessages(['type' => 'error', 'text' => 'No se pudo borrar la autorización seleccionada.']);
        }

        return redirect()
                ->route('accidentes.index')
                ->withMessages(['type' => 'success', 'text' => 'Autorización eliminada correctamente.']);
    }
}
