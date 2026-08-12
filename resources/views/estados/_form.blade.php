<div class="form-horizontal">
    <div class="form-group">
        {!! Form::label('nombre', 'Nombre:', ['class'=>'control-label']) !!}
        {!! Form::text('nombre', null, ['class'=>'form-control', 'placeholder'=>'Nombre del Estado', 'autofocus'=>'true']) !!}

        {!! Form::label('descripcion', 'Descripcion:', ['class'=>'control-label']) !!}
        {!! Form::textarea('descripcion', null, ['class'=>'form-control', 'placeholder'=>'Descripción del Estado']) !!}
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
