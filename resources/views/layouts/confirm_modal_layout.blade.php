@extends('partials.modal')

@section('modalContent')
    <div class="modal-body">
        <p>Realmente desea borrar el registro seleccionado?</p>
    </div>
    <div class="modal-footer">
        {!! Form::open(['id' => 'delete-form', 'method' => 'DELETE', 'url'=>$params['route'], 'class'=>'form-inline']) !!}
        {!! Form::button('Cancelar', ['type' => 'button', 'class' => 'btn btn-default','data-dismiss' => 'modal']) !!}
        {!! Form::button('Eliminar Registro', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    </div>
@endsection

@section('footer-javascript')
    @parent
    <script type="text/javascript" charset="utf-8">
        $('#modalWindow').on('show.bs.modal', function (event) {
            var messageText = $(event.relatedTarget).data('message-text');
            if (messageText) {
                $('#modalWindow').find('div.modal-body').append($('<p class="text-danger">' + messageText + '</p>'));
            }
            else {
                $('#modalWindow').find('div.modal-body').html($('<p>Realmente desea borrar el registro seleccionado?</p>'));
            }
            var id = $(event.relatedTarget).data('id');
            var delRoute = '{{ url($params['route']) }}/' + id;
            $('#delete-form').attr('action', delRoute);
        })
    </script>
@endsection
