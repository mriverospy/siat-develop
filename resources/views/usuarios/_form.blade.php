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

            <div class="col-xs-12 col-md-6">
                {!! Form::label('sede_id', 'Sede:', ['class' => 'control-label']) !!}
                <br />
                {!! Form::select('sede_id', $params['data']['patern_list'], null, ['class' => 'select2', 'autofocus' => 'true', 'style' => 'width: 100%'] ) !!}
            </div>
            <div class="col-xs-12 col-md-3">
                {!! Form::label('documento', 'Nro. Documento:', ['class' => 'control-label']) !!}
                {!! Form::text('documento', null, ['class' => 'form-control', 'placeholder' => 'Número de Cedula']) !!}
            </div>
            <div class="col-xs-12 col-md-3">
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
            <div class="col-xs-12">&nbsp;</div>
        </div>

        <p class="h4 text-info">Credenciales del Usuario</p>
        <div class="alert alert-dismissable alert-info">
            <div class="row">
                <div class="col-xs-12 col-md-4 col-lg-4">
                    {!! Form::label('rol_id', 'Rol de Usuario:', ['class' => 'control-label']) !!}
                    @if (isset($params['data']['item']))
                    {!! Form::select('rol_id', $params['data']['rol_list'], $params['data']['current_rol'], ['class'=>'form-control'] ) !!}
                    @else
                    {!! Form::select('rol_id', $params['data']['rol_list'], null, ['class' => 'form-control'] ) !!}
                    @endif
                </div>
                <div class="col-xs-12 col-md-4 col-lg-4">
                    {!! Form::label('email', 'E-Mail:', ['class' => 'control-label']) !!}
                    {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Dirección de E-Mail']) !!}
                </div>
                <div class="col-xs-12 col-md-2 col-lg-2">
                    {!! Form::label('username', 'Nombre de Usuario:', ['class' => 'control-label']) !!}
                    {!! Form::text('username', null, ['class' => 'form-control', 'placeholder' => 'Nombre de Usuario']) !!}
                </div>
                @if(!isset($params['data']['item']))
                    <div class="col-xs-12 col-md-2 col-lg-2">
                        {!! Form::label('password', 'Contraseña:', ['class' => 'control-label']) !!}
                        {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Primera contraseña del usuario']) !!}
                    </div>
                @endif
                @if(isset($params['data']['item']))
                    <div class="col-xs-12 col-md-2 col-lg-2">
                        {!! Form::label('','Estado:', ['class' => 'control-label']) !!}
                        <div class="block">
                            <div class="checkbox-inline"><label for="activo" class="control-lablel">{!! Form::checkbox('activo','yes',null) !!}Usuario Activo</label></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="panel-footer">
    <div class="row">
        @if(isset($params['data']['item']))
            <div class="pull-left">
                <a data-toggle="modal" data-target="#modalWindow" class="btn btn-warning" data-id="{{ $params['data']['item']['id'] }}">Cambiar Contraseña</a>
            </div>
        @endif
        <div class="pull-right">
            <a href = "{{ url($params['route']) }}" class = 'btn btn-default'>Cancelar</a>
            {!! Form::submit('Guardar', ['class'=>'btn btn-primary']) !!}
        </div>
    </div>
</div>
