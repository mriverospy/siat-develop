@extends('app')

@section('page-title', $params['subtitle'])

@section('content')
<div class="page-heading">
    <h1>{!! $params['title'] !!}</h1>
    <div class="options">
        <div class="btn-toolbar">
            @yield('title_options')
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            @include('partials.alerts')
            @yield('layout_content')
        </div>
    </div>
</div>
@endsection
