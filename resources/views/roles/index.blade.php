@extends('layouts.list_layout')

@section('page-title', 'Roles')

@section('title_options')
    @if ($currentUser->ability('sysadmin', 'roles-create'))
        <a href="{{ url($params['route']) }}/create" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Agregar un nuevo Rol"><i class="fa fa-fw fa-plus"></i> Agregar</a>
    @endif
@endsection

@section('table_items')
    <thead>
        <tr>
            <th class="text-center">ID</th>
            <th>Nombre</th>
            <th>Nombre para Mostrar</th>
            <th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($params['data']['items'] as $item)
        <tr>
            <td class="text-center">{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->display_name }}</td>
            <td class="text-center">
                <!-- Edit -->
                @if ($currentUser->ability('sysadmin', 'roles-edit'))
                <a href="{{ url($params['route'], [$item->id, 'edit']) }}" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Editar este Rol"><i class="fa fa-pencil"></i></a>
                @endif

                <!-- Permisos -->
                @if ($item->name != 'sysadmin' AND $currentUser->ability('sysadmin', 'permisos_por_rol-edit'))
                <a href="{{ url($params['route'], [$item->id, 'permisos']) }}" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Gestionar Permisos de este Rol"><i class="fa fa-tasks"></i></a>
                @endif

                <!-- Delete -->
                @if ($currentUser->ability('sysadmin', 'roles-delete') AND (trim(strtolower($item->name)) != 'sysadmin'))
                <span data-toggle="tooltip" data-placement="top" title="Borrar este Rol">
                    <a data-toggle="modal" data-target="#modalWindow" class="btn btn-danger btn-sm" data-id="{{ $item->id }}"><i class="fa fa-times"></i></a>
                </span>
                @endif
            </td>
        </tr>
        @endforeach

        @if (count($params['data']['items']) == 0)
        <tr>
            <td colspan="4" class="h4 text-center">No se encontró ningún registro</td>
        </tr>
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
