<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'DailyWorks')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Tahoma', 'Segoe UI', sans-serif;
            background: #f3f6fb;
            color: #1f2937;
        }

        .sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 12px;
            margin-bottom: 0.3rem;
            padding: 0.7rem 0.9rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }

        .card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .btn {
            border-radius: 12px;
        }

        .table thead th {
            background: #edf2ff;
            color: #1f2937;
            font-weight: 700;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 0.7rem 0.9rem;
        }

        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .quantity-input {
            -moz-appearance: textfield;
            appearance: textfield;
        }

        .badge-soft {
            background: #e2e8f0;
            color: #0f172a;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid p-0">
    <div class="row g-0">
        @auth
            <aside class="col-lg-2 sidebar no-print p-3">
                <div class="d-flex align-items-center mb-4 px-2">
                    <div class="me-2 rounded-circle bg-light text-dark d-flex align-items-center justify-content-center" style="width:42px;height:42px;font-weight:700;">D</div>
                    <div>
                        <div class="text-white fw-bold">DailyWorks</div>
                        <small class="text-light">نظام إدارة العقود</small>
                    </div>
                </div>

                <nav class="nav flex-column">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">الرئيسية</a>
                    <a class="nav-link {{ request()->routeIs('contracts.*') ? 'active' : '' }}" href="{{ route('contracts.index') }}">إدارة العقود</a>
                    <a class="nav-link {{ request()->routeIs('daily-works.*') ? 'active' : '' }}" href="{{ route('daily-works.index') }}">إدخال الأعمال اليومية</a>
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">التقارير والطباعة</a>
                </nav>

                <div class="mt-4 px-2">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-light w-100">تسجيل الخروج</button>
                    </form>
                </div>
            </aside>
        @endauth

        <main class="@auth col-lg-10 @else col-12 @endauth py-4 px-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
