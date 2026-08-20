@extends('login')

@section('page-title', 'Recuperar Contraseña')

@section('content')
<div class="container" id="forgotpassword-form">
    <a href="{{ url('/') }}" class="login-logo"><img src="{{ url($logo_login) }}"></a>
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2>Recuperar Contraseña</h2>
                </div>
                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <strong>Tenemos un problema...</strong> <br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/email') }}">
                        <div><input type="hidden" name="_token" value="{{ csrf_token() }}"></div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <p>Ingresa tu dirección de correo electrónico para recuperar tu contraseña. Enviaremos un mensaje, a la dirección que especifiques, con un enlace.<br />Para cambiar tu contraseña, haz click en el enlace e introduce tu nueva contraseña.</p>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" placeholder="Correo electrónico" name="email" value="{{ old('email') }}">
                                </div>
                            </div>
                        </div>


                        <div class="panel-footer">
                            <div class="clearfix">
                                <a href="{{ url('/') }}" class="btn btn-default pull-left">Volver</a>
                                <button type="submit" class="btn btn-primary pull-right">Recuperar Contraseña</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
