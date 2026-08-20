<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class NotificacionesController extends Controller
{
    private $route_name = 'notificaciones';

    private $params = [];

    public function __construct()
    {
        $this->params = [
            'title' => 'NOTIFICACIONES',
            'subtitle' => 'LISTADO DE NOTIFICACIONES',
            'panel_color' => 'panel-midnightblue',
            'search' => 'YES',
            'filter' => '',
            'items_per_page' => 10,
            'route_name' => $this->route_name,
            'route' => '/'.$this->route_name,
            'data' => null,
        ];
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->params['filter'] = $request->input('filter');
        $this->params['data']['items'] = Auth::user()->notificaciones()->orderBy('fecha', 'desc')->orderBy('id', 'desc')->filteredPaginate($this->params['filter'], $this->params['items_per_page']);

        return view($this->route_name.'.index')->with('params', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return redirect()->route('notificaciones.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return redirect()->route('notificaciones.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $this->params['item'] = Auth::user()->notificaciones()->where('id', $id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('notificaciones.index')
                ->withMessages(['type' => 'error', 'text' => 'No se encontró un mensaje con el ID especificado.']);
        }

        if (! $this->params['item']->leido) {
            $this->params['item']->leido = true;
            $this->params['item']->save();
        }

        $this->params['title'] = 'NOTIFICACIÓN';

        return view($this->route_name.'.show')->with('params', $this->params);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return redirect()->route('notificaciones.index');
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
        return redirect()->route('notificaciones.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return redirect()->route('notificaciones.index');
    }

    public function flagAllAsRead()
    {
        Auth::user()->notificaciones()->noleidos()->update(['leido' => true]);

        return redirect()->route('notificaciones.index')->withMessages(['type' => 'success', 'text' => 'Se marcaron todos los mensajes como leídos']);
    }
}
