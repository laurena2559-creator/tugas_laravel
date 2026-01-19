<header class="app-header">
    <nav class="navbar navbar-expand-lg navbar-light">

        <!-- Left Items -->
        <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
                <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                    <i class="ti ti-menu-2"></i>
                </a>
            </li>

            <!-- Lonceng notifikasi DIHAPUS -->
        </ul>

        <!-- Right Items -->
        <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center">

                <!-- Tombol nama user / Admin SIMINLAB DIHAPUS -->
                
                <!-- Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon-hover" href="javascript:void(0)"
                       id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset('template-admin/src/assets/images/profile/user-2.jpg') }}"
                             alt="profile" width="35" height="35" class="rounded-circle">
                    </a>

                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up"
                         aria-labelledby="userDropdown">

                        <div class="message-body px-3">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-dark w-100 mt-2 d-block">
                                    Logout
                                </button>
                            </form>
                        </div>

                    </div>
                </li>

            </ul>
        </div>

    </nav>
</header>
