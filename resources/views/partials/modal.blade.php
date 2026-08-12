<div class="modal fade" id="modalWindow" tabindex="-1" role="dialog" aria-labelledby="modalWindowLabel" aria-hidden="true" >
    <div class="modal-dialog  {!! $modalSize !!}">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title">{!! $modalTitle !!}</h2>
            </div>
            @yield('modalContent')
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

