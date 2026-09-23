@extends('layouts.base_layout')

@section('title_options')
    @if ($currentUser->ability('sysadmin', $params['route_name'].'-create'))
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-fw fa-plus"></i> Agregar Pregunta <span class="caret"></span>
            </button>
            <ul class="dropdown-menu col-xs-12" role="menu">
                <span class="h5"> &nbsp; &nbsp; Tipo de Respuesta:</span>
                <li class="divider"></li>
                <li><a href="{{ url($params['route']) }}/create/text"><i class="fa fa-fw fa-tumblr"></i> Texto</a></li>
                <li><a href="{{ url($params['route']) }}/create/number"><i class="fa fa-fw fa-slack"></i> Número</a></li>
                <li><a href="{{ url($params['route']) }}/create/radio"><i class="fa fa-fw fa-dot-circle-o"></i> Selección Simple</a></li>
                <li><a href="{{ url($params['route']) }}/create/check"><i class="fa fa-fw fa-check-square-o"></i> Selección Múltiple</a></li>
                <li><a href="{{ url($params['route']) }}/create/date"><i class="fa fa-fw fa-calendar"></i> Fecha</a></li>
            </ul>
        </div>
    @endif
    @if ($currentUser->ability('sysadmin', $params['route_name'].'-edit'))
        <a href="#" id="saveOrderBtn" class="btn btn-success"><i class="fa fa-fw fa-save"></i> Guardar Orden</a>
    @endif
@endsection

@section('layout_content')
    @if (count($params['data']['items']) == 0)
        <div class="col-sm-12 h4 text-center"><strong>{{ $params['data']['grupo']->nombre }}</strong> no contiene preguntas, desea agregar una pregunta?</div>
        <div class="col-sm-12 text-center">
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-fw fa-plus"></i> Agregar Pregunta <span class="caret"></span>
                </button>
                <ul class="dropdown-menu col-xs-12" role="menu">
                    <span class="h5"> &nbsp; &nbsp; Tipo de Respuesta:</span>
                    <li class="divider"></li>
                    <li><a href="{{ url($params['route']) }}/create/text"><i class="fa fa-fw fa-tumblr"></i> Texto</a></li>
                    <li><a href="{{ url($params['route']) }}/create/number"><i class="fa fa-fw fa-slack"></i> Número</a></li>
                    <li><a href="{{ url($params['route']) }}/create/radio"><i class="fa fa-fw fa-dot-circle-o"></i> Selección Simple</a></li>
                    <li><a href="{{ url($params['route']) }}/create/check"><i class="fa fa-fw fa-check-square-o"></i> Selección Múltiple</a></li>
                    <li><a href="{{ url($params['route']) }}/create/date"><i class="fa fa-fw fa-calendar"></i> Fecha</a></li>
                </ul>
            </div>
        </div>
    @else
        <!--Número de Orden -->
        <div class="col-sm-1 text-center">
            @foreach($params['data']['items'] as $item)
                <div class="panel panel-collapsed"><div class="panel-heading h2">{{ $item->orden }}</div></div>
            @endforeach
        </div>

        <!-- Listado de Grupos de Preguntas -->
        <div class="col-sm-11">
            <div class="row ui-sortable" data-widget-group="group-demo">
                @foreach($params['data']['items'] as $item)
                    <div class="panel panel-collapsed" data-widget="{'id' : '{{ $item->id }}'}" data-pregunta-id="{{ $item->id }}">
                        <div class="panel-heading">
                            <div class="panel-ctrls button-icon-bg" >
                                @if ($currentUser->ability('sysadmin', $params['route_name'].'-delete'))
                                <span data-toggle="tooltip" data-placement="top" title="Borrar esta Pregunta">
                                    <a data-toggle="modal" class="button-icon" data-target="#modalWindow" data-id="{{ $item->id }}"><i class="fa fa-times"></i></a>
                                </span>
                                @endif
                                @if ($currentUser->ability('sysadmin', $params['route_name'].'-edit'))
                                    <a href="{{ url($params['route'], [$item->id, 'edit']) }}" class="button-icon" data-toggle="tooltip" data-placement="top" title="Editar esta Pregunta"><i class="fa fa-pencil"></i></a>
                                @endif
                            </div>
                            <h2>@if ($item->codigo)<span class="label label-info">{{ $item->codigo }}</span> @endif{{ $item->pregunta }} (ID: {{ $item->id }})</h2>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection

@section('modals')
    @parent
    @include('layouts.confirm_modal_layout', ['modalTitle' => 'Confirmación', 'modalSize' => 'modal-sm'])
@endsection

@section('footer-javascript')
    @parent
    <script>
        jQuery(document).ready(function($) {
            $.wijets().make();

            $('#saveOrderBtn').on('click', function (event) {
                var items = [];
                $('.ui-sortable .panel').each(function(index, element) {
                    items.push({ id: $(element).data('pregunta-id'), orden: index + 1});
                });

                $.ajax({
                    url: 'save_order',
                    type: 'POST',
                    data: {_token: {!! "'".Session::token()."'" !!}, items: items},
                    success: function(data) {
                        location.reload();
                    }
                });
            });
        });
    </script>
@endsection
