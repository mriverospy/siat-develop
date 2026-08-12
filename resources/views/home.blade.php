@extends('app')

@section('page-title', 'Bienvenido!')

@section('content')
    <div id="dashboard">
        <div class="page-heading">
            <h1>Dashboard</h1>
            <div class="options">
                <div class="btn-toolbar">
                    @if ($currentUser->ability('sysadmin', 'accidentes-create'))
                    <a href="{{ route('cargar-respuestas') }}" class="btn btn-primary"><i class="fa fa-fw fa-plus"></i> Agregar Accidente</a>
                    @endif
                    <a href="{{ route('accidentes.bajar-formulario-unico') }}" class="btn btn-inverse"><i class="fa fa-fw fa-file-pdf-o"></i> Formulario Único en Blanco</a>
                    <a href="{{ route('accidentes.bajar-anexo-vehiculos') }}" class="btn btn-inverse"><i class="fa fa-fw fa-file-pdf-o"></i> Bajar Anexo Vehículos</a>
                    <a href="{{ route('accidentes.bajar-anexo-personas') }}" class="btn btn-inverse"><i class="fa fa-fw fa-file-pdf-o"></i> Bajar Anexo Personas</a>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xs-12">
                    @include('partials.alerts')
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <a class="info-tiles tiles-inverse has-footer" href="{{ url('accidentes') }}">
                        <div class="tiles-heading">
                            <div class="pull-left">Total Accidentes</div>
                            <div class="pull-right"></div>
                        </div>
                        <div class="tiles-body">
                            <div id="total-accidentes" class="text-center"><i class="fa fa-spin fa-spinner"></i></div>
                        </div>
                        <div class="tiles-footer">
                            <div class="pull-left">ver todos</div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a class="info-tiles tiles-green has-footer" href="{{ url('derivaciones/sent') }}">
                        <div class="tiles-heading">
                            <div class="pull-left">Accidentes Derivados</div>
                            <div class="pull-right"></div>
                        </div>
                        <div class="tiles-body">
                            <div id="accidentes-derivados" class="text-center"><i class="fa fa-spin fa-spinner"></i></div>
                        </div>
                        <div class="tiles-footer">
                            <div class="pull-left">ver todos</div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a class="info-tiles tiles-blue has-footer" href="{{ url('autorizaciones/sent') }}">
                        <div class="tiles-heading">
                            <div class="pull-left">Autorizaciones Enviadas</div>
                            <div class="pull-right"></div>
                        </div>
                        <div class="tiles-body">
                            <div id="autorizaciones-enviadas" class="text-center"><i class="fa fa-spin fa-spinner"></i></div>
                        </div>
                        <div class="tiles-footer">
                            <div class="pull-left">ver todos</div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <div class="panel panel-default" data-widget=''>
                        <div class="panel-heading">
                            <h2>Derivaciones Recibidas</h2>
                        </div>
                        <div class="panel-editbox"></div>
                        <div class="panel-body panel-no-padding">
                            <div class="tabel-responsive">
                                <table class="table table-hover mb0">
                                    <thead>
                                        <tr>
                                            <th width="25">Estado</th>
                                            <th width="35">Accidente</th>
                                            <th width="50">Enviado por</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($derivaciones as $item)
                                        <tr>
                                            <td><span class="label {{ ($item->estado == 'recibido') ? 'label-success' : 'label-default' }}">{{ strtoupper($item->estado) }}</span></td>
                                            <td>{{ $item->accidente->caratula }}</td>
                                            <td>{{ $item->usuario_emisor->nombres }} {{ $item->usuario_emisor->apellidos }} ({{ $item->institucion_emisor->nombre }})</td>
                                        </tr>
                                        @endforeach
                                        @if (count($derivaciones) == 0)
                                        <tr>
                                            <td colspan="3" class="text-center">No se recibieron derivaciones</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                                <p class="text-center">
                                    <a class="btn btn-default" href="{{ url('derivaciones/received') }}">Ver Todas las Derivaciones</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6">
                    <div class="panel panel-default" data-widget=''>
                        <div class="panel-heading">
                            <h2>Autorizaciones Recibidas</h2>
                        </div>
                        <div class="panel-editbox"></div>
                        <div class="panel-body panel-no-padding">
                            <div class="tabel-responsive">
                                <table class="table table-hover mb0">
                                    <thead>
                                        <tr>
                                            <th width="25">Accidente</th>
                                            <th width="35">Autorizado por</th>
                                            <th width="50">Vence</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($autorizaciones as $item)
                                        <tr>
                                            <td>{{ $item->accidente->caratula }}</td>
                                            <td>{{ $item->usuario_emisor->nombres }} {{ $item->usuario_emisor->apellidos }}</td>
                                            <td>{{ $item->fecha_fin_autorizacion->diffForHumans() }}</td>
                                        </tr>
                                        @endforeach
                                        @if (count($autorizaciones) == 0)
                                        <tr>
                                            <td colspan="3" class="text-center">No se recibieron autorizaciones</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                                <p class="text-center">
                                    <a class="btn btn-default" href="{{ url('autorizaciones/received') }}">Ver Todas las Autorizaciones</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h2 id="mapa-titulo">Ubicación de Accidentes (Agrupados)</h2>
                        </div>
                        <div class="panel-editbox"></div>
                        <div class="panel-body">
                            <div class="clearfix">
                                <button class="btn btn-default pull-left" id="mapa-selector-fecha">
                                    <i class="fa fa-calendar visible-xs"></i>
                                    <span class="hidden-xs" style="text-transform: uppercase;"><?php echo date('F j, Y', strtotime('-30 day')); ?> - <?php echo date('F j, Y'); ?></span> <b class="caret"></b>
                                </button>
                            </div>
                            <div id="map" style="height:600px;"><i class="fa fa-spin fa-spinning"></i></div>
                            <p class="text-muted">Sólo se incluyen accidentes con datos de geolocalización.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h2>Estadística de Accidentes por dia</h2>
                        </div>
                        <div class="panel-editbox"></div>
                        <div class="panel-body">
                            <div class="clearfix">
                                <button class="btn btn-default pull-left" id="victimas-dia-selector-fecha">
                                    <i class="fa fa-calendar visible-xs"></i>
                                    <span class="hidden-xs" style="text-transform: uppercase;"><?php echo date('F j, Y', strtotime('-30 day')); ?> - <?php echo date('F j, Y'); ?></span> <b class="caret"></b>
                                </button>
                            </div>
                            <div id="graph-victimas-pordia" height="250"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('header-css')
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.css">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.Default.css">
@endsection

