@extends('layouts.list_layout')

@section('page-title', 'Notificaciones')

@section('title_options')
    <a href="{{ route('notificaciones.flag-as-read') }}" class="btn btn-primary"><i class="fa fa-fw fa-eye"></i> Marcar Todo como Leído</a>
@endsection

@section('table_items')
    <thead>
        <tr>
            <th class="text-center">Fecha</th>
            <th>Asunto</th>
            <th class="text-center">Estado</th>
            <th class="col-lg-2 col-md-2 col-sm-3 col-xs-2 text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($params['data']['items'] as $item)
        <tr>
            <td class="text-center">{{ $item->fecha->timezone('America/Asuncion')->format('d/m/Y h:i a') }}</td>
            <td><a href="{{ route('notificaciones.show', [$item->id]) }}">{{ $item->asunto }}</a></td>
            <td class="text-center">
                @if ($item->leido)
                <span class="label label-default">Leído</span>
                @else
                <span class="label label-primary">No Leído</span>
                @endif
            </td>
            <td class="text-center">
                <a href="{{ route('notificaciones.show', [$item->id]) }}" class="btn btn-default btn-xs">Ver Mensaje</a>
            </td>
        </tr>
        @endforeach

        @if (count($params['data']['items']) == 0)
        <tr>
            <td colspan="5" class="h4 text-center">No se encontraron notificaciones.</td>
        </tr>
        @endif
    </tbody>
@endsection

@section('pagination')
    {!! $params['data']['items']->appends(Request::input())->render() !!}
@endsection
