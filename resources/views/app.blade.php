<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>@yield('page-title') &mdash; SIAT - Sistema de Información de Accidentes de Tránsito</title>
		<meta name="_token" content="{!! csrf_token() !!}">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-touch-fullscreen" content="yes">
		<meta name="description" content="Sistema Integrado de Accidentes de Tránsito">
		<meta name="author" content="Borealis">
		<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400italic,700' rel='stylesheet' type='text/css'>
		<!--[if lt IE 10]>
		<script src="{{ asset('js/media.match.min.js') }}"></script>
		<script src="{{ asset('js/placeholder.min.js') }}"></script>
		<![endif]-->
		<link href="{{ asset('css/styles.css') }}" rel="stylesheet">
		<link href="{{ asset('css/loading.css') }}" rel="stylesheet">
        <style type="text/css" media="print">
            .navbar-fixed-top+#layout-fixed,
            .navbar-fixed-top+#wrapper {
                margin-top: 0px !important; padding-top: 0px !important;
            }
        </style>
		@yield('header-css')
		<link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries. Placeholdr.js enables the placeholder attribute -->
		<!--[if lt IE 9]>
		<link href="{{ asset('css/ie8.css') }}" type="text/css" rel="stylesheet">
		<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/respond.js/1.1.0/respond.min.js"></script>
		<script type="text/javascript" src="{{ asset('plugins/charts-flot/excanvas.min.js') }}"></script>
		<script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
        @yield('header-js')
	</head>
	<body class="infobar-offcanvas">

		@include('partials.header')

		<div id="wrapper">
			<div id="layout-static">
				<div class="static-sidebar-wrapper sidebar-inverse">
					<div class="static-sidebar">
						<div class="sidebar">
							@include('partials.sidebar')
						</div>
					</div>
				</div>
				<div class="static-content-wrapper">
					<div class="static-content">
						<div class="page-content">
							@yield('content')
						</div>
					</div>
					@include('partials.footer')
				</div>
			</div>
		</div>

		<div class="modal-loader"></div>

        @yield('modals')

		<!-- Scripts -->
        @yield('before-vendor-javascript')
		<script src="{{ asset('js/jquery-1.11.3.min.js') }}"></script>
		<script src="{{ asset('js/jqueryui-1.10.3.min.js') }}"></script>
		<script src="{{ asset('js/bootstrap-3.3.1.min.js') }}"></script>
        @yield('after-vendor-javascript')
        @yield('before-app-javascript')
		<script src="{{ asset('js/all.js') }}"></script>
        @yield('after-app-javascript')
		@yield('footer-javascript')
	</body>
</html>
