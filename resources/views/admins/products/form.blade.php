@extends('admins.layouts.master')

@php
    $isEdit = $product->exists;
    $pageTitle = $isEdit ? 'Chỉnh sửa sản phẩm' : 'Tạo sản phẩm mới';
    
    // Lấy selected tag IDs từ relationship hoặc tag_ids JSON
    $selectedTagIds = old('tag_ids', []);
    if (empty($selectedTagIds) && $product->exists) {
        $selectedTagIds = $product->tags->pluck('id')->toArray();
    }
    if (empty($selectedTagIds) && $product->exists && !empty($product->tag_ids)) {
        $selectedTagIds = is_array($product->tag_ids) ? $product->tag_ids : [];
    }
    
    // Lấy selected tag names để hiển thị
    $selectedTagNames = [];
    if (!empty($selectedTagIds)) {
        $selectedTags = \App\Models\Tag::whereIn('id', $selectedTagIds)->get();
        $selectedTagNames = $selectedTags->pluck('name')->toArray();
    }
    
    // Xử lý tag_names từ old input (nếu có)
    $tagNamesInput = old('tag_names', '');
    
    // Load images từ image_ids JSON
    // Trong database chỉ lưu tên file (ví dụ: abc123.jpg)
    // Đường dẫn đầy đủ: /clients/assets/img/clothes/ + tên file
    $productImages = [];
    if ($product->exists && !empty($product->image_ids)) {
        $imageIds = is_array($product->image_ids) ? $product->image_ids : [];
        $images = \App\Models\Image::whereIn('id', $imageIds)->get()->keyBy('id');
        foreach ($imageIds as $id) {
            if (isset($images[$id])) {
                $img = $images[$id];
                $productImages[] = [
                    'id' => $img->id,
                    'url' => $img->url, // Chỉ tên file (ví dụ: abc123.jpg)
                    'title' => $img->title,
                    'notes' => $img->notes,
                    'alt' => $img->alt,
                    'is_primary' => $img->is_primary,
                    'order' => $img->order,
                ];
            }
        }
    }

    $includedCategoryIds = old('category_included_ids', $product->category_included_ids ?? []);
    if (! is_array($includedCategoryIds)) {
        $includedCategoryIds = (array) $includedCategoryIds;
    }
    $includedCategoryIds = array_values(array_unique(array_map('intval', array_filter($includedCategoryIds))));

    // Load variants
    $productVariants = [];
    if ($product->exists) {
        $variants = old('variants', $product->allVariants->toArray() ?? []);
        foreach ($variants as $variant) {
            // Xử lý attributes - có thể là array hoặc JSON string
            $attributes = $variant['attributes'] ?? null;
            if (is_string($attributes)) {
                $attributes = json_decode($attributes, true) ?: [];
            }
            if (!is_array($attributes)) {
                $attributes = [];
            }
            
            $productVariants[] = [
                'id' => $variant['id'] ?? null,
                'name' => $variant['name'] ?? '',
                'sku' => $variant['sku'] ?? '',
                'price' => $variant['price'] ?? 0,
                'sale_price' => $variant['sale_price'] ?? null,
                'cost_price' => $variant['cost_price'] ?? null,
                'stock_quantity' => $variant['stock_quantity'] ?? null,
                'image_id' => $variant['image_id'] ?? null,
                'attributes' => $attributes,
                'is_active' => $variant['is_active'] ?? true,
                'sort_order' => $variant['sort_order'] ?? 0,
            ];
        }
    }
@endphp

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@push('head')
    @if($isEdit)
        <link rel="shortcut icon" href="{{ asset('admins/img/icons/edit-product-icon.png') }}" type="image/x-icon">
    @else
        <link rel="shortcut icon" href="{{ asset('admins/img/icons/create-product-icon.png') }}" type="image/x-icon">
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('admins/css/product_form.css') }}">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script src="{{ asset('admins/js/products-form.js') }}"></script>
@endpush

