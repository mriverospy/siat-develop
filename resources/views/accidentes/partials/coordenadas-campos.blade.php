<label class="col-sm-4 control-label">Coordenadas:</label>
<div class="col-sm-8 bloque24-campos">
    <div class="bloque24-campo">
        <label for="gps_latitud">Latitud <small>A_8</small></label>
        {!! Form::text('gps_latitud', $accidenteActual ? $accidenteActual->gps_latitud : null, ['id' => 'gps_latitud', 'class' => 'form-control bloque24-coordenada', 'readonly' => true, 'required' => true]) !!}
    </div>
    <div class="bloque24-campo">
        <label for="gps_longitud">Longitud <small>A_9</small></label>
        {!! Form::text('gps_longitud', $accidenteActual ? $accidenteActual->gps_longitud : null, ['id' => 'gps_longitud', 'class' => 'form-control bloque24-coordenada', 'readonly' => true, 'required' => true]) !!}
    </div>
</div>
