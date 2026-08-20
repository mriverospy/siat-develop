@extends('layouts.base_layout')

@section('layout_content')
    <div class="panel {{ $params['panel_color'] }}">
        <div class="panel-heading">
            <h2>{{ $params['subtitle'] }} {{ $params['data']['item']->caratula }}</h2>
        </div>
        <div class="panel-body">
            {!! Form::open(['route' => ['accidentes.send', $params['data']['item']->id]]) !!}
                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('institucion_id', 'Siguiente Institución', ['class' => 'control-label']) !!}
                        <select name="institucion_id" class="select2" style="width: 100% !important">
                            @foreach($params['instituciones'] as $institucion)
                            <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="panel-footer">
                    <div class="row">
                        <div class="pull-right">
                            <a href = "{{ url($params['route']) }}" class = 'btn btn-default'>Cancelar</a>
                            {!! Form::submit('Derivar', ['class'=>'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
