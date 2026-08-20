<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Cargar Accidente &mdash; SIAT - Sistema de Información de Accidentes de Tránsito</title>
    <meta name="_token" content="{!! csrf_token() !!}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="description" content="Sistema Integrado de Accidentes de Tránsito">
    <meta name="author" content="Borealis">
    <!--[if lt IE 10]>
    <script src="{{ asset('js/media.match.min.js') }}"></script>
    <script src="{{ asset('js/placeholder.min.js') }}"></script>
    <![endif]-->
    @if (env('APP_DEBUG'))
    <!-- <link src="{{ asset('css/animate.css') }}" rel="stylesheet"> -->
    @else
    <!-- <link src="{{ asset('css/animate.min.css') }}" rel="stylesheet"> -->
    @endif
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href="{{ asset('css/carga_evento.css') }}" rel="stylesheet">
    <!-- <link href="{{ asset('css/offline-theme-default.css') }}" rel="stylesheet"> -->
    <link href="{{ asset('css/offline-theme-default-indicator.css') }}" rel="stylesheet">
    <!-- <link href="{{ asset('css/offline-language-spanish.css') }}" rel="stylesheet"> -->
    <link href="{{ asset('css/offline-language-spanish-indicator.css') }}" rel="stylesheet">
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries. Placeholdr.js enables the placeholder attribute -->
    <!--[if lt IE 9]>
        <link href="{{ asset('css/ie8.css') }}" type="text/css" rel="stylesheet">
        <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/respond.js/1.1.0/respond.min.js"></script>
        <script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>

<body class="focused-form">
    @yield('content')

    <!-- Moved the script tags to ensure Leaflet is loaded before custom script -->
    <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="{{ asset('js/offline.min.js') }}"></script>
    @if (env('APP_DEBUG'))
    <script src="{{ asset('js/jquery.storageapi.js') }}"></script>
    <script src="{{ asset('js/jquery.geolocation.js') }}"></script>
    <script src="{{ asset('js/mousetrap.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script>
        Vue.config.debug = true;
    </script>
    @else
    <script src="{{ asset('js/jquery.storageapi.min.js') }}"></script>
    <script src="{{ asset('js/jquery.geolocation.min.js') }}"></script>
    <script src="{{ asset('js/mousetrap.min.js') }}"></script>
    <script src="{{ asset('js/vue.min.js') }}"></script>
    @endif
    <script src="{{ asset('js/carga_evento.js?id=20200911225500') }}"></script>
    <script type="text/javascript">
        let setAccidenteLatLng = function (lat, lng) {
            $('input[name=gps_latitud]').val(lat);
            $('input[name=gps_longitud]').val(lng);
        }

        jQuery(document).ready(function ($) {


            if ($('#coordenadas-accidente').length > 0) {


                let lat = $('input[name=gps_latitud]').val();
                let lng = $('input[name=gps_longitud]').val();


                if (!lat || !lng) {
                    lat = -25.273197650011156;
                    lng = -57.17451948494272;
                }


                let opsm = L.tileLayer("http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    maxZoom: 18,
                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                });

                let map = L.map('coordenadas-accidente', {
                    center: [lat, lng],
                    zoom: 7,
                    layers: [opsm]
                });

                let marker = L.marker([lat, lng]).addTo(map);

                // Por defecto el mapa queda desabilitado
                map.dragging.disable();
                map.scrollWheelZoom.disable();
                map.zoomControl.remove();
                map.on('moveend', function (e) {

                    if (map.dragging.enabled()) {
                        let center = map.getCenter();

                        marker.setLatLng(center);
                        // Actualizacion de cordenadas cque funcione con el boton
                        setAccidenteLatLng(center.lat, center.lng);
                    }
                });

                // Boton para habilitar/desabilitar
                let toggleButton = $('<button/>', {
                    text: 'Modificar',
                    click: function (e) {
                        e.preventDefault(); // Previene que se envie el formulario
                        map.dragging.enabled() ? disableEditing() : enableEditing();
                    },
                    css: {
                        position: 'absolute',
                        top: '10px',
                        left: '10px',
                        zIndex: '1000',
                        fontSize: '16px', // Adjust the font size to make it bigger
                        padding: '10px 15px', // Adjust padding to make the button bigger
                    },
                }).appendTo('#coordenadas-accidente'); // Colocar en el mismo contenedor

                function enableEditing() {
                    map.dragging.enable();
                    map.scrollWheelZoom.enable();
                    toggleButton.text('Guardar Ubicacion'); //Cambia texto de edicion mientras se edita
                }

                function disableEditing() {
                    map.dragging.disable();
                    map.scrollWheelZoom.disable();
                    toggleButton.text('Modificar Ubicacion'); //Texto del boton cuando mapa esta desabilitado
                }
            }
        });
    </script>
    @yield('footer-javascript')
</body>

</html>
