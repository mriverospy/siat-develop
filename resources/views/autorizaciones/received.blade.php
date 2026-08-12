@extends('layouts.list_layout')

@section('page-title', 'Autorizaciones Recibidas')

@section('table_items')
    <thead>
        <tr>
            <th class="col-md-1 text-center">ID</th>
            <th>Accidente</th>
            <th>Autorizado por</th>
            <th class="text-center">Fecha</th>
            <th>Otorgado a</th>
            <th class="text-center">Desde</th>
            <th class="text-center">Hasta</th>
            <th>Vence</th>
            <th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($params['data']['items'] as $item)
        <tr>
            <td class='text-center' >{{ $item->id }}</td>
            <td>{{ $item->accidente->caratula }}</td>
            <td>{{ $item->usuario_emisor->nombres }} {{ $item->usuario_emisor->apellidos }}</td>
            <td class="text-center">{{ $item->fecha_autorizacion->timezone('America/Asuncion')->format('d/m/Y') }}</td>
            <td>{{ $item->usuario_receptor->nombres }} {{ $item->usuario_receptor->apellidos }}</td>
            <td class="text-center">{{ $item->fecha_inicio_autorizacion->timezone('America/Asuncion')->format('d/m/Y') }}</td>
            <td class="text-center">{{ $item->fecha_fin_autorizacion->timezone('America/Asuncion')->format('d/m/Y') }}</td>
            <td>{{ $item->fecha_fin_autorizacion->diffForHumans() }}</td>
            <td class="text-center">
                <div class="btn-group">
                    <a href="{{ route('autorizaciones.show', [$item->id]) }}" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Autorización de Cambios"><i class="fa fa-eye"></i></a>
                </div>
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
