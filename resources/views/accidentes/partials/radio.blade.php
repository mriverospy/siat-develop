<div class="form-group">
    <label class="col-sm-4 control-label">{{ $pregunta->pregunta }}</label>
    <div class="col-sm-8 icheck-square">
        @foreach (explode('|', $pregunta->opciones) as $opcion)
        <div class="radio blue icheck">
            <label>
                <input type="radio" name="respuestas[{{ $pregunta->id }}]" value="{{ $opcion }}"> {{ $opcion }}
            </label>
        </div>
        @endforeach
    </div>
    @if ($pregunta->instrucciones)
    <div class="col-sm-8 col-sm-offset-4">
        <span class="help-block">{{ $pregunta->instrucciones }}</span>
    </div>
    @endif
</div>
