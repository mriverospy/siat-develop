@extends('layouts.list_layout')

@section('page-title', 'Accidentes')

@section('title_options')
    <a href="{{ route('accidentes.index', ['estado' => 'abierto']) }}" class="btn btn-default {{ $params['estado'] == 'abierto' ? 'active' : '' }}"><i class="fa fa-fw fa-folder-open"></i>Accidentes Abiertos</a>
    <a href="{{ route('accidentes.index', ['estado' => 'cerrado']) }}" class="btn btn-default {{ $params['estado'] == 'cerrado' ? 'active' : '' }}"><i class="fa fa-fw fa-folder"></i>Accidentes Cerrados</a>
    <a href="{{ route('accidentes.bajar-formulario-unico') }}" class="btn btn-inverse"><i class="fa fa-fw fa-print"></i> Formulario Único en Blanco</a>
    @if ($currentUser->ability('sysadmin', $params['route_name'] . '-create'))
    <a href="{{ url($params['route']) }}/cargar" class="btn btn-primary"><i class="fa fa-fw fa-plus"></i> Agregar Accidente</a>
    @endif
@endsection

@section('table_items')
    <thead>
        <tr>
            <th class="text-center">ID</th>
            <th>Carátula/Lugar</th>
            <th class="text-center">Apertura</th>
            <th class="text-center">Cierre</th>
            <th class="text-center">Estado</th>
            <th>Cargado por</th>
            <th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($params['data']['items'] as $item)
            <tr>
                <td class="text-center">{{ $item->id }}</td>
                <td>
                    <a href="{{ url($params['route'], [$item->id]) }}">
                        {{ $item->caratula }}
                    </a>
                    <br />
                    {!! implode('<br>', $item->lugar_accidente) !!}
                </td>
                <td class="text-center">
                    {{ $item->fecha_apertura->timezone('America/Asuncion')->format('d/m/Y h:i a') }}
                </td>
                <td class="text-center">
                    {{ $item->fecha_cierre->timezone('America/Asuncion')->format('d/m/Y h:i a') }}
                </td>
                <td class="text-center">
                    @if ($item->estado == 'abierto')
                    <span class="label label-warning">ABIERTO</span>
                    @else
                    <span class="label label-success">CERRADO</span>
                    @endif
                </td>
                <td>
                    {{ $item->usuario->nombreCompleto() }}
                    <br />
                    Sede: {{ $item->sede->nombre }}
                    <br />
                    Institución: {{ $item->institucion->nombre }}
                </td>
                <td class="text-center">
                    <div class="btn-group">
                        @if ($currentUser->ability('sysadmin', $params['route_name'] . '-view'))
                        <a href="{{ url($params['route'], [$item->id]) }}" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Datos del Accidente"><i class="fa fa-eye"></i></a>
                        @endif

                        @if ($currentUser->ability('sysadmin', $params['route_name'] . '-send'))
                        <a href="{{ url($params['route'], [$item->id, 'send']) }}" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Derivar este Accidente"><i class="fa fa-level-down"></i></a>
                        @endif

                        @if ($currentUser->ability('sysadmin', $params['route_name'] . '-send-authorization'))
                        <a href="{{ url($params['route'], [$item->id, 'authorize']) }}" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Autorizar Cambios en este Accidente"><i class="fa fa-unlock-alt"></i></a>
                        @endif

                        @if ($currentUser->ability('sysadmin', $params['route_name'] . '-pdf-report'))
                        <a href="{{ url($params['route'], [$item->id, 'pdf']) }}" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Bajar Informe del Accidente en PDF"><i class="fa fa-download"></i></a>
                        @endif

                        <!-- Edit -->
                        @if ($currentUser->ability('sysadmin', $params['route_name'] . '-edit') and $item->estado == 'abierto')
                            <a href="{{ url($params['route'], [$item->id, 'editar']) }}" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Editar este Accidente"><i class="fa fa-pencil"></i></a>
                        @endif

                        <!-- Delete -->
                        @if ($currentUser->ability('sysadmin', $params['route_name'] . '-delete'))
                        <a data-toggle="modal" data-target="#modalWindow" class="btn btn-danger btn-sm" data-id="{{ $item->id }}" ><i class="fa fa-times" data-toggle="tooltip" data-placement="top" title="Borrar este Accidente"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach

        @if (count($params['data']['items']) == 0)
        <tr>
            <td colspan="6" class="text-center">No se encontró ningún registro</td>
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
