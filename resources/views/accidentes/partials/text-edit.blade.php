<?php
    $value = null;
    if (isset($respuestas[0])) {
        $value = $respuestas[0]->respuesta_texto;
    }
?>
@extends('layouts.base_layout')

@section('layout_content')
    <div class="panel {{ $params['panel_color'] }}">
        <div class="panel-heading">
            <h2>{{ $pregunta->pregunta }}</h2>
        </div>

        <div class="panel-body">
            {!! Form::open(['url' => 'accidentes/' . $accidente->id . '/pregunta/' . $pregunta->id]) !!}
                <div class="form-horizontal">
                    {!! Form::hidden('pregunta_id', $pregunta->id) !!}
                    <div class="form-group">
                        <div class="col-sm-12">
                            {!! Form::text('respuestas[]', $value, ['class' => 'form-control']) !!}
                            @if ($pregunta->instrucciones)
                            <span class="help-block">{{ $pregunta->instrucciones }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="panel-footer">
                    <div class="row">
                        <div class="pull-right">
                            <a href = "{{ url('accidentes/' . $accidente->id) }}" class="btn btn-default">Cancelar</a>
                            {!! Form::submit('Guardar Cambios', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
