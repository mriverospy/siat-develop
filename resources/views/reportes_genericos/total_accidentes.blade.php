@extends('app')

@section('page-title', 'Total de Accidentes')

@section('content')
    <div class="page-heading">
        <div class="options">
            <div class="btn-toolbar">
                {!! Form::open(['method' => 'GET', 'route' => 'accidentes.total_accidentes', 'class' => 'form-inline']) !!}
                <a href="{{ url('/') }}" class="btn btn-default"><i class="fa fa-arrow-left fa-fw"></i> Atrás</a>
                <select name="tipo" class="form-control">
                    <option value="agrupar_anho" @if ($params['tipo'] == 'agrupar_anho') selected="selected" @endif>Agrupar por Año</option>
                    <option value="agrupar_mes" @if ($params['tipo'] == 'agrupar_mes') selected="selected" @endif>Agrupar por Mes</option>
                    <option value="agrupar_dia" @if ($params['tipo'] == 'agrupar_dia') selected="selected" @endif>Agrupar por Día</option>
                    <option value="agrupar_dia_semana" @if ($params['tipo'] == 'agrupar_dia_semana') selected="selected" @endif>Agrupar por Día de la Semana</option>
                    <option value="agrupar_dia_anho" @if ($params['tipo'] == 'agrupar_dia_anho') selected="selected" @endif>Agrupar por Día del Año</option>
                    <option value="agrupar_hora" @if ($params['tipo'] == 'agrupar_hora') selected="selected" @endif>Agrupar por Hora</option>
                </select>
                <button type="button" id="reporte-rango-fechas" class="btn btn-default">
                    <i class="fa fa-calendar"></i>
                    <span><?php echo date('d-m-Y', strtotime('-30 day')); ?> &mdash; <?php echo date('d-m-Y'); ?></span> <b class="caret"></b>
                </button>
                <input type="hidden" name="fecha_inicio" value="" />
                <input type="hidden" name="fecha_fin" value="" />
                <button type="submit" class="btn btn-primary"><i class="fa fa-bolt"></i> Ejecutar</button>
                <a href="#" onclick="window.print(); return false;" class="btn btn-default"><i class="fa fa-print fa-fw"></i> Imprimir</a>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row visible-print-block">
            <div class="col-xs-12 text-center">
                <img src="{{ url($params['logo_membrete']) }}" alt="Logo" class="img-responsive" />
            </div>
            <div class="col-xs-12 text-center">
                <h2>{{ $params['titulo'] }} del {{ $params['fechaInicio']->format('d/m/Y') }} al {{ $params['fechaFin']->format('d/m/Y') }}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-12">
                <div class="panel panel-inverse dont-break-in-print">
                    <div class="panel-heading">
                        <h2>{{ $params['titulo'] }}</h2>
                    </div>
                    <div class="panel-body panel-no-padding">
                        <div id="grafico" width="100%" height="450"></div>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Agrupador</th>
                                    <th class="text-center">Total Accidentes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($params['items'] as $item)
                                <tr>
                                    <td>{{ $item->agrupador }}</td>
                                    <td class="text-center">{{ $item->total }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-javascript')
    @parent
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/8.1.2/highcharts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/8.1.2/modules/exporting.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/8.1.2/modules/offline-exporting.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/8.1.2/modules/export-data.min.js"></script>
    <script type="text/javascript">
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

        jQuery(document).ready(function($) {
            $('#reporte-rango-fechas').daterangepicker({
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Últimos 7 días': [moment().subtract('days', 6), moment()],
                    'Últimos 30 días': [moment().subtract('days', 29), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes pasado': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                },
                opens: 'right',
                startDate: moment('{{ $params['fechaInicio'] }}'),
                endDate: moment('{{ $params['fechaFin'] }}'),
                locale: {
                    applyLabel: 'Definir',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    weekLabel: 'S',
                    customRangeLabel: 'Definir Rango',
                    daysOfWeek: moment()._lang._weekdaysMin.slice(),
                    monthNames: moment()._lang._monthsShort.slice(),
                    firstDay: 0
                }
            }, function(start, end) {
                $('input[name=fecha_inicio]').val(start.format('YYYY-MM-DD'));
                $('input[name=fecha_fin]').val(end.format('YYYY-MM-DD'));
                $('#reporte-rango-fechas span').html(start.format('DD-MM-YYYY') + ' &mdash; ' + end.format('DD-MM-YYYY'));
            });

            var randomColor = '#' + (Math.random().toString(16) + '0000000').slice(2, 8);

            Highcharts.chart('grafico', {
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
                    text: '{!! $params['titulo'] !!} - Periodo {{ $params['fechaInicio']->format('d/m/Y') }} al {{ $params['fechaFin']->format('d/m/Y') }}'
                },
                subtitle: {
                    text: 'Fuente: SIAT/ANTSV'
                },
                xAxis: [
                    {
                        categories: {!! json_encode($params['keys']) !!},
                        crosshair: true,
                        labels: {
                            style: {
                                fontSize: '9px'
                            }
                        }
                    }
                ],
                yAxis: [
                    { // Primary yAxis
                        labels: {
                            format: '{value:,.0f}',
                            style: {
                                color: Highcharts.getOptions().colors[1],
                                fontSize: '9px'
                            }
                        },
                        title: {
                            text: 'Totales',
                            style: {
                                color: Highcharts.getOptions().colors[1],
                                fontSize: '9px'
                            }
                        },
                        min: 0
                    }
                ],
                tooltip: {
                    shared: true
                },
                legend: {
                    layout: 'horizontal',
                    align: 'center',
                    // x: 120,
                    verticalAlign: 'top',
                    // y: 50,
                    floating: false,
                    backgroundColor: Highcharts.defaultOptions.legend.backgroundColor || 'rgba(255,255,255,0.25)'
                },
                series: [
                    {
                        name: 'Agrupados',
                        type: '{{ $params['tipo-serie'] }}',
                        color: randomColor,
                        data: {!! json_encode($params['values']) !!},
                        tooltip: { valueSuffix: '' }
                    }
                ]
            });
        });
    </script>
@endsection
