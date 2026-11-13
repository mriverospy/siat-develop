@extends('app')

@section('page-title', 'Datos del Accidente')

@section('content')
<div class="page-heading">
    <h1>Datos del Accidente</h1>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 text-right">
            <p>
                @if ($currentUser->ability('sysadmin', $params['route_name'] . '-pdf'))
                <a href="{{ url('accidentes/' . $accidente->id . '/pdf') }}" class="btn btn-default"><i class="fa fa-fw fa-download"></i>Exportar a PDF</a>
                @endif

                @if ($currentUser->ability('sysadmin', $params['route_name'] . '-edit') and $accidente->estado == 'abierto')
                <a href="{{ url($params['route'], [$accidente->id, 'editar']) }}" class="btn btn-primary"><i class="fa fa-fw fa-pencil"></i>Editar</a>
                @endif
            </p>
        </div>
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h2>Bloque 24 Horas</h2>

                </div>
                <div class="panel-body">
                    <dl class="dl-horizontal">
                        <dt>Identificador SIAT (A_1):</dt>
                        <dd>{{ $accidente->codigo_siat ?? "Pendiente de asignación" }}</dd>
                        <dt>Carátula (CA):</dt>
                        <dd>{{ $accidente->caratula }}</dd>
                        <dt>Fecha y Hora:</dt>
                        <dd>{{ $accidente->fecha_accidente->format('d/m/Y h:i a') }}</dd>
                        <dt>Lugar:</dt>
                        <dd>{{ $accidente->lugar }}</dd>
                        <dt>Tipo de vía:</dt>
                        <dd>{{ config('formulario_accidente.vias')[$accidente->tipo_camino] ?? ucwords($accidente->tipo_camino) }}</dd>
                        <dt>Área:</dt>
                        <dd>{{ config('formulario_accidente.areas')[$accidente->area] ?? 'Sin registrar' }}</dd>
                        <dt>Barrio/Localidad:</dt>
                        <dd>{{ $accidente->localidad->nombre }}</dd>
                        <dt>Distrito:</dt>
                        <dd>{{ $accidente->localidad->distrito->nombre }}</dd>
                        <dt>Departamento:</dt>
                        <dd>{{ $accidente->localidad->distrito->departamento->nombre }}</dd>
                        <dt>Heridos:</dt>
                        <dd>{{ $accidente->heridos }}</dd>
                        <dt>Fallecidos:</dt>
                        <dd>{{ $accidente->fallecidos }}</dd>
                        <dt>Ilesos:</dt>
                        <dd>{{ $accidente->ilesos }}</dd>
                        <dt>Participantes:</dt>
                        <dd>{{ $accidente->participantes_siniestro }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        @if (count($respuestas) > 0)
        <div class="col-md-12">
            @foreach($respuestas->chunk(2) as $chunks)
            <div class="row">
                @foreach($chunks as $grupo)
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h2>{{ $grupo['grupo'] }}</h2>
                        </div>
                        <div class="panel-body">
                            @foreach($grupo['respuestas'] as $orden => $pregunta)
                            <dl>
                                @foreach($pregunta as $nombre => $respuestas)
                                <dt>
                                    {{ $nombre }}
                                </dt>
                                <dd>
                                    <ul class="list-group">
                                    @foreach($respuestas as $respuesta)
                                        <li class="list-group-item">
                                        @if (in_array($respuesta->pregunta_tipo, array('radio', 'check', 'text')))
                                            {{ $respuesta->respuesta_texto }}
                                        @endif
                                        @if ($respuesta->pregunta_tipo == 'number')
                                            {{ $respuesta->respuesta_numero }}
                                        @endif
                                        @if ($respuesta->pregunta_tipo == 'date')
                                            {{ $respuesta->respuesta_fecha->format('d/m/Y') }}
                                        @endif
                                        </li>
                                    @endforeach
                                    </ul>
                                </dd>
                                @endforeach
                            </dl>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

@section('modals')
@endsection
