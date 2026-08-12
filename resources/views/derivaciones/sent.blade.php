@extends('layouts.list_layout')

@section('page-title', 'Derivaciones')

@section('table_items')
    <thead>
        <tr>
            <th class="col-md-1 text-center" rowspan="2">ID</th>
            <th rowspan="2">Estado</th>
            <th rowspan="2">Accidente</th>
            <th colspan="3" class="text-center">Destinatario</th>
            <th rowspan="2">Enviado</th>
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
            <td class='text-center' >{{ $item->id }}</td>
            <td>
                <span class="label {{ ($item->estado == 'recibido') ? 'label-success' : 'label-default' }}">{{ strtoupper($item->estado) }}</span>
            </td>
            <td>{{ $item->accidente->caratula }}</td>
            <td>{{ $item->institucion_receptor->nombre }}</td>
            <td>{{ $item->sede_receptor ? $item->sede_receptor->nombre : 'Abierto' }}</td>
            <td>{{ $item->usuario_receptor ? $item->usuario_receptor->nombres . ' ' . $item->usuario_receptor->apellidos : 'Abierto' }}</td>
            <td>
                <span data-toggle="tooltip" data-placement="top" title="enviado el {{ $item->fecha_derivacion->timezone('America/Asuncion')->format('d/m/Y') }} a las {{ $item->fecha_derivacion->timezone('America/Asuncion')->format('h:i a') }}">
                    {{ $item->fecha_derivacion->diffForHumans() }}
                </span>
            </td>
            <td class="text-center">
                @if (Auth::user()->ability('sysadmin', 'accidentes-send') AND $item->estado == 'pendiente')
                    <span data-toggle="tooltip" data-placement="top" title="Cancelar esta Derivación">
                        <a data-toggle="modal" data-target="#modalWindow" class="btn btn-danger btn-sm" data-id="{{ $item->id }}"><i class="fa fa-times"></i></a>
                    </span>
                @endif
            </td>
        </tr>
        @endforeach

        @if (count($params['data']['items']) == 0)
            <tr><td colspan="8" class="h4 text-center">No se encontró ningún registro</td></tr>
        @endif
    </tbody>
@endsection

@section('pagination')
    {!! $params['data']['items']->appends(Request::input())->render() !!}
@endsection

@section('modals')
    @parent
    @include('layouts.confirm_modal_layout', ['modalTitle' => 'Confirmación', 'modalSize' => 'modal-sm'])
@endsection
