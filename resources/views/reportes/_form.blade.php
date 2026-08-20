<script>
@if (isset($params['data']['item']))
    var item = {!! $params['data']['item'] !!};
@else
    var item = null;
@endif
    var siat = {
        filtros: <?= json_encode($params['filtros']) ?>,
        comparadores: <?= json_encode($params['comparadores']) ?>,
        instituciones: <?= json_encode($params['instituciones']) ?>
    }
</script>

<div id="reportes-builder" v-cloak>
    <div class="form-horizontal">
        <div class="form-group">
            {!! Form::label('nombre', 'Nombre:', ['class' => 'control-label']) !!}
            {!! Form::text('nombre', null, ['v-model' => 'nombre', 'class' => 'form-control', 'autofocus' => 'true', 'placeholder' => 'Nombre del Reporte']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('descripcion', 'Descripcion:', ['class' => 'control-label']) !!}
            {!! Form::textarea('descripcion', null, ['v-model' => 'descripcion', 'class' => 'form-control', 'placeholder' => 'Descripción del Reporte']) !!}
        </div>

        @if ($currentUser->hasRole('sysadmin'))
        <div class="form-group">
            {!! Form::label('','Compartir Reporte:', ['class' => 'control-label']) !!}
            <div class="block">
                <div class="checkbox-inline">
                    <label for="activo" class="control-lablel">
                        {!! Form::checkbox('activo', '1', null, ['v-model' => 'compartido']) !!} Sí
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group" v-show="compartido">
            {!! Form::label('instituciones', 'Instituciones con acceso a este Reporte', ['class' => 'control-label']) !!}
            <div class="row">
                <div class="col-md-12">
                    <ul class="list-group">
                        <li class="list-group-item" v-for="institucion in instituciones" track-by="$index">
                            <a href="#" @click.prevent="borrarInstitucion(institucion)"><i class="fa fa-times-circle fa-fw"></i></a>
                            @{{ institucion.nombre }}
                        </li>
                        <li class="list-group-item" v-show="instituciones.length == 0">
                            Todas las instituciones tienen acceso a este Reporte. Si desea restringir el acceso a Instituciones específicas, seleccione las instituciones que pueden ejecutar este reporte abajo.
                        </li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <select name="instituciones" class="form-control" style="width: 100% !important">
                        @foreach($params['instituciones'] as $field_id => $field_text)
                        <option value="{{ $field_id }}">{{ $field_text }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <a href="#" class="btn btn-default" @click.prevent="agregarInstitucion()"><i class="fa fa-plus fa-fw"></i>Agregar Institución</a>
                </div>
            </div>
        </div>
        @endif

        <div class="form-group">
            {!! Form::label('filtros', 'Filtros', ['class' => 'control-label']) !!}
            <div class="row">
                <div class="col-md-12">
                    <ul class="list-group" v-show="filtros.length > 0">
                        <li class="list-group-item" v-for="filtro in filtros">
                            <a href="#" @click.prevent="borrarFiltro(filtro)"><i class="fa fa-times-circle fa-fw"></i></a>
                            @{{ filtro.filtro.nombre }} <em>@{{ filtro.comparador.nombre }}</em> <strong>@{{ filtro.valor }}</strong>
                        </li>
                    </ul>
                    <div class="alert alert-info" v-show="filtros.length == 0">No existen filtros definidos. Puede agregar filtros utilizando abajo.</div>
                </div>
                <div class="col-md-3">
                    <select name="filtros" class="select2" v-model="filtro.campo" style="width: 100% !important">
                        @foreach($params['filtros'] as $field_id => $field_text)
                        <option value="{{ $field_id }}">{{ $field_text }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="comparadores" class="form-control" v-model="filtro.comparador">
                        <option v-for="comparador in comparadoresDisponibles" value="@{{ comparador.value }}">@{{ comparador.label }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    {!! Form::text('valores', null, ['class' => 'form-control', 'placeholder' => 'Valor', 'v-model' => 'filtro.valor']) !!}
                </div>
                <div class="col-md-3">
                    <a href="#" class="btn btn-default" @click.prevent="agregarFiltro()"><i class="fa fa-filter fa-fw"></i>Agregar Filtro</a>
                </div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('campos', 'Campos', ['class' => 'control-label']) !!}
            <div class="row">
                <div class="col-md-12">
                    <ul class="list-group" v-show="campos.length > 0">
                        <li class="list-group-item" v-for="campo in campos" track-by="$index">
                            <a href="#" @click.prevent="borrarCampo(campo)"><i class="fa fa-times-circle fa-fw"></i></a>
                            @{{ campo.nombre }}
                            <div class="pull-right">
                                <div class="btn-group btn-group-xs">
                                    <button type="button" v-bind:class="[grupo.habilitado == true ? 'active' : '']" class="btn btn-default" v-for="grupo in campo.grupos" @click.prevent="habilitarAgrupado(campo, grupo)">
                                        <i v-bind:class="[grupo.habilitado == true || grupo.habilitado == 'true' ? 'fa-circle' : 'fa-circle-o']" class="fa fa-fw"></i>
                                        @{{ grupo.nombre }}
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="alert alert-info" v-show="campos.length == 0">No se definieron aún campos para este reporte.</div>
                </div>
                <div class="col-md-8">
                    <select name="campos" class="select2" style="width: 100% !important">
                        @foreach($params['campos'] as $field)
                        <option value="{{ $field['id'] }}" data-tipo="{{ $field['tipo'] }}">{{ $field['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <a href="#" class="btn btn-default" @click.prevent="agregarCampo()"><i class="fa fa-plus fa-fw"></i>Agregar Campo</a>
                </div>
            </div>
        </div>
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

@section('header-css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/sweet-alert.css') }}">
@endsection

@section('footer-javascript')
    @parent
    <script src="{{ asset('js/underscore-min.js') }}"></script>
    <script src="{{ asset('js/sweet-alert.min.js') }}"></script>
    <script src="{{ asset('js/reportes-builder.js') }}"></script>
@endsection
