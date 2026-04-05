{{-- resources/views/admin/layouts/sidebar.blade.php --}}
<nav class="sidebar col-2 p-3">
    <div class="text-center mb-4">
        <h4 class="text-white">{{ config('app.name') }}</h4>
        <p class="text-white-50 small">لوحة التحكم</p>
    </div>
    
    <hr class="bg-white">
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" 
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                الرئيسية
            </a>
        </li>
        
        <li class="nav-item mt-3">
            <small class="text-white-50 px-3">المستخدمين</small>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.users.index') }}" 
               class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                جميع المستخدمين
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.users.sellers') }}" class="nav-link">
                <i class="fas fa-store"></i>
                البائعون
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.users.customers') }}" class="nav-link">
                <i class="fas fa-user"></i>
                العملاء
            </a>
        </li>
        
        <li class="nav-item mt-3">
            <small class="text-white-50 px-3">المنتجات</small>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.products.index') }}" 
               class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="fas fa-boxes"></i>
                جميع المنتجات
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.categories.index') }}" class="nav-link">
                <i class="fas fa-tags"></i>
                التصنيفات
            </a>
        </li>
        
        <li class="nav-item mt-3">
            <small class="text-white-50 px-3">الطلبات</small>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.orders.index') }}" 
               class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                طلبات الشراء
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.rentals.index') }}" 
               class="nav-link {{ request()->routeIs('admin.rentals.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                طلبات التأجير
            </a>
        </li>
        
        <li class="nav-item mt-3">
            <small class="text-white-50 px-3">المالية</small>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.payments.index') }}" class="nav-link">
                <i class="fas fa-credit-card"></i>
                المدفوعات
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.reports.index') }}" 
               class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>
                التقارير
            </a>
        </li>
        
        <li class="nav-item mt-3">
            <small class="text-white-50 px-3">إعدادات</small>
        </li>
        <li class="nav-item">
<a href="#" class="nav-link" onclick="return false;">                <i class="fas fa-cog"></i>
                الإعدادات
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.logs.index') }}" class="nav-link">
                <i class="fas fa-history"></i>
                سجل النظام
            </a>
        </li>
    </ul>
</nav>