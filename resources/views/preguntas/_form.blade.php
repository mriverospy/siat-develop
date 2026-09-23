@extends('layouts.base_layout')

@section('layout_content')
    <div class="panel {{ $params['panel_color'] }}">
        <div class="panel-heading">
            <h2>{{ $params['subtitle'] }}</h2>
            <div class="text-right">{{ $params['tipoNombre'] }}</div>
        </div>

        <div class="panel-body">

            @if (isset($params['data']['item'] ))
                <!-- EDIT -->
                {!! Form::model($params['data']['item'], ['method'=>'PATCH','url'=>[$params['route'], $params['data']['item']->id]] ) !!}
            @else
                <!-- CREATE -->
                {!! Form::open(['url'=>$params['route']]) !!}
            @endif
                <div class="form-horizontal">
                    <div class="form-group">
                        <!-- TIPO -->
                        {!! Form::hidden('tipo', $params['tipo'] ) !!}

                        {!! Form::label('codigo', 'Código de pregunta:', ['class' => 'control-label']) !!}
                        {!! Form::text('codigo', null, ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'Ej.: A-1']) !!}

                        <!-- PREGUNTA -->
                        {!! Form::label('pregunta', 'Pregunta:', ['class' => 'control-label']) !!}
                        {!! Form::text('pregunta', null, ['class' => 'form-control', 'autofocus' => 'true', 'placeholder' => 'Pregunta a realizar (Ej.: Color del vehículo?)']) !!}

                        <!-- OPCIONES -->
                        @if ($params['tipo'] == 'check' or $params['tipo'] == 'radio')
                            @php
                                $filas = isset($params['data']['item'])
                                    ? $params['data']['item']->detalles()->get()->toArray()
                                    : [];
                                if (!$filas && isset($params['data']['item'])) {
                                    foreach (explode('|', $params['data']['item']->opciones ?? '') as $opcion) {
                                        if ($opcion !== '') $filas[] = ['opcion' => $opcion, 'codigo' => '', 'id' => ''];
                                    }
                                }
                                $filas = old('detalles', $filas ?: [['opcion' => '', 'codigo' => '', 'id' => '']]);
                            @endphp
                            <label class="control-label">Opciones:</label>
                            <table class="table" id="detalles-pregunta">
                                <thead><tr><th>Código</th><th>Opción</th><th>Estado</th><th></th></tr></thead>
                                <tbody>
                                @foreach ($filas as $indice => $detalle)
                                    <tr>
                                        <td><input type="hidden" name="detalles[{{ $indice }}][id]" value="{{ $detalle['id'] ?? '' }}">
                                            <input aria-label="Código de opción" class="form-control" name="detalles[{{ $indice }}][codigo]" maxlength="50" value="{{ $detalle['codigo'] ?? '' }}"></td>
                                        <td><input aria-label="Texto de opción" class="form-control" name="detalles[{{ $indice }}][opcion]" maxlength="255" required value="{{ $detalle['opcion'] }}"></td>
                                        <td><select aria-label="Estado de opción" class="form-control" name="detalles[{{ $indice }}][activo]">
                                            <option value="1" {{ ($detalle['activo'] ?? true) ? 'selected' : '' }}>Activo</option>
                                            <option value="0" {{ !($detalle['activo'] ?? true) ? 'selected' : '' }}>Inactivo</option>
                                        </select></td>
                                        <td><button type="button" class="btn btn-default subir-opcion" aria-label="Subir opción">↑</button>
                                            <button type="button" class="btn btn-danger quitar-opcion">Quitar</button></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <button type="button" id="agregar-opcion" class="btn btn-default">Agregar opción</button>
                            <p class="help-block">Los códigos pueden quedar vacíos para opciones anteriores. No repita códigos ni textos dentro de la pregunta. Las opciones inactivas no se ofrecen para nuevas respuestas; puede reactivarlas aquí.</p>
                            @foreach ($errors->getMessages() as $campo => $mensajes)
                                @if ($campo === 'codigo' || strpos($campo, 'detalles') === 0)
                                    @foreach ($mensajes as $mensaje)<p class="text-danger">{{ $mensaje }}</p>@endforeach
                                @endif
                            @endforeach
                        @endif

                        {!! Form::label('instrucciones', 'Instrucciones:', ['class' => 'control-label']) !!}
                        {!! Form::text('instrucciones', null, ['class' => 'form-control', 'autofocus' => 'true', 'placeholder' => 'Instrucciones para el usuario sobre cómo contestar esta pregunta']) !!}

                        @if ($params['tipo'] == 'check' or $params['tipo'] == 'radio')
                            {!! Form::label('mostrar_selector', 'Mostrar Otro?', ['class' => 'control-label']) !!}
                            <div class="radio">
                                <label>
                                    {!! Form::radio('mostrar_otro', 1) !!} Sí
                                </label>
                                <label>
                                    {!! Form::radio('mostrar_otro', 0) !!} No
                                </label>
                            </div>
                            <p class="help-block">Puede incluir una opción adicional "Otro" para que el usuario ingrese una opción no disponible en la lista de opciones.</p>

                            {!! Form::label('titulo_otro', 'Título "Otro":', ['class' => 'control-label']) !!}
                            {!! Form::text('titulo_otro', null, ['class' => 'form-control', 'autofocus' => 'true', 'placeholder' => 'Por defecto se muestra "Otro", puede personalizar el título aquí']) !!}
                            <p class="help-block">Puede definir un título personalizado, por defecto, se muestra "Otro" en el Formulario de Carga.</p>
                        @endif

                        @if ($params['tipo'] == 'radio')
                            {!! Form::label('mostrar_selector', 'Mostrar como Selector?', ['class' => 'control-label']) !!}
                            <div class="radio">
                                <label>
                                    {!! Form::radio('mostrar_selector', 1) !!} Sí
                                </label>
                                <label>
                                    {!! Form::radio('mostrar_selector', 0) !!} No
                                </label>
                            </div>
                            <p class="help-block">Mostrar la pregunta como un selector con buscador. Recomendado si la pregunta tiene muchas opciones de respuesta.</p>
                        @endif



                        {!! Form::label('activo_si', 'Activo?', ['class' => 'control-label']) !!}
                        @php
                            $activo = (string) old('activo', isset($params['data']['item']) ? (int) $params['data']['item']->activo : 1);
                        @endphp
                        <div class="radio">
                            <label>
                                <input type="radio" name="activo" id="activo_si" value="1" {{ $activo === '1' ? 'checked' : '' }}> Sí
                            </label>
                            <label>
                                <input type="radio" name="activo" id="activo_no" value="0" {{ $activo === '0' ? 'checked' : '' }}> No
                            </label>
                        </div>
                        @error('activo')<p class="text-danger">{{ $message }}</p>@enderror

                            <!-- Nuevo campo para "Obligatorio" -->

                            {!! Form::label('obligatorio', 'Obligatorio?', ['class' => 'control-label']) !!}
                            <div class="radio">
                                <label>
                                    {!! Form::radio('obligatorio', 1) !!} Sí
                                </label>
                                <label>
                                    {!! Form::radio('obligatorio', 0) !!} No
                                </label>
                            </div>
                            <p class="help-block">Hacer que esta pregunta sea obligatoria para el usuario.</p>
                    </div>
                </div>

                <div class="panel-footer">
                    <div class="row">
                        <div class="pull-right">
                            <a href="{{ url($params['route']) }}" class="btn btn-default">Cancelar</a>
                            {!! Form::submit('Guardar', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@section('footer-javascript')
    @parent
    <script type="text/javascript" charset="utf-8">
        jQuery(document).ready(function($) {
            var tabla = $('#detalles-pregunta');
            function renumerar() {
                tabla.find('tbody tr').each(function (indice) {
                    $(this).find('input, select').each(function () {
                        this.name = this.name.replace(/detalles\[\d+\]/, 'detalles[' + indice + ']');
                    });
                });
            }
            $('#agregar-opcion').on('click', function () {
                var fila = tabla.find('tbody tr:first').clone();
                fila.find('input').val('');
                fila.find('select').val('1');
                tabla.find('tbody').append(fila);
                renumerar();
            });
            tabla.on('click', '.quitar-opcion', function () {
                if (tabla.find('tbody tr').length > 1) $(this).closest('tr').remove();
                else {
                    $(this).closest('tr').find('input').val('');
                    $(this).closest('tr').find('select').val('1');
                }
                renumerar();
            });
            tabla.on('click', '.subir-opcion', function () {
                var fila = $(this).closest('tr');
                fila.insertBefore(fila.prev());
                renumerar();
            });
        })
    </script>
@endsection
