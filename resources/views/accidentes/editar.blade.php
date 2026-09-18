@extends('layouts.base_layout')

@section('header-css')
    @parent
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/sweet-alert.css') }}">
@endsection

@section('header-js')
    @parent
    <script type="text/javascript" src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"></script>
@endsection



@section('layout_content')

    <script type="text/javascript">
        var coordenadasAccidente = {

            lat: '{{ $params['accidente']->gps_latitud }}',
            lng: '{{ $params['accidente']->gps_longitud }}'
        }
        var edicionAccidente = {
            agrupadoresAccidente: {!! json_encode($params['agrupadores_accidente']) !!},
            grupoPreguntas: {!! json_encode($params['grupo_preguntas']) !!},
            respuestasAccidente: {!! json_encode($params['respuestas_accidente']) !!}
        }
    </script>

    <script type="x/template" id="text">
        <div class="form-group">
            <label class="col-sm-2 control-label">@{{ pregunta.pregunta }}</label>
            <div class="col-sm-6">
                <input
                    type="text"
                    name="respuestas[@{{ pregunta.pregunta_id }}]"
                    class="form-control"
                    v-model="valorRespuesta"
                    value="@{{ (respuesta != null) ? respuesta.respuestas : '' }}"
                    />
                <span class="help-block" v-if="pregunta.instrucciones != ''">@{{ pregunta.instrucciones }}</span>
            </div>
            <div class="col-sm-4">
                <a href="#" class="btn btn-primary" @click.prevent="guardar()"><i class="fa fa-fw fa-floppy-o"></i> Guardar</a>
                <a href="#" class="btn btn-danger" @click.prevent="borrar()" v-if="respuesta != null"><i class="fa fa-fw fa-trash"></i> Borrar</a>
            </div>
        </div>
    </script>

    <script type="x/template" id="number">
        <div class="form-group">
            <label class="col-sm-2 control-label">@{{ pregunta.pregunta }}</label>
            <div class="col-sm-6">
                <input
                    type="number"
                    name="respuestas[@{{ pregunta.pregunta_id }}]"
                    class="form-control"
                    v-model="valorRespuesta"
                    value="@{{ (respuesta != null) ? respuesta.respuestas : null }}"
                    />
                <span class="help-block" v-if="pregunta.instrucciones != ''">@{{ pregunta.instrucciones }}</span>
                <img v-if="pregunta.pregunta_id == 33" class="img-responsive img-rounded" style="width: 50%;" src="{{ asset('img/ubicacion-pasajeros.png') }}" />
            </div>
            <div class="col-sm-4">
                <a href="#" class="btn btn-primary" @click.prevent="guardar()"><i class="fa fa-fw fa-floppy-o"></i> Guardar</a>
                <a href="#" class="btn btn-danger" @click.prevent="borrar()" v-if="respuesta != null"><i class="fa fa-fw fa-trash"></i> Borrar</a>
            </div>
        </div>
    </script>

    <script type="x/template" id="radio">
        <div class="form-group">
            <label class="col-sm-2 control-label">@{{ pregunta.pregunta }}</label>
            <div class="col-sm-6 icheck-square">
                <div class="radio" v-for="opcion in pregunta.opciones">
                    <label>
                        <input
                            type="radio"
                            name="respuestas[@{{ pregunta.pregunta_id }}][]"
                            value="@{{ opcion }}"
                            v-model="valorRespuesta"
                            :checked="opcion == respuesta.respuestas"
                            /> @{{ opcion }}
                    </label>
                </div>
                <div v-if="pregunta.instrucciones != ''">
                    <span class="help-block">@{{ pregunta.instrucciones }}</span>
                </div>
            </div>
            <div class="col-sm-4">
                <a href="#" class="btn btn-primary" @click.prevent="guardar()"><i class="fa fa-fw fa-floppy-o"></i> Guardar</a>
                <a href="#" class="btn btn-danger" @click.prevent="borrar()" v-if="respuesta != null"><i class="fa fa-fw fa-trash"></i> Borrar</a>
            </div>
        </div>
    </script>

    <script type="x/template" id="check">
        <div class="form-group">
            <label class="col-sm-2 control-label">@{{ pregunta.pregunta }}</label>
            <div class="col-sm-6 icheck-square">
                <div class="checkbox" v-for="opcion in pregunta.opciones">
                    <label>
                        <input
                            type="checkbox"
                            name="respuestas[@{{ pregunta.pregunta_id }}][]"
                            value="@{{ opcion }}"
                            v-model="valorRespuesta"
                            :checked="respuesta.respuestas.includes(opcion)"
                            /> @{{ opcion }}
                    </label>
                </div>
                <div v-if="pregunta.instrucciones != ''">
                    <span class="help-block">@{{ pregunta.instrucciones }}</span>
                </div>
            </div>
            <div class="col-sm-4">
                <a href="#" class="btn btn-primary" @click.prevent="guardar()"><i class="fa fa-fw fa-floppy-o"></i> Guardar</a>
                <a href="#" class="btn btn-danger" @click.prevent="borrar()" v-if="respuesta != null"><i class="fa fa-fw fa-trash"></i> Borrar</a>
            </div>
        </div>
    </script>

    <script type="x/template" id="date">
        <div class="form-group">
            <label class="col-sm-2 control-label">@{{ pregunta.pregunta }}</label>
            <div class="col-sm-6">
                <input
                    type="text"
                    name="respuestas[@{{ pregunta.pregunta_id }}]"
                    class="form-control datepicker"
                    value="@{{ respuesta. }}"
                    v-model="valorRespuesta"
                    value="@{{ (respuesta != null) ? respuesta.respuesta_fecha : '' }}"
                    />
                <span class="help-block" v-if="pregunta.instrucciones != ''">@{{ pregunta.instrucciones }}</span>
            </div>
            <div class="col-sm-4">
                <a href="#" class="btn btn-primary" @click.prevent="guardar()"><i class="fa fa-fw fa-floppy-o"></i> Guardar</a>
                <a href="#" class="btn btn-danger" @click.prevent="borrar()" v-if="respuesta != null"><i class="fa fa-fw fa-trash"></i> Borrar</a>
            </div>
        </div>
    </script>

    {!! Form::open(['route' => ['accidentes.actualizar', $params['accidente']->id]]) !!}
        <div id="editar-accidente" v-cloak>
            <div class="panel {{ $params['panel_color'] }}">
                <div class="panel-heading">
                    <h2>{{ $params['subtitle'] }}</h2>
                    <div class="pull-right">
                        <a class="btn btn-default" @click.prevent="editarBloque()" v-bind:class="{ 'active': modoEdicion == 'bloque' }">
                            <i class="fa fa-fw fa-circle" v-show="modoEdicion == 'bloque'"></i>Editar Bloque 24 Horas
                        </a>
                        <a class="btn btn-default" @click.prevent="editarRespuestas()" v-bind:class="{ 'active': modoEdicion == 'respuestas' }">
                            <i class="fa fa-fw fa-circle" v-show="modoEdicion == 'respuestas'"></i>Editar Respuestas
                        </a>
                        <a href="{{ url($params['route']) }}" class="btn btn-default">Cancelar</a>
                        <a href="#" @click.prevent="guardarCambios()" class="btn btn-primary">Guardar</a>
                    </div>
                </div>
                <div class="panel-body" v-show="setEdiciones.length > 0">
                    <ul class="list-group">
                        <li class="list-group-item" v-for="edicion in setEdiciones" track-by="$index">
                            <a href="#" @click.prevent="borrarEdicion(edicion)"><i class="fa fa-times-circle fa-fw"></i></a>
                            <span v-show="edicion.modoEdicion == 'agregar'">
                                <span class="label label-primary">AGREGAR</span>
                                Se va a agregar la respuesta <strong>@{{ edicion.respuestaNueva }}</strong> a la pregunta <strong>@{{ edicion.pregunta }}</strong>
                            </span>

                            <span v-show="edicion.modoEdicion == 'modificar'">
                                <span class="label label-warning">MODIFICAR</span>
                                Se va a modificar la respuesta de la pregunta <strong>@{{ edicion.pregunta }}</strong>, de <strong>@{{ edicion.respuestaAnterior }}</strong> a <strong>@{{ edicion.respuestaNueva }}</strong>
                            </span>

                            <span v-show="edicion.modoEdicion == 'borrar'">
                                <span class="label label-danger">BORRAR</span>
                                Se va a borrar la respuesta <strong>@{{ edicion.respuestaAnterior }}</strong> de la pregunta <strong>@{{ edicion.pregunta }}</strong>
                            </span>
                        </li>
                    </ul>
                </div>
                <div class="panel-body" v-show="modoEdicion == 'bloque'">
                    <div class="form-horizontal row-border">
                        <div class="form-group">
                            {!! Form::label('caratula', 'Carátula:', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('caratula', $params['accidente']->caratula, ['class' => 'form-control', 'placeholder' => 'Carátula']) !!}
                            </div>
                        </div>


                        <div class="form-group">
                            {!! Form::label('fecha_accidente', 'Fecha y Hora:', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('fecha_accidente', $params['accidente']->fecha_accidente, ['class' => 'form-control datetime-picker', 'placeholder' => 'Fecha y Hora del Accidente', 'data-date-language' => 'es']) !!}
                                <span class="help-block">Fecha y Hora del Accidente</span>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('lugar', 'Lugar:', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('lugar', $params['accidente']->lugar, ['class' => 'form-control', 'placeholder' => 'Lugar o Dirección']) !!}
                            </div>
                        </div>

                        @include('accidentes.partials.via-area', ['accidenteActual' => $params['accidente']])

                        <div class="form-group">
                            {!! Form::label('localidad', 'Barrio/Localidad:', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-8">
                                <select name="localidad_id" class="select2" style="width: 100% !important">
                                    @foreach($params['localidades'] as $localidad)
                                    <option
                                        value="{{ $localidad->id }}"
                                        @if ($localidad->id == $params['accidente']->localidad_id) selected="selected" @endif
                                        >
                                        {{ $localidad->nombre }} - {{ $localidad->distrito->nombre }} - {{ $localidad->distrito->departamento->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Coordenadas</label>
                            <div class="col-sm-4">
                                {!! Form::text('gps_latitud', $params['accidente']->gps_latitud, ['class' => 'form-control', 'readonly' => true, 'required' => true]) !!}
                            </div>
                            <div class="col-sm-4">
                                {!! Form::text('gps_longitud', $params['accidente']->gps_longitud, ['class' => 'form-control', 'readonly' => true, 'required' => true]) !!}
                            </div>
                            <div class="col-sm-10 col-sm-offset-2">
                                <p class="help-block"><small>Para ubicar el accidente en el mapa, sólo debe hacer click en el mapa y arrastrarlo hasta que el marcador rojo quede exactamente en el lugar donde ocurrió el accidente. Puede utilizar el control de zoom a la derecha (botones "+" y "-") para acercar o alejar más el mapa.</small></p>
                                <div id="coordenadas-accidentes"><small>Cargando mapa...</small></div>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('heridos', 'Heridos:', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('heridos', $params['accidente']->heridos, ['class' => 'form-control', 'placeholder' => 'Cantidad de Heridos']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('fallecidos', 'Fallecidos:', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('fallecidos', $params['accidente']->fallecidos, ['class' => 'form-control', 'placeholder' => 'Cantidad de Fallecidos']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('ilesos', 'Ilesos:', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('ilesos', $params['accidente']->ilesos, ['class' => 'form-control', 'placeholder' => 'Cantidad de Ilesos']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('participantes_siniestro', 'Participantes:', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('participantes_siniestro', $params['accidente']->participantes_siniestro, ['class' => 'form-control', 'placeholder' => 'Cantidad de Participantes']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body" v-show="modoEdicion == 'respuestas'">
                    <div class="form-horizontal row-border">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Grupo de Preguntas</label>
                            <div class="col-sm-8">
                                <div class="btn-group-vertical">
                                    <a href="#" class="btn btn-default"
                                        v-for="grupoPregunta in grupoPreguntas"
                                        v-bind:class="{ 'active': grupoPreguntaSeleccionado && grupoPregunta.id == grupoPreguntaSeleccionado.id }"
                                        @click.prevent="seleccionarGrupoPreguntas(grupoPregunta)"
                                        >@{{ grupoPregunta.grupo_nombre }} - ID @{{ grupoPregunta.id }}</a>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" v-show="agrupadores.length > 0">
                            <label class="col-sm-2 control-label">Agrupador</label>
                            <div class="col-sm-8">
                                <div class="btn-group">
                                    <a href="#" class="btn btn-default"
                                        v-for="agrupador in agrupadores"
                                        v-bind:class="{ 'active': agrupador.agrupador == agrupadorSeleccionado.agrupador }"
                                        @click.prevent="seleccionarAgrupador(agrupador)"
                                        >@{{ agrupador.titulo }}</a>
                                    <a href="#" class="btn btn-info" @click.prevent="nuevoAgrupador()"><i class="fa fa-fw fa-plus"></i>Agregar</a>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" v-show="mostrarPregunta">
                            <label class="col-sm-2 control-label">Pregunta</label>
                            <div class="col-sm-8">
                                <div class="btn-group">
                                    <a href="#" class="btn btn-default"
                                        v-for="pregunta in grupoPreguntaSeleccionado.preguntas"
                                        v-bind:class="{ 'active': pregunta.pregunta_id == preguntaSeleccionada.pregunta_id }"
                                        @click.prevent="seleccionarPregunta(pregunta)"
                                        >@{{ pregunta.pregunta }}</a>
                                </div>
                            </div>
                        </div>

                        <component
                            v-show="preguntaSeleccionada"
                            :is="preguntaSeleccionada.tipo"
                            :pregunta="preguntaSeleccionada"
                            :respuesta="respuestaSeleccionada"
                            >
                        </component>
                    </div>
                </div>
                {{-- <div class="panel-footer">
                    <div class="row text-center">
                        <a href="{{ url($params['route']) }}" class="btn btn-default">Cancelar</a>
                        <a href="#" @click.prevent="guardarCambios()" class="btn btn-primary">Guardar</a>
                    </div>
                </div> --}}
            </div>
        </div>
    {!! Form::close() !!}
@endsection

@section('after-vendor-javascript')
    @parent
    @if (env('APP_DEBUG'))
    <script src="{{ asset('js/underscore-min.js') }}"></script>
    <script src="{{ asset('js/sweet-alert.min.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script>
        Vue.config.debug = true;
    </script>
    @else
    <script src="{{ asset('js/vue.min.js') }}"></script>
    @endif
@endsection

@section('after-app-javascript')
    @parent
    <script src="{{ asset('js/editar_accidente.js?id=form70-2026') }}"></script>
@endsection

@section('footer-javascript')
    @parent
    <script type="text/javascript" charset="utf-8">
        jQuery(document).ready(function($) {
            var windowHeight = $(window).height();
            $('#wrapper').css({
                'min-height': windowHeight + 'px'
            });
            if ($('select.select2').size()) {
                $('select.select2').select2({
                    width: '100%',
                    placeholder: 'Seleccione una opción...',
                });
            }
        })
    </script>

<script type="text/javascript">
        var setAccidenteLatLng = function(lat, lng) {
            $('input[name=gps_latitud]').val(lat);
            $('input[name=gps_longitud]').val(lng);
        }

        jQuery(document).ready(function($) {


            let lat = $('input[name=gps_latitud]').val();
            let lng = $('input[name=gps_longitud]').val();


            if (!lat || !lng) {
                lat = -25.273197650011156;
                lng = -57.17451948494272;
            }


            if ($('#coordenadas-accidentes').length > 0) {
                console.log("Element found");

                let opsm = L.tileLayer("http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    maxZoom: 18,
                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                });

                let map = L.map('coordenadas-accidentes', {
                    center: [lat, lng],
                    zoom: 7,
                    layers: [opsm]
                });

                let marker = L.marker([lat, lng]).addTo(map);

                // Por defecto el mapa queda desabilitado
                map.dragging.disable();
                map.scrollWheelZoom.disable();
                map.zoomControl.remove();
                map.on('moveend', function (e) {

                    if (map.dragging.enabled()) {
                        let center = map.getCenter();
                        console.log(center);
                        marker.setLatLng(center);
                        // Actualizacion de cordenadas cque funcione con el boton
                        setAccidenteLatLng(center.lat, center.lng);
                    }
                });

                // Boton para habilitar/desabilitar
                let toggleButton = $('<button/>', {
                    text: 'Modificar',
                    click: function(e) {
                        e.preventDefault(); // Previene que se envie el formulario
                        map.dragging.enabled() ? disableEditing() : enableEditing();
                    },
                    css: {
                        position: 'absolute',
                        top: '10px',
                        left: '10px',
                        zIndex: '1000',
                        fontSize: '16px', // Adjust the font size to make it bigger
                        padding: '10px 15px', // Adjust padding to make the button bigger
                    },
                }).appendTo('#coordenadas-accidentes'); // Colocar en el mismo contenedor

                function enableEditing() {
                    map.dragging.enable();
                    map.scrollWheelZoom.enable();
                    toggleButton.text('Guardar Ubicacion'); //Cambia texto de edicion mientras se edita
                }

                function disableEditing() {
                    map.dragging.disable();
                    map.scrollWheelZoom.disable();
                    toggleButton.text('Modificar Ubicacion'); //Texto del boton cuando mapa esta desabilitado
                }
            }
        });
    </script>
@endsection
