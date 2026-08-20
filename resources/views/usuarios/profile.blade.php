@extends('layouts.base_layout')

@section('layout_content')
    <div class="panel {{ $params['panel_color'] }}">
        <div class="panel-heading">
            <h2>{{ $params['subtitle'] }}</h2>
        </div>
        <div class="panel-body">
            {!! Form::model($params['data']['item'], ['method' => 'PATCH', 'route' => 'user.profile.update', 'files' => (isset($params['files']) ? $params['files'] : false)]) !!}
            <div class="form-horizontal">
                <div class="form-group">
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            {!! Form::label('nombres', 'Nombres:', ['class' => 'control-label']) !!}
                            {!! Form::text('nombres', null, ['class' => 'form-control', 'placeholder' => 'Nombres']) !!}
                        </div>

                        <div class="col-xs-12 col-md-6">
                            {!! Form::label('apellidos', 'Apellidos:', ['class' => 'control-label']) !!}
                            {!! Form::text('apellidos', null, ['class' => 'form-control', 'placeholder' => 'Apellidos']) !!}
                        </div>

                        <div class="col-xs-12 col-md-4">
                            {!! Form::label('email', 'Correo Electrónico:', ['class' => 'control-label']) !!}
                            {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Correo Electrónico']) !!}
                        </div>

                        <div class="col-xs-12 col-md-4">
                            {!! Form::label('documento', 'Nro. Documento:', ['class' => 'control-label']) !!}
                            {!! Form::text('documento', null, ['class' => 'form-control', 'placeholder' => 'Número de Cedula']) !!}
                        </div>

                        <div class="col-xs-12 col-md-4">
                            {!! Form::label('genero', 'Genero:', ['class'=>'control-label']) !!}
                            {!! Form::select('genero', ['' => 'Seleccione...', 'Masculino' => 'Masculino', 'Femenino' => 'Femenino'], null, ['class' => 'form-control'] ) !!}
                        </div>

                        <div class="col-xs-12 col-md-7">
                            {!! Form::label('direccion', 'Dirección:', ['class' => 'control-label']) !!}
                            {!! Form::text('direccion', null, ['class' => 'form-control', 'placeholder' => 'Dirección']) !!}
                        </div>

                        <div class="col-xs-12 col-md-5">
                            {!! Form::label('telefonos', 'Teléfonos:', ['class' => 'control-label']) !!}
                            {!! Form::text('telefonos', null, ['class' => 'form-control', 'placeholder' => 'Número de Teléfono']) !!}
                        </div>
                    </div>

                    <p class="h4 text-info">Contraseña</p>

                    <div class="alert alert-info">
                        <div class="row">
                            <p>Para cambiar su contraseña, ingrese la nueva contraseña:</p>
                            <div class="col-xs-12 col-md-4 col-lg-4">
                                {!! Form::label('password', 'Contraseña:', ['class' => 'control-label']) !!}
                                {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Contraseña']) !!}
                            </div>
                            <div class="col-xs-12 col-md-4 col-lg-4">
                                {!! Form::label('password_confirmation', 'Confirmar Contraseña:', ['class' => 'control-label']) !!}
                                {!! Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'Confirmar Contraseña']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-footer">
                <div class="row">
                    <div class="pull-right">
                        <a href = "{{ url($params['route']) }}" class = 'btn btn-default'>Cancelar</a>
                        {!! Form::submit('Guardar', ['class'=>'btn btn-primary']) !!}
                    </div>
                </div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection


