<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>INVENARIS - Login</title>

    <link rel="shortcut icon" type="image/png" href="{{ asset('template-admin/src/assets/images/logos/favicon2.png') }}" />
    <link rel="stylesheet" href="{{ asset('template-admin/src/assets/css/styles.min.css') }}" />
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
        data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">

        <div class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">

            <div class="row justify-content-center w-100">
                <div class="col-md-8 col-lg-6 col-xxl-3">

                    <div class="card mb-0">
                        <div class="card-body">

                            <a href="#" class="logo-img text-center d-block py-3 w-100">
                                <img src="{{ asset('template-admin/src/assets/images/logos/logo3.png') }}" width="200" alt="">
                            </a>

                            <p class="text-center mb-4">Sistem Inventaris dan Peminjaman Barang</p>

                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <input type="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="Masukkan email"
                                        required autofocus>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-semibold">Password</label>
                                    <input type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        id="password"
                                        name="password"
                                        placeholder="Masukkan password"
                                        required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Remember -->
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                                        <label class="form-check-label" for="remember_me">
                                            Ingat perangkat ini
                                        </label>
                                    </div>
                                </div>

                                <!-- Button -->
                                <button type="submit"
                                    class="btn w-100 py-8 fs-4 mb-4 rounded-2"
                                    style="background-color: #FFCDE4; color: #000; border: 1px solid #FFCDE4;">
                                    Sign In
                                </button>

                            </form>

                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>

    <script src="{{ asset('template-admin/src/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('template-admin/src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>

    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        @if (session('status'))
            swal({
                title: 'Berhasil!',
                text: '{{ session('status') }}',
                icon: 'success',
            });
        @endif
    </script>

</body>

</html>
