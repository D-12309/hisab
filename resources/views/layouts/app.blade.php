<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hisab-Kitab ERP - Modern Bookkeeping</title>
    
    <!-- Google Fonts & FontAwesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Core Custom Stylesheet (cache-busted) -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    @yield('styles')
</head>
<body>

    <!-- Toast Notifications -->
    <div class="alert-container" id="alertContainer">
        @if(session('success'))
            <div class="alert-toast success">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-toast error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                <div class="alert-toast error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        @endif
    </div>

    <div class="app-container">
        <!-- Sidebar Backdrop (mobile only) -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="appSidebar">
            <div class="sidebar-brand" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div class="sidebar-logo-icon">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <span class="sidebar-brand-name">HisabKitab ERP</span>
                </div>
                <button class="close-sidebar-btn" id="closeSidebar" aria-label="Close Sidebar">&times;</button>
            </div>
            
            <ul class="sidebar-menu">
                <li class="sidebar-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::routeIs('renters.*') ? 'active' : '' }}">
                    <a href="{{ route('renters.index') }}">
                        <i class="fa-solid fa-users"></i>
                        <span>Renters</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::routeIs('expense-parties.*') ? 'active' : '' }}">
                    <a href="{{ route('expense-parties.index') }}">
                        <i class="fa-solid fa-handshake"></i>
                        <span>Expense Parties</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::routeIs('transactions.*') ? 'active' : '' }}">
                    <a href="{{ route('transactions.index') }}">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Transactions Log</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::routeIs('banks.*') ? 'active' : '' }}">
                    <a href="{{ route('banks.index') }}">
                        <i class="fa-solid fa-university"></i>
                        <span>Bankers List</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center; background: rgba(239, 68, 68, 0.1); color: var(--color-danger); border: 1px solid rgba(239, 68, 68, 0.2);">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Secure Logout
                    </button>
                </form>
                <div style="margin-top: 1rem; opacity: 0.6; font-size: 0.7rem;">&copy; 2026 Hisab-Kitab</div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-left" style="display: flex; align-items: center; gap: 0.75rem;">
                    <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h1 class="page-title">@yield('page_title', 'ERP Dashboard')</h1>
                </div>
                
                <div class="navbar-actions">
                    <!-- Quick action buttons -->
                    @yield('header_actions')
                    <div class="navbar-date" style="color: var(--text-secondary); font-size: 0.9rem; white-space: nowrap;">
                        <i class="fa-regular fa-calendar"></i>
                        <span id="navLiveDate"></span>
                    </div>
                </div>
            </header>
            
            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>

        <!-- Mobile Bottom Navigation -->
        <nav class="bottom-nav">
            <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i>
            </a>
            <a href="{{ route('transactions.index') }}" class="bottom-nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                <i class="fa-solid fa-list-check"></i>
            </a>
            <a href="{{ route('renters.index') }}" class="bottom-nav-item {{ request()->routeIs('renters.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i>
                <span class="nav-badge">0</span>
            </a>
            <a href="{{ route('expense-parties.index') }}" class="bottom-nav-item {{ request()->routeIs('expense-parties.*') ? 'active' : '' }}">
                <i class="fa-solid fa-store"></i>
            </a>
            <a href="{{ route('banks.index') }}" class="bottom-nav-item {{ request()->routeIs('banks.*') ? 'active' : '' }}">
                <i class="fa-solid fa-building-columns"></i>
            </a>
        </nav>
    </div>

    <!-- Modals Utility Scripts -->
    <script>
        // Set date in header
        const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
        document.getElementById('navLiveDate').innerText = ' ' + new Date().toLocaleDateString('en-US', options);

        // Sidebar responsive toggling
        const sidebar = document.getElementById('appSidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');

        function openSidebar() {
            if (sidebar) sidebar.classList.add('active');
            if (sidebarBackdrop) sidebarBackdrop.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('active');
            if (sidebarBackdrop) sidebarBackdrop.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                if (sidebar && sidebar.classList.contains('active')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }

        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', closeSidebar);
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', closeSidebar);
        }

        // Auto-close sidebar when a menu link is tapped on mobile
        document.querySelectorAll('.sidebar-item a').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });

        // Toast handling
        const toasts = document.querySelectorAll('.alert-toast');
        toasts.forEach(toast => {
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'all 0.5s ease';
                setTimeout(() => toast.remove(), 500);
            }, 4500);
        });

        // Helper Functions to open and close modals
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // Close modal when clicking outside content
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Universal Loading Spinner on Form Submissions
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form && form.tagName === 'FORM') {
                const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                submitBtns.forEach(btn => {
                    // Disable to prevent multiple clicks / double entry
                    btn.disabled = true;
                    btn.style.opacity = '0.75';
                    btn.style.cursor = 'not-allowed';
                    
                    if (btn.tagName === 'BUTTON') {
                        btn.dataset.originalHtml = btn.innerHTML;
                        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Processing...';
                    } else {
                        btn.dataset.originalValue = btn.value;
                        btn.value = 'Processing...';
                    }
                });
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
