<div class="form-horizontal">
    <div class="form-group">
        {!! Form::label('name', 'Nombre:', ['class'=>'control-label']) !!}
        {!! Form::text('name', null, ['class'=>'form-control', 'placeholder'=>'Nombre del Rol', 'autofocus'=>'true']) !!}

        {!! Form::label('display_name', 'Nombre para mostrar:', ['class'=>'control-label']) !!}
        {!! Form::text('display_name', null, ['class'=>'form-control', 'placeholder'=>'Nombre  para mostrar']) !!}

        {!! Form::label('description', 'Descripción:', ['class'=>'control-label']) !!}
        {!! Form::textarea('description', null, ['class'=>'form-control', 'placeholder'=>'Descripción del Rol']) !!}
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
