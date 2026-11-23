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

    {{-- Font Awesome 6 CDN fallback --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Vite CSS & JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="admin-body" id="admin-body">

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
    </script>

    @stack('scripts')
</body>
</html>
