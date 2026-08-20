<div class="form-horizontal">
    <div class="form-group">
        {!! Form::label('departamento_id', 'Departamento:', ['class'=>'control-label']) !!}
        {!! Form::select('departamento_id', $params['data']['patern_list'], null, ['class' => 'select2', 'autofocus' => 'true'] ) !!}

        {!! Form::label('nombre', 'Nombre:', ['class'=>'control-label']) !!}
        {!! Form::text('nombre', null, ['class'=>'form-control', 'placeholder'=>'Nombre del Distrito']) !!}

        {!! Form::label('descripcion', 'Descripcion:', ['class'=>'control-label']) !!}
        {!! Form::textarea('descripcion', null, ['class'=>'form-control', 'placeholder'=>'Descripción del Distrito']) !!}
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
