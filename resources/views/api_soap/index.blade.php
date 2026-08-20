@extends('layouts.base_layout')

@section('layout_content')
	<div class="panel panel-default">
		<div class="panel-heading">
			<h2>{{ $params['subtitle'] }}</h2>
			<div class="panel-ctrls">
				<div class="dataTables_filter pull-right">
					<a href="{{ url($params['route']) }}/get_token" class="btn btn-default"><i class="fa fa-fw fa-key"></i> Generar Token</a>
				</div>
			</div>
		</div>
		<div class="panel-body panel-no-padding">
			<div id="example_wrapper" class="dataTables_wrapper form-inline no-footer">
				<div class="row">
					<div class="col-sm-6"></div>
					<div class="col-sm-6"></div>
				</div>
				<div class="col-sm-12">
					<h3>Instrucciones para el uso del Web service</h3>
					<p>
						El SIAT cuenta con una API del tipo SOAP por medio de la cual se puede acceder a las diversas funciones
						que fueron diseñadas especialmente para hacer posible la intercomunicación del SIAT con otros sistemas.
					</p>
					<p>
						Para acceder a esta funcionalidad puede utilizar los siguientes datos:</br>
						<ul>
							<li>
                                <strong>Ruta: </strong>
                                <br />
                                <code>{!! url('soap/api.php') !!}</code>
                                <a href="{!! url('soap/api.php') !!}"><i class="fa fa-external-link"></i></a>
                            </li>
							<li>
                                <strong>WSDL: </strong>
                                <br />
                                <code>{!! url('soap/api.php?wsdl') !!}</code>
                                <a href="{!! url('soap/api.php?wsdl') !!}"><i class="fa fa-external-link"></i></a>
                            </li>
							<li>
                                <strong>Token de Usuario: </strong>
                                <br />
								@if (is_null(Auth::user()->api_key))
									Usted aún no cuenta con una llave de acceso. <a href="{{ url($params['route']) }}/get_token">Haga click aquí para generar una llave de acceso</a>.
								@else
									{{ Auth::user()->api_key }}
								@endif
							</li>
						</ul>
					</p>

					<!-- FORMATOS DE SOLICITUD Y RESPUESTA -->
					<p>&nbsp;</p>
					<div class="col-md-6 col-sm-12">
						<h3>Formatos de solicitud y respuesta</h3>
						<p>La utilización de esta herramienta es sencilla, se realiza la llamada, siempre a la misma función, enviando como parámetro una cadena en formato JSON. Una vez ejecutada la petición el servidor responderá en el mismo formato.</p>
						<p>&nbsp;</p>
						<p>
							<strong>Claves para el envío de datos o solicitud JSON:</strong></br>
							<strong> - api_key: </strong>Token o llave del Usuario.</br>
							<strong> - action: </strong>Llamada a la función requerida.</br>
							<strong> - data: </strong>Utilizado en los casos que se necesite el envío de datos.</br>
						</p>
						<p>&nbsp;</p>
						<p>
							<strong>Formato de recepción de datos del Servidor:</strong></br>
							<strong> - status: </strong>Resultado de la operación, puede ser SUCCESS o ERROR.</br>
							<strong> - message: </strong>Mensaje recibido desde el Servidor.</br>
							<strong> - data: </strong>Datos solicitados, en caso que los haya.</br>
						</p>

					</div>
					<div class="col-md-6 col-sm-12">
						<h4>Ejemplo solicitud</h4>
						<figure class="highlight">
							<pre><code>{</br>   "api_key":"{{ Auth::user()->api_key }}",</br>   "action":"get_localidades",</br>   "data":null</br>}</code></pre></figure>
						</figure>
						<h4>Ejemplo respuesta</h4>
						<figure class="highlight">
							<pre><code>{</br>  "status":"SUCCESS",</br>  "message":"",</br>  "data":[</br>    {"id":1, "nombre":"SAJONIA", ... },</br>    {"id":2, "nombre":"SAN ANTONIO", ... },</br>    {"id":4, "nombre":"ITA PYTA", ... }</br>  ]</br>}</code></pre></figure>
					</div>

					<!-- FUNCIONES DISPONIBLES -->
					<p>&nbsp;</p>
					<h3>Funciones disponibles</h3>
					<p>Por medio de la etiqueta "action" podemos solicitar la llamada a las diferentes funciones disponibles:</p>
					<div class="col-sm-12">
						<div class="col-md-11 col-sm-12">
							<ul>
								<li><strong>get_preguntas: </strong>Retorna el listado de Grupos con sus respectivas Preguntas, correspondientes a la Institución del Usuario solicitante. No tiene opción de filtrar.</li>
								<li><strong>get_localidades: </strong>Listado completo de los Departamentos, Distritos y Localidades registrados en el sistema. No tiene opción de filtrar</li>
								<li><strong>cargar_respuestas: </strong>Esta función es capaz de registrar un nuevo accidente con las respuestas del mismo, estos datos deben ser enviados dentro de la etiqueta "data".</li>
								<li><strong>get_accidentes: </strong>Devuelve un listado de todos los accidentes registrados, puede filtrar la respuesta enviando bajo la etiqueta "data" los criterios requeridos. También es posible limitar la cantidad de registros (limit) y el desplazamiento (offset).</li>
							</ul>
						</div>
					</div>

					<!-- FUNCIONES DISPONIBLES -->
					<p>&nbsp;</p>
					<h3>Ejemplos de llamadas a Funciones del WS</h3>
					<p>A continuación se muestran ejemplos de llamadas al web service para cada función disponible:</p>

					<h4>Obtener Listado de Preguntas</h4>
					<figure class="highlight">
						<pre><code>{</br>  "api_key":"{{ Auth::user()->api_key }}",</br>  "action":"get_preguntas",</br>  "data":null</br>}</code></pre></figure>
					</figure>

					<h4>Obtener Listado de Localidades</h4>
					<figure class="highlight">
						<pre><code>{</br>  "api_key":"{{ Auth::user()->api_key }}",</br>  "action":"get_localidades",</br>  "data":null</br>}</code></pre></figure>
					</figure>

					<h4>Cargar Respuestas</h4>
					<figure class="highlight">
						<pre><code>{</br>  "api_key":"{{ Auth::user()->api_key }}",</br>  "action":"cargar_respuestas",</br>  "data":{</br>     "caratula":"Derrape en calzada",</br>     "fecha_accidente":"2015-12-15 13:25:00",</br>     "expediente_judicial":"3252\/15",</br>     "sumario_policial":"7362",</br>     "otro":"4023",</br>     "dependencia":"4ta. Compañía",</br>     "lugar":"Boqueron esquina Isla Poi",</br>     "tipo_camino":"calle",</br>     "altura":600,</br>     "codigo_postal":6000,</br>     "gps_latitud":"-27.337134400000004",</br>     "gps_longitud":"-55.8505767",</br>     "heridos":2,</br>     "fallecidos":0,</br>     "ilesos":0,</br>     "localidad_id":2396,</br>     "participantes_siniestro":2,</br>     "respuestas": [</br>        {"pregunta_id":1, "agrupador":0, "respuesta":"Choque frontal"},</br>        {"pregunta_id":1, "agrupador":0, "respuesta":"Caida de Motociclista"},</br>        {"pregunta_id":2, "agrupador":0, "respuesta":"No se registra"},</br>        {"pregunta_id":3, "agrupador":0, "respuesta":"Exceso de velocidad"},</br>        {"pregunta_id":4, "agrupador":0, "respuesta":"No hubo imprudencia de peatones"},</br>        {"pregunta_id":17, "agrupador":0, "respuesta":1},</br>        {"pregunta_id":42, "agrupador":0, "respuesta":"1990-01-09 00:00:00"}</br>     ]</br>  }</br>}</code></pre></figure>
					</figure>

					<h4>Obtener Listado de Accidentes</h4>
					<figure class="highlight">
						<pre><code>{</br>  "api_key":"{{ Auth::user()->api_key }}",</br>  "action":"get_accidentes",</br>  "data":{</br>     "filters":[</br>         {"field":"caratula","operator":"like","value":"choque"},</br>         {"field":"fallecidos","operator":"=","value":0},</br>         {"field":"heridos","operator":"=","value":2}</br>     ],</br>     "limit":2,</br>     "offset":0,</br>     "orderBy":{"field":"id", "order":"asc"}</br>  }</br>}</code></pre></figure>
					</figure>
				</div>
			</div>
		</div>
		<div class="panel-footer">
				<div class='pull-right mr10'>
					@yield('pagination')
				</div>
		</div>
	</div>

@endsection

@section('footer-javascript')
    <script type="text/javascript" charset="utf-8">
        $('document').ready(function(){
            $('#bx_filter').focus();
            $('#bx_filter').select();
        });
    </script>
@endsection
