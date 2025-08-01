<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <img src="{{ asset('assets/img/AdminLTELogo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">AdminLTE 3</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('assets/img/avatar.png') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->FullName }}</a>
            </div>
        </div>
        <div class="user-panel pb-3">
            <div class="info d-block">
                <a href="#" class="d-block" style="cursor: unset">Periode 4: <span id="spanMonth">{{ $periode->BULAN }}</span> Bulan <span id="spanYear">{{ $periode->TAHUN  }}</span> Tahun</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ active_menu('dashboard', 'active') }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                @foreach ($menus as $menu)
                    @php
                        $codeActive;
                    @endphp
                    @if (count($menu->submenu) < 1)
                        <li class="nav-item">
                            <a href="{{ Illuminate\Support\Facades\Route::has($menu->routename) ? route($menu->routename) : ($menu->routename !== NULL ? $menu->routename : '#') }}" class="nav-link {{ active_menu($menu->routename, 'active') }}">
                                <i class="nav-icon fas {{ $menu->icon ?? 'list-alt' }}"></i>
                                <p>
                                    {{ $menu->Keterangan }}
                                </p>
                            </a>
                        </li>
                    @else
                        <li class="nav-item {{ active_menu($menu->routename) }}">
                            <a href="#" class="nav-link {{ active_menu($menu->routename, 'active') }}" data-kodemenu="{{ $menu->KODEMENU }}">
                                <i class="nav-icon fas {{ $menu->icon ?? 'fa-list-alt' }}"></i>
                                <p>
                                    {{ $menu->Keterangan }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            @foreach ($menu->submenu as $i => $item)
                                @if ($i === array_key_first($menu->submenu->toArray()))
                                    <ul class="nav nav-treeview">
                                @endif
                                    @if (count($item->submenu) < 1)
                                    <li class="nav-item">
                                        <a href="{{ Illuminate\Support\Facades\Route::has($item->routename) ? route($item->routename) : ($item->routename !== NULL ? $item->routename : '#') }}" class="nav-link {{ active_menu($item->routename, 'active') }}" data-kodemenu="{{ $item->KODEMENU }}">
                                            <i class="far {{ $item->icon == null ? 'fa-circle' : $item->icon }} nav-icon"></i>
                                            <p>{{ $item->Keterangan }}</p>
                                        </a>
                                    </li>
                                    @else
                                    <li class="nav-item {{ active_menu($item->routename) }}">
                                        <a href="#" class="nav-link {{ active_menu($item->routename, 'active') }}" data-kodemenu="{{ $item->KODEMENU }}">
                                            <i class="far {{ $item->icon == null ? 'fa-dot-circle' : $item->icon }} nav-icon"></i>
                                            <p>
                                                {{ $item->Keterangan }}
                                                <i class="right fas fa-angle-left"></i>
                                            </p>
                                        </a>
                                        @foreach ($item->submenu as $x => $it)
                                            @if ($x === array_key_first($item->submenu->toArray()))
                                                <ul class="nav nav-treeview">
                                            @endif
                                                <li class="nav-item">
                                                    <a href="{{ Illuminate\Support\Facades\Route::has($it->routename) ? route($it->routename) : ($it->routename !== NULL ? $it->routename : '#') }}" class="nav-link {{ active_menu($it->routename, 'active') }}" data-kodemenu="{{ $it->KODEMENU }}">
                                                        <i class="far {{ $it->icon == null ? 'fa-circle ' : $it->icon }} nav-icon"></i>
                                                        <p>{{ $it->Keterangan }}</p>
                                                    </a>
                                                </li>
                                            @if ($x === array_key_last($item->submenu->toArray()))
                                                </ul>
                                            @endif
                                        @endforeach
                                    </li>
                                    @endif
                                @if ($i === array_key_last($menu->submenu->toArray()))
                                    </ul>
                                @endif
                            @endforeach
                        </li>
                    @endif
                @endforeach

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
