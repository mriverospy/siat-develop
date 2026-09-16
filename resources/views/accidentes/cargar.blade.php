@extends('carga_accidente')

@section('content')
    <script>
        var siat = {
            secciones: {!! $secciones !!},
            reglas: @json(config("formulario_accidente")),
            departamentos: {!! $departamentos !!}
        };
        var departamentos = {!! $departamentos !!};
    </script>

    <div class="loading container text-center">
        <br />
        <br />
        <i class="fa fa-refresh fa-spin fa-5x"></i>
        <br />
        <br />
        Cargando...
    </div>

    {!! Form::open(['route' => 'cargar-respuestas', 'id' => 'cargar-accidente', 'class' => 'hide', '@submit.prevent' => 'saveForm()']) !!}
        <div id="form-carga" class="col-xs-12 col-sm-12 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">
            <div class="row evento-header">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                    <a href="{{ url('accidentes/') }}" class="btn btn-default">&laquo; Volver</a>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                    <h1>Cargar Accidente</h1>
                </div>
                <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3 text-center">
                    <small>
                        Usuario
                        <br />
                        <strong>{{ $currentUser->nombreCompleto() }}</strong>
                    </small>
                </div>
                <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3 text-center">
                    <small>
                        Institución/Sede
                        <br />
                        <strong>{{ $currentUser->sede->institucion->nombre }}/{{ $currentUser->sede->nombre }}</strong>
                    </small>
                </div>
                <div class="col-xs-6 col-sm-2 col-md-2 col-lg-2 text-center">
                    <small>
                        Fecha
                        <br />
                        <strong>{{ date('d/m/Y') }}</strong>
                    </small>
                </div>
                <div class="col-xs-6 col-sm-2 col-md-2 col-lg-2 text-center">
                    <small>
                        Hora
                        <br />
                        <strong>{{ date('h:ia') }}</strong>
                    </small>
                </div>
            </div>

            <div class="row" v-show="alert.message != ''">
                <div class="alert" v-bind:class="{ 'alert-success': alert.type == 'success', 'alert-info': alert.type == 'info', 'alert-warning': alert.type == 'warning', 'alert-danger': alert.type == 'danger' }">
                    @{{ alert.message }}
                </div>
            </div>

            <div class="row" v-show="showForm" style="margin-bottom: 1em;">
                <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 text-center">
                    @{{ currentIndex + 1 }} de @{{ secciones.length }}
                </div>
                <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 text-center">
                    <select v-model="currentIndex" class="form-control">
                        <option v-for="seccion in secciones" :value="seccion.index">@{{ seccion.seccion_nombre }}</option>
                    </select>
                </div>
                <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 text-center">
                    <a class="btn btn-default btn-previous" href="#" @click.prevent="previousQuestion()" v-show="canGoPrevious"><i class="fa fa-arrow-left fa-fw"></i></a>
                    <a class="btn btn-primary btn-next" href="#" @click.prevent="nextQuestion()" v-show="canGoNext">Siguiente<i class="fa fa-arrow-right fa-fw"></i></a>
                </div>
                <div class="col-xs-4 col-sm-2 col-md-2 col-lg-2 text-right">
                    <a class="btn btn-midnightblue" href="#" @click.prevent="saveForm()"><i class="fa fa-floppy-o fa-fw"></i>Guardar</a>
                </div>
            </div>

            <div class="panel panel-danger" v-show="errors && errors.length > 0" style="margin-bottom: 1em;">
                <div class="panel-heading">
                    <h2>Se encontraron errores al intentar guardar sus datos!</h2>
                </div>
                <ul class="list-group mb0">
                    <li class="list-group-item text-danger" v-for="error in errors"><i class="fa fa-exclamation-triangle fa-fw"></i>@{{ error.mensaje }}</li>
                </ul>
            </div>

            <component
                v-for="seccion in secciones"
                v-show="currentIndex == seccion.index && showForm"
                :is="seccion.tipo"
                :agrupado="seccion">
            </component>
            <div class="row" v-show="showForm">
                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-center">
                    @{{ currentIndex + 1 }} de @{{ secciones.length }}
                </div>
                <div class="col-xs-6 col-sm-4 col-md-4 col-lg-4 text-center">
                    <a class="btn btn-default btn-previous" href="#" @click.prevent="previousQuestion()" v-show="canGoPrevious"><i class="fa fa-arrow-left fa-fw"></i></a>
                    <a class="btn btn-primary btn-next" href="#" @click.prevent="nextQuestion()" v-show="canGoNext">Siguiente<i class="fa fa-arrow-right fa-fw"></i></a>
                    <div class="hidden-xs">
                        <br />
                        <small>También puede utilizar las teclas <span class="fa-stack fa-lg"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x fa-inverse"></i></span> y <span class="fa-stack fa-lg"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-arrow-right fa-stack-1x fa-inverse"></i></span></small>
                    </div>
                </div>
                <div class="col-xs-6 col-sm-4 col-md-4 col-lg-4 text-center">
                    <a class="btn btn-midnightblue" href="#" @click.prevent="saveForm()"><i class="fa fa-floppy-o fa-fw"></i>Guardar</a>
                </div>
            </div>

            <div class="row" v-show="savingForm">
                <div class="col-xs-12 text-center">
                    <p><strong>Guardando datos...</strong></p>
                    <div class="progress progress-lg progress-striped active">
                        <div class="progress-bar progress-bar-inverse" style="width: 100%"></div>
                    </div>
                </div>
            </div>

            <div class="row" v-show="showSuccess">
                <div class="panel panel-green">
                    <div class="panel-heading">
                        <h2>Carga Finalizada!</h2>
                    </div>
                    <div class="panel-body">
                        <h3>Gracias {{ $currentUser->nombreCompleto() }}!</h3>
                        <p>Los datos que ingresaste para este accidente se registraron correctamente.</p>
                        <p class="text-center">
                            <a class="btn btn-success btn-lg" href="{{ url('accidentes') }}">Volver</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}

    <script type="x/template" id="agrupado">
        <div class="agrupado">
            <component
                v-for="grupo in agrupado.grupos"
                :is="grupo.tipo"
                :grupo="grupo"
                :seccion="agrupado.index"></component>
        </div>
    </script>

    <script type="x/template" id="grupo">
        <div class="panel panel-grape">
            <div class="panel-heading">
                <h2>@{{ grupo.grupo_nombre }}</h2>
                <div class="pull-right" v-show="grupo.duplicable">
                    <a href="#" class="btn btn-default btn-xs" @click.prevent="duplicarGrupo(grupo.id, grupo)">
                        <i class="fa fa-files-o fa-fw"></i>Duplicar Grupo
                    </a>
                    <a href="#" class="btn btn-warning btn-xs" @click.prevent="eliminarGrupo(grupo.id, grupo)">
                        <i class="fa fa-files-o fa-fw"></i>Eliminar
                    </a>
                </div>
            </div>
            <div class="panel-body">
                <div v-for="pregunta in grupo.preguntas" class="form70-question" :data-question="pregunta.pregunta_id" :data-group="grupo.duplicable ? grupo.id * 1000000 + grupo.agrupador + 1 : grupo.id">
                <component
                    :is="pregunta.tipo"
                    :grupo="grupo.id"
                    :question="pregunta"
                    :duplicable="grupo.duplicable"
                    :agrupador="grupo.agrupador"
                    :index="$index">
                </component>
                <span class="help-block form70-required" style="display:none">Este campo es obligatorio.</span>
                </div>
            </div>
        </div>
    </script>

    <script type="x/template" id="cabecera">
        <div class="panel panel-brown">
            <div class="panel-heading">
                <h2>Bloque 24 Horas</h2>
            </div>
            <div class="panel-body">
                <div class="form-horizontal row-border">
                    <div class="form-group">
                        {!! Form::label('caratula', 'Carátula:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-8">
                            {!! Form::text('caratula', null, ['class' => 'form-control', 'placeholder' => 'Carátula']) !!}
                            <span class="help-block">Este campo es requerido.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('expediente_judicial', 'Nro. Expediente:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-8">
                            {!! Form::text('expediente_judicial', null, ['class' => 'form-control', 'placeholder' => 'Número de Expediente Judicial']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('sumario_policial', 'Nro. Sumario:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-8">
                            {!! Form::text('sumario_policial', null, ['class' => 'form-control', 'placeholder' => 'Número de Sumario Policial']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('otro', 'Otro:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-8">
                            {!! Form::text('otro', null, ['class' => 'form-control', 'placeholder' => '']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('dependencia', 'Dependencia:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-8">
                            {!! Form::text('dependencia', null, ['class' => 'form-control', 'placeholder' => 'Dependencia']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('fecha_accidente', 'Fecha y Hora:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-8">
                            {!! Form::text('fecha_accidente', null, ['class' => 'form-control datetime-picker', 'placeholder' => 'Fecha y Hora del Accidente', 'data-date-language' => 'es']) !!}
                            <span class="help-block">Fecha y Hora del Accidente. Este campo es requerido.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('lugar', 'Ubicación del siniestro:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-8">
                            {!! Form::text('lugar', null, ['class' => 'form-control', 'placeholder' => 'Lugar o Dirección']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('altura', 'Altura:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-8">
                            {!! Form::text('altura', null, ['class' => 'form-control', 'placeholder' => 'Altura']) !!}
                            <span class="help-block">Altura o Kilómetro</span>
                        </div>
                    </div>

                    @include('accidentes.partials.via-area', ['accidenteActual' => null])

                    <div class="form-group">
                        {!! Form::label('departamento', 'Departamento:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-8">
                            <select name="departamento_id" class="form-control" v-model="departamento_id">
                                <option value="">Seleccione Departamento...</option>
                                <option v-for="departamento in departamentos" v-bind:value="departamento.id">@{{ departamento.nombre }}</option>
                            </select>
                            <span class="help-block">Este campo es requerido.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('distrito', 'Distrito:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-8">
                            <select name="distrito_id" class="form-control" v-model="distrito_id">
                                <option value="">Seleccione Distrito...</option>
                                <option v-for="distrito in distritos" v-bind:value="distrito.id">@{{ distrito.nombre }}</option>
                            </select>
                            <span class="help-block" v-html="distrito_text">Este campo es requerido.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('localidad', 'Localidad:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-8">
                            <select name="localidad_id" class="form-control" v-model="localidad_id">
                                <option value="">Seleccione Localidad...</option>
                                <option v-for="localidad in localidades" v-bind:value="localidad.id">@{{ localidad.nombre }}</option>
                            </select>
                            <span class="help-block" v-html="localidad_text">Este campo es requerido.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('codigo_postal', 'Código Postal:', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-8">
                            {!! Form::text('codigo_postal', null, ['class' => 'form-control', 'placeholder' => 'Código Postal']) !!}
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
                            <span class="help-block">Estos campos son requeridos.</span>
                            <input type="hidden" name="ubicacion_confirmada" value="0">
                            <div id="coordenadas-accidente" data-geografia-url="{{ url('accidentes/geografia') }}" style="width: 100%; height: 400px;"></div>
                            <p id="mapa-estado" class="help-block" role="status" aria-live="polite">Seleccione un departamento para ubicar el siniestro.</p>
                            <button type="button" id="mapa-modificar" class="btn btn-default" disabled>Modificar ubicación</button>
                            <button type="button" id="mapa-confirmar" class="btn btn-primary" disabled>Confirmar ubicación</button>
                            <button type="button" id="mapa-reintentar" class="btn btn-default" style="display:none">Reintentar carga del límite</button>
                            <span class="help-block">Seleccione el distrito, ajuste el punto y confirme la ubicación exacta del siniestro. Límites referenciales: INE, Censo 2022.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="x/template" id="text">
        <div class="form-horizontal row-border">
            <div class="form-group"
            >
                <label class="col-sm-4 control-label">@{{ question.pregunta }}</label>
                <div class="col-sm-8">
                    <input type="text" name="respuestas[@{{ (duplicable == true) ? ((grupo * 1000000) + agrupador + 1) : grupo }}][@{{ question.pregunta_id }}]" class="form-control">
                    <span class="help-block" v-if="question.instrucciones &amp;&amp; question.instrucciones.trim() &amp;&amp; question.instrucciones.replace(/^\s*\d+\s*[.\-):]\s*/, '').trim().replace(/\s+/g, ' ').toLowerCase() != question.pregunta.replace(/^\s*\d+\s*[.\-):]\s*/, '').trim().replace(/\s+/g, ' ').toLowerCase()">@{{ question.instrucciones }}</span>
                </div>
            </div>
        </div>
    </script>

    <script type="x/template" id="number">
        <div class="form-horizontal row-border">
            <div class="form-group"
            >
                <label class="col-sm-4 control-label">@{{ question.pregunta }}</label>
                <div class="col-sm-8">
                    <input type="number" inputmode="numeric" min="0" pattern="[0-9]*" name="respuestas[@{{ (duplicable == true) ? ((grupo * 1000000) + agrupador + 1) : grupo }}][@{{ question.pregunta_id }}]" class="form-control">
                    <span class="help-block" v-if="question.instrucciones &amp;&amp; question.instrucciones.trim() &amp;&amp; question.instrucciones.replace(/^\s*\d+\s*[.\-):]\s*/, '').trim().replace(/\s+/g, ' ').toLowerCase() != question.pregunta.replace(/^\s*\d+\s*[.\-):]\s*/, '').trim().replace(/\s+/g, ' ').toLowerCase()">@{{ question.instrucciones }}</span>
                    <img v-if="question.pregunta_id == 33" class="img-responsive img-rounded" style="width: 50%;" src="{{ asset('img/ubicacion-pasajeros.png') }}" />
                </div>
            </div>
        </div>
    </script>

    <script type="x/template" id="radio">
        <div class="form-horizontal row-border">
            <div class="form-group"
            >
                <label class="col-sm-4 control-label">@{{ question.pregunta }}</label>
                <div class="col-sm-8 icheck-square">
                    <div v-if="question.mostrar_selector">
                        <select
                            class="select2" style="width: 100% !important"
                            name="respuestas[@{{ (duplicable == true) ? ((grupo * 1000000) + agrupador + 1) : grupo }}][@{{ question.pregunta_id }}][]">
                            <option></option>
                            <option v-for="opcion in question.opciones" value="@{{ opcion }}"> @{{ opcion }}</option>
                        </select>
                    </div>
                    <div v-if="!question.mostrar_selector">
                        <div class="radio blue icheck" v-for="opcion in question.opciones" >
                            <label>
                                <input type="radio" name="respuestas[@{{ (duplicable == true) ? ((grupo * 1000000) + agrupador + 1) : grupo }}][@{{ question.pregunta_id }}][]" value="@{{ opcion }}"> @{{ opcion }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8 col-sm-offset-4" style="margin-top: 0.5em;" v-if="question.mostrar_otro">
                    <input type="text" :placeholder="question.titulo_otro" name="respuestas[@{{ (duplicable == true) ? ((grupo * 1000000) + agrupador + 1) : grupo }}][@{{ question.pregunta_id }}]" class="form-control">
                </div>
                <div class="col-sm-8 col-sm-offset-4" v-if="question.instrucciones &amp;&amp; question.instrucciones.trim() &amp;&amp; question.instrucciones.replace(/^\s*\d+\s*[.\-):]\s*/, '').trim().replace(/\s+/g, ' ').toLowerCase() != question.pregunta.replace(/^\s*\d+\s*[.\-):]\s*/, '').trim().replace(/\s+/g, ' ').toLowerCase()">
                    <span class="help-block">@{{ question.instrucciones }}</span>
                </div>
            </div>
        </div>
    </script>

    <script type="x/template" id="check">
        <div class="form-horizontal row-border">
            <div class="form-group"
            >
                <label class="col-sm-4 control-label">@{{ question.pregunta }}</label>
                <div class="col-sm-8 icheck-square">
                    <div class="checkbox blue icheck" v-for="opcion in question.opciones">
                        <label>
                            <input type="checkbox" name="respuestas[@{{ (duplicable == true) ? ((grupo * 1000000) + agrupador + 1) : grupo }}][@{{ question.pregunta_id }}][]" value="@{{ opcion }}"> @{{ opcion }}
                        </label>
                    </div>
                </div>
                <div class="col-sm-8 col-sm-offset-4" style="margin-top: 0.5em;" v-if="question.mostrar_otro">
                    <input type="text" :placeholder="question.titulo_otro" name="respuestas[@{{ (duplicable == true) ? ((grupo * 1000000) + agrupador + 1) : grupo }}][@{{ question.pregunta_id }}][]" class="form-control">
                </div>
                <div class="col-sm-8 col-sm-offset-4" v-if="question.instrucciones &amp;&amp; question.instrucciones.trim() &amp;&amp; question.instrucciones.replace(/^\s*\d+\s*[.\-):]\s*/, '').trim().replace(/\s+/g, ' ').toLowerCase() != question.pregunta.replace(/^\s*\d+\s*[.\-):]\s*/, '').trim().replace(/\s+/g, ' ').toLowerCase()">
                    <span class="help-block">@{{ question.instrucciones }}</span>
                </div>
            </div>
        </div>
    </script>

    <script type="x/template" id="date">
        <div class="form-horizontal row-border">
            <div class="form-group"
            >
                <label class="col-sm-4 control-label">@{{ question.pregunta }}</label>
                <div class="col-sm-8">
                    <input type="text" name="respuestas[@{{ (duplicable == true) ? ((grupo * 1000000) + agrupador + 1) : grupo }}][@{{ question.pregunta_id }}]" class="form-control datepicker">
                    <span class="help-block" v-if="question.instrucciones &amp;&amp; question.instrucciones.trim() &amp;&amp; question.instrucciones.replace(/^\s*\d+\s*[.\-):]\s*/, '').trim().replace(/\s+/g, ' ').toLowerCase() != question.pregunta.replace(/^\s*\d+\s*[.\-):]\s*/, '').trim().replace(/\s+/g, ' ').toLowerCase()">@{{ question.instrucciones }}</span>
                </div>
            </div>
        </div>
    </script>
@endsection
