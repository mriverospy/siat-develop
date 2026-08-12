@extends('layouts.base_layout')

@section('page-title', 'Agrupar Grupos por Institución')

@section('title_options')
    <a href="{{ url('instituciones') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Volver</a>
    @if (Auth::user()->hasRole('sysadmin'))
        @if (Auth::user()->ability('sysadmin', $params['route_name'] . '-create'))
            <a href="{{ url($params['route']) }}/add" class="btn btn-primary"><i class="fa fa-fw fa-plus"></i> Agregar Grupo</a>
        @endif
    @else
        @if (Auth::user()->ability('sysadmin', 'grupos-create'))
            <a href="{{ url('/grupos/create') }}" class="btn btn-primary"><i class="fa fa-fw fa-plus"></i> Nuevo Grupo de Preguntas</a>
        @endif
    @endif
    @if (Auth::user()->ability('sysadmin', $params['route_name'] . '-edit'))
        <a href="#" id="saveNestingBtn" class="btn btn-success"><i class="fa fa-fw fa-save"></i> Guardar Agrupación</a>
    @endif
@endsection

@section('layout_content')
    <?php $grupos = $params['data']['items']; ?>
    @if (count($params['data']['items']) == 0)
        <div class="col-sm-12 h4 text-center">No se encontró ningún grupo de preguntas...</div>
        @if(Auth::user()->ability('sysadmin', $params['route_name'].'-create'))
            <div class="col-sm-12 text-center"><a href="{{ url($params['route']) }}/add" class="btn btn-xl btn-primary"><i class="fa fa-fw fa-plus"></i> Nuevo Grupo de Preguntas</a></div>
        @endif
    @else
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>AGRUPAR GRUPOS DE PREGUNTAS</h2>
            </div>
            <div class="panel-body">
                <div id="grupopreguntas-nestable" class="dd">
                    <ol class="dd-list">
                        @foreach($params['data']['nestable'] as $item)
                        <li class="dd-item dd3-item" data-id="{{ $item['id'] }}">
                            <div class="dd-handle dd3-handle"></div>
                            <div class="dd3-content">
                                {{ $grupos[$item['id']]['nombre'] }}
                                @if ((int)$grupos[$item['id']]['master'] == 1)
                                <span class="label label-success">MASTER</span>
                                @endif
                            </div>
                            @if (isset($item['children']))
                            <ol class="dd-list">
                                @foreach($item['children'] as $children)
                                <li class="dd-item dd3-item" data-id="{{ $children['id'] }}">
                                    <div class="dd-handle dd3-handle"></div>
                                    <div class="dd3-content">
                                        {{ $grupos[$children['id']]['nombre'] }}
                                        @if ((int)$grupos[$children['id']]['master'] == 1)
                                        <span class="label label-success">MASTER</span>
                                        @endif
                                    </div>
                                </li>
                                @endforeach
                            </ol>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                </div>
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
        $('#saveNestingBtn').on('click', function (e) {
            e.preventDefault();

            // Guardar agrupados
            $.ajax({
                method: 'POST',
                url: '{{ url('instituciones', [$params['data']['institucion']->id, 'agrupar_grupos']) }}',
                data: {
                    '_token': $('meta[name=_token]').attr('content'),
                    'grupos_agrupados': JSON.stringify($('#grupopreguntas-nestable').nestable('serialize'))
                },
                error: function() {
                    alert('Error al procesar su pedido, por favor, intente más tarde.')
                },
                success: function(response) {
                    if (response.status == 'success') {
                        location.reload();
                    }
                    else {
                        alert(response.message);
                    }
                }
            });
        })
    </script>
@endsection
