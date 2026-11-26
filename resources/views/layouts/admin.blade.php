<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bảng Điều Khiển Quản Trị') - AURA ADMIN</title>
    <meta name="description" content="Trang quản trị nội thất cao cấp AURA LIVING - Quản lý sản phẩm, đơn hàng, đổi trả, mã giảm giá, thành viên và báo cáo doanh thu.">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Vite CSS & JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="admin-body" id="admin-body">

    {{-- Backdrop for Mobile Sidebar --}}
    <div class="admin-sidebar-backdrop" id="admin-sidebar-backdrop"></div>

    {{-- 1. Admin Sidebar Navigation --}}
    @include('layouts.partials.admin-sidebar')

    {{-- 2. Admin Main Content Viewport --}}
    <main class="admin-main" id="admin-main">
        {{-- Topbar --}}
        @include('layouts.partials.admin-topbar')

        {{-- Dynamic Admin Content Panel --}}
        <div class="admin-content" id="admin-content">
            <x-flash-message />
            @yield('content')
        </div>
    </main>

    {{-- 3. Toast Notifications Container --}}
    <x-toast />

    {{-- 4. Shared Admin Scripts --}}
    <script>
        function closeAuraModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.remove('active');
        }

        function openAuraModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.add('active');
        }

        // Responsive Admin Sidebar Toggle
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('admin-sidebar');
            const toggleBtn = document.getElementById('admin-sidebar-toggle');
            const closeBtn = document.getElementById('admin-sidebar-close');
            const backdrop = document.getElementById('admin-sidebar-backdrop');

            function toggleSidebar() {
                if (!sidebar) return;
                const isOpen = sidebar.classList.toggle('active');
                if (backdrop) backdrop.classList.toggle('active', isOpen);
                document.body.classList.toggle('sidebar-open', isOpen);
            }

            function closeSidebar() {
                if (!sidebar) return;
                sidebar.classList.remove('active');
                if (backdrop) backdrop.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            }

            if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (backdrop) backdrop.addEventListener('click', closeSidebar);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeSidebar();
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
