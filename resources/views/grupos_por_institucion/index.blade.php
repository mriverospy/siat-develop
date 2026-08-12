@extends('layouts.base_layout')

@section('page-title', 'Grupos por Institución')

@section('title_options')
    <a href="{{ url('instituciones') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Volver</a>
    @if (Auth::user()->ability('sysadmin', 'grupos-nest'))
        <a href="{{ url('instituciones', [$params['data']['institucion']->id, 'agrupar_grupos']) }}" class="btn btn-default">
            <i class="fa fa-code-fork fa-fw"></i> Agrupar Grupos
        </a>
    @endif
    @if(Auth::user()->hasRole('sysadmin'))
        @if(Auth::user()->ability('sysadmin', $params['route_name'].'-create'))
            <a href="{{ url($params['route']) }}/add" class="btn btn-primary"><i class="fa fa-fw fa-plus"></i> Agregar Grupo</a>
        @endif
    @endif
    @if(Auth::user()->ability('sysadmin', 'grupos-create'))
        <a href="{{ url('/grupos/create') }}" class="btn btn-primary"><i class="fa fa-fw fa-plus"></i> Nuevo Grupo de Preguntas</a>
    @endif
    @if(Auth::user()->ability('sysadmin', $params['route_name'].'-edit'))
        <a href="#" id="saveOrderBtn" class="btn btn-success"><i class="fa fa-fw fa-save"></i> Guardar Orden</a>
    @endif
@endsection

@section('layout_content')
    @if (count($params['data']['items']) == 0)
        <div class="col-sm-12 h4 text-center">No se encontró ningún grupo de preguntas.</div>
        {{-- @if(Auth::user()->ability('sysadmin', $params['route_name'].'-create'))
            <div class="col-sm-12 text-center"><a href="{{ url($params['route']) }}/add" class="btn btn-xl btn-primary"><i class="fa fa-fw fa-plus"></i> Nuevo Grupo de Preguntas</a></div>
        @endif --}}
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
                    <div class="panel panel-collapsed" data-widget="{'id' : '{{ $item->id }}'}" data-grupo-id="{{ $item->id }}">
                        <div class="panel-heading">
                            <div class="panel-ctrls button-icon-bg" >
                                @if(Auth::user()->ability('sysadmin', $params['route_name'].'-delete'))
                                    <div class="panel-ctrls button-icon-bg" >
                                        <a data-toggle="modal" class="button-icon" data-target="#modalWindow" data-id="{{ $item->grupoDePreguntas->id }}"><i class="fa fa-times"></i></a>
                                    </div>
                                @endif
                                @if(!Auth::user()->hasRole('sysadmin'))
                                    @if ($currentUser->ability('sysadmin', 'preguntas-list'))
                                        <a href="{{ url('/grupos/'.$item->grupoDePreguntas->id.'/preguntas') }}" class="button-icon"><i class="fa fa-tasks"></i></a>
                                    @endif
                                    @if ($currentUser->ability('sysadmin', 'grupos-edit'))
                                        <a href="{{ url('grupos', [$item->grupoDePreguntas->id, 'edit']) }}" class="button-icon"><i class="fa fa-pencil"></i></a>
                                    @endif
                                @endif
                            </div>
                            <h2>
                                {{ $item->grupoDePreguntas->nombre }}
                                @if ($item->grupoDePreguntas->puede_duplicar)
                                (<i class="fa fa-copy fa-lg"></i> Duplicable)
                                @endif
                            </h2>
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
        $.wijets().make();

        $('#saveOrderBtn').on('click', function (event) {
            var items = [];
            $('.ui-sortable .panel').each(function(index, element) {
                items.push({ id: $(element).data('grupo-id'), orden: index + 1});
            });

            $.ajax({
              url: "save_order",
              type: "POST",
              data: {_token: {!! "'".Session::token()."'" !!}, items: items},
              success: function(data){
                  location.reload();
              }
            });
        })
    </script>
@endsection
