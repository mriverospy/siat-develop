<?php

namespace App\Http\Controllers;

use App\Settings\GeneralSettings;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Storage;

class ConfiguracionController extends Controller
{
    private $route_name = 'configuracion';

    private $params = [];

    public function __construct()
    {
        $this->params = [
            'title' => 'CONFIGURACIÓN',
            'subtitle' => 'CONFIGURACIÓN DEL SIAT',
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(GeneralSettings $generalSettings)
    {
        if (! Auth::user()->hasRole('sysadmin')) {
            return redirect('/')
                    ->withMessages(['type' => 'error', 'text' => 'No tiene los permisos para acceder a esta sección.']);
        }

        $settings = [
            'formato_formulario_unico' => $generalSettings->formato_formulario_unico,
            'dias_edicion' => $generalSettings->dias_edicion,
            'logo_membrete' => $generalSettings->logo_membrete,
            'logo_login' => $generalSettings->logo_login,
            'logo_principal' => $generalSettings->logo_principal,
            'logo_cabecera' => $generalSettings->logo_cabecera,
        ];

        return view($this->route_name.'.show', compact('settings'))->with('params', $this->params);
    }

    public function save(Request $request, GeneralSettings $generalSettings)
    {
        if (! Auth::user()->hasRole('sysadmin')) {
            return redirect('/')
                    ->withMessages(['type' => 'error', 'text' => 'No tiene los permisos para acceder a esta sección.']);
        }
        $generalSettings->formato_formulario_unico = $request->get('formato_formulario_unico');

        $generalSettings->dias_edicion = (int) $request->get('dias_edicion');

        // Verificar si se seleccionó un logo
        if ($request->hasFile('logo_membrete')) {
            $targetPath = public_path().'/logos';
            $baseFileName = Str::random(30);
            $targetFileName = 'logo_membrete-'.$baseFileName.'.'.$request->file('logo_membrete')->getClientOriginalExtension();

            Storage::makeDirectory($targetPath);

            $request->file('logo_membrete')->move($targetPath, $targetFileName);

            $generalSettings->logo_membrete = str_replace(public_path(), '', $targetPath).'/'.$targetFileName;
        }

        // Verificar si se seleccionó un logo para el login
        if ($request->hasFile('logo_login')) {
            $targetPath = public_path().'/logos';
            $baseFileName = Str::random(30);
            $targetFileName = 'logo_login-'.$baseFileName.'.'.$request->file('logo_login')->getClientOriginalExtension();

            Storage::makeDirectory($targetPath);

            $request->file('logo_login')->move($targetPath, $targetFileName);

            $generalSettings->logo_login = str_replace(public_path(), '', $targetPath).'/'.$targetFileName;
        }

        // Verificar si se seleccionó un logo principal
        if ($request->hasFile('logo_principal')) {
            $targetPath = public_path().'/logos';
            $baseFileName = Str::random(30);
            $targetFileName = 'logo_principal-'.$baseFileName.'.'.$request->file('logo_principal')->getClientOriginalExtension();

            Storage::makeDirectory($targetPath);

            $request->file('logo_principal')->move($targetPath, $targetFileName);

            $generalSettings->logo_principal = str_replace(public_path(), '', $targetPath).'/'.$targetFileName;
        }

        // Verificar si se seleccionó un logo para la cabecera
        if ($request->hasFile('logo_cabecera')) {
            $targetPath = public_path().'/logos';
            $baseFileName = Str::random(30);
            $targetFileName = 'logo_cabecera-'.$baseFileName.'.'.$request->file('logo_cabecera')->getClientOriginalExtension();

            Storage::makeDirectory($targetPath);

            $request->file('logo_cabecera')->move($targetPath, $targetFileName);

            $generalSettings->logo_cabecera = str_replace(public_path(), '', $targetPath).'/'.$targetFileName;
        }
        $generalSettings->save();

        return redirect()
                ->route('configuracion.show')
                ->withMessages(['type' => 'success', 'text' => 'Los cambios se guardaron correctamente.']);
    }
}
