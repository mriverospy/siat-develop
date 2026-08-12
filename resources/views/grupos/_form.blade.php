<div class="form-horizontal">
    <div class="form-group">
        {!! Form::label('nombre', 'Nombre:', ['class' => 'control-label']) !!}
        {!! Form::text('nombre', null, ['class' => 'form-control', 'autofocus' => 'true', 'placeholder' => 'Nombre del Grupo de preguntas']) !!}

        {!! Form::label('descripcion', 'Descripcion:', ['class' => 'control-label']) !!}
        {!! Form::textarea('descripcion', null, ['class' => 'form-control', 'placeholder' => 'Descripción del Grupo de preguntas']) !!}

        <div class="radio">
            <label>
                {!! Form::radio('puede_duplicar', 1, null) !!} Este grupo <strong>se puede duplicar</strong> en la Interfaz de Carga
            </label>
        </div>
        <div class="radio">
            <label>
                {!! Form::radio('puede_duplicar', 0, null) !!} Este grupo <strong>no se puede duplicar</strong> en la Interfaz de Carga
            </label>
        </div>
    </div>
</div>

<!-- Tu código JavaScript -->
<script>
document.getElementById('siRadio').addEventListener('click', function() {
    console.log('Seleccionaste "Sí", el valor de obligatorio es ahora 1');
});

document.getElementById('noRadio').addEventListener('click', function() {
    console.log('Seleccionaste "No", el valor de obligatorio es ahora 0');
});
</script>

<div class="panel-footer">
    <div class="row">
        @if(isset($params['data']['item']))
        <div class="pull-left">
            @if($params['data']['item']['master'])
            <a href="{{ url($params['route'], $params['data']['item']['id']) }}/unset_master" class="btn btn-success"><i class="fa fa-fw fa-star-o"></i> Restablecer como Grupo Normal</a>
            @else
            <a href="{{ url($params['route'], $params['data']['item']['id']) }}/set_master" class="btn btn-warning"><i class="fa fa-fw fa-star"></i> Establecer como Grupo Master</a>
            @endif
        </div>
        @endif
        <div class="pull-right">
            <a href="{{ url($params['route']) }}" class="btn btn-default">Cancelar</a>
            {!! Form::submit('Guardar', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
</div>
