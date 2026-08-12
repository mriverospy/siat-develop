<div class="form-horizontal">
    <div class="form-group">
        @if(Auth::user()->hasRole('sysadmin'))
            {!! Form::label('institucion_id', 'Institución:', ['class'=>'control-label']) !!}
            {!! Form::select('institucion_id', $params['data']['patern_list'], null, ['class' => 'select2'] ) !!}
        @endif

        {!! Form::label('nombre', 'Nombre:', ['class'=>'control-label']) !!}
        {!! Form::text('nombre', null, ['class'=>'form-control', 'placeholder'=>'Nombre de la Sede', 'autofocus'=>'true']) !!}

        {!! Form::label('descripcion', 'Descripcion:', ['class'=>'control-label']) !!}
        {!! Form::textarea('descripcion', null, ['class'=>'form-control', 'placeholder'=>'Descripción de la Sede']) !!}
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
