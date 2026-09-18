<div class="form-horizontal">
    <div class="form-group">
        {!! Form::label('nombre', 'Nombre:', ['class' => 'control-label']) !!}
        {!! Form::text('nombre', null, ['class' => 'form-control', 'autofocus' => 'true', 'placeholder' => 'Nombre del Grupo de preguntas']) !!}

        {!! Form::label('descripcion', 'Descripcion:', ['class' => 'control-label']) !!}
        {!! Form::textarea('descripcion', null, ['class' => 'form-control', 'placeholder' => 'Descripción del Grupo de preguntas']) !!}

        {!! Form::label('visible', 'Visible en los formularios de accidentes:', ['class' => 'control-label']) !!}
        {!! Form::select('visible', [1 => 'Sí', 0 => 'No'], isset($params['data']['item']) ? null : 1, ['class' => 'form-control']) !!}
        <span class="help-block">Determina si el grupo aparece al cargar o editar un accidente.</span>

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
