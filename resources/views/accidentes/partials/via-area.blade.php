<div class="form-group">
    <label class="col-sm-2 control-label" for="tipo_camino">Tipo de vía:</label>
    <div class="col-sm-8">
        <select name="tipo_camino" id="tipo_camino" class="form-control" required>
            <option value="">Seleccionar...</option>
            @if ($accidenteActual && $accidenteActual->tipo_camino && !array_key_exists($accidenteActual->tipo_camino, config('formulario_accidente.vias')))
                <option value="{{ $accidenteActual->tipo_camino }}" selected>{{ $accidenteActual->tipo_camino }} (valor anterior)</option>
            @endif
            @foreach(config('formulario_accidente.vias') as $valor => $texto)
                <option value="{{ $valor }}" @if($accidenteActual && $accidenteActual->tipo_camino === $valor) selected @endif>{{ $texto }}</option>
            @endforeach
        </select>
        <span class="help-block">Este campo es obligatorio.</span>
    </div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label" for="area">Área:</label>
    <div class="col-sm-8">
        <select name="area" id="area" class="form-control" required>
            <option value="">Seleccionar...</option>
            @foreach(config('formulario_accidente.areas') as $valor => $texto)
                <option value="{{ $valor }}" @if($accidenteActual && $accidenteActual->area === $valor) selected @endif>{{ $texto }}</option>
            @endforeach
        </select>
        <span class="help-block">Este campo es obligatorio.</span>
    </div>
</div>
