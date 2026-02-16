<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <i class="fas fa-chart-pie brand-icon"></i>
            <span class="brand-name">لوحة التحكم</span>
        </div>
        <button class="toggle-btn d-none d-lg-block" onclick="toggleSidebar()">
            <i class="fas fa-chevron-right"></i>
        </button>
        <button class="toggle-btn d-lg-none mobile-toggle" onclick="toggleMobileSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span class="nav-text">الرئيسية</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span class="nav-text">المستخدمين</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products*') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <span class="nav-text">المنتجات</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span class="nav-text">الطلبات</span>
                <span class="badge" style="margin-right: auto;">12</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="#" class="nav-link {{ request()->routeIs('categories*') ? 'active' : '' }}">
                <i class="fas fa-tags"></i>
                <span class="nav-text">التصنيفات</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="#" class="nav-link {{ request()->routeIs('reports*') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <span class="nav-text">التقارير</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="#" class="nav-link {{ request()->routeIs('settings*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span class="nav-text">الإعدادات</span>
            </a>
        </li>
    </ul>
    
    <div style="position: absolute; bottom: 20px; width: 100%; padding: 0 20px;">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn" style="width: 100%; background: rgba(255,255,255,0.1); color: #fff; text-align: right;">
                <i class="fas fa-sign-out-alt" style="margin-left: 10px;"></i>
                <span class="nav-text">تسجيل خروج</span>
            </button>
        </form>
    </div>
</aside>