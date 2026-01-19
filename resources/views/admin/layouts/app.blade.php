<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>INVENTARIS - {{ $title ?? 'Dashboard' }}</title>
  <link rel="shortcut icon" type="image/png" href="{{ asset('template-admin/src/assets/images/logos/favicon2.png') }}" />
  <link rel="stylesheet" href="{{ asset('template-admin/src/assets/css/styles.min.css') }}" />
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css" />
  <link rel="stylesheet" href="{{ asset('css/glass.css') }}" />
  <style>
    /* Latar belakang sidebar yang benar-benar cerah & transparan */
    .left-sidebar {
        background: rgba(255, 255, 255, 0.6) !important; 
        backdrop-filter: blur(25px) saturate(150%);
        -webkit-backdrop-filter: blur(25px) saturate(150%);
        border-right: 1px solid rgba(255, 255, 255, 0.5) !important;
        box-shadow: 15px 0 35px rgba(0, 0, 0, 0.02);
    }

    /* Section Logo */
    .brand-logo {
        border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important;
        background: transparent !important;
    }

    /* Teks judul kecil (Home, Datamaster, dll) */
    .nav-small-cap {
        color: #94a3b8 !important; /* Warna abu-abu soft, bukan hitam */
        font-weight: 700 !important;
        font-size: 0.75rem !important;
        letter-spacing: 1px;
        margin-top: 25px !important;
        padding-left: 20px !important;
    }

    /* Link navigasi default (saat tidak aktif) */
    .sidebar-link {
        color: #64748b !important; /* Abu-abu kebiruan yang lembut */
        border-radius: 15px !important;
        margin: 4px 15px !important;
        padding: 12px 18px !important;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    /* Efek saat menu di-hover */
    .sidebar-link:hover {
        background: rgba(255, 255, 255, 0.8) !important;
        color: #334155 !important;
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }

    /* Scrollbar Tipis & Transparan */
    .scroll-sidebar::-webkit-scrollbar {
        width: 5px;
    }
    .scroll-sidebar::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 10px;
    }
</style>
  @yield('css')
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    @include('admin.layouts.sidebar')
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      @include('admin.layouts.header')
      <!--  Header End -->
      <div class="container-fluid">
        @yield('content')
      </div>
    </div>
  </div>
  <script src="{{ asset('template-admin/src/assets/libs/jquery/dist/j') }}query.min.js"></script>
  <script src="{{ asset('template-admin/src/assets/libs/bootstrap/dis') }}t/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('template-admin/src/assets/js/sidebarmenu.js"') }}></script>
  <script src="{{ asset('template-admin/src/assets/js/app.min.js"></s') }}cript>
  <script src="{{ asset('template-admin/src/assets/libs/simplebar/dis') }}t/simplebar.js"></script>
  @yield('js')
  <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  <script>
  @if (session('status'))
  swal({
    title: '{{ session('title') }}',
    text: '{{ session('message') }}',
    icon: '{{ session('status') }}',
  });
  @endif
  </script>
</body>

</html>