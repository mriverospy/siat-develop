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
    <link href="{{ asset('css/accidentes-cargar.css') }}?v={{ filemtime(public_path('css/accidentes-cargar.css')) }}" rel="stylesheet">
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
    <script src="{{ asset('js/mapa_accidente.js') }}?v={{ filemtime(public_path('js/mapa_accidente.js')) }}"></script>
    <script src="{{ asset('js/carga_evento.js') }}?v={{ filemtime(public_path('js/carga_evento.js')) }}"></script>
    @yield('footer-javascript')
</body>

</html>