@section('content')
    <div class="product-form-layout">
        <div class="product-form-main">
            <form id="product-form" data-dirty-guard="true"
                  action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}"
                  @if($isEdit) data-release-lock-url="{{ route('admin.products.release-lock', $product) }}" @endif
                  method="POST" enctype="multipart/form-data">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                @if($isEdit && $product->locked_by === auth('web')->id())
                    <div class="alert-lock">
                        <strong>🔒 Đang chỉnh sửa:</strong>
                        <span>Bạn đang khóa sản phẩm này để chỉnh sửa. Hệ thống sẽ tự động mở khóa khi bạn lưu hoặc sau {{ config('app.editor_lock_minutes', 15) }} phút không hoạt động.</span>
                    </div>
                @endif

                <div class="card">
            <h3>Thông tin cơ bản</h3>
            <div class="grid-3">
                <div>
                    <label>SKU</label>
                    <input type="text" class="form-control" name="sku" value="{{ old('sku', $product->sku) }}" required>
                </div>
                <div>
                    <label>Tên sản phẩm</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $product->name) }}" required>
                </div>
                <div>
                    <label>Slug</label>
                    <input type="text" class="form-control" name="slug" value="{{ old('slug', $product->slug) }}">
                </div>
                <div>
                    <label>Giá bán</label>
                    <input type="number" step="0.01" class="form-control" name="price" value="{{ old('price', $product->price) }}" required>
                </div>
                <div>
                    <label>Giá khuyến mãi</label>
                    <input type="number" step="0.01" class="form-control" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}">
                </div>
                <div>
                    <label>Giá nhập</label>
                    <input type="number" step="0.01" class="form-control" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}">
                </div>
                <div>
                    <label>Tồn kho</label>
                    <input type="number" class="form-control" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}">
                </div>
                <div>
                    <label>Trạng thái</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $product->is_active ?? true) ? 'selected' : '' }}>Đang bán</option>
                        <option value="0" {{ old('is_active', $product->is_active ?? true) ? '' : 'selected' }}>Tạm ẩn</option>
                    </select>
                </div>
                <div>
                    <label>Sản phẩm nổi bật</label>
                    <div class="d-flex-center gap-2 mt-2">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}>
                        <span>Hiển thị tại mục "Sản phẩm nổi bật"</span>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <label>Mô tả ngắn</label>
                <textarea
                    class="form-control tinymce-editor"
                    name="short_description"
                    rows="2"
                    data-media-folder="clothes"
                >{{ old('short_description', $product->short_description) }}</textarea>
            </div>
            <div class="card">
                <div class="repeater-header">
                    <h3>Mô tả chi tiết (JSON)</h3>
                    <button type="button" class="btn btn-secondary" id="add-description-section" data-template="#description-section-template">+ Thêm section</button>
                </div>
                
                <div class="repeater-list" id="description-sections-list">
                    @php
                        $description = old('description', $product->description);
                        $sections = $description['sections'] ?? [];
                    @endphp

                    @foreach($sections as $index => $section)
                        @include('admins.products.partials.description-section', ['index' => $index, 'section' => $section])
                    @endforeach
                </div>
                
                <template id="description-section-template">
                    @include('admins.products.partials.description-section', ['index' => '__INDEX__', 'section' => []])
                </template>
            </div>
        </div>

        <div class="card">
            <h3>Danh mục & Tags</h3>
            <div class="grid-3">
                <div>
                    <label>Danh mục chính</label>
                    <select class="form-control" id="primary-category" name="primary_category_id">
                        <option value="">-- Chọn danh mục --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('primary_category_id', $product->primary_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Danh mục sản phẩm đi kèm</label>
                    <select class="form-control" id="included-categories" name="category_included_ids[]" multiple>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ in_array($category->id, $includedCategoryIds, true) ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1">Chọn danh mục để hệ thống tự gợi ý tối đa 10 sản phẩm ngẫu nhiên thuộc danh mục đó (kể cả danh mục con/cháu).</small>
                </div>
                <div>
                    <label>Danh mục phụ</label>
                    <select class="form-control" id="extra-categories" name="category_ids[]" multiple>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ in_array($category->id, old('category_ids', $product->category_ids ?? [])) ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <label>Tags</label>
                <div class="mb-2">
                    <label class="small text-muted">Chọn từ danh sách có sẵn:</label>
                    <select name="tag_ids[]" id="tagSelect" class="form-select" multiple>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" @selected(in_array($tag->id, $selectedTagIds))>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="small text-muted">Hoặc thêm tags mới (phân cách bằng dấu phẩy):</label>
                    <input type="text" 
                           name="tag_names" 
                           id="tagNamesInput" 
                           class="form-control" 
                           placeholder="Ví dụ: Fashion, Style, Trend"
                           value="{{ $tagNamesInput }}">
                    <small class="text-muted">Nhập tên tags mới, phân cách bằng dấu phẩy. Tags mới sẽ được tạo tự động.</small>
                </div>
            </div>
        </div>

        @php
            $metaKeywordsValue = old('meta_keywords');
            if (is_null($metaKeywordsValue)) {
                $metaKeywordsValue = is_array($product->meta_keywords ?? null)
                    ? implode(', ', $product->meta_keywords)
                    : ($product->meta_keywords ?? '');
            }
        @endphp
        <div class="card">
            <h3>SEO Meta</h3>
            <div class="grid-3">
                <div>
                    <label>Meta Title</label>
                    <input type="text" class="form-control" name="meta_title"
                           value="{{ old('meta_title', $product->meta_title) }}"
                           placeholder="Tiêu đề hiển thị trên Google">
                </div>
                <div>
                    <label>Meta Canonical</label>
                    <input type="text" class="form-control" name="meta_canonical"
                           value="{{ old('meta_canonical', $product->meta_canonical) }}"
                           placeholder="https://example.com/san-pham/...">
                </div>
                <div>
                    <label>Meta Keywords</label>
                    <input type="text" class="form-control" name="meta_keywords"
                           value="{{ $metaKeywordsValue }}"
                           placeholder="từ khóa 1, từ khóa 2">
                </div>
            </div>
            <div class="mt-2">
                <label>Meta Description</label>
                <textarea class="form-control" rows="2" name="meta_description"
                          placeholder="Mô tả ngắn hiển thị trên Google">{{ old('meta_description', $product->meta_description) }}</textarea>
            </div>
        </div>

        <div class="card">
            <div class="repeater-header">
                <h3>Gallery</h3>
                <button type="button" class="btn btn-secondary" data-add="#image-list" data-template="#image-template">+ Thêm ảnh</button>
            </div>
            <div class="repeater-list" id="image-list">
                @foreach(old('images', $productImages) as $index => $image)
                    <div class="repeater-item">
                        <div class="repeater-header">
                            <strong>Ảnh #{{ $index + 1 }}</strong>
                            <button type="button" class="btn-link" data-remove data-item=".repeater-item">Xóa</button>
                        </div>
                        <input type="hidden" name="images[{{ $index }}][id]" value="{{ $image['id'] ?? null }}">
                        <div class="grid-2">
                            <div>
                                <label>Title</label>
                                <input type="text" class="form-control" name="images[{{ $index }}][title]" value="{{ $image['title'] ?? '' }}">
                            </div>
                            <div>
                                <label>Ghi chú</label>
                                <input type="text" class="form-control" name="images[{{ $index }}][notes]" value="{{ $image['notes'] ?? '' }}">
                            </div>
                            <div>
                                <label>Alt</label>
                                <input type="text" class="form-control" name="images[{{ $index }}][alt]" value="{{ $image['alt'] ?? '' }}">
                            </div>
                            <div>
                                <label>Thứ tự</label>
                                <input type="number" class="form-control" name="images[{{ $index }}][order]" value="{{ $image['order'] ?? $index }}">
                            </div>
                        </div>
                        <div class="mt-2">
                            <label>File ảnh</label>
                            <input type="file" name="images[{{ $index }}][file]" class="form-control image-file-input">
                            @php
                                // Trong DB chỉ lưu tên file (ví dụ: abc123.jpg)
                                $imageFileName = $image['url'] ?? null;
                                // Tạo URL đầy đủ để hiển thị
                                $imageFullUrl = $imageFileName ? asset('clients/assets/img/clothes/' . $imageFileName) : null;
                            @endphp
                            <input type="hidden" id="image-path-{{ $index }}" name="images[{{ $index }}][existing_path]" value="{{ $imageFileName }}">
                            <div class="image-preview" id="image-preview-{{ $index }}">
                                @if($imageFullUrl)
                                    <img src="{{ $imageFullUrl }}" alt="" style="max-width:100%;height:auto;">
                                @endif
                            </div>
                            <div class="d-flex-center gap-2 mt-2">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-media-picker
                                        data-mode="single"
                                        data-target="#image-path-{{ $index }}"
                                        data-preview="#image-preview-{{ $index }}">
                                    📚 Chọn từ thư viện (mới)
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label><input type="checkbox" name="images[{{ $index }}][is_primary]" value="1" {{ !empty($image['is_primary']) ? 'checked' : '' }}> Ảnh chính</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="repeater-header">
                <h3>FAQs</h3>
                <button type="button" class="btn btn-secondary" data-add="#faq-list" data-template="#faq-template">+ Thêm FAQ</button>
            </div>
            <div class="repeater-list" id="faq-list">
                @foreach(old('faqs', $product->faqs->toArray() ?? []) as $index => $faq)
                    <div class="repeater-item">
                        <div class="repeater-header">
                            <strong>FAQ #{{ $index + 1 }}</strong>
                            <button type="button" class="btn-link" data-remove data-item=".repeater-item">Xóa</button>
                        </div>
                        <input type="hidden" name="faqs[{{ $index }}][id]" value="{{ $faq['id'] ?? null }}">
                        <div>
                            <label>Câu hỏi</label>
                            <input type="text" class="form-control" name="faqs[{{ $index }}][question]" value="{{ $faq['question'] ?? '' }}" required>
                        </div>
                        <div class="mt-2">
                            <label>Trả lời</label>
                            <textarea class="form-control" name="faqs[{{ $index }}][answer]" rows="2">{{ $faq['answer'] ?? '' }}</textarea>
                        </div>
                        <input type="hidden" name="faqs[{{ $index }}][order]" value="{{ $faq['order'] ?? $index }}">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="repeater-header">
                <h3>How-To</h3>
                <button type="button" class="btn btn-secondary" data-add="#howto-list" data-template="#howto-template">+ Thêm hướng dẫn</button>
            </div>
            <div class="repeater-list" id="howto-list">
                @foreach(old('how_tos', $product->howTos->toArray() ?? []) as $index => $howTo)
                    @php
                        $steps = $howTo['steps'] ?? [];
                        if (is_string($steps)) {
                            $steps = json_decode($steps, true) ?? [];
                        }
                        if (!is_array($steps)) {
                            $steps = [];
                        }
                        $supplies = $howTo['supplies'] ?? [];
                        if (is_string($supplies)) {
                            $supplies = json_decode($supplies, true) ?? [];
                        }
                        if (!is_array($supplies)) {
                            $supplies = [];
                        }
                    @endphp
                    <div class="repeater-item">
                        <div class="repeater-header">
                            <strong>How-To #{{ $index + 1 }}</strong>
                            <button type="button" class="btn-link" data-remove data-item=".repeater-item">Xóa</button>
                        </div>
                        <input type="hidden" name="how_tos[{{ $index }}][id]" value="{{ $howTo['id'] ?? null }}">
                        <div class="grid-2">
                            <div>
                                <label>Tiêu đề</label>
                                <input type="text" class="form-control" name="how_tos[{{ $index }}][title]" value="{{ $howTo['title'] ?? '' }}" required>
                            </div>
                            <div>
                                <label>Hoạt động</label>
                                <select class="form-control" name="how_tos[{{ $index }}][is_active]">
                                    <option value="1" {{ !empty($howTo['is_active']) ? 'selected' : '' }}>Hiển thị</option>
                                    <option value="0" {{ empty($howTo['is_active']) ? 'selected' : '' }}>Ẩn</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label>Mô tả</label>
                            <textarea class="form-control" name="how_tos[{{ $index }}][description]" rows="2">{{ $howTo['description'] ?? '' }}</textarea>
                        </div>
                        <div class="mt-2">
                            <div class="d-flex-center justify-content-between">
                                <label>Danh sách bước</label>
                                <button type="button" class="btn btn-secondary btn-sm" data-add-step>+ Thêm bước</button>
                            </div>
                            <div class="steps-list" data-index="{{ $index }}">
                                @foreach($steps as $step)
                                    <div class="step-item">
                                        <input type="text" class="form-control" name="how_tos[{{ $index }}][steps][]" value="{{ $step }}" placeholder="Bước">
                                        <button type="button" data-remove-step>&times;</button>
                                    </div>
                                @endforeach
                                @if(empty($steps))
                                    <div class="step-item">
                                        <input type="text" class="form-control" name="how_tos[{{ $index }}][steps][]" placeholder="Bước 1">
                                        <button type="button" data-remove-step>&times;</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="d-flex-center justify-content-between">
                                <label>Dụng cụ cần thiết</label>
                                <button type="button" class="btn btn-secondary btn-sm" data-add-supply>+ Thêm dụng cụ</button>
                            </div>
                            <div class="supplies-list" data-index="{{ $index }}">
                                @foreach($supplies as $supply)
                                    <div class="supply-item">
                                        <input type="text" class="form-control" name="how_tos[{{ $index }}][supplies][]" value="{{ $supply }}" placeholder="Dụng cụ">
                                        <button type="button" data-remove-supply>&times;</button>
                                    </div>
                                @endforeach
                                @if(empty($supplies))
                                    <div class="supply-item">
                                        <input type="text" class="form-control" name="how_tos[{{ $index }}][supplies][]" placeholder="Dụng cụ 1">
                                        <button type="button" data-remove-supply>&times;</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="repeater-header">
                <h3>Biến thể sản phẩm (Variants)</h3>
                <button type="button" class="btn btn-secondary" data-add="#variant-list" data-template="#variant-template">+ Thêm biến thể</button>
            </div>
            <div class="repeater-list" id="variant-list">
                @foreach($productVariants as $index => $variant)
                    <div class="repeater-item">
                        <div class="repeater-header">
                            <strong>Biến thể #{{ $index + 1 }}</strong>
                            <button type="button" class="btn-link" data-remove data-item=".repeater-item">Xóa</button>
                        </div>
                        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant['id'] ?? null }}">
                        <div class="grid-2">
                            <div>
                                <label>Tên biến thể <span class="text-danger">*</span> (VD: 1m, 2m có chậu, Combo 3 cây)</label>
                                <input type="text" class="form-control" name="variants[{{ $index }}][name]" value="{{ $variant['name'] ?? '' }}" required placeholder="1m có chậu">
                            </div>
                            <div>
                                <label>SKU</label>
                                <input type="text" class="form-control" name="variants[{{ $index }}][sku]" value="{{ $variant['sku'] ?? '' }}" placeholder="PROD-1M-CHAU">
                            </div>
                            <div>
                                <label>Kích thước (VD: 1m, 2m, 5m)</label>
                                <input type="text" class="form-control" name="variants[{{ $index }}][size]" value="{{ old("variants.{$index}.size", $variant['attributes']['size'] ?? '') }}" placeholder="1m">
                            </div>
                            <div>
                                <label>Có chậu</label>
                                <select class="form-control" name="variants[{{ $index }}][has_pot]">
                                    <option value="">-- Chọn --</option>
                                    @php
                                        $hasPotValue = old("variants.{$index}.has_pot", $variant['attributes']['has_pot'] ?? '');
                                        $hasPotBool = $hasPotValue === '1' || $hasPotValue === 1 || $hasPotValue === true;
                                    @endphp
                                    <option value="1" {{ $hasPotBool ? 'selected' : '' }}>Có chậu</option>
                                    <option value="0" {{ ($hasPotValue !== '' && !$hasPotBool) ? 'selected' : '' }}>Không chậu</option>
                                </select>
                            </div>
                            <div>
                                <label>Loại combo (VD: Combo 3 cây, Combo 5 cây)</label>
                                <input type="text" class="form-control" name="variants[{{ $index }}][combo_type]" value="{{ old("variants.{$index}.combo_type", $variant['attributes']['combo_type'] ?? '') }}" placeholder="Combo 3 cây">
                            </div>
                            <div>
                                <label>Ghi chú thêm</label>
                                <input type="text" class="form-control" name="variants[{{ $index }}][notes]" value="{{ old("variants.{$index}.notes", $variant['attributes']['notes'] ?? '') }}" placeholder="Thông tin bổ sung">
                            </div>
                            <div>
                                <label>Giá gốc <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="variants[{{ $index }}][price]" value="{{ $variant['price'] ?? 0 }}" min="0" step="1000" required>
                            </div>
                            <div>
                                <label>Giá khuyến mãi</label>
                                <input type="number" class="form-control" name="variants[{{ $index }}][sale_price]" value="{{ $variant['sale_price'] ?? '' }}" min="0" step="1000" placeholder="Để trống nếu không có">
                            </div>
                            <div>
                                <label>Giá vốn</label>
                                <input type="number" class="form-control" name="variants[{{ $index }}][cost_price]" value="{{ $variant['cost_price'] ?? '' }}" min="0" step="1000" placeholder="Để trống nếu không có">
                            </div>
                            <div>
                                <label>Số lượng tồn kho</label>
                                <input type="number" class="form-control" name="variants[{{ $index }}][stock_quantity]" value="{{ $variant['stock_quantity'] ?? '' }}" min="0" placeholder="Để trống = không giới hạn">
                            </div>
                            <div>
                                <label>Thứ tự sắp xếp</label>
                                <input type="number" class="form-control" name="variants[{{ $index }}][sort_order]" value="{{ $variant['sort_order'] ?? $index }}" min="0">
                            </div>
                            <div>
                                <label>Trạng thái</label>
                                <select class="form-control" name="variants[{{ $index }}][is_active]">
                                    <option value="1" {{ !empty($variant['is_active']) ? 'selected' : '' }}>Kích hoạt</option>
                                    <option value="0" {{ empty($variant['is_active']) ? 'selected' : '' }}>Tắt</option>
                                </select>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

            </form>
        </div>

        <!-- Sidebar -->
        <div class="product-form-sidebar">
            <!-- Actions Card -->
            <div class="sidebar-card">
                <h4>Thao tác</h4>
                <div class="sidebar-actions">
                    <button type="submit" form="product-form" class="btn btn-primary">💾 Lưu sản phẩm</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">↩️ Quay lại danh sách</a>
                    @if($isEdit)
                        <a href="{{ route('client.product.detail', $product) }}" class="btn btn-outline-secondary" target="_blank">👁️ Xem chi tiết</a>
                        <a href="{{ route('admin.products.inventory', $product) }}" class="btn btn-outline-secondary">📦 Quản lý kho</a>
                    @endif
                </div>
            </div>

            <!-- Quick Info Card -->
            @if($isEdit)
            <div class="sidebar-card">
                <h4>Thông tin nhanh</h4>
                <div class="sidebar-info-item">
                    <span class="sidebar-info-label">SKU:</span>
                    <span class="sidebar-info-value">{{ $product->sku ?? '-' }}</span>
                </div>
                <div class="sidebar-info-item">
                    <span class="sidebar-info-label">Trạng thái:</span>
                    <span class="sidebar-info-value">
                        <span class="sidebar-status-badge {{ $product->is_active ? 'active' : 'inactive' }}">
                            {{ $product->is_active ? 'Đang bán' : 'Tạm ẩn' }}
                        </span>
                    </span>
                </div>
                <div class="sidebar-info-item">
                    <span class="sidebar-info-label">Giá bán:</span>
                    <span class="sidebar-info-value">{{ number_format($product->price ?? 0) }}₫</span>
                </div>
                @if($product->sale_price)
                <div class="sidebar-info-item">
                    <span class="sidebar-info-label">Giá KM:</span>
                    <span class="sidebar-info-value">{{ number_format($product->sale_price) }}₫</span>
                </div>
                @endif
                <div class="sidebar-info-item">
                    <span class="sidebar-info-label">Tồn kho:</span>
                    <span class="sidebar-info-value">{{ $product->stock_quantity ?? 0 }}</span>
                </div>
                <div class="sidebar-info-item">
                    <span class="sidebar-info-label">Danh mục:</span>
                    <span class="sidebar-info-value">{{ $product->primaryCategory->name ?? '-' }}</span>
                </div>
                <div class="sidebar-info-item">
                    <span class="sidebar-info-label">Ngày tạo:</span>
                    <span class="sidebar-info-value">{{ $product->created_at ? $product->created_at->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="sidebar-info-item">
                    <span class="sidebar-info-label">Cập nhật:</span>
                    <span class="sidebar-info-value">{{ $product->updated_at ? $product->updated_at->format('d/m/Y') : '-' }}</span>
                </div>
            </div>
            @endif

            <!-- Quick Links Card -->
            @if($isEdit)
            <div class="sidebar-card">
                <h4>Liên kết nhanh</h4>
                <div class="sidebar-actions">
                    @php
                        $frontendUrl = route('client.product.detail', $product->slug);
                    @endphp
                    <a href="{{ $frontendUrl }}" class="btn btn-outline-primary" target="_blank">🔗 Xem trang sản phẩm</a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <template id="image-template">
        <div class="repeater-item">
            <div class="repeater-header">
                <strong>Ảnh mới</strong>
                <button type="button" class="btn-link" data-remove data-item=".repeater-item">Xóa</button>
            </div>
            <input type="hidden" name="images[__INDEX__][id]">
            <div class="grid-2">
                <div>
                    <label>Title</label>
                    <input type="text" class="form-control" name="images[__INDEX__][title]">
                </div>
                <div>
                    <label>Ghi chú</label>
                    <input type="text" class="form-control" name="images[__INDEX__][notes]">
                </div>
                <div>
                    <label>Alt</label>
                    <input type="text" class="form-control" name="images[__INDEX__][alt]">
                </div>
                <div>
                    <label>Thứ tự</label>
                    <input type="number" class="form-control" name="images[__INDEX__][order]" value="__INDEX__">
                </div>
            </div>
            <div class="mt-2">
                <label>File ảnh</label>
                <input type="file" name="images[__INDEX__][file]" class="form-control image-file-input">
                <input type="hidden" id="image-path-__INDEX__" name="images[__INDEX__][existing_path]">
                <div class="image-preview" id="image-preview-__INDEX__"></div>
                <button type="button" 
                        class="btn btn-primary btn-sm mt-2" 
                        data-open-gallery-picker
                        data-target="#image-path-__INDEX__"
                        data-preview="#image-preview-__INDEX__"
                        data-alt-input="input[name='images[__INDEX__][alt]']"
                        class="mt-2">
                    📷 Chọn ảnh từ thư viện
                </button>
            </div>
            <div class="mt-2">
                <label><input type="checkbox" name="images[__INDEX__][is_primary]" value="1"> Ảnh chính</label>
            </div>
        </div>
    </template>

    <template id="faq-template">
        <div class="repeater-item">
            <div class="repeater-header">
                <strong>FAQ mới</strong>
                <button type="button" class="btn-link" data-remove data-item=".repeater-item">Xóa</button>
            </div>
            <input type="hidden" name="faqs[__INDEX__][id]">
            <div>
                <label>Câu hỏi</label>
                <input type="text" class="form-control" name="faqs[__INDEX__][question]" required>
            </div>
            <div class="mt-2">
                <label>Trả lời</label>
                <textarea class="form-control" name="faqs[__INDEX__][answer]" rows="2"></textarea>
            </div>
            <input type="hidden" name="faqs[__INDEX__][order]" value="__INDEX__">
        </div>
    </template>

    <template id="variant-template">
        <div class="repeater-item">
            <div class="repeater-header">
                <strong>Biến thể mới</strong>
                <button type="button" class="btn-link" data-remove data-item=".repeater-item">Xóa</button>
            </div>
            <input type="hidden" name="variants[__INDEX__][id]">
            <div class="grid-2">
                <div>
                    <label>Tên biến thể <span class="text-danger">*</span> (VD: 1m, 2m có chậu, Combo 3 cây)</label>
                    <input type="text" class="form-control" name="variants[__INDEX__][name]" required placeholder="1m có chậu">
                </div>
                <div>
                    <label>SKU</label>
                    <input type="text" class="form-control" name="variants[__INDEX__][sku]" placeholder="PROD-1M-CHAU">
                </div>
                <div>
                    <label>Kích thước (VD: 1m, 2m, 5m)</label>
                    <input type="text" class="form-control" name="variants[__INDEX__][size]" placeholder="1m">
                </div>
                <div>
                    <label>Có chậu</label>
                    <select class="form-control" name="variants[__INDEX__][has_pot]">
                        <option value="">-- Chọn --</option>
                        <option value="1">Có chậu</option>
                        <option value="0">Không chậu</option>
                    </select>
                </div>
                <div>
                    <label>Loại combo (VD: Combo 3 cây, Combo 5 cây)</label>
                    <input type="text" class="form-control" name="variants[__INDEX__][combo_type]" placeholder="Combo 3 cây">
                </div>
                <div>
                    <label>Ghi chú thêm</label>
                    <input type="text" class="form-control" name="variants[__INDEX__][notes]" placeholder="Thông tin bổ sung">
                </div>
                <div>
                    <label>Giá gốc <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="variants[__INDEX__][price]" value="0" min="0" step="1000" required>
                </div>
                <div>
                    <label>Giá khuyến mãi</label>
                    <input type="number" class="form-control" name="variants[__INDEX__][sale_price]" min="0" step="1000" placeholder="Để trống nếu không có">
                </div>
                <div>
                    <label>Giá vốn</label>
                    <input type="number" class="form-control" name="variants[__INDEX__][cost_price]" min="0" step="1000" placeholder="Để trống nếu không có">
                </div>
                <div>
                    <label>Số lượng tồn kho</label>
                    <input type="number" class="form-control" name="variants[__INDEX__][stock_quantity]" min="0" placeholder="Để trống = không giới hạn">
                </div>
                <div>
                    <label>Thứ tự sắp xếp</label>
                    <input type="number" class="form-control" name="variants[__INDEX__][sort_order]" value="__INDEX__" min="0">
                </div>
                <div>
                    <label>Trạng thái</label>
                    <select class="form-control" name="variants[__INDEX__][is_active]">
                        <option value="1" selected>Kích hoạt</option>
                        <option value="0">Tắt</option>
                    </select>
                </div>
            </div>
        </div>
    </template>

    <template id="howto-template">
        <div class="repeater-item">
            <div class="repeater-header">
                <strong>How-To mới</strong>
                <button type="button" class="btn-link" data-remove data-item=".repeater-item">Xóa</button>
            </div>
            <input type="hidden" name="how_tos[__INDEX__][id]">
            <div class="grid-2">
                <div>
                    <label>Tiêu đề</label>
                    <input type="text" class="form-control" name="how_tos[__INDEX__][title]" required>
                </div>
                <div>
                    <label>Hoạt động</label>
                    <select class="form-control" name="how_tos[__INDEX__][is_active]">
                        <option value="1" selected>Hiển thị</option>
                        <option value="0">Ẩn</option>
                    </select>
                </div>
            </div>
            <div class="mt-2">
                <label>Mô tả</label>
                <textarea class="form-control" name="how_tos[__INDEX__][description]" rows="2"></textarea>
            </div>
            <div class="mt-2">
                <div class="d-flex-center justify-content-between">
                    <label>Danh sách bước</label>
                    <button type="button" class="btn btn-secondary btn-sm" data-add-step>+ Thêm bước</button>
                </div>
                <div class="steps-list" data-index="__INDEX__">
                    <div class="step-item">
                        <input type="text" class="form-control" name="how_tos[__INDEX__][steps][]" placeholder="Bước 1">
                        <button type="button" class="btn-link" data-remove-step>&times;</button>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <div class="d-flex-center justify-content-between">
                    <label>Dụng cụ cần thiết</label>
                    <button type="button" class="btn btn-secondary btn-sm" data-add-supply>+ Thêm dụng cụ</button>
                </div>
                <div class="supplies-list" data-index="__INDEX__">
                    <div class="supply-item">
                        <input type="text" class="form-control" name="how_tos[__INDEX__][supplies][]" placeholder="Dụng cụ 1">
                        <button type="button" class="btn-link" data-remove-supply>&times;</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection
