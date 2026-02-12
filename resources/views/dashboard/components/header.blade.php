<header class="top-header">
    <div class="header-title">
        <h1>@yield('page-title', 'لوحة التحكم')</h1>
    </div>
    
    <div class="header-actions">
        <div class="notification-badge">
            <i class="far fa-bell"></i>
            <span class="badge">3</span>
            
            <!-- يمكن إضافة قائمة الإشعارات هنا -->
        </div>
        
        <div class="user-menu" onclick="window.location='{{ route('profile') }}'">
            <div class="user-avatar">
                {{ substr(Auth::user()->name ?? 'مستخدم', 0, 1) }}
            </div>
            <div class="user-info d-none d-md-block">
                <div class="user-name">{{ Auth::user()->name ?? 'مستخدم النظام' }}</div>
                <div class="user-role">مدير النظام</div>
            </div>
            <i class="fas fa-chevron-down d-none d-md-block" style="color: #6c757d; font-size: 12px;"></i>
        </div>
    </div>
</header>