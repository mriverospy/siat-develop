@extends('layouts.list_layout')

@section('page-title', 'Instituciones')

@section('title_options')
    @if(Auth::user()->ability('sysadmin', $params['route_name'].'-create'))
        <a href="{{ url($params['route']) }}/create" class="btn btn-primary"><i class="fa fa-fw fa-plus"></i> Agregar</a>
    @endif
@endsection

@section('table_items')
    <thead>
        <tr>
            <th class="col-md-1 text-center">ID</th>
            <th class="col-md-3">Nombre</th>
            <th class="col-md-4">Descripcion</th>
            <th class="col-md-2 text-center">Días Edición</th>
            <th class="col-md-2 text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($params['data']['items'] as $item)
        <tr>
            <td class="text-center">{{ $item->id }}</td>
            <td>{{ $item->nombre }}</td>
            <td>{{ $item->descripcion }}</td>
            <td class="text-center">{{ $item->dias_edicion }}</td>
            <td class="text-center">
                <!-- Edit -->
                @if (Auth::user()->ability('sysadmin', $params['route_name'].'-edit'))
                    <a href="{{ url($params['route'], [$item->id, 'edit']) }}" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Editar esta Institución"><i class="fa fa-pencil"></i></a>
                @endif
                <!-- Grupos de Preguntas -->
                @if (Auth::user()->ability('sysadmin', 'grupos-nest'))
                    <a href="{{ url($params['route'], [$item->id, 'agrupar_grupos']) }}" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Agrupar Grupos de Preguntas asignados a esta Institución"><i class="fa fa-code-fork"></i></a>
                @endif
                <!-- Grupos de Preguntas -->
                @if (Auth::user()->ability('sysadmin', 'grupos-list'))
                    <a href="{{ url($params['route'], [$item->id, 'grupos_por_institucion']) }}" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Grupos de Preguntas asignados a esta Institución"><i class="fa fa-tasks"></i></a>
                @endif
                <!-- Delete -->
                @if (Auth::user()->ability('sysadmin', $params['route_name'].'-delete'))
                    <span data-toggle="tooltip" data-placement="top" title="Borrar esta Institución">
                        <a data-toggle="modal" data-target="#modalWindow" class="btn btn-danger btn-sm" data-id="{{ $item->id }}"><i class="fa fa-times"></i></a>
                    </span>
                @endif
            </td>
        </tr>
        @endforeach

        @if (count($params['data']['items']) == 0)
        <tr>
            <td colspan="5" class="h4 text-center">No se encontró ningún registro</td>
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
