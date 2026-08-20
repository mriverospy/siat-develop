@extends('layouts.list_layout')

@section('header-css')
    @parent
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap.min.css">
@endsection

@section('title_options')
    @if (Auth::user()->ability('sysadmin', $params['route_name'].'-create'))
        <a href="{{ url($params['route']) }}/create" class="btn btn-primary"><i class="fa fa-fw fa-plus"></i> Agregar</a>
    @endif
@endsection

@section('table_items')
    <thead>
        <tr>
            <th class="col-md-1 text-center">ID</th>
            <th>Nombre</th>
            <th class="col-lg-2 col-md-2 col-sm-3 col-xs-2 text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($params['data']['items'] as $item)
        <tr>
            <td class="text-center">{{ $item->id }}</td>
            <td>
                {{ $item->nombre }}
                @if ($item->compartido)
                    <small class="text-success"><i class="fa fa-star fa-fw"></i>Compartido</small>
                @endif
            </td>
            <td class="text-center">
                @if (Auth::user()->ability('sysadmin', $params['route_name'] . '-list'))
                    <a href="{{ url($params['route'], [$item->id]) }}" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Ejecutar este Reporte"><i class="fa fa-bolt"></i></a>
                @endif
                @if (Auth::user()->ability('sysadmin', $params['route_name'] . '-edit'))
                    <a href="{{ url($params['route'], [$item->id, 'edit']) }}" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Editar este Reporte"><i class="fa fa-pencil"></i></a>
                @endif
                @if (Auth::user()->ability('sysadmin', $params['route_name'] . '-delete'))
                    <span data-toggle="tooltip" data-placement="top" title="Borrar este Reporte">
                        <a data-toggle="modal" data-target="#modalWindow" class="btn btn-danger btn-sm" data-id="{{ $item->id }}"><i class="fa fa-times"></i></a>
                    </span>
                @endif
            </td>
        </tr>
        @endforeach

        @if (count($params['data']['items']) == 0)
        <tr><td colspan="4" class="h4 text-center">No se encontró ningún registro</td></tr>
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