@section('header-js')
    <script type="text/javascript" src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"></script>
    <script type="text/javascript" src="https://unpkg.com/leaflet.markercluster@1.3.0/dist/leaflet.markercluster.js"></script>
@endsection

@section('footer-javascript')
    @parent
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/8.1.2/highcharts.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/8.1.2/modules/exporting.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/8.1.2/modules/offline-exporting.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/8.1.2/modules/export-data.min.js"></script>
    <script type="text/javascript">
        var mapaAccidentes = null;

        if (typeof Highcharts !== 'undefined') {
            Highcharts.setOptions({
                lang: {
                    decimalPoint: ',',
                    downloadCSV: 'Descargar CSV',
                    downloadJPEG: 'Descargar JPEG',
                    downloadPDF: 'Descargar PDF',
                    downloadPNG: 'Descargar PNG',
                    downloadSVG: 'Descargar SVG',
                    downloadXLS: 'Descargar XLS',
                    loading: 'Cargando...',
                    months: [
                        'Enero', 'Febrero', 'Marzo', 'Abril',
                        'Mayo', 'Junio', 'Julio', 'Agosto',
                        'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'
                    ],
                    noData: 'No hay datos para mostrar',
                    printChart: 'Imprimir Gráfico',
                    viewData: 'Ver tabla de datos',
                    viewFullscreen: 'Ver a pantalla completa',
                    weekdays: [
                        'Domingo', 'Lunes', 'Martes', 'Miércoles',
                        'Jueves', 'Viernes', 'Sábado'
                    ]
                }
            });
        }

        var renderMapaAccidentes = function(fechaInicio, fechaFin) {
            $.ajax({
                url: '{{ url('accidentes/stats') }}?stat=cluster&inicio=' + fechaInicio + '&fin=' + fechaFin,
                method: 'get',
                success: function(points) {
                    var accidentesCluster = L.markerClusterGroup({
                        maxClusterRadius: 10
                    });

                    var markers = [];
                    jQuery.each( points, function( key, value ) {
                        markers.push(L.marker([value.gps_latitud, value.gps_longitud]).bindPopup(
                            '<strong>' + value.caratula + '</strong><br />' +
                            '<strong>ID:</strong> ' + value.id + '<br />' +
                            '<strong>Fecha:</strong> ' + value.fecha_accidente + '<br />' +
                            '<a href="/accidentes/' + value.id + '">Ver Datos del Accidente</a>'
                            ).addTo(accidentesCluster));
                    });

                    var opsm = L.tileLayer("http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                      maxZoom: 18,
                      attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    });

                    mapaAccidentes = map = L.map('map', {
                        center: [-23.518963, -57.872537],
                        zoom: 7,
                        layers: [opsm, accidentesCluster]
                    });

                    var baseLayers = {
                        'OpenStreetMap': opsm,
                    };

                    var overlays = { };

                    mapaAccidentes.scrollWheelZoom.disable();

                    L.control.layers(baseLayers,overlays).addTo(mapaAccidentes);

                    var group = L.featureGroup(markers).addTo(mapaAccidentes);
                    mapaAccidentes.fitBounds(group.getBounds().pad(0.5));
                }
            });
        }

        var renderAccidentesPorDia = function(fechaInicio, fechaFin) {
            $.ajax({
                url: '{{ url('accidentes/stats') }}?stat=accidentes&inicio=' + fechaInicio + '&fin=' + fechaFin,
                method: 'get',
                success: function(data) {
                    var colorTotalAccidentes = '#' + (Math.random().toString(16) + '0000000').slice(2, 8);
                    var colorIlesos = '#' + (Math.random().toString(16) + '0000000').slice(2, 8);
                    var colorHeridos = '#' + (Math.random().toString(16) + '0000000').slice(2, 8);
                    var colorFallecidos = '#' + (Math.random().toString(16) + '0000000').slice(2, 8);
                    var colorTotalPersonas = '#' + (Math.random().toString(16) + '0000000').slice(2, 8);

                    Highcharts.chart('graph-victimas-pordia', {
                        exporting: {
                            buttons: {
                                contextButton: {
                                    menuItems: [
                                        'viewFullscreen',
                                        'printChart',
                                        'separator',
                                        'downloadPNG',
                                        'downloadJPEG',
                                        'downloadPDF',
                                        'downloadSVG',
                                        'separator',
                                        'downloadCSV',
                                        'downloadXLS'
                                    ]
                                }
                            }
                        },
                        chart: {
                            zoomType: 'xy'
                        },
                        title: {
                            text: 'Accidentes por Día, del ' + fechaInicio + ' al ' + fechaFin
                        },
                        subtitle: {
                            text: 'Fuente: SIAT/ANTSV'
                        },
                        xAxis: [
                            {
                                categories: data.labels,
                                crosshair: true,
                                labels: {
                                    style: {
                                        fontSize: '10px'
                                    }
                                }
                            }
                        ],
                        yAxis: [
                            {
                                // Primary yAxis
                                title: {
                                    text: 'Total Personas',
                                    style: {
                                        color: Highcharts.getOptions().colors[1]
                                    }
                                },
                                labels: {
                                    format: '{value:,.0f}',
                                    style: {
                                        color: Highcharts.getOptions().colors[1]
                                    }
                                },
                                min: 0,
                                labels: {
                                    style: {
                                        fontSize: '10px'
                                    }
                                }
                            },
                            {
                                // Secondary yAxis
                                title: {
                                    text: 'Total Accidentes',
                                    style: {
                                        color: Highcharts.getOptions().colors[1]
                                    }
                                },
                                labels: {
                                    format: '{value:,.0f}',
                                    style: {
                                        color: Highcharts.getOptions().colors[1]
                                    }
                                },
                                opposite: true,
                                min: 0,
                                labels: {
                                    style: {
                                        fontSize: '10px'
                                    }
                                }
                            }
                        ],
                        tooltip: {
                            shared: true
                        },
                        legend: {
                            layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'top',
                            // y: 50,
                            floating: false,
                            backgroundColor: Highcharts.defaultOptions.legend.backgroundColor || 'rgba(255,255,255,0.25)',
                            labels: {
                                style: {
                                    fontSize: '10px'
                                }
                            }
                        },
                        series: [
                            {
                                name: 'Total Accidentes',
                                type: 'column',
                                yAxis: 1,
                                color: '#8D99AD',
                                data: data.total_accidentes,
                                tooltip: { valueSuffix: '' }
                            },
                            {
                                name: 'Ilesos',
                                type: '',
                                color: '#006D77',
                                data: data.ilesos,
                                tooltip: { valueSuffix: '' }
                            },
                            {
                                name: 'Heridos',
                                type: '',
                                color: '#FDC055',
                                data: data.heridos,
                                tooltip: { valueSuffix: '' }
                            },
                            {
                                name: 'Fallecidos',
                                type: '',
                                color: '#D20004',
                                data: data.fallecidos,
                                tooltip: { valueSuffix: '' }
                            },
                            {
                                name: 'Total Participantes',
                                type: '',
                                color: '#2B2D41',
                                data: data.total_personas,
                                tooltip: { valueSuffix: '' }
                            }
                        ]
                    });
                }
            });
        }

        jQuery(document).ready(function($) {
            // Totales
            $.ajax({
                url: '{{ url('accidentes/stats') }}?stat=quick',
                method: 'get',
                success: function(data) {
                    $('#total-accidentes').html(data.total_accidentes);
                    $('#accidentes-derivados').html(data.accidentes_derivados);
                    $('#autorizaciones-enviadas').html(data.autorizaciones_enviadas);
                }
            });

            if ($('#graph-victimas').size() > 0) {
                $('#graph-victimas').attr('width', $('#graph-victimas').parent().width());
            }
            if ($('#graph-victimas-pordia').size() > 0) {
                $('#graph-victimas-pordia').attr('width', $('#graph-victimas-pordia').parent().width());
            }

            if ($('#mapa-selector-fecha').size() > 0) {
                $('#mapa-selector-fecha').daterangepicker({
                    ranges: {
                        'Últimos 7 días': [moment().subtract('days', 6), moment()],
                        'Últimos 30 días': [moment().subtract('days', 29), moment()],
                        'Este mes': [moment().startOf('month'), moment().endOf('month')],
                        'Mes pasado': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                    },
                    startDate: moment().subtract('days', 30),
                    endDate: moment(),
                    maxDate: moment(),
                    dateLimit: {
                        days: 90
                    },
                    locale: {
                        separator: '&mdash;',
                        applyLabel: 'Definir',
                        cancelLabel: 'Cancelar',
                        fromLabel: 'Desde',
                        toLabel: 'Hasta',
                        weekLabel: 'W',
                        customRangeLabel: 'Definir Rango',
                        daysOfWeek: [ 'Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa' ],
                        monthNames: [ 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic' ],
                        firstDay: 0
                        }
                    },
                    function(start, end) {
                        if (mapaAccidentes) {
                            mapaAccidentes.remove();
                            mapaAccidentes.off();
                            mapaAccidentes = null;
                        }
                        L.map('map').remove();
                        $('#mapa-selector-fecha span').html(start.format('YYYY-MM-DD') + ' &mdash; ' + end.format('YYYY-MM-DD'));
                        $('#mapa-titulo').text('Ubicación de Accidentes (Agrupados) del ' + start.format('YYYY-MM-DD') + ' al ' + end.format('YYYY-MM-DD'));
                        renderMapaAccidentes(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
                    });
            }

            if ($('#victimas-dia-selector-fecha').size() > 0) {
                $('#victimas-dia-selector-fecha').daterangepicker({
                    ranges: {
                        'Últimos 7 días': [moment().subtract('days', 6), moment()],
                        'Últimos 30 días': [moment().subtract('days', 29), moment()],
                        'Este mes': [moment().startOf('month'), moment().endOf('month')],
                        'Mes pasado': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                    },
                    startDate: moment().subtract('days', 30),
                    endDate: moment(),
                    maxDate: moment(),
                    dateLimit: {
                        days: 90
                    },
                    locale: {
                        separator: '&mdash;',
                        applyLabel: 'Definir',
                        cancelLabel: 'Cancelar',
                        fromLabel: 'Desde',
                        toLabel: 'Hasta',
                        weekLabel: 'W',
                        customRangeLabel: 'Definir Rango',
                        daysOfWeek: [ 'Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa' ],
                        monthNames: [ 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic' ],
                        firstDay: 0
                        }
                    },
                    function(start, end) {
                        $('#victimas-dia-selector-fecha span').html(start.format('YYYY-MM-DD') + ' &mdash; ' + end.format('YYYY-MM-DD'));
                        renderAccidentesPorDia(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
                    });
            }
        });
    </script>
@endsection
