@extends('layouts.list_layout')

@section('table_items')
    <thead>
        <tr>
            <th>Grupo de Preguntas</th>
            <th class="col-lg-2 col-md-2 col-sm-3 col-xs-2 text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($params['data']['items'] as $item)
        <tr>
            <td><i>{{ $item->nombre }}</i></td>
            <td class="text-center">
                <a href="{{ url($params['route'], [$item->id, 'add_group']) }}" class="btn btn-success btn-sm">Agregar</a>
            </td>
        </tr>
        @endforeach

        @if (count($params['data']['items']) == 0)
        <tr>
            <td colspan="2" class="h4 text-center">
                No existen Grupos de Preguntas disponibles para agregar a esta institución.
                @if ($currentUser->ability('sysadmin', $params['route_name'] . '-create'))
                <br />
                <a href="{{ url('grupos/create') }}" class="btn btn-primary"><i class="fa fa-fw fa-plus"></i> Agregar nuevo Grupo de Preguntas</a>
                @endif
            </td>
        </tr>
        @endif
    </tbody>
@endsection
