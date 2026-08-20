<div class="form-horizontal">
    <div class="form-group">
        {!! Form::label('nombre', 'Nombre:', ['class' => 'control-label']) !!}
        {!! Form::text('nombre', null, ['class' => 'form-control', 'autofocus' => 'true', 'placeholder' => 'Nombre de la Institución']) !!}

        {!! Form::label('descripcion', 'Descripcion:', ['class' => 'control-label']) !!}
        {!! Form::textarea('descripcion', null, ['class' => 'form-control', 'placeholder' => 'Descripción de la Institución']) !!}

        {!! Form::label('dias_edicion', 'Días Edición:', ['class' => 'control-label']) !!}
        {!! Form::text('dias_edicion', null, ['class' => 'form-control', 'placeholder' => 'Cantidad de Días disponibles para Editar el Accidente']) !!}
        <p class="help-block">Cantidad de Días disponibles para Editar el Accidente, desde la fecha de carga del Accidente</p>

        @if (isset($params['data']['item']) AND $params['data']['item']->logo != '')
        <hr />
        <div class="row">
            <div class="col-xs-12 col-md-4">
                <img src="{{ url($params['data']['item']->logo) }}" alt="Logo Institución" class="img-responsive">
            </div>
            <div class="col-xs-12 col-md-8">
                {!! Form::label('logo', 'Logo/Membrete', ['class' => 'control-label']) !!}
                {!! Form::file('logo') !!}
            </div>
        </div>
        @else
        {!! Form::label('logo', 'Logo/Membrete', ['class' => 'control-label']) !!}
        {!! Form::file('logo') !!}
        @endif
        <p class="help-block">Tamaño recomendado: al menos 1000px x 400px</p>
    </div>
</div>

<div class="panel-footer">
    <div class="row">
        <div class="pull-right">
            <a href = "{{ url($params['route']) }}" class = 'btn btn-default'>Cancelar</a>
            {!! Form::submit('Guardar', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
</div>
