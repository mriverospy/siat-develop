<div class="form-group">
    <label class="col-sm-4 control-label">{{ $pregunta->pregunta }}</label>
    <div class="col-sm-8">
        {!! Form::text('respuestas[' . $pregunta->id . ']', null, ['class' => 'form-control', 'type' => 'number', 'inputmode' => 'numeric', 'min' => 0, 'pattern' => '[0-9]*']) !!}
        @if ($pregunta->instrucciones)
        <span class="help-block">{{ $pregunta->instrucciones }}</span>
        @endif
    </div>
</div>
