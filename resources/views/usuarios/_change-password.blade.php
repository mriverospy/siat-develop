@extends('partials.modal')

@section('modalContent')
    {!! Form::open(['id' => 'change-pass-form', 'method' => 'POST', 'url' => $params['route'] .'/' . $params['data']['item']['id'] . '/changepass', 'class' => 'form-horizontal']) !!}
        <div class="modal-body">
            <div class="row">
                <div class="col-xs-12">
                    <p>Introduzca una nueva contraseña para {!! $params['data']['item']['nombres'] !!} {!! $params['data']['item']['apellidos'] !!}:</p>
                </div>
                <div class="col-xs-12">
                    {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Nueva Contraseña']) !!}
                    <p class="help-block">La contraseña debe ser de al menos 6 caracteres, debe incluir mayúsculas, minúsculas y números. Cantidad máxima de caracteres es de 50.</p>
                </div>
                <div class="col-xs-12">
                    <div class="checkbox blue icheck">
                        <label>
                            <input type="checkbox" name="cambiar_password" value="1"> Requerir cambio de contraseña al iniciar sesión
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            {!! Form::button('Cancelar', ['type' => 'button', 'class' => 'btn btn-default', 'data-dismiss' => 'modal']) !!}
            {!! Form::button('Cambiar Contraseña', ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
@endsection

@section('footer-javascript')
    <script type="text/javascript" charset="utf-8">
        $('#modalWindow').on('show.bs.modal', function (event) {
            var delRoute = "{{ url($params['route']) }}/" + $(event.relatedTarget).attr('data-id');
            $('#delete-form').attr("action", delRoute);
        });
    </script>
@endsection
