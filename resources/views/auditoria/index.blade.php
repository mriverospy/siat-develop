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
                                {!! Form::select('objeto', $params['data']['objetos'], $params['current_filters']['objeto'], ['class' => 'form-control', 'placeholder' => 'Objeto']) !!}
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
            <th class="text-center">ID Objeto</th>
            <th class="text-center">Objeto</th>
            <th>Usuario</th>
            <th class="text-center">Acción</th>
            <th class="text-center">Campo</th>
            <th>Fecha Acción</th>
        </tr>
    </thead>
    <tbody>
        @foreach($params['data']['items'] as $item)
        <tr>
            <td class="text-center">{{ $item->revisionable_id }}</td>
            <td class="text-center">{{ str_replace('App\\Models\\', '', $item->revisionable_type) }}</td>
            <td>
                @if ($item->usuario)
                {{ $item->usuario->nombres }} {{ $item->usuario->apellidos }}
                @endif
            </td>
            @if ($item->key == 'created_at')
            <td class="text-center" colspan="2">Creación</td>
            <td>{{ $item->created_at }}</td>
            @else
            <td class="text-center">Modificación</td>
            <td class="text-center">{{ $item->key }}</td>
            <td>{{ $item->created_at }}</td>
            @endif
        </tr>
        @if ($item->key != 'created_at')
        <tr>
            <td colspan="6">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Valor Anterior</h5>
                        {{ $item->old_value }}
                    </div>
                    <div class="col-md-6">
                        <h5>Nuevo Valor</h5>
                        {{ $item->new_value }}
                    </div>
                </div>
            </td>
        </tr>
        @endif
        @endforeach

        @if (count($params['data']['items']) == 0)
        <tr>
            <td colspan="6" class="h4 text-center">No se encontró ningún registro</td>
        </tr>
        @endif
    </tbody>
@endsection

@section('pagination')
    {!! $params['data']['items']->appends(Request::input())->render() !!}
@endsection
