@extends('layouts.base_layout')

@section('layout_content')
    <div class="panel {{ $params['panel_color'] }}">
        <div class="panel-heading">
            <h2>{{ $params['subtitle'] }}</h2>
            <div class="text-right">{{ $params['tipoNombre'] }}</div>
        </div>

        <div class="panel-body">

            @if (isset($params['data']['item'] ))
                <!-- EDIT -->
                {!! Form::model($params['data']['item'], ['method'=>'PATCH','url'=>[$params['route'], $params['data']['item']->id]] ) !!}
            @else
                <!-- CREATE -->
                {!! Form::open(['url'=>$params['route']]) !!}
            @endif
                <div class="form-horizontal">
                    <div class="form-group">
                        <!-- TIPO -->
                        {!! Form::hidden('tipo', $params['tipo'] ) !!}

                        <!-- PREGUNTA -->
                        {!! Form::label('pregunta', 'Pregunta:', ['class' => 'control-label']) !!}
                        {!! Form::text('pregunta', null, ['class' => 'form-control', 'autofocus' => 'true', 'placeholder' => 'Pregunta a realizar (Ej.: Color del vehículo?)']) !!}

                        <!-- OPCIONES -->
                        @if ($params['tipo'] == 'check' or $params['tipo'] == 'radio')
                            {!! Form::label('opciones', 'Opciones:', ['class' => 'control-label']) !!}
                            {!! Form::hidden('opciones', null, ['style' => 'width:100% !important'] ) !!}
                        @endif

                        {!! Form::label('instrucciones', 'Instrucciones:', ['class' => 'control-label']) !!}
                        {!! Form::text('instrucciones', null, ['class' => 'form-control', 'autofocus' => 'true', 'placeholder' => 'Instrucciones para el usuario sobre cómo contestar esta pregunta']) !!}

                        @if ($params['tipo'] == 'check' or $params['tipo'] == 'radio')
                            {!! Form::label('mostrar_selector', 'Mostrar Otro?', ['class' => 'control-label']) !!}
                            <div class="radio">
                                <label>
                                    {!! Form::radio('mostrar_otro', 1) !!} Sí
                                </label>
                                <label>
                                    {!! Form::radio('mostrar_otro', 0) !!} No
                                </label>
                            </div>
                            <p class="help-block">Puede incluir una opción adicional "Otro" para que el usuario ingrese una opción no disponible en la lista de opciones.</p>

                            {!! Form::label('titulo_otro', 'Título "Otro":', ['class' => 'control-label']) !!}
                            {!! Form::text('titulo_otro', null, ['class' => 'form-control', 'autofocus' => 'true', 'placeholder' => 'Por defecto se muestra "Otro", puede personalizar el título aquí']) !!}
                            <p class="help-block">Puede definir un título personalizado, por defecto, se muestra "Otro" en el Formulario de Carga.</p>
                        @endif

                        @if ($params['tipo'] == 'radio')
                            {!! Form::label('mostrar_selector', 'Mostrar como Selector?', ['class' => 'control-label']) !!}
                            <div class="radio">
                                <label>
                                    {!! Form::radio('mostrar_selector', 1) !!} Sí
                                </label>
                                <label>
                                    {!! Form::radio('mostrar_selector', 0) !!} No
                                </label>
                            </div>
                            <p class="help-block">Mostrar la pregunta como un selector con buscador. Recomendado si la pregunta tiene muchas opciones de respuesta.</p>
                        @endif



                            <!-- Nuevo campo para "Obligatorio" -->

                            {!! Form::label('obligatorio', 'Obligatorio?', ['class' => 'control-label']) !!}
                            <div class="radio">
                                <label>
                                    {!! Form::radio('obligatorio', 1) !!} Sí
                                </label>
                                <label>
                                    {!! Form::radio('obligatorio', 0) !!} No
                                </label>
                            </div>
                            <p class="help-block">Hacer que esta pregunta sea obligatoria para el usuario.</p>
                    </div>
                </div>

                <div class="panel-footer">
                    <div class="row">
                        <div class="pull-right">
                            <a href="{{ url($params['route']) }}" class="btn btn-default">Cancelar</a>
                            {!! Form::submit('Guardar', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@section('footer-javascript')
    @parent
    <script type="text/javascript" charset="utf-8">
        jQuery(document).ready(function($) {
            $('#opciones').select2({
                width: '100%',
                separator: '|',
                placeholder: 'Opciones de respuesta (presione ENTER para establecer cada una)',
                tags: ['Otro']
            });
        })
    </script>
@endsection
