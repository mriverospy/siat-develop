@extends('app')

@section('page-title', 'Ubicación de Accidentes')

@section('header-css')
    @parent
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.css">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.Default.css">
@endsection

@section('header-js')
    @parent
    <script type="text/javascript" src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"></script>
    <script type="text/javascript" src="https://unpkg.com/leaflet.markercluster@1.3.0/dist/leaflet.markercluster.js"></script>
@endsection

@section('content')
    <div class="page-heading">
        <div class="options">
            <div class="btn-toolbar">
                {!! Form::open(['method' => 'GET', 'route' => 'accidentes.ubicacion_accidentes', 'class' => 'form-inline']) !!}
                <a href="{{ url('/') }}" class="btn btn-default"><i class="fa fa-arrow-left fa-fw"></i> Atrás</a>
                <button type="button" id="reporte-rango-fechas" class="btn btn-default">
                    <i class="fa fa-calendar"></i>
                    <span><?php echo date('d-m-Y', strtotime('-30 day')); ?> &mdash; <?php echo date('d-m-Y'); ?></span> <b class="caret"></b>
                </button>
                <input type="hidden" name="fecha_inicio" value="" />
                <input type="hidden" name="fecha_fin" value="" />
                <button type="submit" class="btn btn-primary"><i class="fa fa-bolt"></i> Ejecutar</button>
                <a href="#" onclick="printMap()" class="btn btn-default"><i class="fa fa-print fa-fw"></i> Imprimir</a>
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
                <h2>Ubicación de Accidentes del {{ $params['fechaInicio']->format('d/m/Y') }} al {{ $params['fechaFin']->format('d/m/Y') }}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-12">
                <div class="panel panel-inverse dont-break-in-print">
                    <div class="panel-heading">
                        <h2>Ubicación de Accidentes</h2>
                    </div>
                    <div class="panel-body">
                        <div id="map" style="width: 100%; height: 600px;"><i class="fa fa-spin fa-spinning"></i></div>
                        <p class="text-muted">Sólo se incluyen accidentes con datos de geolocalización.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-javascript')
    @parent
    <script type="text/javascript">
        var points = {!! json_encode($params['items']) !!};
        var mapaAccidentes = null;

        function printMap() {
            $('#map').css('width', '800px');
            mapaAccidentes.invalidateSize();

            const $body = $('body');
            const $mapContainer = $('div.panel-body');
            const $mapContainerParent = $mapContainer.parent();
            const $printContainer = $('<div style="position:relative;">');

            $printContainer
                .height($mapContainer.height())
                .append('<p><img src="{{ url($params['logo_membrete']) }}" alt="Logo" /></p>')
                .append('<h1 style="color: #000000;">Ubicación de Accidentes del {{ $params['fechaInicio']->format('d/m/Y') }} al {{ $params['fechaFin']->format('d/m/Y') }}</h1>')
                .append($mapContainer)
                .prependTo($body);

            const $content = $body
                .children()
                .not($printContainer)
                .not('script')
                .detach();

            // Needed for those who use Bootstrap 3.x, because some of
            // its `@media print` styles ain't play nicely when printing.
            const $patchedStyle = $('<style media="print">')
                .text(`
                    img { max-width: none !important; }
                    a[href]:after { content: ""; }
                `)
                .appendTo('head');

            window.print();

            $body.prepend($content);
            $mapContainerParent.prepend($mapContainer);

            $printContainer.remove();
            $patchedStyle.remove();

            $('#map').css('width', '100%');
            mapaAccidentes.invalidateSize();
        }

        jQuery(document).ready(function($) {
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

            mapaAccidentes = L.map('map', {
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
