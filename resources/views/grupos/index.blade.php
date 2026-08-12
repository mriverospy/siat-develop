@extends('layouts.list_layout')

@section('page-title', 'Grupo de Preguntas')

@section('title_options')
    @if ($currentUser->ability('sysadmin', $params['route_name'] . '-create'))
        <a href="{{ url($params['route']) }}/create" class="btn btn-primary"><i class="fa fa-fw fa-plus"></i> Agregar</a>
    @endif
    @if(Auth::user()->hasRole('sysadmin'))
        <a href="{{ url($params['route']) }}/masters_order" class="btn btn-warning"><i class="fa fa-fw fa-list-ol"></i> Ordenar Masters</a>
    @endif
@endsection

@section('table_items')
    <thead>
        <tr>
            <th class="text-center">ID</th>
            <th>Nombre</th>
            <th class="text-center">Cantidad Preguntas</th>
            <th class="text-center">Maestro?</th>
            <th class="text-center">Duplicable?</th>
            <th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($params['data']['items'] as $item)
        <tr class="{{ $item->total_preguntas == 0 ? 'danger' : '' }}">
            <td class="text-center">{{ $item->id }}</td>
            <td>
                {{ $item->nombre }}
                <br>
                <small>{{ $item->descripcion }}</small>
            </td>
            <td class="text-center">
                {{ $item->total_preguntas }}
            </td>
            <td class="text-center">
                @if ($item->master)
                <span class="label label-primary">Sí</span>
                @else
                <span class="label label-default">No</span>
                @endif
            </td>
            <td class="text-center">
                @if ($item->puede_duplicar)
                <span class="label label-primary">Sí</span>
                @else
                <span class="label label-default">No</span>
                @endif
            </td>
            <td class="text-center">
                <!-- Edit -->
                @if ($currentUser->ability('sysadmin', $params['route_name'].'-edit'))
                <a href="{{ url($params['route'], [$item->id, 'edit']) }}" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Editar este Grupo de Preguntas"><i class="fa fa-pencil"></i></a>
                @endif
                <!-- Preguntas -->
                @if ($currentUser->ability('sysadmin', 'preguntas-list'))
                <a href="{{ url($params['route'], [$item->id, 'preguntas']) }}" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Listar Preguntas en este Grupo de Preguntas"><i class="fa fa-tasks"></i></a>
                @endif
                <!-- Delete -->
                @if ($currentUser->ability('sysadmin', $params['route_name'].'-delete') AND $item->master === FALSE)
                <span data-toggle="tooltip" data-placement="top" title="Borrar este Grupo de Preguntas">
                    <a data-toggle="modal" data-target="#modalWindow" class="btn btn-danger btn-sm" data-id="{{ $item->id }}" data-message-text="Esta acción es **permanente** y se borrarán adicionalmente, todas las preguntas y respuestas asociadas a este Grupo de Preguntas!!!"><i class="fa fa-times"></i></a>
                </span>
                @endif
            </td>
        </tr>
        @endforeach

        @if (count($params['data']['items']) == 0)
            <tr><td colspan="3" class="h4 text-center">No se encontró ningún registro</td></tr>
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
