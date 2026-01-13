        <!-- Sidebar -->
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar" style="background-color: #33393f;">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex flex-column align-items-center justify-content-center" href="{{ url('/') }}" style="height: auto; padding: 1rem 0;">
                <div class="sidebar-brand-icon">
                    <img src="{{ url('/template/img/logorental.png') }}" alt="Logo Rental" style="height: 50px; width: 120px;">
                </div>
                <div class="sidebar-brand-text mx-3 mt-2">MSJ TRANS</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item {{ Request::is('/') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Management
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item {{ Request::is('management-kendaraan*') || Request::is('input-data*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('management-kendaraan*') || Request::is('input-data*') ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseManagement"
                    aria-expanded="{{ Request::is('management-kendaraan*') || Request::is('input-data*') ? 'true' : 'false' }}" aria-controls="collapseManagement">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Management Data</span>
                </a>
                <div id="collapseManagement" class="collapse {{ Request::is('management-kendaraan*') || Request::is('input-data*') ? 'show' : '' }}" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                         <a class="collapse-item {{ Request::is('management-kendaraan*') ? 'active' : '' }}" href="{{ url('/management-kendaraan') }}">
                            <i class="fas fa-fw fa-car mr-2"></i>Daftar Kendaraan
                        </a>
                        <a class="collapse-item {{ Request::is('input-data*') ? 'active' : '' }}" href="{{ url('/input-data') }}">
                            <i class="fas fa-fw fa-list mr-2"></i>Daftar Transaksi
                        </a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Peramalan
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
             <li class="nav-item {{ Request::is('peramalan-smp*') || Request::is('peramalan-tes*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('peramalan-smp*') || Request::is('peramalan-tes*') ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapsePeramalan"
                    aria-expanded="{{ Request::is('peramalan-smp*') || Request::is('peramalan-tes*') ? 'true' : 'false' }}" aria-controls="collapsePeramalan">
                    <i class="fas fa-fw fa-chart-line"></i>
                    <span>Metode Peramalan</span>
                </a>
                <div id="collapsePeramalan" class="collapse {{ Request::is('peramalan-smp*') || Request::is('peramalan-tes*') ? 'show' : '' }}" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item {{ Request::is('peramalan-smp*') ? 'active' : '' }}" href="{{ url('/peramalan-smp') }}">Simple Moving Average</a>
                        <a class="collapse-item {{ Request::is('peramalan-tes*') ? 'active' : '' }}" href="{{ url('/peramalan-tes') }}" style="white-space: normal;">Triple Exponential Smoothing</a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Hasil
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item {{ Request::is('perbandingan*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/perbandingan') }}">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Perbandingan</span>
                </a>
            </li>

            {{-- <!-- Divider -->
            <hr class="sidebar-divider">
            <!-- Heading -->
            <div class="sidebar-heading">
                Interface
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Components</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Custom Components:</h6>
                        <a class="collapse-item" href="buttons.html">Buttons</a>
                        <a class="collapse-item" href="cards.html">Cards</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Utilities Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="fas fa-fw fa-wrench"></i>
                    <span>Utilities</span>
                </a>
                <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Custom Utilities:</h6>
                        <a class="collapse-item" href="utilities-color.html">Colors</a>
                        <a class="collapse-item" href="utilities-border.html">Borders</a>
                        <a class="collapse-item" href="utilities-animation.html">Animations</a>
                        <a class="collapse-item" href="utilities-other.html">Other</a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Addons
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
                    aria-expanded="true" aria-controls="collapsePages">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Pages</span>
                </a>
                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Login Screens:</h6>
                        <a class="collapse-item" href="login.html">Login</a>
                        <a class="collapse-item" href="register.html">Register</a>
                        <a class="collapse-item" href="forgot-password.html">Forgot Password</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Other Pages:</h6>
                        <a class="collapse-item" href="404.html">404 Page</a>
                        <a class="collapse-item" href="blank.html">Blank Page</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Charts -->
            <li class="nav-item">
                <a class="nav-link" href="charts.html">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Charts</span></a>
            </li>

            <!-- Nav Item - Tables -->
            <li class="nav-item">
                <a class="nav-link" href="tables.html">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Tables</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block"> --}}

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->