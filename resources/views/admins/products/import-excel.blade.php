@extends('admins.layouts.master')

@section('title', 'Import Sản Phẩm từ Excel')
@section('page-title', '📥 Import Excel')

@push('head')
    <link rel="shortcut icon" href="{{ asset('admins/img/icons/imports-excel.png') }}" type="image/x-icon">
@endpush

@push('styles')
    <style>
        .import-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0;
            padding: 1.5rem;
            box-shadow: none;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .page-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-color);
        }
        .page-subtitle {
            margin: 0;
            font-size: 0.875rem;
            color: var(--secondary-color);
        }
        .header-actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.8rem;
            font-size: 0.8125rem;
            font-weight: 500;
            border-radius: 0.25rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-back {
            background-color: #f1f5f9;
            color: var(--text-color);
            border: 1px solid #e2e8f0;
        }
        .btn-back:hover {
            background-color: #e2e8f0;
        }
        .btn-download {
            background-color: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
        }
        .btn-download:hover {
            background-color: var(--primary-hover);
        }

        .alert-box {
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            border: 1px solid transparent;
        }
        .alert-success { background: #ecfdf5; color: #047857; border-color: #d1fae5; }
        .alert-error { background: #fef2f2; color: #b91c1c; border-color: #fee2e2; }

        .info-section {
            margin-bottom: 2rem;
        }
        .info-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .sheet-grid {
            display: grid;
            gap: 1rem;
        }
        
        .sheet-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.25rem;
            padding: 1rem;
        }
        
        .sheet-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .sheet-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--primary-color);
        }
        .sheet-badge {
            font-size: 0.7rem;
            padding: 0.1rem 0.4rem;
            border-radius: 0.2rem;
            font-weight: 600;
        }
        .badge-required { background: #fee2e2; color: #991b1b; }
        .badge-optional { background: #e2e8f0; color: #475569; }

        .code-block {
            font-family: 'Consolas', 'Monaco', monospace;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 0.5rem;
            font-size: 0.75rem;
            border-radius: 0.25rem;
            color: #d63384;
            overflow-x: auto;
            white-space: nowrap;
            margin-bottom: 0.5rem;
        }

        .details-list {
            margin: 0;
            padding-left: 1.25rem;
            font-size: 0.8125rem;
            color: #475569;
            line-height: 1.4;
        }
        .details-list li {
            margin-bottom: 0.25rem;
        }
        .details-list strong {
            color: var(--text-color);
        }

        .upload-area {
            background: #ffffff;
            border: 2px dashed #e2e8f0;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.2s;
        }
        .upload-area:hover {
            border-color: var(--primary-color);
            background: #f8fafc;
        }
        .upload-input {
            width: 100%;
            margin-bottom: 1rem;
        }
        .btn-submit {
            padding: 0.6rem 2rem;
            background: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-submit:hover {
            background: var(--primary-hover);
        }
        
        .important-note {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.8125rem;
            color: #92400e;
        }
        .important-note ul {
            margin: 0.5rem 0 0;
            padding-left: 1.25rem;
        }
    </style>
@endpush

@section('content')
    <div class="import-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Import Sản Phẩm từ Excel</h1>
                <p class="page-subtitle">Upload file Excel để import sản phẩm, chi tiết, hình ảnh, biến thể và FAQs ngầm định.</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.products.index') }}" class="btn-action btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
                <a href="{{ route('admin.products.export-excel') }}" class="btn-action btn-download">
                    <i class="fa-solid fa-file-export"></i> Export Mẫu
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-box alert-success">
                <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                @if(session('log_file'))
                    <div style="margin-top: 0.5rem; font-size: 0.75rem;">
                        <strong>Log:</strong> <code>{{ session('log_file') }}</code>
                    </div>
                @endif
            </div>
        @endif

        @if(session('error'))
            <div class="alert-box alert-error">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                @if(session('log_file'))
                    <div style="margin-top: 0.5rem; font-size: 0.75rem;">
                        <strong>Log:</strong> <code>{{ session('log_file') }}</code>
                    </div>
                @endif
            </div>
        @endif

        <div class="info-section">
            <div class="info-title"><i class="fa-solid fa-table"></i> Cấu trúc file Excel yêu cầu (4 Sheets)</div>
            
            <div class="sheet-grid">
                <!-- Sheet 1 -->
                <div class="sheet-card">
                    <div class="sheet-header">
                        <span class="sheet-name">1. products</span>
                        <span class="sheet-badge badge-required">Bắt buộc</span>
                    </div>
                    <div class="code-block">
                        sku | name | slug | description | short_description | price | sale_price | cost_price | stock_quantity | meta_title | ...
                    </div>
                    <ul class="details-list">
                        <li><strong>sku</strong>: Khóa chính duy nhất.</li>
                        <li><strong>category_slugs</strong>: Ngăn cách bởi dấu phẩy.</li>
                        <li><strong>image_ids</strong>: <code>IMG1,IMG2</code> (tham chiếu Sheet Images).</li>
                    </ul>
                </div>

                <!-- Sheet 1b: Product Descriptions -->
                <div class="sheet-card">
                    <div class="sheet-header">
                        <span class="sheet-name">2. product_descriptions</span>
                        <span class="sheet-badge badge-optional">Khuyến nghị (Mới)</span>
                    </div>
                    <div class="code-block">
                        sku | intro_title | intro_content | intro_image | feature_title | feature_content | feature_image | use_title | ...
                    </div>
                    <ul class="details-list">
                        <li><strong>sku</strong>: Để khớp với sản phẩm.</li>
                        <li><strong>Sections</strong>: <code>intro</code>, <code>feature</code>, <code>use</code> (Công dụng), <code>meaning</code> (Ý nghĩa), <code>care</code> (Chăm sóc).</li>
                        <li>Mỗi section có 3 cột: <code>_title</code>, <code>_content</code>, <code>_image</code>.</li>
                        <li>Dùng sheet này để nhập nội dung chi tiết dạng khối thay vì HTML thô.</li>
                    </ul>
                </div>

                <!-- Sheet 2 -->
                <div class="sheet-card">
                    <div class="sheet-header">
                        <span class="sheet-name">3. images</span>
                        <span class="sheet-badge badge-optional">Tùy chọn</span>
                    </div>
                    <div class="code-block">
                        sku | image_key | url | title | alt | is_primary | order
                    </div>
                    <ul class="details-list">
                        <li><strong>sku</strong>: Bắt buộc để map ảnh.</li>
                        <li><strong>url</strong>: Chỉ cần tên file (vd: <code>anh.jpg</code>) trong <code>public/clients/assets/img/clothes/</code>.</li>
                    </ul>
                </div>

                <!-- Standard Sheets -->
                <div class="sheet-card" style="display: flex; gap: 1rem;">
                    <div style="flex: 1;">
                        <div class="sheet-header">
                            <span class="sheet-name">4. faqs</span>
                            <span class="sheet-badge badge-optional">Tùy chọn</span>
                        </div>
                        <div class="code-block">sku | question | answer | order</div>
                    </div>
                    <div style="flex: 1;">
                        <div class="sheet-header">
                            <span class="sheet-name">5. how_tos</span>
                            <span class="sheet-badge badge-optional">Tùy chọn</span>
                        </div>
                        <div class="code-block">sku | title | steps | supplies | is_active</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="important-note">
            <strong><i class="fa-solid fa-triangle-exclamation"></i> Lưu ý quan trọng:</strong>
            <ul>
                <li><strong>Cấu trúc Sheet:</strong> Tên sheet phải chính xác 100%. Không đổi tên cột.</li>
                <li><strong>Danh mục:</strong> Phải dùng Slug của danh mục đã tồn tại.</li>
                <li><strong>Ảnh:</strong> Upload ảnh vào host/server trước, file excel chỉ map tên file.</li>
                <li><strong>Description:</strong> Nếu dùng Sheet <code>product_descriptions</code>, nó sẽ ghi đè nội dung description cũ.</li>
            </ul>
        </div>

        <div class="info-section" style="margin-top: 2rem;">
            <div class="info-title"><i class="fa-solid fa-upload"></i> Upload File</div>
            <form action="{{ route('admin.products.import-excel.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="upload-area">
                    <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" class="upload-input" required>
                    <br>
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Bắt đầu Import
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

