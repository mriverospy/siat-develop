@extends('layouts.base_layout')

@section('page-title', 'Grupos Master\'s')

@section('title_options')
    @if(Auth::user()->hasRole('sysadmin'))
        <a href="#" id="saveOrderBtn" class="btn btn-success"><i class="fa fa-fw fa-save"></i> Guardar Orden</a>
    @endif
@endsection

@section('layout_content')

    @if (count($params['data']['items']) == 0)
        <div class="col-sm-12 h4 text-center">No se encontró ningún Grupo Master...</div>
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
                                <h2>{{ $item->nombre }}</h2>
                            </div>
                        </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection


@section('footer-javascript')
    @parent
    <!--wijets-->
    <script type="text/javascript" charset="utf-8">
        $.wijets().make();

        $('#saveOrderBtn').on('click', function (event) {
            var items = [];
            $('.ui-sortable .panel').each(function(index, element) {
                items.push({ id: $(element).data('grupo-id'), orden: index + 1});
            });

            $.ajax({
                url: 'save_masters_order',
                type: 'POST',
                data: {
                    '_token': {!! "'".Session::token()."'" !!},
                    items: items
                },
                success: function(data){
                    location.reload();
                }
            });
        })
    </script>
@endsection
