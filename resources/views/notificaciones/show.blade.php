@extends('layouts.base_layout')

@section('page-title', $params['item']->asunto)

@section('layout_content')
    <div class="panel panel-inbox-read">
        <div class="panel-body">
            <h3 class="inbox-read-title">{{ $params['item']->asunto }}</h3>
            <hr class="mb10">
            <div class="inbox-read-details clearfix">
                <span class="inbox-read-sent-info">recibido el <em>{{ $params['item']->fecha->timezone('America/Asuncion')->format('d/m/y') }}</em> a las <em>{{ $params['item']->fecha->timezone('America/Asuncion')->format('h:i a') }}</em></span>
            </div>
            <hr class="mt10">
            <div class="msg-body">
                {!! nl2br(e($params['item']->mensaje)) !!}
            </div>
        </div>
    </div>
@endsection
