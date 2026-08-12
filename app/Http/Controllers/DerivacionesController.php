<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestUtils;
use App\Models\Accidente;
use App\Models\Derivacion;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class DerivacionesController extends Controller
{
    private $route_name = 'derivaciones';

    private $params = [];

    public function __construct()
    {
        $this->params = [
            'title' => 'DERIVACIONES',
            'subtitle' => 'LISTADO DE DERIVACIONES',
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
        if (! RequestUtils::havePermission('accidentes', 'send')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $this->params['title'] = 'DERIVACIONES ENVIADAS';
        $this->params['subtitle'] = 'LISTADO DE DERIVACIONES ENVIADAS';
        $this->params['data']['items'] = Auth::user()->derivaciones_enviadas()->filteredPaginate($this->params['filter'], $this->params['items_per_page']);

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
        if (! RequestUtils::havePermission('accidentes', 'receive')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $this->params['title'] = 'DERIVACIONES RECIBIDAS';
        $this->params['subtitle'] = 'LISTADO DE DERIVACIONES RECIBIDAS';
        $this->params['data']['items'] = Derivacion::where('deriva_a_institucion_id', Auth::user()->sede->institucion_id)->filteredPaginate($this->params['filter'], $this->params['items_per_page']);

        return view($this->route_name.'.received')->with('params', $this->params);
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
        try {
            $derivacion = Derivacion::where('id', $id)->where('deriva_a_institucion_id', Auth::user()->sede->institucion_id)->first();
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('derivaciones.received')
                ->withMessages(['type' => 'error', 'text' => 'No se encontró la derivación seleccionada.']);
        }

        if ($derivacion->estado == 'recibido') {
            return redirect()
                ->route('derivaciones.received')
                ->withMessages(['type' => 'error', 'text' => 'La derivación seleccionada ya se ha aceptado.']);
        }

        try {
            $accidente = Accidente::where('id', $derivacion->accidente_id)->first();
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('derivaciones.received')
                ->withMessages(['type' => 'error', 'text' => 'El accidente asociado a la derivación no se ha encontrado.']);
        }

        // Actualizar Derivación
        $derivacion->deriva_a_institucion_id = Auth::user()->sede->institucion_id;
        $derivacion->deriva_a_sede_id = Auth::user()->sede_id;
        $derivacion->deriva_a_usuario_id = Auth::user()->id;
        $derivacion->save();

        // Actualizar accidente
        $accidente->usuario_id = $derivacion->deriva_a_usuario_id;
        $accidente->sede_id = $derivacion->deriva_a_sede_id;
        $accidente->institucion_id = $derivacion->deriva_a_institucion_id;
        $accidente->save();

        // Marcar derivación como recibido
        $derivacion->estado = 'recibido';
        $derivacion->save();

        // Redireccionar
        return redirect()
            ->route('derivaciones.received')
            ->withMessages(['type' => 'success', 'text' => 'La derivación se ha aceptado correctamente.']);
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
            $derivacion = Derivacion::where('id', $id)
                ->orWhere([
                    'usuario_id' => Auth::user()->id,
                    'deriva_a_usuario_id' => Auth::user()->id,
                ])
                ->first();
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('accidentes.index')
                ->withMessages(['type' => 'error', 'text' => 'Error al borrar la derivación seleccionada.']);
        }

        if ($derivacion->estado == 'recibido') {
            return redirect()
                ->route('accidentes.index')
                ->withMessages(['type' => 'error', 'text' => 'La derivación seleccionada ya se ha recibido y no se puede borrar.']);
        }

        try {
            $derivacion->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('accidentes.index')
                ->withMessages(['type' => 'error', 'text' => 'No se pudo borrar la derivación seleccionada.']);
        }

        return redirect()
                ->route('accidentes.index')
                ->withMessages(['type' => 'success', 'text' => 'Derivación eliminada correctamente.']);
    }
}
