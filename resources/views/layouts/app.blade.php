<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'نظام إدارة المساجد') - لوحة التحكم</title>

    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css">

    <!-- Custom CSS -->
    @stack('styles')
    <style>
        @font-face {
            font-family: 'Droid';
            src: url('{{ asset('css/NotoKufiArabic-Light.ttf') }}') format('truetype');
        }

        :root {
            --primary-color: #1e4a6b;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #00acc1;
            --dark-color: #2c3e50;
        }

        body {
            font-family: 'Droid', 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
            background-color: #f8f9fc;
            color: #333;
        }

        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: 260px;
            background: linear-gradient(180deg, var(--dark-color) 0%, #1a2634 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
            padding: 0;
            transition: all 0.3s;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 4px 8px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link i {
            margin-left: 10px;
            width: 20px;
            text-align: center;
        }

        .content {
            margin-right: 260px;
            padding: 20px;
            transition: all 0.3s;
        }

        .navbar-top {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: white;
            border-bottom: 2px solid #f1f5f9;
            padding: 15px 20px;
            font-weight: 600;
            border-radius: 12px 12px 0 0 !important;
        }

        .btn {
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: #153e58;
            border-color: #153e58;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid #e9ecef;
            color: var(--dark-color);
            font-weight: 600;
            background: #f8f9fa;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 25px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 10px 15px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30, 74, 107, 0.25);
        }

        @media (max-width: 768px) {
            .sidebar {
                right: -260px;
            }

            .sidebar.show {
                right: 0;
            }

            .content {
                margin-right: 0;
            }
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="p-4 text-center text-white border-bottom border-secondary">
            <i class="fas fa-mosque fa-3x mb-3"></i>
            <h5 class="mb-0">نظام إدارة المساجد</h5>
            <small class="text-white-50">الإصدار 1.0</small>
        </div>

        <div class="p-3">
            <div class="text-white-50 small px-3 mb-2">القائمة الرئيسية</div>
            <nav class="nav flex-column">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    لوحة التحكم
                </a>

                <div class="text-white-50 small px-3 mt-3 mb-2">إدارة المستخدمين</div>
                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                    href="{{ route('users.index') }}">
                    <i class="fas fa-users"></i>
                    المستخدمين
                </a>

                <div class="text-white-50 small px-3 mt-3 mb-2">إدارة المساجد</div>
                <a class="nav-link {{ request()->routeIs('mosques.*') ? 'active' : '' }}"
                    href="{{ route('mosques.index') }}">
                    <i class="fas fa-mosque"></i>
                    المساجد
                </a>
                <a class="nav-link {{ request()->routeIs('centers.*') ? 'active' : '' }}"
                    href="{{ route('centers.index') }}">
                    <i class="fas fa-school"></i>
                    المراكز
                </a>

                <div class="text-white-50 small px-3 mt-3 mb-2">الهيكل التنظيمي</div>
                <a class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}"
                    href="{{ route('branches.index') }}">
                    <i class="fas fa-code-branch"></i>
                    الفروع
                </a>
                <a class="nav-link {{ request()->routeIs('regions.*') ? 'active' : '' }}"
                    href="{{ route('regions.index') }}">
                    <i class="fas fa-map-marker-alt"></i>
                    المناطق
                </a>

                <div class="text-white-50 small px-3 mt-3 mb-2">الثوابت</div>
                <a class="nav-link {{ request()->routeIs('constants.*') ? 'active' : '' }}"
                    href="{{ route('constants.index') }}">
                    <i class="fas fa-cogs"></i>
                    الثوابت
                </a>
                <a class="nav-link {{ request()->routeIs('constant-types.*') ? 'active' : '' }}"
                      href="{{ route('constant-types.index') }}">
                    <i class="fas fa-tags"></i>
                    أنواع الثوابت
                </a>

                <div class="text-white-50 small px-3 mt-3 mb-2">الخطط الدراسية</div>
                <a class="nav-link {{ request()->routeIs('plans.*') ? 'active' : '' }}"
                    href="{{ route('plans.index') }}">
                    <i class="fas fa-layer-group"></i>
                    الخطط
                </a>
                <a class="nav-link {{ request()->routeIs('plan-levels.*') ? 'active' : '' }}"
                    href="{{ route('plan-levels.index') }}">
                    <i class="fas fa-level-up-alt"></i>
                    مستويات الخطط
                </a>

                <div class="text-white-50 small px-3 mt-3 mb-2">التأهيل</div>
                <a class="nav-link {{ request()->routeIs('grades.*') ? 'active' : '' }}"
                    href="{{ route('grades.index') }}">
                    <i class="fas fa-star"></i>
                    الدرجات
                </a>
                <a class="nav-link {{ request()->routeIs('academic-qualifications.*') ? 'active' : '' }}"
                    href="{{ route('academic-qualifications.index') }}">
                    <i class="fas fa-graduation-cap"></i>
                    المؤهلات الأكاديمية
                </a>
                <a class="nav-link {{ request()->routeIs('personal-courses.*') ? 'active' : '' }}"
                    href="{{ route('personal-courses.index') }}">
                    <i class="fas fa-certificate"></i>
                    الدورات
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content" id="content">
        <!-- Top Navbar -->
        <nav class="navbar-top d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-link d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle text-dark" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell me-1"></i>
                        <span class="badge bg-danger rounded-pill">3</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <div>
                                    <small class="fw-bold">مستخدم جديد</small>
                                    <br>
                                    <small class="text-muted">منذ 5 دقائق</small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="#">عرض كل الإشعارات</a>
                    </div>
                </div>

                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                        data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'Admin' }}&size=40&background=1e4a6b&color=fff"
                            class="avatar">
                        <span class="me-2 d-none d-md-inline">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>الملف الشخصي</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>الإعدادات</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Auto hide alerts
        setTimeout(function () {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Confirm delete
        function confirmDelete(event, title = 'تأكيد الحذف', text = 'هل أنت متأكد من حذف هذا العنصر؟') {
            event.preventDefault();
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    event.target.submit();
                }
            });
        }
    </script>

    @stack('scripts')
</body>

</html>
