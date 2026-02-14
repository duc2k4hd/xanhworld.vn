@extends('admins.layouts.master')

@section('title', 'Quản lý danh mục')
@section('page-title', '🏷️ Danh mục sản phẩm')

@push('head')
    <link rel="shortcut icon" href="{{ asset('admins/img/icons/category-icon.png') }}" type="image/x-icon">
@endpush

@push('styles')
    <style>
        :root {
            --primary-color: #3b82f6;
            --text-color: #1f2937;
            --bg-light: #f9fafb;
            --border-color: #e5e7eb;
        }

        .category-container {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 20px;
            align-items: start;
        }
        
        /* Sidebar Styles */
        .category-sidebar {
            position: sticky;
            top: 20px;
            background: #fff;
            border-radius: 10px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            border: 1px solid var(--border-color);
        }
        
        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .sidebar-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        
        .category-tree {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .tree-item {
            padding: 5px 8px;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            font-size: 13px;
            color: #4b5563;
        }
        
        .tree-item:hover {
            background: #f3f4f6;
            color: var(--primary-color);
        }
        
        .tree-item.active {
            background: #eff6ff;
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .tree-toggle {
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 10px;
            color: #9ca3af;
            transition: transform 0.2s;
        }
        
        .tree-toggle:hover {
            color: var(--primary-color);
        }
        
        .tree-children {
            margin-left: 18px;
            padding-left: 8px;
            border-left: 1px solid #f3f4f6;
            display: none;
        }
        
        .tree-children.expanded {
            display: block;
        }
        
        /* Main Content Styles */
        .category-main {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .main-header {
            padding: 12px 16px;
            background: #fff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filter-section {
            padding: 10px 16px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-box {
            position: relative;
            flex: 1;
            min-width: 200px;
            max-width: 300px;
        }
        
        .search-box input {
            width: 100%;
            padding: 6px 10px 6px 30px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
            transition: border-color 0.2s;
        }
        .search-box input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 12px;
        }

        .filter-select {
            padding: 6px 24px 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
            background-color: #fff;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            max-height: calc(100vh - 220px);
        }
        
        .compact-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12px;
        }
        
        .compact-table th {
            background: #f8fafc;
            position: sticky;
            top: 0;
            z-index: 10;
            padding: 8px 10px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        
        .compact-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #334155;
        }
        
        .compact-table tr:hover td {
            background: #f8fafc;
        }
        
        .compact-table tr:last-child td {
            border-bottom: none;
        }

        .col-checkbox { width: 30px; text-align: center; }
        .col-id { width: 50px; text-align: center; color: #94a3b8; }
        .col-image { width: 50px; text-align: center; }
        .col-name { min-width: 200px; }
        .col-slug { width: 140px; color: #64748b; font-family: monospace; font-size: 11px; }
        .col-parent { width: 160px; }
        .col-status { width: 90px; text-align: center; }
        .col-order { width: 60px; text-align: center; }
        .col-created { width: 90px; text-align: right; color: #64748b; font-size: 11px; }
        .col-actions { width: 90px; text-align: right; }

        .img-thumb {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        
        .category-name-wrapper {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .category-name {
            font-weight: 600;
            color: #1e293b;
        }
        
        .children-badge {
            background: #e0e7ff;
            color: #4338ca;
            padding: 1px 5px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 600;
            min-width: 18px;
            text-align: center;
        }

        .status-dot {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 99px;
        }
        .status-active { background: #dcfce7; color: #166534; }
        .status-inactive { background: #fee2e2; color: #991b1b; }

        .parent-select {
            padding: 2px 20px 2px 6px;
            border: 1px solid transparent;
            background: transparent;
            font-size: 12px;
            color: #475569;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            max-width: 100%;
        }
        .parent-select:hover {
            border-color: #cbd5e1;
            background: #fff;
        }
        
        .btn-icon {
            padding: 4px;
            border-radius: 4px;
            color: #64748b;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }
        .btn-icon:hover { background: #f1f5f9; color: var(--primary-color); }
        .btn-icon.text-danger:hover { background: #fee2e2; color: #ef4444; }
        
        .btn-action {
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-primary-new { background: #2563eb; color: #fff; border: 1px solid #1d4ed8; }
        .btn-primary-new:hover { background: #1d4ed8; }
        
        .pagination-wrapper {
            padding: 10px 16px;
            border-top: 1px solid var(--border-color);
            background: #fff;
        }

        .bulk-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-left: auto;
        }

        @media (max-width: 1024px) {
            .category-container {
                grid-template-columns: 1fr;
            }
            .category-sidebar {
                position: relative;
                top: 0;
                max-height: 300px;
                margin-bottom: 16px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="category-container">
        <!-- Sidebar - Tree View -->
        <aside class="category-sidebar">
            <div class="sidebar-header">
                <h3 class="sidebar-title">📁 Cây danh mục</h3>
            </div>
            <ul class="category-tree" id="categoryTree">
                @foreach($tree ?? [] as $item)
                    @include('admins.categories.partials.tree-item', ['item' => $item, 'level' => 0])
                @endforeach
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="category-main">
            <!-- Header with Actions -->
            <div class="main-header">
                <div>
                    <h2 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0;">Danh sách danh mục</h2>
                    <p style="font-size: 12px; color: #6b7280; margin: 2px 0 0;">Quản lý toàn bộ danh mục sản phẩm</p>
                </div>
                <div style="display: flex; gap: 8px;">
                    @if($parentId)
                        <a href="{{ route('admin.categories.edit', $parentId) }}" class="btn-action" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                            <span>✏️</span> Sửa cha
                        </a>
                    @endif
                    <a href="{{ route('admin.categories.create') }}" class="btn-action btn-primary-new">
                        <span>➕</span> Thêm mới
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" class="filter-section">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="keyword" placeholder="Tìm kiếm danh mục..." value="{{ request('keyword') }}">
                </div>
                
                <select name="status" class="filter-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hiển thị</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Đang ẩn</option>
                </select>

                <select name="only_root" class="filter-select">
                    <option value="">Tất cả cấp</option>
                    <option value="1" {{ request('only_root') === '1' ? 'selected' : '' }}>Chỉ danh mục gốc</option>
                </select>
                
                <select name="sort_by" class="filter-select">
                    <option value="order" {{ request('sort_by') === 'order' ? 'selected' : '' }}>Sắp xếp: Thứ tự</option>
                    <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Sắp xếp: Tên A-Z</option>
                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Sắp xếp: Mới nhất</option>
                </select>

                <button type="submit" class="btn-action" style="background: #4b5563; color: white;">Lọc</button>
                
                @if(request()->anyFilled(['keyword', 'status', 'only_root', 'sort_by', 'parent_id']))
                    <a href="{{ route('admin.categories.index') }}" class="btn-action" style="background: #f3f4f6; color: #4b5563; padding: 6px 10px;">✕</a>
                @endif
                
                <div class="bulk-actions" id="bulkActions" style="display: none;">
                    <span style="font-size: 12px; color: #64748b; padding-right: 8px; border-right: 1px solid #e2e8f0;">Đã chọn <b id="selectedCount">0</b></span>
                    <button type="submit" form="category-bulk-form" name="bulk_action" value="hide" class="btn-icon" title="Ẩn đã chọn" style="width: 28px; height: 28px;">👁️‍🗨️</button>
                    <button type="submit" form="category-bulk-form" name="bulk_action" value="show" class="btn-icon" title="Hiện đã chọn" style="width: 28px; height: 28px;">👁️</button>
                    @can('deleteAny', \App\Models\Category::class)
                        <button type="submit" form="category-bulk-form" name="bulk_action" value="delete" class="btn-icon text-danger" title="Xóa đã chọn" style="width: 28px; height: 28px;" onclick="return confirm('Xóa các danh mục đã chọn?');">🗑️</button>
                    @endcan
                </div>
            </form>

            <!-- Table -->
            <form id="category-bulk-form" action="{{ route('admin.categories.bulk-action') }}" method="POST" class="table-container">
                @csrf
                <table class="compact-table">
                    <thead>
                        <tr>
                            <th class="col-checkbox"><input type="checkbox" id="select-all-categories"></th>
                            <th class="col-id">ID</th>
                            <th class="col-image">Ảnh</th>
                            <th class="col-name">Tên danh mục</th>
                            <th class="col-slug">Slug</th>
                            <th class="col-parent">Danh mục cha</th>
                            <th class="col-status">Trạng thái</th>
                            <th class="col-order">TT</th>
                            <th class="col-created">Ngày tạo</th>
                            <th class="col-actions">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="col-checkbox">
                                    @if($category->id !== 1)
                                        <input type="checkbox" name="selected[]" value="{{ $category->id }}" class="category-checkbox">
                                    @else
                                        <input type="checkbox" disabled style="opacity: 0.3;">
                                    @endif
                                </td>
                                <td class="col-id">#{{ $category->id }}</td>
                                <td class="col-image">
                                    @php
                                        $imagePath = 'clients/assets/img/categories/' . $category->image;
                                        $imageUrl = $category->image && file_exists(public_path($imagePath)) 
                                            ? asset($imagePath) 
                                            : asset('clients/assets/img/categories/no-image.webp');
                                    @endphp
                                    <img src="{{ $imageUrl }}" class="img-thumb" alt="{{ $category->name }}" loading="lazy" onerror="this.src='{{ asset('clients/assets/img/categories/no-image.webp') }}'">
                                </td>
                                <td class="col-name">
                                    <div class="category-name-wrapper">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="category-name text-decoration-none">
                                            {{ \Illuminate\Support\Str::limit($category->name, 40) }}
                                        </a>
                                        @if($category->children_count > 0)
                                            <span class="children-badge" title="{{ $category->children_count }} danh mục con">{{ $category->children_count }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="col-slug" title="{{ $category->slug }}">
                                    {{ \Illuminate\Support\Str::limit($category->slug, 20) }}
                                </td>
                                <td class="col-parent">
                                    @if($category->id === 1)
                                        <span class="text-muted" style="font-size: 11px;">-</span>
                                    @else
                                        <form action="{{ route('admin.categories.update-parent', $category) }}" method="POST" class="parent-change-form">
                                            @csrf @method('PATCH')
                                            <select name="parent_id" class="parent-select" onchange="this.form.submit()">
                                                <option value="">-- Root --</option>
                                                @foreach(\App\Helpers\CategoryHelper::getDropdownOptions($category->id) as $option)
                                                    <option value="{{ $option['value'] }}" {{ $category->parent_id == $option['value'] ? 'selected' : '' }}>
                                                        {{ $option['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif
                                </td>
                                <td class="col-status">
                                    @if($category->is_active)
                                        <span class="status-dot status-active">Hiển thị</span>
                                    @else
                                        <span class="status-dot status-inactive">Ẩn</span>
                                    @endif
                                </td>
                                <td class="col-order">{{ $category->order }}</td>
                                <td class="col-created">
                                    {{ $category->created_at?->format('d/m/y') }}
                                </td>
                                <td class="col-actions">
                                    <div style="display: flex; justify-content: flex-end; gap: 2px;">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn-icon" title="Chỉnh sửa">
                                            ✏️
                                        </a>
                                        @if($category->id !== 1)
                                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Xóa danh mục này?');" style="display: inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-icon text-danger" title="Xóa">
                                                    🗑️
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align:center; padding: 40px; color: #94a3b8;">
                                    <div style="font-size: 40px; margin-bottom: 10px; opacity: 0.5;">📭</div>
                                    <p>Không tìm thấy danh mục nào</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>

            <div class="pagination-wrapper">
                {{ $categories->links('pagination::bootstrap-5') }}
            </div>
            <div style="padding: 0 16px 12px; font-size: 11px; color: #9ca3af; text-align: right;">
                Hiển thị {{ $categories->firstItem() ?? 0 }}-{{ $categories->lastItem() ?? 0 }} trên tổng số {{ $categories->total() }} danh mục
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Bulk Selection Logic
            const selectAll = document.getElementById('select-all-categories');
            const checkboxes = document.querySelectorAll('.category-checkbox');
            const bulkActions = document.getElementById('bulkActions');
            const selectedCountDisplay = document.getElementById('selectedCount');

            function updateBulkActions() {
                const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
                if (checkedCount > 0) {
                    bulkActions.style.display = 'flex';
                    selectedCountDisplay.textContent = checkedCount;
                } else {
                    bulkActions.style.display = 'none';
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', () => {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateBulkActions();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkActions);
            });

            // Prevent Action form submission if no selection
            const form = document.getElementById('category-bulk-form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    if (e.submitter && e.submitter.name === 'bulk_action') {
                        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                        if (!anyChecked) {
                            e.preventDefault();
                            alert('Vui lòng chọn ít nhất một danh mục.');
                        }
                    }
                });
            }

            // Tree toggle
            document.querySelectorAll('.tree-toggle').forEach(toggle => {
                toggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const children = toggle.closest('.tree-item').nextElementSibling;
                    if (children && children.classList.contains('tree-children')) {
                        children.classList.toggle('expanded');
                        toggle.textContent = children.classList.contains('expanded') ? '▼' : '▶';
                    }
                });
            });

            // Tree item click
            document.querySelectorAll('.tree-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    if (e.target.classList.contains('tree-toggle')) return;
                    
                    const categoryId = item.dataset.categoryId;
                    if (categoryId) {
                        window.location.href = '?parent_id=' + categoryId;
                    }
                });
            });
        });
    </script>
@endpush
