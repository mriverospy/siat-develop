@extends('layouts.list_layout')

@section('page-title', 'Autorizaciones Enviadas')

@section('table_items')
    <thead>
        <tr>
            <th class="col-md-1 text-center">ID</th>
            <th>Accidente</th>
            <th>Autorizado a</th>
            <th class="text-center">Fecha</th>
            <th>Otorgado a</th>
            <th class="text-center">Desde</th>
            <th class="text-center">Hasta</th>
            <th class="col-lg-2 col-md-2 col-sm-3 col-xs-2 text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($params['data']['items'] as $item)
        <tr>
            <td class="text-center">{{ $item->id }}</td>
            <td>{{ $item->accidente->caratula }}</td>
            <td>
                {{ $item->usuario_receptor->nombres }} {{ $item->usuario_receptor->apellidos }}
                <br />
                {{ $item->usuario_receptor->sede->institucion->nombre }} / {{ $item->usuario_receptor->sede->nombre }}
            </td>
            <td class="text-center">{{ $item->fecha_autorizacion->timezone('America/Asuncion')->format('d/m/Y') }}</td>
            <td>{{ $item->usuario_receptor->nombres }} {{ $item->usuario_receptor->apellidos }}</td>
            <td class="text-center">{{ $item->fecha_inicio_autorizacion->timezone('America/Asuncion')->format('d/m/Y') }}</td>
            <td class="text-center">{{ $item->fecha_fin_autorizacion->timezone('America/Asuncion')->format('d/m/Y') }}</td>
            <td class="text-center">
                @if (Auth::user()->ability('sysadmin', 'accidentes-send-authorization'))
                    <span data-toggle="tooltip" data-placement="top" title="Cancelar esta Autorización">
                        <a data-toggle="modal" data-target="#modalWindow" class="btn btn-danger btn-sm" data-id="{{ $item->id }}"><i class="fa fa-times"></i></a>
                    </span>
                @endif
            </td>
        </tr>
        @if (trim($item->descripcion) != '')
        <tr>
            <td colspan="8" class="text-left">
                Descripción: {{ $item->descripcion }}
            </td>
        </tr>
        @endif
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
