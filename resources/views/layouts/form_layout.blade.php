@extends('layouts.base_layout')

@section('header-css')
    @parent
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css">
@endsection

@section('header-js')
    @parent
    <script type="text/javascript" src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"></script>
@endsection

@section('layout_content')
    <div class="panel {{ $params['panel_color'] }}">
        <div class="panel-heading">
            <h2>{{ $params['subtitle'] }}</h2>
        </div>
        <div class="panel-body">
            @if (isset($params['data']['item'] ))
                <!-- EDIT -->
                {!! Form::model($params['data']['item'], ['method' => 'PATCH', 'url' => [$params['route'], $params['data']['item']->id], 'files' => (isset($params['files']) ? $params['files'] : false)]) !!}
            @else
                <!-- CREATE -->
                {!! Form::open(['url' => $params['route'], 'files' => (isset($params['files']) ? $params['files'] : false)]) !!}
            @endif
                @include($params['route'] . '._form')
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@section('footer-javascript')
    @parent
    <script type="text/javascript" charset="utf-8">
        jQuery(document).ready(function($) {
            if ($('select.select2').size()) {
                $('select.select2').select2({
                    width: '100%',
                    placeholder: 'Seleccione una opción...',
                });
            }
        })
    </script>
@endsection
