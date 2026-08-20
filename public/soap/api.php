<?php

require __DIR__.'/../../bootstrap/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
require '../../vendor/deviservi/nusoap/lib/nusoap.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$ws = $app->make('App\Soap\WebService');

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$kernel->terminate($request, $response);

$URL = url();
$namespace = $URL.'?wsdl';

$server = new soap_server();
$server->debug_flag = false;
$server->configureWSDL('SiatAPI', $namespace);

//Registro de funciones a publicar
$server->register('handler', ['parametros' => 'xsd:string'], ['return' => 'xsd:string'], 'urn:handler', 'urn:SiatAPI#handler');

function handler($params = false)
{
    global $ws;
    if (! $params) {
        $respuesta = [
            'status' => 'ERROR',
            'message' => 'No se ha recibido ningún parámetro.',
            'data' => null,
        ];

        return $respuesta;
    }

    $respuesta = $ws->handler(utf8_encode($params));

    return json_encode($respuesta);
}

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

exit();
