<div class="widget stay-on-collapse">
    <div class="widget-body welcome-box tabular">
        <a href="{{ url('/home') }}">
            <img  class="img-responsive"src="{{ url($logo_principal) }}">
        </a>
    </div>
</div>

<div class="widget stay-on-collapse" id="widget-sidebar">
    <span class="widget-heading">Menu</span>
    <nav role="navigation" class="widget-body">
        @if (count($mainMenu) > 0)
            <ul class="acc-menu">
            @foreach($mainMenu as $topMenu)
                <?php if (is_array($topMenu['items']) AND count($topMenu['items']) == 0 AND $topMenu['is_home'] == false) continue; ?>
                <li class="{{ (count($topMenu['items']) > 0) ? 'hasChild' : '' }} {{ $topMenu['container_classes'] }}">
                    <a href="{{ $topMenu['url'] }}">
                        @if ( ! is_null($topMenu['icon_classes']))
                        <i class="{{ $topMenu['icon_classes'] }}"></i>
                        @endif
                        <span>{{ $topMenu['title'] }}</span>
                    </a>
                    @if (count($topMenu['items']) > 0)
                    <ul class="acc-menu">
                        @foreach($topMenu['items'] as $menu)
                            <li>
                                <a href="{{ $menu['url'] }}">
                                    @if ( ! is_null($menu['icon_classes']))
                                    <i class="{{ $menu['icon_classes'] }}"></i>
                                    @endif
                                    <span>{{ $menu['title'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    @endif
                </li>
            @endforeach
            </ul>
        @endif
    </nav>
</div>
