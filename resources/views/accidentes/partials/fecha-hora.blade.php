<div class="form-group">
    <label for="fecha_siniestro" class="col-sm-4 control-label">Fecha y hora:</label>
    <div class="col-sm-8 bloque24-campos">
        <div class="bloque24-campo">
            <label for="fecha_siniestro">Fecha <small>A_2</small></label>
            <input id="fecha_siniestro" name="fecha_siniestro" type="date" value="{{ $accidenteActual ? $accidenteActual->fecha_accidente->format('Y-m-d') : '' }}" class="form-control bloque24-fecha" required>
        </div>
        <div class="bloque24-campo">
            <label for="hora_siniestro">Hora <small>A_3</small></label>
            <input id="hora_siniestro" name="hora_siniestro" type="time" step="60" value="{{ $accidenteActual ? $accidenteActual->fecha_accidente->format('H:i') : '' }}" class="form-control bloque24-hora" required>
        </div>
    </div>
</div>
