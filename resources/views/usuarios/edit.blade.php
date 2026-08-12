@extends('layouts.base_layout')

@section('page-title', 'Editar Usuario')

@section('layout_content')
    <div class="panel panel-midnightblue">
        <div class="panel-heading">
            <h2>{{ $params['subtitle'] }}</h2>
        </div>

        <div class="panel-body">
            {!! Form::model($params['data']['item'], ['method' => 'PATCH', 'url' => [$params['route'], $params['data']['item']->id]] ) !!}
                @include('usuarios._form')
            {!! Form::close() !!}
        </div>
    </div>
@endsection

<!-- Include Modal Change Password -->
@include('usuarios._change-password', ['modalTitle' => 'Cambiar Contraseña', 'modalSize' => 'modal-sm'])
