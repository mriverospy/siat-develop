@extends('layouts.list_layout')

@section('title_options')
    <form action="{{ url($params['route'], [$reporte->id]) }}" method="get">
        @if (Auth::user()->ability('sysadmin', $params['route_name'].'-list'))
            <a href="{{ url($params['route']) }}" class="btn btn-default"><i class="fa fa-arrow-left fa-fw"></i> Atrás</a>
        @endif
        <button type="button" id="reporte-rango-fechas" class="btn btn-default">
            <i class="fa fa-calendar"></i>
            <span><?php echo date('d-m-Y', strtotime('-30 day')); ?> &mdash; <?php echo date('d-m-Y'); ?></span> <b class="caret"></b>
        </button>
        <input type="hidden" name="fecha_inicio" value="" />
        <input type="hidden" name="fecha_fin" value="" />
        <button class="btn btn-primary" type="submit"><i class="fa fa-bolt"></i> Ejecutar</button>
        @if (Auth::user()->ability('sysadmin', $params['route_name'].'-list'))
            <a href="{{ url($params['route'], [$reporte->id, 'edit']) }}" class="btn btn-default"><i class="fa fa-pencil fa-fw"></i> Editar</a>
        @endif
        @if ($fecha_inicio)
        <a href="{{ route('reporte_datos.csv', [$reporte->id, 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin]) }}" class="btn btn-default"><i class="fa fa-file-o fa-fw"></i> Exportar a CSV</a>
        <a href="{{ route('reporte_datos.pdf', [$reporte->id, 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin]) }}" class="btn btn-default"><i class="fa fa-file-pdf-o fa-fw"></i> Exportar a PDF</a>
        @endif
    </form>
@endsection

@section('table_items')
    <thead>
        <tr>
            @foreach($columns as $column_key => $column_name)
            <th class="text-center">{{ $column_name }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($reporteDatos as $item)
        <tr>
            @foreach($columns as $column_key => $column_name)
            <td class="{{ (stristr($column_key, 'total') !== FALSE) ? 'text-right' : '' }}">{{ $item->$column_key }}</td>
            @endforeach
        </tr>
        @endforeach

        @if ($reporteDatos->count() == 0)
        <tr><td colspan="{{ count($columns) }}" class="h4 text-center">No se encontró ningún registro</td></tr>
        @endif
    </tbody>
@endsection

@section('pagination')
    @if ($reporteDatos->count() > 0)
        {!! $reporteDatos->appends(Request::input())->render() !!}
    @endif
@endsection

@section('modals')
    @parent
    @include('layouts.confirm_modal_layout', ['modalTitle' => 'Confirmación', 'modalSize' => 'modal-sm'])
@endsection

@section('footer-javascript')
    @parent
    <script type="text/javascript">
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
                @if (is_null($fecha_inicio))
                startDate: moment().subtract('days', 29),
                @else
                startDate: moment('{{ $fecha_inicio }}'),
                @endif
                @if (is_null($fecha_fin))
                endDate: moment(),
                @else
                endDate: moment('{{ $fecha_fin }}'),
                @endif
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
                $('#reporte-rango-fechas span').html(start.format('DD-MM-YYYY') + ' &mdash; ' + end.format('DD-MM-YYYY'));
                $('input[name=fecha_inicio]').val(start.format('YYYY-MM-DD'));
                $('input[name=fecha_fin]').val(end.format('YYYY-MM-DD'));
            });
        });
    </script>
@endsection
