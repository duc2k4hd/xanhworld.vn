@extends('admins.layouts.master')

@section('title', 'Dashboard Admin')
@section('page-title', '📊 Dashboard')

@push('head')
    <link rel="shortcut icon" href="{{ asset('admins/img/icons/dashboard-icon.png') }}" type="image/x-icon">
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('admins/css/dashboard.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
    <div class="content-wrapper">
        <!-- HEADER -->
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Overview</h1>
                <p>Hi, Welcome back to your admin dashboard!</p>
            </div>
            <!-- Export or Filter buttons could go here -->
        </div>

        <!-- TỔNG QUAN -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">Tổng Sản Phẩm</span>
                    <div class="stat-card-icon icon-blue">📦</div>
                </div>
                <div class="stat-card-value">{{ number_format($stats['total_products']) }}</div>
                <div class="stat-card-change">
                    <span>Đang hoạt động: <strong>{{ number_format($stats['active_products']) }}</strong></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">Tổng Đơn Hàng</span>
                    <div class="stat-card-icon icon-green">🛒</div>
                </div>
                <div class="stat-card-value">{{ number_format($stats['total_orders']) }}</div>
                <div class="stat-card-change">
                    <span>Hôm nay: <strong>{{ number_format($orders['today']) }}</strong></span>
                    @if($orders['today_change'] != 0)
                        <span class="{{ $orders['today_change'] > 0 ? 'positive' : 'negative' }}">
                            ({{ $orders['today_change'] > 0 ? '+' : '' }}{{ $orders['today_change'] }}%)
                        </span>
                    @endif
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">Tổng Khách Hàng</span>
                    <div class="stat-card-icon icon-purple">👥</div>
                </div>
                <div class="stat-card-value">{{ number_format($stats['total_customers']) }}</div>
                <div class="stat-card-change">
                    <span>Đã đăng ký tài khoản</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">Doanh Thu Hôm Nay</span>
                    <div class="stat-card-icon icon-orange">💰</div>
                </div>
                <div class="stat-card-value">{{ number_format($revenue['today']) }}₫</div>
                <div class="stat-card-change {{ $revenue['today_change'] >= 0 ? 'positive' : 'negative' }}">
                    @if($revenue['today_change'] != 0)
                        <span>{{ $revenue['today_change'] > 0 ? '↑' : '↓' }} {{ abs($revenue['today_change']) }}%</span>
                        <span>so với hôm qua</span>
                    @else
                        <span>Không có thay đổi</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- DOANH THU -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">💰 Summary Revenue</h2>
            </div>
            <div class="section-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Hôm Nay</span>
                            <div class="stat-card-icon icon-green">📈</div>
                        </div>
                        <div class="stat-card-value">{{ number_format($revenue['today']) }}₫</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Tháng Này</span>
                            <div class="stat-card-icon icon-blue">📊</div>
                        </div>
                        <div class="stat-card-value">{{ number_format($revenue['this_month']) }}₫</div>
                        <div class="stat-card-change {{ $revenue['month_change'] >= 0 ? 'positive' : 'negative' }}">
                            @if($revenue['month_change'] != 0)
                                <span>{{ $revenue['month_change'] > 0 ? '↑' : '↓' }} {{ abs($revenue['month_change']) }}%</span>
                                <span>so với tháng trước</span>
                            @endif
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Năm Nay</span>
                            <div class="stat-card-icon icon-purple">🎯</div>
                        </div>
                        <div class="stat-card-value">{{ number_format($revenue['this_year']) }}₫</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Tổng Doanh Thu</span>
                            <div class="stat-card-icon icon-orange">🏆</div>
                        </div>
                        <div class="stat-card-value">{{ number_format($revenue['all_time']) }}₫</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BIỂU ĐỒ -->
        <div class="grid-2">
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">📈 Doanh Thu 7 Ngày Gần Nhất</h2>
                </div>
                <div class="section-body">
                    <div class="chart-container">
                        <canvas id="dailyRevenueChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">📊 Đơn Hàng 7 Ngày Gần Nhất</h2>
                </div>
                <div class="section-body">
                    <div class="chart-container">
                        <canvas id="dailyOrdersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-header">
                <h2 class="section-title">📅 Doanh Thu 12 Tháng Gần Nhất</h2>
            </div>
            <div class="section-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- TRẠNG THÁI ĐƠN HÀNG -->
        <div class="section">
             <div class="section-header">
                <h2 class="section-title">🛒 Trạng Thái Đơn Hàng</h2>
            </div>
            <div class="section-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Chờ Xử Lý</span>
                            <span class="badge badge-warning">{{ $orders['pending'] }}</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill bg-orange" style="width: {{ $stats['total_orders'] > 0 ? ($orders['pending'] / $stats['total_orders'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Đang Xử Lý</span>
                            <span class="badge badge-info">{{ $orders['processing'] }}</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill bg-blue" style="width: {{ $stats['total_orders'] > 0 ? ($orders['processing'] / $stats['total_orders'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Hoàn Thành</span>
                            <span class="badge badge-success">{{ $orders['completed'] }}</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill bg-green" style="width: {{ $stats['total_orders'] > 0 ? ($orders['completed'] / $stats['total_orders'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Đã Hủy</span>
                            <span class="badge badge-danger">{{ $orders['cancelled'] }}</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill bg-red" style="width: {{ $stats['total_orders'] > 0 ? ($orders['cancelled'] / $stats['total_orders'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SẢN PHẨM SẮP HẾT HÀNG -->
        @if(!$lowStockProducts->isEmpty())
        <div class="section">
             <div class="section-header">
                <h2 class="section-title">⚠️ Sản phẩm sắp hết hàng</h2>
            </div>
            <div class="section-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Tên sản phẩm</th>
                            <th>Tồn kho</th>
                            <th>Hành động</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($lowStockProducts as $p)
                            <tr>
                                <td>{{ $p->sku }}</td>
                                <td>{{ $p->name }}</td>
                                <td>
                                    @if($p->stock_quantity <= 0)
                                        <span class="badge badge-danger">Hết hàng</span>
                                    @elseif($p->stock_quantity <= 5)
                                        <span class="badge badge-warning">Sắp hết ({{ $p->stock_quantity }})</span>
                                    @else
                                        <span class="badge badge-success">{{ $p->stock_quantity }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.inventory', $p->id) }}" class="btn btn-sm btn-primary">
                                        Quản lý kho
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- SẢN PHẨM BÁN CHẠY -->
        <div class="section">
             <div class="section-header">
                <h2 class="section-title">🔥 Top 10 Sản Phẩm Bán Chạy</h2>
            </div>
            <div class="section-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên Sản Phẩm</th>
                                <th>SKU</th>
                                <th>Số Lượng Bán</th>
                                <th>Doanh Thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $index => $product)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $product['name'] }}</strong></td>
                                    <td><code>{{ $product['sku'] }}</code></td>
                                    <td><span class="badge badge-primary">{{ number_format($product['total_sold']) }}</span></td>
                                    <td><strong>{{ number_format($product['total_revenue']) }}₫</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #999; padding: 20px;">
                                        Chưa có dữ liệu bán hàng
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ĐƠN HÀNG GẦN ĐÂY -->
        <div class="section">
             <div class="section-header">
                <h2 class="section-title">📋 Đơn Hàng Gần Đây</h2>
            </div>
            <div class="section-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mã Đơn</th>
                                <th>Khách Hàng</th>
                                <th>Tổng Tiền</th>
                                <th>Trạng Thái</th>
                                <th>Thanh Toán</th>
                                <th>Ngày Tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td><code>{{ $order->code }}</code></td>
                                    <td>{{ $order->account->name ?? $order->receiver_name ?? 'Khách vãng lai' }}</td>
                                    <td><strong>{{ number_format($order->final_price) }}₫</strong></td>
                                    <td>
                                        @if($order->status == 'completed')
                                            <span class="badge badge-success">Hoàn thành</span>
                                        @elseif($order->status == 'processing')
                                            <span class="badge badge-info">Đang xử lý</span>
                                        @elseif($order->status == 'pending')
                                            <span class="badge badge-warning">Chờ xử lý</span>
                                        @else
                                            <span class="badge badge-danger">Đã hủy</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->payment_status == 'paid')
                                            <span class="badge badge-success">Đã thanh toán</span>
                                        @else
                                            <span class="badge badge-warning">Chưa thanh toán</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #999; padding: 20px;">
                                        Chưa có đơn hàng nào
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- THÔNG TIN KHÁC -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">Voucher</span>
                    <div class="stat-card-icon icon-yellow">🎫</div>
                </div>
                <div class="stat-card-value">{{ $voucherStats['total'] }}</div>
                <div class="stat-card-change">
                    <span>Đang hoạt động: <strong>{{ $voucherStats['active'] }}</strong></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">Liên Hệ Mới</span>
                    <div class="stat-card-icon icon-pink">📧</div>
                </div>
                <div class="stat-card-value">{{ $newContacts }}</div>
                <div class="stat-card-change">
                    <span>Chưa đọc: <strong>{{ $unreadContacts }}</strong></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">Danh Mục</span>
                    <div class="stat-card-icon icon-blue">📁</div>
                </div>
                <div class="stat-card-value">{{ $stats['total_categories'] }}</div>
                <div class="stat-card-change">
                    <span>Đang hoạt động</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">Đơn Hàng Tháng Này</span>
                    <div class="stat-card-icon icon-green">📊</div>
                </div>
                <div class="stat-card-value">{{ number_format($orders['this_month']) }}</div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Common colors
        const colors = {
            primary: '#6366f1',
            primaryLight: 'rgba(99, 102, 241, 0.1)',
            primaryStrong: 'rgba(99, 102, 241, 0.8)',
            purple: '#764ba2',
            grid: '#f3f4f6'
        };

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: colors.grid } },
                x: { grid: { display: false } }
            }
        };

        // Biểu đồ doanh thu 7 ngày
        const dailyRevenueCtx = document.getElementById('dailyRevenueChart');
        if (dailyRevenueCtx) {
            new Chart(dailyRevenueCtx, {
                type: 'line',
                data: {
                    labels: @json(array_column($dailyStats, 'date')),
                    datasets: [{
                        label: 'Doanh Thu (₫)',
                        data: @json(array_column($dailyStats, 'revenue')),
                        borderColor: colors.primary,
                        backgroundColor: colors.primaryLight,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: value => new Intl.NumberFormat('vi-VN').format(value) }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Biểu đồ đơn hàng 7 ngày
        const dailyOrdersCtx = document.getElementById('dailyOrdersChart');
        if (dailyOrdersCtx) {
            new Chart(dailyOrdersCtx, {
                type: 'bar',
                data: {
                    labels: @json(array_column($dailyStats, 'date')),
                    datasets: [{
                        label: 'Số Đơn Hàng',
                        data: @json(array_column($dailyStats, 'orders')),
                        backgroundColor: colors.purple,
                        borderRadius: 6
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Biểu đồ doanh thu 12 tháng
        const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart');
        if (monthlyRevenueCtx) {
            new Chart(monthlyRevenueCtx, {
                type: 'bar',
                data: {
                    labels: @json(array_column($monthlyStats, 'month')),
                    datasets: [{
                        label: 'Doanh Thu (₫)',
                        data: @json(array_column($monthlyStats, 'revenue')),
                        backgroundColor: colors.primaryStrong,
                        borderRadius: 6
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: value => new Intl.NumberFormat('vi-VN').format(value) }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    </script>
@endsection

