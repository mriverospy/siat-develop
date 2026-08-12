@extends('app')

@section('page-title', 'Autorización de Cambios')

@section('content')
<div class="page-heading">
    <h1>Autorización de Cambios</h1>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-midnightblue">
                <div class="panel-heading">
                    <h2>Autorización para Modificar Datos de {{ $autorizacion->accidente->caratula }} - {{ $autorizacion->accidente->fecha_accidente->format('d/m/Y') }}</h2>
                </div>
                <div class="panel-body">
                    <div class="row">
                        @if (count($camposEditables) > 0)
                        <div class="col-md-6">
                            <div class="panel-default">
                                <div class="panel-heading">
                                    <h2>Datos del Accidente</h2>
                                </div>
                                <ul class="list-group mb0">
                                    @foreach($camposEditables as $campo => $titulo)
                                    <li class="list-group-item">
                                        <strong>{{ $titulo }}</strong>
                                    </li>
                                    @endforeach
                                    <li class="list-group-item text-center">
                                        <a class="btn btn-default" href="{{ route('accidentes.edit', [$autorizacion->accidente_id]) }}"><i class="fa fa-fw fa-edit"></i> Modificar Datos</a>
                                    </li>
                                </ul>
                            </div>
                            @endif
                        </div>
                        @if (count($camposPreguntas) > 0)
                        <div class="col-md-6">
                            <div class="panel-default">
                                <div class="panel-heading">
                                    <h2>Datos de Preguntas</h2>
                                </div>
                                <ul class="list-group">
                                    @foreach($camposPreguntas as $campo)
                                    <li class="list-group-item">
                                        <a class="btn btn-default btn-xs" href="{{ url('accidentes/' . $autorizacion->accidente_id . '/pregunta/' . $campo['id'] . '/edit') }}"><i class="fa fa-fw fa-edit"></i></a>
                                        <strong>{{ $campo['titulo'] }}</strong>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2>Datos de la Autorización</h2>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="dl-horizontal">
                                <dt>Código Autorización</dt>
                                <dd>{{ $autorizacion->id }}</dd>
                                <dt>Accidente</dt>
                                <dd>{{ $autorizacion->accidente->caratula }}</dd>
                                <dt>Lugar del Accidente</dt>
                                <dd>{!! nl2br(implode("\n", $autorizacion->accidente->lugar_accidente)) !!}</dd>
                                <dt>Fecha del Accidente</dt>
                                <dd>{{ $autorizacion->accidente->fecha_accidente->format('d/m/Y') }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="dl-horizontal">
                                <dt>Autorizado por</dt>
                                <dd>{{ $autorizacion->usuario_emisor->nombreCompleto() }}</dd>
                                <dt>Autorizado a</dt>
                                <dd>{{ $autorizacion->usuario_receptor->nombreCompleto() }}</dd>
                                <dt>Autorizado el</dt>
                                <dd>{{ $autorizacion->fecha_autorizacion->timezone('America/Asuncion')->format('d/m/Y') }}</dd>
                                <dt>Periodo Validez</dt>
                                <dd>
                                    {{ $autorizacion->fecha_inicio_autorizacion->timezone('America/Asuncion')->format('d/m/Y') }}
                                    al
                                    {{ $autorizacion->fecha_fin_autorizacion->timezone('America/Asuncion')->format('d/m/Y') }}
                                </dd>
                                <dt>Vence en</dt>
                                <dd>{{ $autorizacion->fecha_fin_autorizacion->timezone('America/Asuncion')->diffForHumans() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
