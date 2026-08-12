@if ($errors->any())
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>Tenemos algunos problemas...</strong><br>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(Session::has('messages'))
    <?php
        $msg = Session::get('messages');

        if($msg['type'] == 'error') {
            $msg['type'] = 'alert-danger';
        } elseif($msg['type'] == 'success') {
            $msg['type'] = 'alert-success';
        } elseif($msg['type'] == 'warning') {
            $msg['type'] = 'alert-warning';
        } else {
            $msg['type'] = 'alert-info';
        }
    ?>
    <div class="alert {{ $msg['type'] }}">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>{{ $msg['text'] }}</strong><br>
    </div>
@endif
