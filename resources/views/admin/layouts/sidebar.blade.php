<aside class="left-sidebar shadow-sm">
    <div class="h-100 d-flex flex-column">
        <div class="brand-logo d-flex align-items-center justify-content-between p-4 mb-2">
            <a href="{{ route('dashboard') }}" class="text-nowrap logo-img">
                <img src="{{ asset('template-admin/src/assets/images/logos/logo3.png') }}" 
                     width="180" alt="Logo" class="logo-filter" />
            </a>
            <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                <i class="ti ti-x fs-6 text-muted"></i>
            </div>
        </div>

        <nav class="sidebar-nav scroll-sidebar">
            <ul id="sidebarnav" class="list-unstyled px-3"> 
                @if (Auth::user()->role === 'Admin')
                    
                    <li class="nav-small-cap"><span>Menu Utama</span></li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ Route::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <span class="icon-box"><i class="ti ti-layout-dashboard"></i></span>
                            <span class="hide-menu">Dashboard</span>
                        </a>
                    </li>

                    <li class="sidebar-divider"></li>

                    <li class="nav-small-cap"><span>Master Data</span></li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ Route::is('users*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <span class="icon-box"><i class="ti ti-users"></i></span>
                            <span class="hide-menu">Data Users</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ Route::is('item.index') ? 'active' : '' }}" href="{{ route('item.index') }}">
                            <span class="icon-box"><i class="ti ti-box"></i></span>
                            <span class="hide-menu">Data Barang</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ Route::is('item.rusak') ? 'active-danger' : '' }}" href="{{ route('item.rusak') }}">
                            <span class="icon-box"><i class="ti ti-alert-circle"></i></span>
                            <span class="hide-menu">Barang Rusak</span>
                        </a>
                    </li>

                    <li class="sidebar-divider"></li>

                    <li class="nav-small-cap"><span>Transaksi</span></li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ Route::is('loan*') ? 'active' : '' }}" href="{{ route('loan.index') }}">
                            <span class="icon-box"><i class="ti ti-clipboard"></i></span>
                            <span class="hide-menu">Peminjaman</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
        
        <div class="mt-auto p-4">
            <div class="sidebar-footer-box">
                <div class="footer-icon">
                    <i class="ti ti-device-laptop"></i>
                </div>
                <div class="ms-3">
                    <p class="footer-label">ADMIN</p>
                    <p class="footer-title">INVENTARIS <span>sekolah</span></p>
                </div>
            </div>
        </div>
    </div>
</aside>

<style>
    /* Sidebar General */
.left-sidebar {
    background: #ffffff;
    border-right: 1px solid #f1f5f9;
}

.logo-filter {
    filter: brightness(1.05);
}

/* Category Label */
.nav-small-cap {
    font-size: 0.65rem;
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: 1.5px;
    color: #94a3b8;
    padding: 10px 15px;
    opacity: 0.8;
}

/* Divider */
.sidebar-divider {
    margin: 1.5rem 1rem;
    border-bottom: 1px solid #f1f5f9;
}

/* Sidebar Link Base */
.sidebar-link {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    border-radius: 8px;
    color: #475569;
    font-weight: 600;
    text-decoration: none !important;
    transition: all 0.2s ease-in-out;
    margin-bottom: 4px;
}

.icon-box {
    margin-right: 12px;
    display: flex;
    font-size: 1.25rem;
}

/* Hover State */
.sidebar-link:hover:not(.active):not(.active-danger) {
    background-color: #f8fafc;
    color: #0d6efd;
    transform: translateX(5px);
}

/* Active State (Blue) */
.sidebar-link.active {
    background: linear-gradient(45deg, #0d6efd, #0a58ca);
    color: #ffffff !important;
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
}

/* Active State (Red for Broken Items) */
.sidebar-link.active-danger {
    background: linear-gradient(45deg, #ef4444, #dc2626);
    color: #ffffff !important;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}

/* Footer Box */
.sidebar-footer-box {
    padding: 15px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.footer-icon {
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #0d6efd;
    color: #0d6efd;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.footer-label {
    margin-bottom: 0;
    font-size: 0.6rem;
    font-weight: 800;
    color: #94a3b8;
    letter-spacing: 1px;
}

.footer-title {
    margin-bottom: 0;
    font-weight: 800;
    font-size: 0.85rem;
    color: #1e293b;
}

.footer-title span {
    color: #0d6efd;
}
</style>