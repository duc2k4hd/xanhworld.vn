@extends('admins.layouts.master')

@section('title', 'Quản lý danh mục')
@section('page-title', '🏷️ Danh mục sản phẩm')

@push('head')
    <link rel="shortcut icon" href="{{ asset('admins/img/icons/category-icon.png') }}" type="image/x-icon">
@endpush

@push('styles')
    <style>
        .category-container {
            display: flex;
            gap: 20px;
        }
        
        .category-sidebar {
            width: 280px;
            flex-shrink: 0;
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
        
        .category-main {
            flex: 1;
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .category-tree {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .tree-item {
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }
        
        .tree-item:hover {
            background: #f1f5f9;
        }
        
        .tree-item.active {
            background: #dbeafe;
            color: #1e40af;
            font-weight: 600;
        }
        
        .tree-toggle {
            width: 20px;
            text-align: center;
            cursor: pointer;
        }
        
        .tree-children {
            margin-left: 24px;
            margin-top: 4px;
            display: none;
        }
        
        .tree-children.expanded {
            display: block;
        }
        
        .category-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        
        .category-table th, .category-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #eef2f7;
            text-align: left;
            font-size: 13px;
        }
        
        .category-table th {
            background: #f8fafc;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            font-weight: 600;
        }
        
        .category-table tr:hover td {
            background: #f1f5f9;
        }
        
        .category-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .filter-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .filter-bar input,
        .filter-bar select {
            padding: 8px 12px;
            border: 1px solid #cbd5f5;
            border-radius: 6px;
            font-size: 13px;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #dcfce7;
            color: #15803d;
        }
        
        .badge-danger {
            background: #fee2e2;
            color: #b91c1c;
        }
        
        .actions {
            display: flex;
            gap: 6px;
        }
        
        .btn-sm {
            padding: 4px 10px;
            font-size: 12px;
        }
    </style>
@endpush

@section('content')
    <div class="category-container">
        <!-- Sidebar - Tree View -->
        <div class="category-sidebar">
            <h3 style="margin:0 0 16px;font-size:16px;font-weight:600;">📁 Cây danh mục</h3>
            <ul class="category-tree" id="categoryTree">
                @foreach($tree ?? [] as $item)
                    @include('admins.categories.partials.tree-item', ['item' => $item, 'level' => 0])
                @endforeach
            </ul>
        </div>

        <!-- Main Content -->
        <div class="category-main">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h2 style="margin:0;">Danh sách danh mục</h2>
                @if($parentId)
                    <div style="display:flex;gap:10px;">
                        <a href="{{ route('admin.categories.edit', $parentId) }}" class="btn btn-info">➕ Sửa danh mục cha</a>
                    </div>
                @endif

                <div style="display:flex;gap:10px;">
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">➕ Thêm danh mục</a>
                </div>
            </div>

            <form class="filter-bar" method="GET">
                <input type="text" name="keyword" placeholder="🔍 Tìm tên hoặc slug..."
                       value="{{ request('keyword') }}" style="flex:1;min-width:200px;">
                <select name="status">
                    <option value="">-- Trạng thái --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hiển thị</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tạm ẩn</option>
                </select>
                <select name="only_root">
                    <option value="">-- Tất cả --</option>
                    <option value="1" {{ request('only_root') === '1' ? 'selected' : '' }}>Chỉ danh mục gốc</option>
                </select>
                <select name="sort_by">
                    <option value="order" {{ request('sort_by') === 'order' ? 'selected' : '' }}>Sắp xếp theo thứ tự</option>
                    <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Sắp xếp theo tên</option>
                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Sắp xếp theo ngày tạo</option>
                </select>
                <select name="per_page">
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50/trang</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100/trang</option>
                </select>
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Xóa bộ lọc</a>
            </form>

            <form id="category-bulk-form" action="{{ route('admin.categories.bulk-action') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="category-table">
                        <thead>
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" id="select-all-categories">
                            </th>
                            <th style="width:60px;">ID</th>
                            <th style="width:80px;">Ảnh</th>
                            <th>Tên</th>
                            <th>Slug</th>
                            <th>Danh mục cha</th>
                            <th style="width:80px;">Thứ tự</th>
                            <th style="width:100px;">Số lượng con</th>
                            <th style="width:100px;">Trạng thái</th>
                            <th style="width:120px;">Ngày tạo</th>
                            <th style="width:150px;">Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    @if($category->id === 1)
                                        <input type="checkbox" disabled title="Không thể chọn danh mục mặc định (ID: 1)" style="opacity:0.5;cursor:not-allowed;">
                                    @else
                                        <input type="checkbox" name="selected[]" value="{{ $category->id }}" class="category-checkbox" form="category-bulk-form">
                                    @endif
                                </td>
                                <td>{{ $category->id }}</td>
                                <td>
                                    @if($category->image)
                                        <img src="{{ asset('clients/assets/img/categories/' . $category->image) }}" 
                                             alt="{{ $category->name }}" 
                                             class="category-image"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'50\' height=\'50\'%3E%3Crect width=\'50\' height=\'50\' fill=\'%23ddd\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' font-size=\'10\'%3ENo Image%3C/text%3E%3C/svg%3E';">
                                    @else
                                        <div style="width:50px;height:50px;background:#f1f5f9;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:20px;">📁</div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $category->name }}</strong>
                                </td>
                                <td>
                                    <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:11px;">{{ $category->slug }}</code>
                                </td>
                                <td>
                                    @if($category->id === 1)
                                        <span style="color:#94a3b8;">Root (Mặc định)</span>
                                    @else
                                        <form action="{{ route('admin.categories.update-parent', $category) }}" method="POST" style="display:inline;" class="parent-change-form">
                                            @csrf
                                            @method('PATCH')
                                            <select name="parent_id" class="form-control form-control-sm" style="min-width:200px;display:inline-block;font-size:13px;" onchange="this.form.submit()">
                                                <option value="" {{ !$category->parent_id ? 'selected' : '' }}>🏠 Root (Không có)</option>
                                                @foreach(\App\Helpers\CategoryHelper::getDropdownOptions($category->id) as $option)
                                                    <option value="{{ $option['value'] }}" {{ $category->parent_id == $option['value'] ? 'selected' : '' }}>
                                                        {{ $option['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif
                                </td>
                                <td style="text-align:center;">{{ $category->order }}</td>
                                <td style="text-align:center;">
                                    <span class="badge" style="background:#e0e7ff;color:#4338ca;">
                                        {{ $category->children()->count() }}
                                    </span>
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td style="font-size:11px;color:#64748b;">
                                    {{ $category->created_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-secondary btn-sm">✏️ Sửa</a>
                                        @if($category->id === 1)
                                            <button type="button" class="btn btn-danger btn-sm" disabled 
                                                    title="Không thể xóa danh mục mặc định (ID: 1)" 
                                                    style="opacity:0.5;cursor:not-allowed;">🗑️ Xóa</button>
                                        @else
                                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display:inline;" 
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">🗑️ Xóa</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" style="text-align:center;padding:40px;color:#94a3b8;">
                                    <div style="font-size:48px;margin-bottom:16px;">📁</div>
                                    <div>Chưa có danh mục nào</div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:16px;display:flex;gap:10px;align-items:center;">
                    <button type="submit" class="btn btn-secondary btn-sm" name="bulk_action" value="hide">Ẩn đã chọn</button>
                    <button type="submit" class="btn btn-primary btn-sm" name="bulk_action" value="show">Hiển thị đã chọn</button>
                    @can('deleteAny', \App\Models\Category::class)
                        <button type="submit" class="btn btn-danger btn-sm" name="bulk_action" value="delete" 
                                onclick="return confirm('Bạn có chắc muốn xóa các danh mục đã chọn?');">Xóa đã chọn</button>
                    @endcan
                </div>
            </form>

            <div style="margin-top:20px;">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Select all checkbox
            const selectAll = document.getElementById('select-all-categories');
            const checkboxes = document.querySelectorAll('.category-checkbox');
            const form = document.getElementById('category-bulk-form');

            if (selectAll) {
                selectAll.addEventListener('change', () => {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                });
            }

            if (form) {
                form.addEventListener('submit', (e) => {
                    const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                    if (!anyChecked) {
                        e.preventDefault();
                        alert('Vui lòng chọn ít nhất một danh mục.');
                    }
                });
            }

            // Tree toggle
            document.querySelectorAll('.tree-toggle').forEach(toggle => {
                toggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const children = toggle.closest('.tree-item').querySelector('.tree-children');
                    if (children) {
                        children.classList.toggle('expanded');
                        toggle.textContent = children.classList.contains('expanded') ? '▼' : '▶';
                    }
                });
            });

            // Tree item click
            document.querySelectorAll('.tree-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    if (e.target.classList.contains('tree-toggle')) return;
                    
                    // Update active state
                    document.querySelectorAll('.tree-item').forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                    
                    // Filter by parent
                    const categoryId = item.dataset.categoryId;
                    if (categoryId) {
                        window.location.href = '{{ route("admin.categories.index") }}?parent_id=' + categoryId;
                    }
                });
            });
        });
    </script>
@endpush
