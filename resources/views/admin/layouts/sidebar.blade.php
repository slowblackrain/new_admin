<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>대시보드</p>
            </a>
        </li>

        @foreach(config('admin_menu') as $key => $section)
        <li class="nav-item has-treeview {{ request()->is('admin/'. $key .'*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('admin/'. $key .'*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-folder"></i>
                <p>
                    {{ $section['name'] }}
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                @foreach($section['items'] as $item)
                <li class="nav-item">
                    <a href="{{ url($item['url']) }}" class="nav-link {{ request()->getRequestUri() == $item['url'] ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>{{ $item['name'] }}</p>
                    </a>
                </li>
                @endforeach
            </ul>
        </li>
        @endforeach

    </ul>
</nav>
