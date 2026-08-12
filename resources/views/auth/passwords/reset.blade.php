@extends('login')

@section('page-title', 'Cambiar Contraseña')

@section('content')
<div class="container" id="login-form">
    <a href="{{ url('/') }}" class="login-logo"><img src="{{ url($logo_login) }}"></a>
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2>Cambiar Contraseña</h2>
                </div>
                <div class="panel-body">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <strong>Tenemos un problema...</strong><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/reset') }}">
                        <div>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="token" value="{{ $token }}">
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" name="email" placeholder="Correo electrónico" value="{{ old('email') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-key"></i>
                                    </span>
                                    <input type="password" class="form-control" name="password" placeholder="Contraseña">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-key"></i>
                                    </span>
                                    <input type="password" class="form-control" name="password_confirmation" placeholder="Confirmar contraseña">
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <div class="clearfix">
                                <button type="submit" class="btn btn-primary pull-right">Cambiar Contraseña</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
