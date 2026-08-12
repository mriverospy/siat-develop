@extends('app')

@section('page-title', $reporte->nombre)

@section('content')
    <div class="page-heading">
        <h1>{{ $reporte->nombre }}</h1>
        <div class="options">
            <div class="btn-toolbar">
                <form class="form-inline1">
                @if (Auth::user()->ability('sysadmin', $params['route_name'].'-list'))
                    <a href="{{ url($params['route']) }}" class="btn btn-default"><i class="fa fa-arrow-left fa-fw"></i> Atrás</a>
                @endif
                <button type="button" id="reporte-rango-fechas" class="btn btn-default">
                    <i class="fa fa-calendar"></i>
                    <span><?php echo date('d-m-Y', strtotime('-30 day')); ?> &mdash; <?php echo date('d-m-Y'); ?></span> <b class="caret"></b>
                </button>
                <button id="ejecutar-reporte" class="btn btn-primary"><i class="fa fa-bolt"></i> Ejecutar</button>
                @if (Auth::user()->ability('sysadmin', $params['route_name'].'-list'))
                    <a href="{{ url($params['route'], [$reporte->id, 'edit']) }}" class="btn btn-default"><i class="fa fa-pencil fa-fw"></i> Editar</a>
                @endif
                <a href="#" onclick="window.print(); return false;" class="btn btn-default"><i class="fa fa-print fa-fw"></i> Imprimir</a>
                </form>
            </div>
        </div>
    </div>
    <div id="reportes" class="container-fluid" v-cloak>
        <div class="row visible-print-block">
            <div class="col-xs-12 text-center">
                <img src="{{ url($logo_membrete) }}" alt="Logo" class="img-responsive" />
            </div>
            <div class="col-xs-12 text-center">
                <h2>{{ $reporte->nombre }}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-12">
                <div class="panel panel-default dont-break-in-print" style="page-break-after: always; page-break-inside: avoid;">
                    <div class="panel-heading">
                        <h2>Sumario de Accidentes</h2>
                    </div>
                    <div class="panel-body panel-no-padding">
                        <summary reporte="{{ $reporte->id }}"></summary>
                    </div>
                </div>
            </div>
        </div>
        @foreach($campos->chunk(2) as $chunk)
            @foreach($chunk as $campo)
        <div class="row">
            <div class="col-xs-12 col-md-12">
                <div class="panel panel-midnightblue dont-break-in-print" style="page-break-after: always; page-break-inside: avoid;">
                    <div class="panel-heading">
                        <h2>{{ $campo['campo']['nombre'] }}</h2>
                    </div>
                    <div class="panel-body panel-no-padding">
                        <graph
                            reporte="{!! $reporte->id !!}"
                            titulo="{!! $campo['campo']['nombre'] !!}"
                            campo="{!! $campo['campo']['valor'] !!}"
                            agrupador="{!! $campo['agrupador']['valor'] !!}"
                            agrupador-nombre="{!! $campo['agrupador']['nombre'] !!}"
                            ></graph>
                    </div>
                </div>
            </div>
        </div>
            @endforeach
        @endforeach
        <div class="row">
            <div class="col-xs-12 col-md-12">
                <div class="panel panel-inverse dont-break-in-print" style="page-break-after: always; page-break-inside: avoid;">
                    <div class="panel-heading">
                        <h2>Filtros aplicados</h2>
                    </div>
                    <ul class="list-group mb0">
                    @if ($reporte->filtros)
                        @foreach($reporte->filtros as $filtro)
                        <li class="list-group-item">
                            {{ $filtro['filtro']['nombre'] }} <em>{{ $filtro['comparador']['valor'] }}</em> <strong>{{ $filtro['valor'] }}</strong>
                        </li>
                        @endforeach
                    @else
                        <li class="list-group-item">
                            <strong>No se definieron filtros para este Reporte</strong>
                        </li>
                    @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-javascript')
    @parent
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/8.1.2/highcharts.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/8.1.2/modules/exporting.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/8.1.2/modules/offline-exporting.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/8.1.2/modules/export-data.min.js"></script>
    <script type="text/javascript">
        var siatGraphSummaryEndpoint = '{{ url('reportes/get-graph-summary-data') }}';
        var siatGraphEndpoint = '{{ url('reportes/get-graph-data') }}';

        $.fn.beforeprint = function(callback) {
            return $(this).each(function() {
                if (!jQuery.isWindow(this)) return;
                if (this.onbeforeprint !== undefined) $(this).on('beforeprint', callback);
                else if (this.matchMedia) this.matchMedia('print').addListener(callback);
            });
        };

        $.fn.afterprint = function(callback) {
            return $(this).each(function() {
                if (!jQuery.isWindow(this)) return;
                if (this.onafterprint !== undefined) $(this).one('afterprint', callback);
                else if (this.matchMedia) $(this).one('mouseover', callback);
            });
        };

        $(window).beforeprint(function() {
            $(Highcharts.charts).each(function(i, chart) {
                if (!chart.printSizeMode) {
                    chart.oldhasUserSize = chart.hasUserSize;
                    chart.resetParams = [chart.chartWidth, chart.chartHeight, false];
                    chart.printSizeMode = true;
                }

                // var height = $('#container').height();
                var height = 350;
                // var width = $('#container').width();
                var width = 680;
                console.log("print mode:", width, height);
                chart.setSize(width, height, false);
                chart.reflow();
            });

            $(window).afterprint(function() {
                $(Highcharts.charts).each(function(i, chart) {
                    if (chart.printSizeMode) {
                        // var height = $('#container').height();
                        var height = 200;
                        // var width = $('#container').width();
                        var width = 680;
                        console.log("normal mode:", width, height);
                        chart.setSize.apply(chart, chart.resetParams);
                        chart.hasUserSize = chart.oldhasUserSize;
                        chart.reflow();
                        chart.printSizeMode = false;
                    }
                });
            });
        });

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
    </script>
    <script src="{{ asset('js/reportes.js') }}"></script>
@endsection
