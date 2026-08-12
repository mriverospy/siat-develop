@extends('layouts.base_layout')

@section('layout_content')
    @yield('page_filters')

	<div class="panel panel-default">
		<div class="panel-heading">
			<h2>{{ $params['subtitle'] }}</h2>
			<div class="panel-ctrls">
				<div class="dataTables_filter pull-right">
				@if (isset($params['search']) and $params['search'] == 'YES')
					<form action="" method="GET" accept-charset="UTP-8">
						<label class="panel-ctrls-center">
							<input type="search" id="bx_filter" name="filter" class="form-control" value="{{ $params['filter'] }}" placeholder="Buscar...">
						</label>
					</form>
				@endif
				</div>
			</div>
		</div>
		<div class="panel-body panel-no-padding">
			<div id="example_wrapper" class="dataTables_wrapper form-inline no-footer">
				<div class="row">
					<div class="col-sm-6"></div>
					<div class="col-sm-6"></div>
				</div>

				<div class="table-responsive">
					<table class="table table-striped table-bordered dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="list-table" style="width: 100%;">
	                    @yield('table_items')
					</table>
				</div>
			</div>
		</div>
		<div class="panel-footer">
			<div class="pull-right mr10">
				@yield('pagination')
			</div>
		</div>
	</div>
@endsection

@section('footer-javascript')
    <script type="text/javascript" charset="utf-8">
        $('document').ready(function(){
            $('#bx_filter').focus();
            $('#bx_filter').select();
        });
    </script>
@endsection
