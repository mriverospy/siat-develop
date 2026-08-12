@extends('layouts.list_layout')

@section('page_filters')
    <form action="" method="GET" accept-charset="UTP-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>FILTRAR DATOS</h2>
                <div class="panel-ctrls">
                    <div class="pull-right">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                    <div class="form-inline row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::text('fecha_inicio', $params['current_filters']['fecha_inicio'], ['class' => 'form-control datepicker', 'placeholder' => 'Fecha Inicio']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::text('fecha_fin', $params['current_filters']['fecha_fin'], ['class' => 'form-control datepicker', 'placeholder' => 'Fecha Fin']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::select('accion', $params['data']['acciones'], $params['current_filters']['accion'], ['class' => 'form-control', 'placeholder' => 'Acción']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::select('usuario', $params['data']['usuarios'], $params['current_filters']['usuario'], ['class' => 'form-control', 'placeholder' => 'Usuario']) !!}
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </form>
@endsection

@section('table_items')
    <thead>
        <tr>
            <th class="text-center">ID Usuario</th>
            <th>Usuario</th>
            <th class="text-center">Acción</th>
            <th class="text-center">Fecha Acción</th>
        </tr>
    </thead>
    <tbody>
        @foreach($params['data']['items'] as $item)
        <tr>
            <td class="text-center">{{ $item->usuario->id }}</td>
            <td>{{ $item->usuario->nombres }} {{ $item->usuario->apellidos }}</td>
            <td class="text-center">
            @if ($item->accion == 'login')
                Inicio Sesión
            @else
                Cierre Sesión
            @endif
            </td>
            <td class="text-center">{{ $item->created_at }}</td>
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
