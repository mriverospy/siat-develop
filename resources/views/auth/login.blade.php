@extends('login')

@section('page-title', 'Iniciar sesión')

@section('content')
<div class="container" id="login-form">
    <a href="{{ url('/') }}" class="login-logo"><img src="{{ url($logo_login) }}"></a>
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2>Iniciar sesión</h2>
                </div>
                <div class="panel-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Tenemos un problema...</strong><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form class="form-horizontal" role="form" method="POST" id="validate-form" action="{{ url('/login') }}">
                        <div><input type="hidden" name="_token" value="{{ csrf_token() }}"></div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" name="credencial" placeholder="Nombre de Usuario" value="{{ old('credencial') }}" data-parsley-minlength="3" placeholder="At least 3 characters" autofocus required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" name="password" placeholder="Contraseña">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <a href="{{ url('/password/reset') }}" class="pull-left">Recuperar mi contraseña</a>
                                <div class="checkbox-inline icheck pull-right pt0">
                                    <label for="">
                                        <input type="checkbox" name="remember">
                                        Recordarme
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <div class="clearfix">
                                <button type="submit" class="btn btn-primary pull-right">Iniciar Sesión</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
