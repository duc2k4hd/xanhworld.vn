<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') | {{ $settings->site_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('admins/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/css/layout.css') }}">
    @stack('styles')
    @stack('head')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div><img src="{{ asset('clients/assets/img/business/'. $settings->site_logo) }}" alt="{{ $settings->site_name }}" style="width: 100%; height: 100%;"></div>
            {{-- <h1>📊 Admin Panel</h1>
            <p style="color: white; text-align: center;">{{ $settings->site_name }}</p> --}}
        </div>
        <nav class="sidebar-menu">
            <div class="menu-section">Tổng Quan</div>
            <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="menu-item-icon">📊</span>
                Dashboard
            </a>
            
            <div class="menu-section">Sản Phẩm</div>
            <div class="menu-group">
                <div class="menu-group-header" data-group="products">
                    <span class="menu-item-icon">📦</span>
                    <span>Sản Phẩm</span>
                    <span class="menu-arrow">▶</span>
                </div>
                <div class="menu-group-items" id="products-group">
                    @if(Route::has('admin.products.index'))
                        <a href="{{ route('admin.products.index') }}" class="menu-item {{ request()->routeIs('admin.products.index') && !request('status') ? 'active' : '' }}">
                            Tất cả sản phẩm
                        </a>
                        <a href="{{ route('admin.products.index', ['status' => 'active']) }}" class="menu-item {{ request()->routeIs('admin.products.index') && request('status') === 'active' ? 'active' : '' }}">
                            Đang bán
                        </a>
                        <a href="{{ route('admin.products.index', ['status' => 'inactive']) }}" class="menu-item {{ request()->routeIs('admin.products.index') && request('status') === 'inactive' ? 'active' : '' }}">
                            Tạm ẩn
                        </a>
                        @if(Route::has('admin.products.import-excel'))
                            <a href="{{ route('admin.products.import-excel') }}" class="menu-item {{ request()->routeIs('admin.products.import-excel*') ? 'active' : '' }}">
                                Nhập Excel
                            </a>
                        @endif
                        @if(Route::has('admin.products.export-excel'))
                            <a href="{{ route('admin.products.export-excel') }}" class="menu-item {{ request()->routeIs('admin.products.export-excel') ? 'active' : '' }}">
                                Xuất Excel
                            </a>
                        @endif
                    @else
                        <div class="menu-item" style="color: #999; cursor: not-allowed;">
                            Sản phẩm (Chưa khả dụng)
                        </div>
                    @endif
                </div>
            </div>
            @if(Route::has('admin.categories.index'))
                <a href="{{ route('admin.categories.index') }}" class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">🏷️</span>
                    Danh Mục
                </a>
            @endif
            @if(Route::has('admin.flash-sales.index'))
                <a href="{{ route('admin.flash-sales.index') }}" class="menu-item {{ request()->routeIs('admin.flash-sales.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">⚡</span>
                    Flash Sale
                </a>
            @endif
            
            <div class="menu-section">Nội Dung</div>
            @if(Route::has('admin.posts.index'))
                <a href="{{ route('admin.posts.index') }}" class="menu-item {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">📝</span>
                    Bài viết
                </a>
            @endif
            @if(Route::has('admin.comments.index'))
                <a href="{{ route('admin.comments.index') }}" class="menu-item {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">💬</span>
                    Bình luận
                </a>
            @endif
            @if(Route::has('admin.email-accounts.index'))
                <a href="{{ route('admin.email-accounts.index') }}" class="menu-item {{ request()->routeIs('admin.email-accounts.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">📧</span>
                    Email
                </a>
            @endif
            @if(Route::has('admin.tags.index'))
                <a href="{{ route('admin.tags.index') }}" class="menu-item {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">🏷️</span>
                    Thẻ (Tags)
                </a>
            @endif
            @if(Route::has('admin.banners.index'))
                <a href="{{ route('admin.banners.index') }}" class="menu-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">🖼️</span>
                    Banner
                </a>
            @endif
            @if(Route::has('admin.media.index'))
                <a href="{{ route('admin.media.index') }}" class="menu-item {{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">🗂️</span>
                    Media
                </a>
            @endif
            @if(Route::has('admin.sitemap.index'))
                <a href="{{ route('admin.sitemap.index') }}" class="menu-item {{ request()->routeIs('admin.sitemap.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">🗺️</span>
                    Sitemap
                </a>
            @endif
            
            <div class="menu-section">Đơn Hàng</div>
            @if(Route::has('admin.orders.index'))
                <div class="menu-group">
                    <div class="menu-group-header" data-group="orders">
                        <span class="menu-item-icon">📋</span>
                        <span>Đơn Hàng</span>
                        <span class="menu-arrow">▶</span>
                    </div>
                    <div class="menu-group-items" id="orders-group">
                        <a href="{{ route('admin.orders.index') }}" class="menu-item {{ request()->routeIs('admin.orders.index') && !request('status') && !request('delivery_status') ? 'active' : '' }}">
                            Tất cả đơn hàng
                        </a>
                        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="menu-item {{ request()->routeIs('admin.orders.*') && request('status') === 'pending' ? 'active' : '' }}">
                            Chờ xử lý
                        </a>
                        <a href="{{ route('admin.orders.index', ['delivery_status' => 'shipped']) }}" class="menu-item {{ request()->routeIs('admin.orders.*') && request('delivery_status') === 'shipped' ? 'active' : '' }}">
                            Đang giao hàng
                        </a>
                        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="menu-item {{ request()->routeIs('admin.orders.*') && request('status') === 'completed' ? 'active' : '' }}">
                            Hoàn thành
                        </a>
                    </div>
                </div>
            @endif
            @if(Route::has('admin.carts.index'))
                <a href="{{ route('admin.carts.index') }}" class="menu-item {{ request()->routeIs('admin.carts.index') || (request()->routeIs('admin.carts.show') || request()->routeIs('admin.carts.edit')) ? 'active' : '' }}">
                    <span class="menu-item-icon">🛒</span>
                    Giỏ Hàng
                </a>
            @endif
            @if(Route::has('admin.carts.create-order.index'))
                <a href="{{ route('admin.carts.create-order.index') }}" class="menu-item {{ request()->routeIs('admin.carts.create-order.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">📦</span>
                    Lên Đơn Hàng
                </a>
            @endif
            
            <div class="menu-section">Khách Hàng</div>
            @if(Route::has('admin.accounts.index'))
                <a href="{{ route('admin.accounts.index') }}" class="menu-item {{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">👤</span>
                    Tài khoản
                </a>
            @endif
            @if(Route::has('admin.newsletters.index'))
                <a href="{{ route('admin.newsletters.index') }}" class="menu-item {{ request()->routeIs('admin.newsletters.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">📧</span>
                    <span style="flex: 1;">Newsletter</span>
                    @php
                        try {
                            $pendingNewsletterCount = \App\Models\Newsletter::where('is_verified', false)->count();
                        } catch (\Exception $e) {
                            $pendingNewsletterCount = 0;
                        }
                    @endphp
                    @if($pendingNewsletterCount > 0)
                        <span style="background: #f59e0b; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px;">{{ $pendingNewsletterCount }}</span>
                    @endif
                </a>
            @endif
            @if(Route::has('admin.contacts.index'))
                <a href="{{ route('admin.contacts.index') }}" class="menu-item {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">💬</span>
                    <span style="flex: 1;">Liên Hệ</span>
                    @php
                        try {
                            $newContactCount = \App\Models\Contact::where('status', 'new')->count();
                        } catch (\Exception $e) {
                            $newContactCount = 0;
                        }
                    @endphp
                    @if($newContactCount > 0)
                        <span style="background: #ef4444; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px; font-weight: 600;">{{ $newContactCount }}</span>
                    @endif
                </a>
            @endif
            @if(Route::has('admin.notifications.index'))
                <a href="{{ route('admin.notifications.index') }}" class="menu-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">🔔</span>
                    Thông báo
                </a>
            @endif
            @if(Route::has('admin.addresses.index'))
                <a href="{{ route('admin.addresses.index') }}" class="menu-item {{ request()->routeIs('admin.addresses.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">📍</span>
                    Địa Chỉ Giao Hàng
                </a>
            @endif
            
            <div class="menu-section">Khuyến Mãi</div>
            @if(Route::has('admin.vouchers.index'))
                <div class="menu-group">
                    <div class="menu-group-header" data-group="vouchers">
                        <span class="menu-item-icon">🎫</span>
                        <span>Voucher</span>
                        <span class="menu-arrow">▶</span>
                    </div>
                    <div class="menu-group-items" id="vouchers-group">
                        <a href="{{ route('admin.vouchers.index') }}" class="menu-item {{ request()->routeIs('admin.vouchers.index') ? 'active' : '' }}">
                            Tất cả voucher
                        </a>
                        <a href="{{ route('admin.vouchers.create') }}" class="menu-item {{ request()->routeIs('admin.vouchers.create') ? 'active' : '' }}">
                            Tạo voucher mới
                        </a>
                        @if(Route::has('admin.vouchers.analytics'))
                            <a href="{{ route('admin.vouchers.analytics') }}" class="menu-item {{ request()->routeIs('admin.vouchers.analytics*') ? 'active' : '' }}">
                                Analytics & Báo cáo
                            </a>
                        @endif
                    </div>
                </div>
            @endif
            
            <div class="menu-section">Báo Cáo & Phân Tích</div>
            @if(Route::has('admin.reports.index'))
                <a href="{{ route('admin.reports.index') }}" class="menu-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">📊</span>
                    Báo Cáo
                </a>
            @endif
            @if(Route::has('admin.affiliates.index'))
                <a href="{{ route('admin.affiliates.index') }}" class="menu-item {{ request()->routeIs('admin.affiliates.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">🔗</span>
                    Affiliate
                </a>
            @endif
            
            <div class="menu-section">Hệ Thống</div>
            @if(Route::has('admin.email-templates.index'))
                <a href="{{ route('admin.email-templates.index') }}" class="menu-item {{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">📧</span>
                    Email Templates
                </a>
            @endif
            @if(Route::has('admin.backups.index'))
                <a href="{{ route('admin.backups.index') }}" class="menu-item {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">💾</span>
                    Backup & Restore
                </a>
            @endif
            @if(Route::has('admin.settings.index'))
                <a href="{{ route('admin.settings.index') }}" class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">⚙️</span>
                    Cài Đặt
                </a>
            @endif
            @if(Route::has('admin.trash.index'))
                <a href="{{ route('admin.trash.index') }}" class="menu-item {{ request()->routeIs('admin.trash.*') ? 'active' : '' }}">
                    <span class="menu-item-icon">🗑️</span>
                    Thùng Rác
                </a>
            @endif
            <a href="{{ url('/') }}" class="menu-item" target="_blank">
                <span class="menu-item-icon">🌐</span>
                Về Trang Chủ
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div style="display: flex; align-items: center;">
                <button class="menu-toggle" id="sidebarToggle" onclick="toggleSidebar()" title="Đóng/Mở menu">
                    <span id="toggleIcon">☰</span>
                </button>
                <span class="top-bar-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="top-bar-actions">
                {{-- Notification Bell --}}
                <div class="notification-bell-container" style="position: relative; margin-right: 15px;">
                    <a href="{{ route('admin.notifications.index') }}" class="notification-bell" id="notificationBell" title="Thông báo">
                        <i class="fa fa-bell" style="font-size: 20px; color: #495057;"></i>
                        <span class="notification-badge" id="notificationBadge" style="display: none; position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 11px; font-weight: bold; display: flex; align-items: center; justify-content: center;">0</span>
                    </a>
                </div>
                
                <div class="user-info">
                    <div class="user-avatar">
                        @php
                            $user = auth('web')->user() ?? auth()->user();
                            $userInitial = $user ? strtoupper(substr($user->name ?? 'A', 0, 1)) : 'A';
                            $userName = $user ? ($user->name ?? 'Admin') : 'Admin';
                        @endphp
                        {{ $userInitial }}
                    </div>
                    <div>
                        <div style="font-size: 14px; font-weight: 600;">{{ $userName }}</div>
                        <div style="font-size: 12px; color: #666;">Quản trị viên</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            @yield('content')
        </div>
    </main>
    
    <div id="custom-toast-container" class="custom-toast-container"></div>

    <style>
        .custom-toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 9999;
            cursor: pointer;
        }

        .custom-toast {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            color: #fff;
            max-width: 320px;
            opacity: 0;
            transform: translateX(100%);
            transition: transform 0.4s ease, opacity 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .custom-toast.show {
            opacity: 1;
            transform: translateX(0);
        }

        .custom-toast.success {
            background-color: #1c9a4a;
            /* xanh lá */
        }

        .custom-toast.error {
            background-color: #ef4444;
            /* đỏ */
        }

        .custom-toast.warning {
            background-color: #f59e0b;
            /* cam */
        }

        .custom-toast.info {
            background-color: #3b82f6;
            /* xanh dương */
        }

        .custom-toast-icon {
            font-size: 18px;
        }

        .ck.ck-editor__editable_inline>:last-child {
            min-height: 500px !important;
            max-height: 1000px !important;
        }
    </style>

    <script>
        function showCustomToast(
            message = "Thông báo!",
            type = "info",
            duration = 5000
        ) {
            const container = document.getElementById("custom-toast-container");
            const toast = document.createElement("div");
            const icon = document.createElement("span");

            toast.className = `custom-toast ${type}`;
            icon.className = "custom-toast-icon";

            // Gán biểu tượng theo loại
            const icons = {
                success: "✅",
                error: "❌",
                warning: "⚠️",
                info: "💬",
            };
            icon.textContent = icons[type] || "🔔";

            toast.appendChild(icon);
            toast.appendChild(document.createTextNode(message));
            container.appendChild(toast);

            // Kích hoạt animation
            setTimeout(() => toast.classList.add("show"), 100);

            toast.addEventListener("click", () => {
                toast.classList.remove("show");
                setTimeout(() => {
                    container.removeChild(toast);
                }, 300);
                return;
            });

            // Gỡ thông báo sau duration
            setTimeout(() => {
                toast.classList.remove("show");
                setTimeout(() => {
                    container.removeChild(toast);
                }, 300);
                return;
            }, duration);
        }
        @php
            $alerts = [
                'success' => session('success'),
                'error'   => session('error'),
                'warning' => session('warning'),
                'info'    => session('info'),
            ];
        @endphp
        document.addEventListener("DOMContentLoaded", function() {
            let alerts = [];
            @foreach ($alerts as $type => $message)
                @if ($message)
                    alerts.push({type: '{{ $type }}', message: @json($message)});
                @endif
            @endforeach

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    alerts.push({type: 'error', message: @json($error)});
                @endforeach
            @endif

            @if(request()->cookie('updated_account_success'))
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        showCustomToast(
                            "Cập nhật tài khoản thành công. Vui lòng đăng nhập lại.",
                            "success"
                        );
                    });
                </script>
            @endif

            alerts.forEach(a => showCustomToast(a.message, a.type));
        });
    </script>

    @php
        Cookie::queue(Cookie::forget('updated_account_success'));
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        // Load and update notification count
        function loadNotificationCount() {
            fetch('{{ route("admin.notifications.unread-count") }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(error => console.error('Error loading notification count:', error));
        }

        // Load notification count on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadNotificationCount();
            
            // Auto refresh every 30 seconds
            setInterval(loadNotificationCount, 30000);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    {{-- CKEditor 5 - UMD build from CDN, using config adapted from ckeditor5-builder-47.4.0/main.js --}}
    <link rel="stylesheet"
          href="https://cdn.ckeditor.com/ckeditor5/47.4.0/ckeditor5.css"
          crossorigin>
    <script src="https://cdn.ckeditor.com/ckeditor5/47.4.0/ckeditor5.umd.js" crossorigin></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/47.4.0/translations/vi.umd.js" crossorigin></script>
    <script src="{{ asset('admins/js/ckeditor-admin.js') }}"></script>
    <script src="{{ asset('admins/js/layout.js') }}"></script>

    {{-- Popup media picker dùng chung --}}
    @include('admins.media.modal-picker')

    @stack('scripts')

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" aria-label="Lên đầu trang">
        <svg xmlns="http://www.w3.org/2000/svg" height="24" width="24" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M270.8 111.8C266.3 122.5 264 134 264 145.6L264 246L112 360L112 312C112 298.7 101.3 288 88 288C74.7 288 64 298.7 64 312L64 456C64 469.3 74.7 480 88 480C101.3 480 112 469.3 112 456L112 448L264 448L264 502.4L198 555.2C194.2 558.2 192 562.8 192 567.7L192 592C192 600.8 199.2 608 208 608L296 608L296 568C296 554.7 306.7 544 320 544C333.3 544 344 554.7 344 568L344 608L432 608C440.8 608 448 600.8 448 592L448 567.7C448 562.8 445.8 558.2 442 555.2L376 502.4L376 448L528 448L528 456C528 469.3 538.7 480 552 480C565.3 480 576 469.3 576 456L576 312C576 298.7 565.3 288 552 288C538.7 288 528 298.7 528 312L528 360L376 246L376 145.6C376 134 373.7 122.5 369.2 111.8L342.1 46.8C338.4 37.8 329.7 32 320 32C310.3 32 301.6 37.8 297.8 46.8L270.7 111.8z"/></svg>
    </button>
</body>

</html>

