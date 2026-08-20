@extends('layouts.base_layout')

@section('layout_content')
    <script>
        var siat = {
            sedes: {!! $params['sedes'] !!},
            usuarios: {!! $params['usuarios'] !!}
        }
    </script>
    <div class="panel {{ $params['panel_color'] }}">
        <div class="panel-heading">
            <h2>{{ $params['subtitle'] }} {{ $params['data']['item']->caratula }}</h2>
        </div>
        <div class="panel-body">
            {!! Form::open(['route' => ['accidentes.do-authorize', $params['data']['item']->id]]) !!}
                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('institucion_id', 'Institución', ['class' => 'control-label']) !!}
                        <select name="institucion_id" class="select2" style="width: 100% !important">
                            @foreach($params['instituciones'] as $institucion)
                            <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('sede_id', 'Sede ', ['class' => 'control-label']) !!}
                        <select name="sede_id" class="select2" style="width: 100% !important">
                        </select>
                    </div>
                </div>

                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('usuario_id', 'Usuario', ['class' => 'control-label']) !!}
                        <select name="usuario_id" class="select2" style="width: 100% !important">
                        </select>
                    </div>
                </div>

                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('descripcion', 'Descripción', ['class' => 'control-label']) !!}
                        {!! Form::textarea('descripcion', null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-horizontal">
                            <div class="form-group">
                                {!! Form::label('fecha_inicio', 'Fecha Inicio', ['class' => 'control-label']) !!}
                                {!! Form::text('fecha_inicio', null, ['class' => 'form-control datepicker']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="form-horizontal">
                            <div class="form-group">
                                {!! Form::label('fecha_fin', 'Fecha Fin', ['class' => 'control-label']) !!}
                                {!! Form::text('fecha_fin', null, ['class' => 'form-control datepicker']) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('campos', 'Campos', ['class' => 'control-label']) !!}
                        <select name="campos[]" multiple="multiple" class="select2" style="width: 100% !important">
                            @foreach($params['available_fields'] as $field_id => $field_text)
                            <option value="{{ $field_id }}">{{ $field_text }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="panel-footer">
                    <div class="row">
                        <div class="pull-right">
                            <a href = "{{ url($params['route']) }}" class = 'btn btn-default'>Cancelar</a>
                            {!! Form::submit('Autorizar Cambios', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@section('footer-javascript')
    @parent
    <script>
        jQuery(document).ready(function($) {
            $('select[name=institucion_id]').on('change', function() {
                $('select[name=sede_id]').html('');
                $('select[name=sede_id]').val(null).trigger('change');
                $('select[name=usuario_id]').html('');
                $('select[name=usuario_id]').val(null).trigger('change');
                var selectedInstitucion = $(this).val();
                var options = '';
                $.each(siat.sedes, function(i, sede) {
                    if (sede.institucion_id == selectedInstitucion) {
                        options += '<option value="' + sede.id + '">' + sede.nombre + '</option>';
                    }
                });
                $('select[name=sede_id]').html(options);
                $('select[name=sede_id]').val(null).trigger('change');
            });

            $('select[name=sede_id]').on('change', function() {
                var selectedSede = $(this).val();
                var options = '';
                $.each(siat.usuarios, function(i, usuario) {
                    if (usuario.sede_id == selectedSede) {
                        options += '<option value="' + usuario.id + '">' + usuario.nombres + ' ' + usuario.apellidos + '</option>';
                    }
                });
                $('select[name=usuario_id]').html(options);
                $('select[name=usuario_id]').val(null).trigger('change');
            });
        });
    </script>
@endsection
