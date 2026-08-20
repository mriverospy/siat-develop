@extends('layouts.list_layout')

@section('page-title', 'Derivaciones')

<!-- Table List Items -->
@section('table_items')
    <thead>
        <tr>
            <th class="col-md-1 text-center" rowspan="2">ID</th>
            <th rowspan="2" class="text-center">Estado</th>
            <th rowspan="2">Accidente</th>
            <th colspan="3" class="text-center">Enviado por</th>
            <th rowspan="2">Recibido</th>
            <th rowspan="2" class="col-lg-2 col-md-2 col-sm-3 col-xs-2 text-center">Acciones</th>
        </tr>
        <tr>
            <th>Institución</th>
            <th>Sede</th>
            <th>Usuario</th>
        </tr>
    </thead>
    <tbody>
        @foreach($params['data']['items'] as $item)
        <tr>
            <td class="text-center">{{ $item->id }}</td>
            <td class="text-center">
                <span class="label {{ ($item->estado == 'recibido') ? 'label-success' : 'label-default' }}">{{ strtoupper($item->estado) }}</span>
            </td>
            <td>{{ $item->accidente->caratula }}</td>
            <td>{{ $item->institucion_emisor->nombre }}</td>
            <td>{{ $item->sede_emisor->nombre }}</td>
            <td>{{ $item->usuario_emisor->nombres }} {{ $item->usuario_emisor->apellidos }}</td>
            <td>
                <span data-toggle="tooltip" data-placement="top" title="recibido el {{ $item->fecha_derivacion->timezone('America/Asuncion')->format('d/m/Y') }} a las {{ $item->fecha_derivacion->timezone('America/Asuncion')->format('h:i a') }}">
                    {{ $item->fecha_derivacion->diffForHumans() }}
                </span>
            </td>
            <td class="text-center">
                @if ($item->estado != 'recibido')
                    @if (Auth::user()->ability('sysadmin', 'accidentes-receive'))
                    <span data-toggle="tooltip" data-placement="top" title="Aceptar esta Derivación">
                        <a data-toggle="modal" data-target="#aceptar-derivacion" class="btn btn-success btn-sm" data-action="{{ route('derivaciones.update', $item->id) }}">
                            <i class="fa fa-check"></i>
                        </a>
                    </span>
                    @endif

                    @if (Auth::user()->ability('sysadmin', 'accidentes-receive'))
                    <span data-toggle="tooltip" data-placement="top" title="Rechazar esta Derivación">
                        <a data-toggle="modal" data-target="#rechazar-derivacion" class="btn btn-danger btn-sm" data-action="{{ route('derivaciones.destroy', $item->id) }}">
                            <i class="fa fa-times"></i>
                        </a>
                    </span>
                    @endif
                @endif
            </td>
        </tr>
        @endforeach

        @if (count($params['data']['items']) == 0)
            <tr><td colspan="8" class="h4 text-center">No se encontró ningún registro</td></tr>
        @endif
    </tbody>
@endsection

<!-- Pagination buttons -->
@section('pagination')
    {!! $params['data']['items']->appends(Request::input())->render() !!}
@endsection

@section('modals')
    @parent
    <div class="modal fade" id="aceptar-derivacion" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h2 class="modal-title">Aceptar Derivación</h2>
                </div>
                <div class="modal-body">
                    <p>Realmente desea aceptar la derivación seleccionada?</p>
                </div>
                <div class="modal-footer">
                    {!! Form::open(['id' => 'aceptar-derivacion-form', 'method' => 'PUT', 'url' => $params['route'], 'class' => 'form-inline']) !!}
                    {!! Form::button('Cancelar', ['type' => 'button', 'class' => 'btn btn-default','data-dismiss' => 'modal']) !!}
                    {!! Form::button('Aceptar Derivación', ['type' => 'submit', 'class' => 'btn btn-success']) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="rechazar-derivacion" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h2 class="modal-title">Rechazar Derivación</h2>
                </div>
                <div class="modal-body">
                    <p>Realmente desea rechazar la derivación seleccionada?</p>
                </div>
                <div class="modal-footer">
                    {!! Form::open(['id' => 'rechazar-derivacion-form', 'method' => 'DELETE', 'url' => $params['route'], 'class' => 'form-inline']) !!}
                    {!! Form::button('Cancelar', ['type' => 'button', 'class' => 'btn btn-default','data-dismiss' => 'modal']) !!}
                    {!! Form::button('Rechazar Derivación', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-javascript')
    @parent
    <script>
        jQuery(document).ready(function($) {
            $('#aceptar-derivacion').on('show.bs.modal', function (event) {
                $('#aceptar-derivacion-form').attr('action', $(event.relatedTarget).data('action'));
            });

            $('#rechazar-derivacion').on('show.bs.modal', function (event) {
                $('#rechazar-derivacion-form').attr('action', $(event.relatedTarget).data('action'));
            });
        });
    </script>
@endsection

