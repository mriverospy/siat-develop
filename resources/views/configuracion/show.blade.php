@extends('layouts.base_layout')

@section('layout_content')
    <div class="panel {{ $params['panel_color'] }}">
        <div class="panel-heading">
            <h2>{{ $params['subtitle'] }}</h2>
        </div>

        <div class="panel-body">
            <!-- CREATE -->
            {!! Form::open(['route' => 'configuracion.save', 'files' => true]) !!}
                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('formato_formulario_unico', 'Formato Formulario Único:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-10">
                            <label class="radio-inline icheck">
                                {!! Form::radio('formato_formulario_unico', 'expandido', $settings['formato_formulario_unico'] == 'expandido') !!} Expandido
                            </label>
                            <label class="radio-inline icheck">
                                {!! Form::radio('formato_formulario_unico', 'condensado', $settings['formato_formulario_unico'] == 'condensado') !!} Condensado
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('dias_edicion', 'Días para Edición', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-10">
                            {!! Form::text('dias_edicion', $settings['dias_edicion'], ['class' => 'form-control', 'placeholder' => 'Cantidad de Días']) !!}
                            <p class="help-block">Cantidad de Días que un Accidente está disponible para edición, desda la fecha en que fue cargado.</p>
                        </div>
                    </div>
                </div>

                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('logo_membrete', 'Logo/Membrete:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-10">
                            {!! Form::file('logo_membrete', null, ['class' => 'form-control', 'placeholder' => 'Logo Membrete']) !!}
                            <img src="{{ $settings['logo_membrete'] }}" alt="Logo Membrete" />
                            <p class="help-block">Tamaño recomendado: al menos 1000px x 400px.</p>
                        </div>
                    </div>
                </div>

                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('logo_login', 'Logo Inicio Sesión:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-10">
                            {!! Form::file('logo_login', null, ['class' => 'form-control', 'placeholder' => 'Logo Inicio Sesión']) !!}
                            <img src="{{ $settings['logo_login'] }}" alt="Logo Inicio Sesión" />
                            <p class="help-block">Tamaño requerido: 310x83 pixels. <a href="{{ asset('img/login-logo.psd') }}">Ver archivo PSD de ejemplo</a>.</p>
                        </div>
                    </div>
                </div>

                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('logo_principal', 'Logo Principal:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-10">
                            {!! Form::file('logo_principal', null, ['class' => 'form-control', 'placeholder' => 'Logo Principal']) !!}
                            <img src="{{ $settings['logo_principal'] }}" alt="Logo Principal" />
                            <p class="help-block">Tamaño requerido: 310x83 pixels. <a href="{{ asset('img/login-logo.psd') }}">Ver archivo PSD de ejemplo</a>.</p>
                        </div>
                    </div>
                </div>

                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('logo_cabecera', 'Logo/Membrete:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-10">
                            {!! Form::file('logo_cabecera', null, ['class' => 'form-control', 'placeholder' => 'Logo Membrete']) !!}
                            <img src="{{ $settings['logo_cabecera'] }}" alt="Logo Membrete" />
                            <p class="help-block">Tamaño requerido: 140x100 pixels. <a href="{{ asset('img/avalon-logo.psd') }}">Ver archivo PSD de ejemplo</a>.</p>
                        </div>
                    </div>
                </div>

                <div class="panel-footer">
                    <div class="row">
                        <div class="pull-right">
                            <a href = "{{ url($params['route']) }}" class="btn btn-default">Cancelar</a>
                            {!! Form::submit('Guardar', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
