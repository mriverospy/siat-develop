<?php

require '../../vendor/deviservi/nusoap/lib/nusoap.php';
/*
try {

    $options = array(
                'uri' => 'http://siat.local/soap/server.php',
                'soap_version' => SOAP_1_2,
                'encoding'=>'UTF-8');
    $server = new SoapServer("SiatAPI.wsdl", $options);

    $server->setClass('SiatAPI');
    $server->handle();
}
catch (SOAPFault $f)
{
    print $f->faultstring;
}


*/
$URL = 'http://siat.local/soap';
$namespace = $URL.'?wsdl';

$server = new soap_server();
$server->debug_flag = false;
$server->configureWSDL('SiatAPI', $namespace);
//$server->wsdl->schemaTargetNamespace = $namespace;
$server->register('siatAPI', ['parametros' => 'xsd:string'], ['return' => 'xsd:string'], 'urn:siatAPI', 'urn:SiatAPI#siatAPI');
//$server->register("siatAPI");

  function siatAPI($parametros)
  {
      if (! $parametros) {
          return new soap_fault('Client', '', 'Put your name!');
      }

      return 'Hola '.$parametros;
  }

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

exit();
