@extends('app')

@section('header-css')
    @parent
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap.min.css">
@endsection

@section('page-title', 'Carga de Registros por Institución')

@section('content')
    <div class="page-heading">
        <div class="options">
            <div class="btn-toolbar">
                {!! Form::open(['method' => 'GET', 'route' => 'accidentes.carga_registros_por_institucion', 'class' => 'form-inline']) !!}
                <a href="{{ url('/') }}" class="btn btn-default"><i class="fa fa-arrow-left fa-fw"></i> Atrás</a>
                <select name="tipo" class="form-control">
                    <option value="accidente">Por Fecha de Accidente</option>
                    <option value="carga">Por Fecha de Carga</option>
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
                <h2>Carga de Registros por Institución del {{ $params['fechaInicio']->format('d/m/Y') }} al {{ $params['fechaFin']->format('d/m/Y') }}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-12">
                <div class="panel panel-inverse dont-break-in-print" style="page-break-after: always; page-break-inside: avoid;">
                    <div class="panel-heading">
                        <h2>Carga de Registros por Institución</h2>
                    </div>
                    <div class="panel-body panel-no-padding">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Institución</th>
                                        <th class="text-center">Total Accidentes Cargados</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($params['items'] as $item)
                                    <tr>
                                        <td data-order="{{ $item->nombre_institucion }}">{{ $item->nombre_institucion }}</td>
                                        <td class="text-center" data-order="{{ str_replace(',', '.', $item->total) }}">{{ $item->total }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after-vendor-javascript')
    @parent
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap.min.js"></script>
@endsection

@section('footer-javascript')
    @parent
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

        jQuery(document).ready(function($) {
            $('.table').DataTable({
                'responsive': true,
                'ordering': true,
                'searching': false,
                'paging': false,
                'info': false,
                'order': [
                    [ 1, 'asc' ]
                ],
                'language': {
                    'url': '{{ asset('js/spanish.json') }}'
                },
                // columnDefs: [
                //     { type: 'natural', targets : 2 }
                // ]
            });

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
                endDate: moment('{{ $params['fechaFin']->format('Y-m-d H:i:s') }}'),
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
        });
    </script>
@endsection
