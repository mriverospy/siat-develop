@extends('layouts.base_layout')

@section('page-title', 'Permisos por Rol')

@section('title_options')
    @if (Auth::user()->ability('sysadmin', 'permisos-edit') and $params['data']['rol']['name'] != 'sysadmin')
        {!! Form::open(['id' => 'permisos-form', 'method' => 'POST', 'url'=>$params['route'] . '/save_permisos']) !!}
        {!! Form::submit('Guardar Permisos', ['class'=>'btn btn-success']) !!}
    @endif
@endsection

@section('layout_content')

    @if ($params['data']['rol']['name'] == 'sysadmin')
        <div class="col-sm-12 h4 text-center">El Rol de Administrador del Sistema posee todos los permisos!</div>
    @else
        <?php $actual = $titulo = ''; ?>

        @foreach($params['data']['items'] as $item)
            <?php $grupo = explode('-', $item['name'])[0]; ?>

            @if ($grupo != $actual)
                {!! $actual != '' ? '</div></div></div>' : '' !!}
                <?php $actual = $grupo; $titulo = strtoupper(str_replace('_', ' ', $actual)); ?>
                <div class='col-lg-4 col-md-6 col-sm-12'>
                    <div class='panel panel-default'>
                        <div class='panel-heading'><h2>{{$titulo}}</h2>
                            <div class='panel-ctrls'>
                                <a href='#' class='button-icon' data-target='{{ $actual }}'><i class='fa fa-check-square-o'></i></a>
                            </div>
                        </div>
                        <div class='panel-body' style="min-height: 160px">
            @endif
            <div class="checkbox-block">
                <label>
                    <input class="{{ $actual }}" type="checkbox" name="permisos[]" value="{{ $item['id'] }}" {{ isset($item['rol_id']) ? 'checked="true"' : '' }}> {{ $item['display_name'] }}
                </label>
            </div>
        @endforeach
        </div></div></div>
        {!! Form::close() !!}
    @endif
@endsection

@section('footer-javascript')
    @parent
    <script type="text/javascript" charset="utf-8">
        jQuery(document).ready(function($) {
            $('a.button-icon').on('click', function (e) {
                e.preventDefault();
                if ($('input.' + $(this).data('target')).prop('checked') == true) {
                    $('input.' + $(this).data('target')).prop('checked', false);
                }
                else {
                    $('input.' + $(this).data('target')).prop('checked', true);
                }
            });
        });
    </script>
@endsection
