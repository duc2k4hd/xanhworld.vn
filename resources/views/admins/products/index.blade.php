@extends('admins.layouts.master')

@section('title', 'Quản lý sản phẩm')
@section('page-title', '📦 Sản phẩm')

@push('head')
    <link rel="stylesheet" href="{{ asset('admins/css/products.css') }}">
    <link rel="shortcut icon" href="{{ asset('admins/img/icons/products-icon.png') }}" type="image/x-icon">
@endpush

@section('content')
    <div>
        <div class="page-header">
            <h2 class="page-title">Danh sách sản phẩm</h2>
            <div class="header-actions">
                <a href="{{ route('admin.products.import-excel') }}" class="btn btn-secondary">
                    <i class="fas fa-file-import"></i> Import Excel
                </a>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm sản phẩm
                </a>
            </div>
        </div>

        <form class="filter-bar" method="GET">
            <input type="text" name="keyword" placeholder="Tìm SKU hoặc tên..."
                   value="{{ request('keyword') }}">
            <select name="status">
                <option value="">-- Trạng thái --</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang bán</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tạm ẩn</option>
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Lọc
            </button>
        </form>

        <div class="table-responsive product-table-wrapper">
            <table class="product-table">
                <thead>
                <tr>
                    <th class="checkbox-cell">
                        <input type="checkbox" id="select-all-products">
                    </th>
                    <th class="product-image-cell">Ảnh</th>
                    <th>SKU</th>
                    <th>Tên</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Stock</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td class="checkbox-cell">
                            <input type="checkbox" name="selected[]" value="{{ $product->id }}" class="product-checkbox" form="bulk-action-form">
                        </td>
                        <td class="product-image-cell">
                            @php
                                $imageUrl = null;
                                if ($product->primaryImage && $product->primaryImage->url) {
                                    $imagePath = 'clients/assets/img/clothes/' . $product->primaryImage->url;
                                    if (file_exists(public_path($imagePath))) {
                                        $imageUrl = asset($imagePath);
                                    }
                                }
                                if (!$imageUrl) {
                                    $imageUrl = asset('clients/assets/img/clothes/no-image.webp');
                                }
                            @endphp
                            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="product-image" loading="lazy">
                        </td>
                        <td>{{ $product->sku }}</td>
                        <td>
                            <span class="product-name">{{ $product->name }}</span>
                            <small class="product-slug">{{ $product->slug }}</small>
                        </td>
                        <td>{{ $product->primaryCategory->name ?? '-' }}</td>
                        <td>{{ number_format($product->price) }}₫</td>
                        <td class="stock-cell">
                            <span class="stock-count">{{ $product->stock_quantity }}</span>
                            @if(! is_null($product->stock_quantity))
                                @if($product->stock_quantity <= 0)
                                    <span class="badge badge-danger">Hết hàng</span>
                                @elseif($product->stock_quantity <= 5)
                                    <span class="badge badge-warning">Sắp hết</span>
                                @else
                                    <span class="badge badge-success">Còn hàng</span>
                                @endif
                                <a href="{{ route('admin.products.inventory', $product) }}" class="stock-history-link">Xem lịch sử</a>
                            @endif
                        </td>
                        <td>
                            @if($product->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-secondary btn-icon-only" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($product->is_active)
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                           onsubmit="return confirm('Chuyển sản phẩm này sang trạng thái TẠM ẨN?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon-only" style="background-color: var(--danger-color); color: white; border: none;" title="Ẩn">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.products.restore', $product) }}" method="POST"
                                           onsubmit="return confirm('Khôi phục sản phẩm này về trạng thái hiển thị?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary btn-icon-only" title="Khôi phục">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty-state">
                            <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                            Chưa có sản phẩm nào
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($products->count() > 0)
            <form action="{{ route('admin.products.bulk-action') }}" method="POST" id="bulk-action-form" class="bulk-actions">
                @csrf
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-level-up-alt fa-rotate-90"></i>
                    <span style="font-weight: 500; font-size: 0.875rem;">Với các mục đã chọn:</span>
                </div>
                <button type="submit" class="btn btn-sm btn-secondary" name="bulk_action" value="hide">
                    <i class="fas fa-eye-slash"></i> Ẩn
                </button>
                <button type="submit" class="btn btn-sm" style="background-color: var(--danger-color); color: white; border: none;" name="bulk_action" value="delete" onclick="return confirm('Bạn có chắc muốn xóa các sản phẩm này?')">
                    <i class="fas fa-trash"></i> Xóa mềm
                </button>
            </form>
        @endif

        <div style="margin-top: 2rem;">
            {{ $products->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectAll = document.getElementById('select-all-products');
            const checkboxes = document.querySelectorAll('.product-checkbox');
            const form = document.getElementById('bulk-action-form');

            if (!selectAll) return;

            selectAll.addEventListener('change', () => {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });

            if (form) {
                form.addEventListener('submit', (e) => {
                    const hasSelected = Array.from(checkboxes).some(cb => cb.checked);
                    if (!hasSelected) {
                        e.preventDefault();
                        alert('Vui lòng chọn ít nhất một sản phẩm trước khi thực hiện hành động.');
                    }
                });
            }
        });
    </script>
@endpush
