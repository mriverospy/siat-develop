<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestUtils;
use App\Http\Requests\UserProfileRequest;
use App\Http\Requests\UsuarioRequest;
use App\Models\Rol;
use App\Models\Sede;
use App\Models\Usuario;
use Auth;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use League\Csv\Writer;

class UsuariosController extends Controller
{
    /**
     * Nombre de la ruta
     *
     * @var string
     */
    private $route_name = 'usuarios';

    /**
     * Parámetros
     *
     * @var array
     */
    private $params = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->params = [
            'title' => 'USUARIOS',
            'subtitle' => 'LISTADO DE USUARIOS',
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
     * @return Response
     */
    public function index(Request $request)
    {
        if (! RequestUtils::havePermission($this->route_name, 'any')) {
            return redirect('/home');
        }
        $this->params['filter'] = $request->input('filter');
        $this->params['data']['items'] = Usuario::with(['roles', 'sede', 'sede.institucion'])->orderBy('apellidos')->orderBy('nombres')->filteredPaginate($this->params['filter'], $this->params['items_per_page']);

        return view($this->route_name.'.index')->with('params', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->params['subtitle'] = 'NUEVO USUARIO';
        $this->params['data']['patern_list'] = Sede::sedeList()->pluck('nombres', 'id');
        $this->params['data']['rol_list'] = Rol::rolList()->pluck('display_name', 'id');

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(UsuarioRequest $request)
    {
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['sede_id'] = RequestUtils::isSysAdmin() ? $data['sede_id'] : Auth::user()->sede->id;

        try {
            // Forzar que todos los usuarios nuevos cambien su contraseña por defecto
            $data['cambiar_password'] = true;
            $usuario = Usuario::create($data);
            $usuario->attachRole($data['rol_id']);
        } catch (QueryException $e) {
            return redirect($this->route_name)
                ->withMessages([
                    'type' => 'error',
                    'text' => 'No se pudo crear el nuevo Usuario! <br />Error:'.$e->getMessage(),
                ]);
        }

        return redirect($this->route_name)
                ->withMessages(['type' => 'success', 'text' => 'Nuevo Usuario creado exitosamente!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return redirect($this->route_name);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $this->params['subtitle'] = 'EDITAR USUARIO';
        $this->params['data']['patern_list'] = Sede::sedeList()->pluck('nombres', 'id');
        $this->params['data']['item'] = $user = Usuario::with('roles')->findOrFail($id);
        $this->params['data']['current_rol'] = null;
        if ($user->roles()->count()) {
            $this->params['data']['current_rol'] = $user->roles()->first()->id;
        }
        $this->params['data']['rol_list'] = Rol::rolList()->pluck('display_name', 'id');

        return view($this->route_name.'.edit')->with('params', $this->params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, UsuarioRequest $request)
    {
        $row = Usuario::findOrFail($id);

        $rol = Rol::findOrFail($request->rol_id);

        $data = $request->all();

        //SETEA ACTIVO
        $data['activo'] = ($request->activo === 'yes') ? 1 : 0;

        // GUARDA LOS CAMBIOS DEL USUARIO
        try {
            $row->update($data);
        } catch (QueryException $e) {
            return redirect($this->route_name)
                ->withMessages(['type' => 'error', 'text' => 'Las modificaciones no fueron guardadas!!!']);
        }

        // GUARDA EL CAMBIO DE ROL SI LO HUBIERA
        if (! $row->hasRole($rol)) {
            try {
                $row->detachRoles($row->roles);
                $row->attachRole($rol);
            } catch (QueryException $e) {
                return redirect($this->route_name)
                    ->withMessages(['type' => 'error', 'text' => 'No se pudo actualizar el Rol de Usuario']);
            }
        }

        return redirect($this->route_name)
                ->withMessages(['type' => 'success', 'text' => 'Registro actualizado con éxito!!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $row = Usuario::findOrFail($id);

        try {
            $row->delete();
        } catch (QueryException $e) {
            return redirect($this->route_name)
                ->withMessages(['type' => 'error', 'text' => 'El registro seleccionado no puede ser eliminado!!!']);
        }

        return redirect($this->route_name)
                ->withMessages(['type' => 'success', 'text' => 'Registro eliminado exitosamente!!!']);
    }

    public function profile()
    {
        $this->params['title'] = 'MI PERFIL';

        $this->params['subtitle'] = Auth::user()->nombreCompleto();

        $this->params['data']['item'] = Auth::user();

        return view($this->route_name.'.profile')->with('params', $this->params);
    }

    public function profileUpdate(UserProfileRequest $request)
    {
        $data = $request->only(['nombres', 'apellidos', 'email', 'documento', 'genero', 'direccion', 'telefonos']);

        if ($request->has('password')) {
            $data['password'] = Hash::make($request->input('password'));
            $data['cambiar_password'] = false;
        }

        Auth::user()->update($data);

        return redirect()->route('user.profile')->withMessages(['type' => 'success', 'text' => 'Sus datos se han actualizado correctamente']);
    }

    public function changePass($id, UsuarioRequest $request)
    {
        $row = Usuario::findOrFail($id);

        $route = $this->route_name.'/'.$id.'/edit';

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        if ($request->has('cambiar_password') and (int) $request->get('cambiar_password') == 1) {
            $data['cambiar_password'] = true;
        }

        try {
            $row->update($data);
        } catch (QueryException $e) {
            return redirect($route)
                ->withMessages(['type' => 'error', 'text' => 'No se pudo cambiar la Contraseña']);
        }

        return redirect($route)
                ->withMessages(['type' => 'success', 'text' => 'Contraseña modificada exitosamente!!!']);
    }

    public function csv()
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'list')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $csvWriter = Writer::createFromFileObject(new \SplTempFileObject());
        $csvWriter->setDelimiter(',');
        $csvWriter->setNewline("\r\n"); // use windows line endings
        $csvWriter->setOutputBOM(Writer::BOM_UTF8); //adding the BOM sequence on output

        // Insertar fila de encabezados
        $csvWriter->insertOne([
            'Institucion',
            'Sede',
            'Usuario',
            'Email',
            'Apellidos',
            'Nombres',
            'NumeroDocumento',
            'Telefonos',
            'Activo?',
        ]);

        $usuarios = Usuario::with(['sede', 'sede.institucion'])
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        foreach ($usuarios as $usuario) {
            $csvWriter->insertOne([
                $usuario->sede->institucion->nombre ?? 'Valor Inválido',
                $usuario->sede->nombre ?? 'Valor Inválido',
                $usuario->username ?? 'Valor Inválido',
                $usuario->email ?? 'Valor Inválido',
                $usuario->apellidos ?? 'Valor Inválido',
                $usuario->nombres ?? 'Valor Inválido',
                $usuario->documento ?? 'Valor Inválido',
                $usuario->telefonos ?? 'Valor Inválido',
                ($usuario->activo === true) ? 'Si' : ($usuario->activo === false ? 'No' : 'Valor Inválido'),
            ]);
        }

        // Obtener el contenido del CSV completo
        $csvContent = $csvWriter->__toString();

        return response($csvContent, 200)
            ->header('Content-Disposition', 'attachment;filename=usuarios_siat_' . date('YmdHis') . '.csv')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Content-type', 'text/csv')
            ->header('Expires', '0')
            ->header('Pragma', 'public');
    }


    public function AuthRouteAPI(Request $request)
    {
        return $request->user();
    }
}
