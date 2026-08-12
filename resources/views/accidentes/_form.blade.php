<script type="text/javascript">
    var coordenadasAccidente = {
        lat: '{{ $params['data']['item']->gps_latitud }}',
        lng: '{{ $params['data']['item']->gps_longitud }}'
    }
</script>
<div class="form-horizontal row-border">

    @if (in_array('caratula', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('caratula', 'Carátula:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('caratula', null, ['class' => 'form-control', 'placeholder' => 'Carátula']) !!}
        </div>
    </div>
    @endif

    @if (in_array('expediente_judicial', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('expediente_judicial', 'Nro. Expediente:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('expediente_judicial', null, ['class' => 'form-control', 'placeholder' => 'Número de Expediente Judicial']) !!}
        </div>
    </div>
    @endif

    @if (in_array('sumario_policial', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('sumario_policial', 'Nro. Sumario:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('sumario_policial', null, ['class' => 'form-control', 'placeholder' => 'Número de Sumario Policial']) !!}
        </div>
    </div>
    @endif

    @if (in_array('otro', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('otro', 'Otro:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('otro', null, ['class' => 'form-control', 'placeholder' => '']) !!}
        </div>
    </div>
    @endif

    @if (in_array('dependencia', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('dependencia', 'Dependencia:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('dependencia', null, ['class' => 'form-control', 'placeholder' => 'Dependencia']) !!}
        </div>
    </div>
    @endif

    @if (in_array('fecha_accidente', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('fecha_accidente', 'Fecha y Hora:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('fecha_accidente', null, ['class' => 'form-control datetime-picker', 'placeholder' => 'Fecha y Hora del Accidente', 'data-date-language' => 'es']) !!}
            <span class="help-block">Fecha y Hora del Accidente</span>
        </div>
    </div>
    @endif

    @if (in_array('lugar', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('lugar', 'Lugar:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('lugar', null, ['class' => 'form-control', 'placeholder' => 'Lugar o Dirección']) !!}
        </div>
    </div>
    @endif

    @if (in_array('altura', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('altura', 'Altura:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('altura', null, ['class' => 'form-control', 'placeholder' => 'Altura']) !!}
            <span class="help-block">Altura o Kilómetro</span>
        </div>
    </div>
    @endif

    @if (in_array('tipo_camino', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('tipo_camino', 'Tipo Camino:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            <label class="radio-inline icheck">
                {!! Form::radio('tipo_camino', 'calle') !!} Calle
            </label>
            <label class="radio-inline icheck">
                {!! Form::radio('tipo_camino', 'ruta') !!} Ruta
            </label>
            <label class="radio-inline icheck">
                {!! Form::radio('tipo_camino', 'avenida') !!} Avenida
            </label>
            <label class="radio-inline icheck">
                {!! Form::radio('tipo_camino', 'ramal') !!} Ramal
            </label>
            <label class="radio-inline icheck">
                {!! Form::radio('tipo_camino', 'camino_vecinal') !!} Camino Vecinal
            </label>
            <label class="radio-inline icheck">
                {!! Form::radio('tipo_camino', 'interseccion') !!} Intersección
            </label>
        </div>
    </div>
    @endif

    @if (in_array('localidad', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('localidad', 'Localidad:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            <select name="localidad_id" class="select2" style="width: 100% !important">
                @foreach($params['localidades'] as $localidad)
                <option value="{{ $localidad->id }}">{{ $localidad->nombre }} - {{ $localidad->distrito->nombre }} - {{ $localidad->distrito->departamento->nombre }} </option>
                @endforeach
            </select>
        </div>
    </div>
    @endif

    @if (in_array('codigo_postal', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('codigo_postal', 'Código Postal:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('codigo_postal', null, ['class' => 'form-control', 'placeholder' => 'Código Postal']) !!}
        </div>
    </div>
    @endif

    @if (in_array('ubicacion', $params['displayFields']))
    <div class="form-group">
        <div class="row">
            <div class="col-md-6 col-md-offset-2">
                <strong>Lugar</strong>
                <br>
                {{ $params['data']['item']->lugar }}
            </div>
            <div class="col-md-4">
                <strong>Altura</strong>
                <br>
                {{ $params['data']['item']->altura }}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-4 col-md-offset-2">
                <strong>Localidad</strong>
                <br>
                {{ $params['data']['item']->localidad->nombre }}
            </div>
            <div class="col-md-3">
                <strong>Distrito</strong>
                <br>
                {{ $params['data']['item']->localidad->distrito->nombre }}
            </div>
            <div class="col-md-3">
                <strong>Departamento</strong>
                <br>
                {{ $params['data']['item']->localidad->distrito->departamento->nombre }}
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Coordenadas</label>
        <div class="col-sm-4">
            {!! Form::text('gps_latitud', null, ['class' => 'form-control', 'readonly' => true, 'required' => true]) !!}
        </div>
        <div class="col-sm-4">
            {!! Form::text('gps_longitud', null, ['class' => 'form-control', 'readonly' => true, 'required' => true]) !!}
        </div>
        <div class="col-sm-10 col-sm-offset-2">
            <p class="help-block"><small>Para ubicar el accidente en el mapa, sólo debe hacer click en el mapa y arrastrarlo hasta que el marcador rojo quede exactamente en el lugar donde ocurrió el accidente. Puede utilizar el control de zoom a la derecha (botones "+" y "-") para acercar o alejar más el mapa.</small></p>
            <div id="coordenadas-accidente"><small>Cargando mapa...</small></div>
        </div>
    </div>
    @endif

    @if (in_array('heridos', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('heridos', 'Heridos:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('heridos', null, ['class' => 'form-control', 'placeholder' => 'Cantidad de Heridos']) !!}
        </div>
    </div>
    @endif

    @if (in_array('fallecidos', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('fallecidos', 'Fallecidos:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('fallecidos', null, ['class' => 'form-control', 'placeholder' => 'Cantidad de Fallecidos']) !!}
        </div>
    </div>
    @endif

    @if (in_array('ilesos', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('ilesos', 'Ilesos:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('ilesos', null, ['class' => 'form-control', 'placeholder' => 'Cantidad de Ilesos']) !!}
        </div>
    </div>
    @endif

    @if (in_array('participantes_siniestro', $params['displayFields']))
    <div class="form-group">
        {!! Form::label('participantes_siniestro', 'Participantes:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-8">
            {!! Form::text('participantes_siniestro', null, ['class' => 'form-control', 'placeholder' => 'Cantidad de Participantes']) !!}
        </div>
    </div>
    @endif
</div>

<div class="panel-footer">
    <div class="row">
        <div class="pull-right">
            <a href = "{{ url($params['route']) }}" class = 'btn btn-default'>Cancelar</a>
            {!! Form::submit('Guardar', ['class'=>'btn btn-primary']) !!}
        </div>
    </div>
</div>
