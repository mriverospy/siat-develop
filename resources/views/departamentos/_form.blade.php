<div class="form-horizontal">
    <div class="form-group">
        {!! Form::label('nombre', 'Nombre:', ['class'=>'control-label']) !!}
        {!! Form::text('nombre', null, ['class'=>'form-control', 'autofocus'=>'true', 'placeholder'=>'Nombre del Departamento']) !!}
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
