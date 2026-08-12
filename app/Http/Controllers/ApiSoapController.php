<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ApiSoapController extends Controller
{
    private $route_name = 'api_soap';

    private $params = [];

    public function __construct()
    {
        $this->params = [
            'title' => 'API SOAP',
            'subtitle' => 'RPC SOAP - WEB SERVICE',
            'panel_color' => 'panel-midnightblue',
            'route_name' => $this->route_name,
            'route' => '/'.$this->route_name,
            'data' => null,
        ];
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (! Auth::user()->ability('sysadmin', 'api_soap-view,api_soap-edit')) {
            return redirect('/home');
        }

        return view($this->route_name.'.index')->with('params', $this->params);
    }

    /**
     * Crea un nuevo API KEY para el acceso via Web Service
     */
    public function generateApiKey()
    {
        if (! Auth::user()->ability('sysadmin', 'api_soap-edit')) {
            return redirect()->back()
                ->withMessages(['type' => 'error', 'text' => 'No posee permisos para realizar esta acción!!']);
        }
        $hash = md5(time());
        Auth::user()->api_key = $hash;

        try {
            Auth::user()->save();
        } catch (QueryException $e) {
            return redirect()->back()
                ->withMessages(['type' => 'error', 'text' => 'No se pudo generar la nueva clave, por favor reintente más tarde!!']);
        }

        return redirect()->back()->withMessages(['type' => 'success', 'text' => 'Nuevo Token generado Exitosamente!!']);
    }
}
