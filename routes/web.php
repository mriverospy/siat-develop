<?php

use App\Http\Controllers\AccidentesController;
use App\Http\Controllers\ApiSoapController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AutorizacionesController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DepartamentosController;
use App\Http\Controllers\DerivacionesController;
use App\Http\Controllers\DistritosController;
use App\Http\Controllers\EstadosController;
use App\Http\Controllers\GruposDePreguntasController;
use App\Http\Controllers\GruposPorInstitucionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstitucionesController;
use App\Http\Controllers\LocalidadesController;
use App\Http\Controllers\NotificacionesController;
use App\Http\Controllers\PermisosController;
use App\Http\Controllers\PreguntasController;
use App\Http\Controllers\ReporteDatosController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\ReportesGenericosController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SedesController;
use App\Http\Controllers\SystemStatusController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('system-status', [SystemStatusController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::get('mi-perfil', [UsuariosController::class, 'profile'])->name('user.profile');
    Route::patch('mi-perfil', [UsuariosController::class, 'profileUpdate'])->name('user.profile.update');

    Route::middleware('password-check')->group(function () {
        Route::get('/', [WelcomeController::class, 'index']);
        Route::get('/home', [HomeController::class, 'index']);

        // Mantenimiento
        Route::resource('instituciones', InstitucionesController::class);
        Route::resource('sedes', SedesController::class);

        Route::resource('departamentos', DepartamentosController::class);
        Route::resource('distritos', DistritosController::class);
        Route::resource('localidades', LocalidadesController::class);

        Route::get('usuarios/descargar_csv', [UsuariosController::class, 'csv']);
        Route::resource('usuarios', UsuariosController::class);
        Route::post('usuarios/{usuarios}/changepass', [UsuariosController::class, 'changePass']);

        // Roles y Permisos
        Route::resource('roles', RolesController::class);
        Route::get('roles/{roles}/permisos/', [PermisosController::class, 'index']);
        Route::post('roles/{roles}/permisos/save_permisos', [PermisosController::class, 'save_permisos']);

        // Grupo de Preguntas
        Route::resource('grupos', GruposDePreguntasController::class);
        Route::get('grupos/{grupos}/set_master', [GruposDePreguntasController::class, 'set_master']);
        Route::get('grupos/{grupos}/unset_master', [GruposDePreguntasController::class, 'unset_master']);
        Route::post('grupos/save_masters_order', [GruposDePreguntasController::class, 'save_masters_order']);

        Route::resource('grupos.preguntas', PreguntasController::class);
        Route::get('grupos/{grupos}/preguntas/create/{tipos}', [PreguntasController::class, 'create']);
        Route::post('grupos/{grupos}/save_order', [PreguntasController::class, 'save_order']);

        //Api Soap
        Route::get('api_soap', [ApiSoapController::class, 'index']);
        Route::get('api_soap/get_token', [ApiSoapController::class, 'generateApiKey']);

        // Grupos por Instituciones
        //Route::resource('instituciones.grupos_por_institucion','GruposPorInstitucionController');
        Route::get('instituciones/{instituciones}/agrupar_grupos', [GruposPorInstitucionController::class, 'nestGroups']);
        Route::post('instituciones/{instituciones}/agrupar_grupos', [GruposPorInstitucionController::class, 'saveNestedGroups']);
        Route::get('instituciones/{instituciones}/grupos_por_institucion/', [GruposPorInstitucionController::class, 'index']);
        Route::get('instituciones/{instituciones}/grupos_por_institucion/add', [GruposPorInstitucionController::class, 'create']);
        Route::get('instituciones/{instituciones}/grupos_por_institucion/{grupo_de_pregunta}/add_group', [GruposPorInstitucionController::class, 'store']);
        Route::delete('instituciones/{instituciones}/grupos_por_institucion/{grupo_por_institucion}', [GruposPorInstitucionController::class, 'destroy']);
        Route::post('instituciones/{instituciones}/save_order', [GruposPorInstitucionController::class, 'save_order']);

        // Estados de Accidentes
        Route::resource('estados', EstadosController::class);

        // Accidentes
        Route::get('accidentes/stats', [AccidentesController::class, 'stats'])->name('accidentes.stats');
        Route::get('accidentes/{accidentes}/send', [AccidentesController::class, 'send'])->name('accidentes.send');
        Route::post('accidentes/{accidentes}/send', [AccidentesController::class, 'doSend'])->name('accidentes.do-send');
        Route::get('accidentes/{accidentes}/authorize', [AccidentesController::class, 'authorize'])->name('accidentes.authorize');
        Route::post('accidentes/{accidentes}/authorize', [AccidentesController::class, 'doAuthorize'])->name('accidentes.do-authorize');
        Route::get('accidentes/importar', [AccidentesController::class, 'import']);
        Route::post('accidentes/importar-archivo-datos', [AccidentesController::class, 'uploadDataFile'])->name('accidentes.importar-archivo-datos');
        Route::get('accidentes/bajar-formato-archivo', [AccidentesController::class, 'downloadFileFormat'])->name('accidentes.bajar-formato-archivo');
        Route::get('accidentes/bajar-diccionario-datos', [AccidentesController::class, 'downloadDataDictionary'])->name('accidentes.bajar-diccionario-datos');
        Route::get('accidentes/bajar-formulario-unico', [AccidentesController::class, 'downloadQuestionsForm'])->name('accidentes.bajar-formulario-unico');
        Route::get('accidentes/bajar-anexo-vehiculos', [AccidentesController::class, 'downloadAnexoVehiculos'])->name('accidentes.bajar-anexo-vehiculos');
        Route::get('accidentes/bajar-anexo-personas', [AccidentesController::class, 'downloadAnexoPersonas'])->name('accidentes.bajar-anexo-personas');
        Route::get('accidentes/{accidentes}/pregunta/{pregunta}/edit', [AccidentesController::class, 'editarRespuesta']);
        Route::post('accidentes/{accidentes}/pregunta/{pregunta}', [AccidentesController::class, 'guardarRespuesta']);
        Route::get('accidentes/{accidentes}/pdf', [AccidentesController::class, 'pdf']);
        Route::get('accidentes/cargar', [AccidentesController::class, 'cargar']);
        Route::post('accidentes/cargar', [AccidentesController::class, 'cargarRespuestas'])->name('cargar-respuestas');
        Route::get('accidentes/busqueda-por-indicadores', [AccidentesController::class, 'busquedaPorIndicadores'])->name('accidentes.busqueda-por-indicadores');
        Route::post('accidentes/filtrar-por-indicadores', [AccidentesController::class, 'filtrarPorIndicadores'])->name('accidentes.filtrar-por-indicadores');
        Route::get('accidentes/get-opciones', [AccidentesController::class, 'getOpciones'])->name('accidentes.get-opciones');
        Route::get('accidentes/geografia/departamentos/{departamento}', [\App\Http\Controllers\GeografiaAccidenteController::class, 'departamento']);
        Route::get('accidentes/geografia/distritos/{distrito}', [\App\Http\Controllers\GeografiaAccidenteController::class, 'distrito']);
        Route::get('accidentes/distritos/{departamento}', [AccidentesController::class, 'distritos']);
        Route::get('accidentes/localidades/{distrito}', [AccidentesController::class, 'localidades']);
        Route::get('accidentes/{accidentes}/editar', [AccidentesController::class, 'editarAccidente'])->name('accidentes.editar');
        Route::post('accidentes/{accidentes}/editar', [AccidentesController::class, 'actualizarAccidente'])->name('accidentes.actualizar');
        Route::resource('accidentes', AccidentesController::class);

        // Derivaciones
        Route::get('derivaciones/sent', [DerivacionesController::class, 'sent'])->name('derivaciones.sent');
        Route::get('derivaciones/received', [DerivacionesController::class, 'received'])->name('derivaciones.received');
        Route::put('derivaciones/{derivaciones}', [DerivacionesController::class, 'update'])->name('derivaciones.update');
        Route::delete('derivaciones/{derivaciones}', [DerivacionesController::class, 'destroy'])->name('derivaciones.destroy');

        // Autorizaciones
        Route::get('autorizaciones/sent', [AutorizacionesController::class, 'sent'])->name('autorizaciones.sent');
        Route::get('autorizaciones/received', [AutorizacionesController::class, 'received'])->name('autorizaciones.received');
        Route::get('autorizaciones/{autorizaciones}', [AutorizacionesController::class, 'show'])->name('autorizaciones.show');
        Route::put('autorizaciones/{autorizaciones}', [AutorizacionesController::class, 'update'])->name('autorizaciones.update');
        Route::delete('autorizaciones/{autorizaciones}', [AutorizacionesController::class, 'destroy'])->name('autorizaciones.destroy');

        // Auditoría
        Route::get('auditoria/logins', [AuditoriaController::class, 'logins']);
        Route::resource('auditoria', AuditoriaController::class);

        // Reportes
        Route::post('reportes/get-graph-data', [ReportesController::class, 'getGraphData'])->name('reportes.get-graph-data');
        Route::post('reportes/get-graph-summary-data', [ReportesController::class, 'getGraphSummaryData'])->name('reportes.get-graph-summary-data');
        Route::resource('reportes', ReportesController::class);

        // Reporte de Datos
        Route::get('reporte_datos/{reportes}/pdf', [ReporteDatosController::class, 'pdf'])->name('reporte_datos.pdf');
        Route::get('reporte_datos/{reportes}/csv', [ReporteDatosController::class, 'csv'])->name('reporte_datos.csv');
        Route::resource('reporte_datos', ReporteDatosController::class);

        // Reportes Genéricos
        Route::get('reportes_genericos/carga_registros_por_institucion', [ReportesGenericosController::class, 'carga_registros_por_institucion'])->name('accidentes.carga_registros_por_institucion');
        Route::get('reportes_genericos/total_accidentes', [ReportesGenericosController::class, 'total_accidentes'])->name('accidentes.total_accidentes');
        Route::get('reportes_genericos/ubicacion_accidentes', [ReportesGenericosController::class, 'ubicacion_accidentes'])->name('accidentes.ubicacion_accidentes');
        Route::get('reportes_genericos/totales_por_indicador', [ReportesGenericosController::class, 'totales_por_indicador'])->name('accidentes.totales_por_indicador');

        // Notificaciones
        Route::get('notificaciones/marcar-todo-leido', [NotificacionesController::class, 'flagAllAsRead'])->name('notificaciones.flag-as-read');
        Route::resource('notificaciones', NotificacionesController::class);

        // Configuración
        Route::get('configuracion', [ConfiguracionController::class, 'show'])->name('configuracion.show');
        Route::post('configuracion', [ConfiguracionController::class, 'save'])->name('configuracion.save');
        Route::get('logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout']);
    });


    Route::get('/download-pdf', function () {
        $pdfPath = storage_path('app/MANUAL-SIAT.pdf');

        if (!file_exists($pdfPath)) {
            abort(404, 'File not found');
        }

        return response()->download($pdfPath, 'MANUAL-SIAT.pdf');
    })->name('download.pdf');


    Route::get('/diccionario-pdf', function () {
        $pdfPath = storage_path('app/diccionario.pdf');

        if (!file_exists($pdfPath)) {
            abort(404, 'File not found');
        }

        return response()->download($pdfPath, 'diccionario.pdf');
    })->name('diccionario.pdf');
    
});

Auth::routes();
