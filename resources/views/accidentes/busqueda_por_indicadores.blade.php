@extends('layouts.list_layout')

@section('page-title', 'Accidentes')


@section('page_filters')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>BUSCAR ACCIDENTES POR INDICADORES</h2>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12 text-left" style="margin-bottom: 20px;">
                    Para Buscar Accidentes por Indicadores, seleccione el indicador que desea utilizar como parámetro de búsqueda e ingrese el valor del mismo, luego seleccione un rango de fechas para delimitar los resultados:
                </div>
            </div>
            <div class="form row">
                <div class="col-sm-12 col-md-4">
                    <select name="indicador" id="indicador" class="select2" style="width: 100%;">
                        <option value="" data-tipo="" selected="selected" >Seleccione...</option>
                        @foreach($params['data']['indicadores'] as $field)
                        <option value="{{ $field['id'] }}" data-tipo="{{ $field['tipo'] }}" >{{ $field['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-3" id="divValor">
                    {!! Form::text('textValue', NULL, ['class' => 'form-control', 'placeholder' => 'Valor del Indicador', 'disabled' => true]) !!}
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2">
                    {!! Form::text('fecha_inicio', NULL, ['class' => 'form-control datepicker', 'placeholder' => 'Fecha Inicio', 'disabled' => true]) !!}
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2">
                    {!! Form::text('fecha_fin', NULL, ['class' => 'form-control datepicker', 'placeholder' => 'Fecha Fin', 'disabled' => true]) !!}
                </div>
                <div class="col-sm-12 col-md-1">
                    <button class="btn btn-primary btn-block" onclick="filtrate()"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-loader"></div>
@endsection

@section('table_items')
    <thead>
        <tr>
            <th class="text-center">ID</th>
            <th>Carátula</th>
            <th>Institución</th>
            <th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody id="tableBody">        
        <tr>
            <td colspan="4" class="text-center">Seleccione algún indicador y establezca un rango de fechas para realizar la búsqueda.</td>
        </tr>
    </tbody>
@endsection



@section('footer-javascript')
    @parent
    <script type="text/javascript">

        $body = $("body");

        $(document).on({
            ajaxStart: function() { $body.addClass("loading");    },
             ajaxStop: function() { $body.removeClass("loading"); }    
        });

        $("#indicador").on('change', function(e) {
            let tipo = $('select[name=indicador] option:selected').data('tipo');
            let pregunta_id = $('#indicador').val();

            if(tipo === "") {
                return false;
            }

            //TIPO TEXTO Y NUMERO
            if(tipo === "text" || tipo === "number") {
                $('#divValor').html(
                    '{!! Form::text("textValue", NULL, ["class" => "form-control", "placeholder" => "Valor del Indicador", "autofocus"]) !!}'
                );
            }

            //TIPO FECHA
            if(tipo === "date") {
                $('#divValor').html(
                    '{!! Form::text("dateValue", NULL, ["class" => "form-control datepicker", "placeholder" => "Valor del Indicador", "autofocus"]) !!}'
                );
                $( "input[name=dateValue]" ).datepicker({autoclose: true,format: 'yyyy-mm-dd',todayHighlight: true});
            }

            //TIPO RADIO Y CHECK
            if(tipo === "radio" || tipo === "check") {
                jQuery.ajax({
                    url: '{{ route("accidentes.get-opciones") }}',
                    type: 'get',
                    data: 'pregunta_id='+pregunta_id,
                    success: function(response)
                    {
                        var list = response.data;
                        var content = '<select name="selectValue" id="selectValue" class="select2" style="width: 100%;">'
                                        +'<option value="" data-tipo="" selected="selected" >Seleccione...</option>';
                        list.forEach(function(val){
                            content+= '<option value="'+val+'" >'+val+'</option>';
                        });
                        content+='</select>';
                        $('#divValor').html(content);

                        $('#selectValue').select2();                        
                    },
                    error: function(response) {
                        console.log(response.message);
                    }
                });
            }
            $('input[name=fecha_inicio]').attr('disabled', false);
            $('input[name=fecha_fin]').attr('disabled', false);

        });


        function validateFilterFields() {
            let pregunta_id = $('#indicador').val();
            
            //SI NO SELECCIONO NINGUNA PREGUNTA
            if(pregunta_id == "") {
                alert("Debe Seleccionar un Indicador (Pregunta)");
                $('#indicador').select2('open');
                return false;
            }

            //SI LA PREGUNTA SELECCIONADA ES DE TIPO CHECK O RADIO
            let tipo = $('select[name=indicador] option:selected').data('tipo');
            if(tipo === "radio" || tipo === "check") {
                if($('#selectValue').select2('val') == "") {
                    alert("Debe seleccionar un valor para realizar la búsqueda.");
                    $('#selectValue').select2('open');
                    return false;
                }
            }

            //SI LA PREGUNTA ES DE TIPO NUMBER O TEXT
            if(tipo === "text" || tipo === "number") {
                if($('input[name=textValue]').val() === "") {
                    alert("Debe ingresar un valor para realizar la búsqueda.");
                    $('input[name=textValue]').focus();
                    return false;
                }
            }

            //FECHA INICIAL
            if($('input[name=fecha_inicio]').val() === "") {
                alert("Debe ingresar una fecha inicial para la consulta.");
                $('input[name=fecha_inicio]').focus();
                return false;
            }

            //FECHA FINAL
            if($('input[name=fecha_fin]').val() === "") {
                alert("Debe ingresar una fecha final para la consulta.");
                $('input[name=fecha_fin]').focus();
                return false;
            }

            return true;
        }



        function filtrate() {
            if(!validateFilterFields()) {
                return false;
            }
            let tipo = $('select[name=indicador] option:selected').data('tipo');
            var valor = null;
            if(tipo === "radio" || tipo === "check") {
                valor = $('#selectValue').select2('val');
            } else if(tipo === "date") {
                valor = $('input[name=dateValue]').val();
            } else {
                valor = $('input[name=textValue]').val();
            }

            var form_data = new FormData();
            form_data.append('_token', "{{ csrf_token() }}");
            form_data.append('indicador', $('#indicador').select2('val'));
            form_data.append('valor', valor);
            form_data.append('fecha_inicio', $('input[name=fecha_inicio]').val());
            form_data.append('fecha_fin', $('input[name=fecha_fin]').val());

            jQuery.ajax({
                url: '{{ route("accidentes.filtrar-por-indicadores") }}',
                type: 'POST',
                data: form_data,
                contentType: false,
                cache : false,
                processData: false,
                success: function(response) {
                    console.log(response.message);
                    var list = response.data;

                    if(list.length == 0) {
                        $('#tableBody').html('<tr><td colspan="4" class="text-center">No se encontró ningún registro que cumpla con los criterios de búsqueda.</td></tr>');
                        return true;
                    }

                 
                    var content = '<tr>';
                    var url = "{{ url($params['route']) }}";
                    list.forEach(function(val){
                        var fecha = convertDate(val.fecha_apertura);
                        var usuario = val.usuario.nombres+' '+val.usuario.apellidos;
                        content+= '<td class="text-center" style="vertical-align: middle;">'+val.id+'</td>';
                        content+= '<td style="vertical-align: middle;"><strong>'+val.caratula+'</strong>'+
                                        '<br>'+val.lugar+', '+val.localidad.nombre+', '+val.localidad.distrito.nombre+
                                        '<br>Cargado el '+fecha+'. Por '+usuario+'</td>';
                        content+= '<td style="vertical-align: middle;">Institución: '+val.institucion.nombre+'<br>Sede: '+val.sede.nombre+'</td>';
                        content+= '<td class="text-center" style="vertical-align: middle;">'+
                                    '<a href="'+url+'/'+val.id+'" class="btn btn-default btn-sm btn-block" title="Ver Datos del Accidente"><i class="fa fa-eye"></i> Ver Datos del Accidente</a>'+
                                    '<a href="'+url+'/'+val.id+'/pdf" class="btn btn-primary btn-sm btn-block" title="Generar PDF"><i class="fa fa-file-pdf-o"></i> Generar PDF</a></td>';
                    });
                            
                    content+='</tr>';
                    $('#tableBody').html(content);
                },
                error: function(response) {
                    console.log(response.message);
                }
            });
        }


        function convertDate(date) {
            return date.replace( /(\d{4})-(\d{2})-(\d{2}) (\d{2})/, "$3/$2/$1 a las $4");
        }
    </script>
@endsection

