<div id="alerta-ocupantes-edicion" class="alert alert-warning" role="status" aria-live="polite" @if(empty($params['alertas_ocupantes'])) style="display:none" @endif>
    @foreach($params['alertas_ocupantes'] ?? [] as $alerta)
        <div>{{ $alerta['mensaje'] }}</div>
    @endforeach
</div>
