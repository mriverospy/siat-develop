<header id="topnav" class="navbar navbar-inverse navbar-fixed-top clearfix" role="banner">
    <a id="leftmenu-trigger" class="" data-toggle="tooltip" data-placement="right" title="Ver/Ocultar Barra Lateral"></a>
    <a class="navbar-brand" href="{{ url('/') }}" style="background-image: url({{ url($logo_cabecera) }})"></a>
    @if (Auth::guest())
    @else
    <ul class="nav navbar-nav toolbar pull-right">
        @if ($notificaciones_no_leidas > 0)
        <li class="dropdown toolbar-icon-bg">
            <a href="#" class="hasnotifications dropdown-toggle" data-toggle="dropdown">
                <span class="icon-bg"><i class="fa fa-fw fa-bell"></i></span>
                <span class="badge badge-primary">{{ $notificaciones_no_leidas }}</span>
            </a>
            <div class="dropdown-menu notifications arrow">
                <div class="dd-header">
                    <span>Notificaciones</span>
                    <!-- <span><a href="#">Configuración</a></span> -->
                </div>
                <ul class="scrollthis">
                    @foreach($notificaciones as $item)
                    <li>
                        <a href="{{ route('notificaciones.show', [$item->id]) }}" class="notification-inverse">
                            <div class="notification-icon"><i class="fa fa-envelope fa-fw"></i></div>
                            <div class="notification-content">{{ $item->asunto }}</div>
                            <div class="notification-time">{{ $item->fecha->timezone('America/Asuncion')->diffForHumans() }}</div>
                        </a>
                    </li>
                    @endforeach
                </ul>
                <div class="dd-footer">
                    <a href="{{ route('notificaciones.index') }}">Ver todas las notificaciones</a>
                </div>
            </div>
        </li>
        @else
        <li>
            <a href="{{ route('notificaciones.index') }}">
                <span class="icon-bg"><i class="fa fa-fw fa-bell"></i></span>
            </a>
        </li>
        @endif
        <li class="dropdown">
            <a href="#" class="dropdown-toggle username" data-toggle="dropdown">
                <span class="hidden-xs"><i class="fa fa-fw fa-institution"></i> {{ $currentUser->sede->institucion->nombre }}/{{ $currentUser->sede->nombre }} <i class="fa fa-fw fa-user"></i> {{ $currentUser->nombreCompleto() }}</span>
            </a>
            <ul class="dropdown-menu userinfo">
                <li>
                    <a href="{{ route('user.profile') }}">
                        <span class="pull-left">Editar Perfil</span> <i class="pull-right fa fa-pencil"></i>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="{{ url('logout') }}">
                        <span class="pull-left">Cerrar sesión</span> <i class="pull-right fa fa-sign-out"></i>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
    @endif
</header>
