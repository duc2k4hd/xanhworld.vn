@extends('admins.layouts.master')

@php
    $isEdit = $product->exists;
    $pageTitle = $isEdit ? 'Chỉnh sửa sản phẩm' : 'Tạo sản phẩm mới';
    
    // Lấy selected tag IDs từ relationship hoặc tag_ids JSON
    $selectedTagIds = old('tag_ids', []);
    if (empty($selectedTagIds) && $product->exists) {
        $selectedTagIds = $product->tags()->pluck('id')->toArray();
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
@endphp

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@push('head')
    @if($isEdit)
        <link rel="shortcut icon" href="{{ asset('admins/img/icons/edit-product-icon.png') }}" type="image/x-icon">
    @else
        <link rel="shortcut icon" href="{{ asset('admins/img/icons/create-product-icon.png') }}" type="image/x-icon">
    @endif

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
@endpush

@push('styles')
    <style>
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 14px 16px;
            box-shadow: 0 1px 6px rgba(15,23,42,0.06);
            margin-bottom: 16px;
        }
        .card > h3 {
            margin: 0 0 8px;
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 12px 16px;
        }
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 10px 14px;
        }
        .form-control,
        textarea,
        select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #cbd5f5;
            border-radius: 6px;
            font-size: 13px;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 4px;
            color: #111827;
        }
        .repeater-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .repeater-item {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            background: #f9fafb;
        }
        .repeater-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .btn-link {
            background: none;
            border: none;
            color: #2563eb;
            cursor: pointer;
        }
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        .image-library img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            display: block;
        }
        .image-library button[data-hidden="true"] {
            display: none;
        }
        .image-preview {
            margin-top: 10px;
        }
        .image-preview img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .tox-tinymce {
            min-height: 500px;
        }
        .steps-list, .supplies-list {
            margin-top: 10px;
        }
        .step-item, .supply-item {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
            align-items: center;
        }
        .step-item input, .supply-item input {
            flex: 1;
        }
        .step-item button, .supply-item button {
            border: none;
            background: none;
            color: #ef4444;
            font-size: 18px;
            cursor: pointer;
            padding: 0 8px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const counters = {};
            let isDirty = false;
            const markDirty = () => { isDirty = true; };
            
            const initTinyMCE = () => {
                if (typeof tinymce === 'undefined') {
                    return;
                }
                tinymce.remove('.tinymce-editor');
                tinymce.init({
                    selector: '.tinymce-editor',
                    menubar: false,
                    height: 500,
                    language: 'vi',
                    branding: false,
                    plugins: 'link lists image table code autoresize',
                    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table nobi_gallery | code',
                    relative_urls: false,
                    remove_script_host: false,
                    convert_urls: true,
                    automatic_uploads: false,
                    file_picker_types: 'image',
                    file_picker_callback: (callback, value, meta) => {
                        if (meta.filetype === 'image') {
                            if (typeof openMediaPicker === 'function') {
                                openMediaPicker({
                                    mode: 'single',
                                    scope: 'client',
                                    onSelect: (file) => {
                                        if (file && file.url) {
                                            callback(file.url, {
                                                alt: file.alt || file.title || file.filename || file.name || '',
                                                title: file.title || file.filename || file.name || ''
                                            });
                                        }
                                    }
                                });
                            } else {
                                alert('Popup thư viện ảnh chưa được tải. Vui lòng F5 lại trang.');
                            }
                        }
                    },
                    setup: (editor) => {
                        editor.ui.registry.addButton('nobi_gallery', {
                            text: '🖼 Chèn ảnh @img',
                            tooltip: 'Chọn ảnh từ thư viện @img',
                            onAction: () => openImagePicker(editor),
                        });

                        // Convert relative URLs to absolute URLs when loading content
                        editor.on('GetContent', (e) => {
                            if (e.format === 'html' && e.content) {
                                // Convert relative image URLs to absolute URLs
                                e.content = e.content.replace(
                                    /<img([^>]*?)src=["']([^"']+)["']/gi,
                                    (match, attrs, imageUrl) => {
                                        // If already absolute, keep it
                                        if (imageUrl.startsWith('http://') || imageUrl.startsWith('https://') || imageUrl.startsWith('//')) {
                                            return match;
                                        }
                                        // Convert relative to absolute
                                        const baseUrl = window.location.origin;
                                        let absoluteUrl = imageUrl;
                                        
                                        // Remove relative path prefixes
                                        absoluteUrl = absoluteUrl.replace(/^\.\.\/\.\.\/\.\.\//, '').replace(/^\.\.\/\.\.\//, '').replace(/^\.\.\//, '');
                                        
                                        // Ensure it starts with /
                                        if (!absoluteUrl.startsWith('/')) {
                                            absoluteUrl = '/' + absoluteUrl;
                                        }
                                        
                                        absoluteUrl = baseUrl + absoluteUrl;
                                        return `<img${attrs}src="${absoluteUrl}"`;
                                    }
                                );
                            }
                        });

                        // Ensure image URLs are absolute when setting content
                        editor.on('SetContent', (e) => {
                            if (e.load && e.content) {
                                // Convert relative URLs to absolute when loading existing content
                                e.content = e.content.replace(
                                    /<img([^>]*?)src=["']([^"']+)["']/gi,
                                    (match, attrs, imageUrl) => {
                                        // If already absolute, keep it
                                        if (imageUrl.startsWith('http://') || imageUrl.startsWith('https://') || imageUrl.startsWith('//')) {
                                            return match;
                                        }
                                        // Convert relative to absolute
                                        const baseUrl = window.location.origin;
                                        let absoluteUrl = imageUrl;
                                        
                                        // Remove relative path prefixes
                                        absoluteUrl = absoluteUrl.replace(/^\.\.\/\.\.\/\.\.\//, '').replace(/^\.\.\/\.\.\//, '').replace(/^\.\.\//, '');
                                        
                                        // Ensure it starts with /
                                        if (!absoluteUrl.startsWith('/')) {
                                            absoluteUrl = '/' + absoluteUrl;
                                        }
                                        
                                        absoluteUrl = baseUrl + absoluteUrl;
                                        return `<img${attrs}src="${absoluteUrl}"`;
                                    }
                                );
                            }
                            if (!e.load) {
                                markDirty();
                            }
                        });

                        // Handle image double-click to open crop editor
                        editor.on('dblclick', (e) => {
                            const node = editor.selection.getNode();
                            if (node && node.tagName === 'IMG') {
                                e.preventDefault();
                                // Select the image node to ensure it's the active selection
                                editor.selection.select(node);
                                openImageCropper(editor, node);
                            }
                        });

                        // Also handle context menu (right-click) on images
                        editor.on('contextmenu', (e) => {
                            const node = editor.selection.getNode();
                            if (node && node.tagName === 'IMG') {
                                e.preventDefault();
                                // Select the image node to ensure it's the active selection
                                editor.selection.select(node);
                                openImageCropper(editor, node);
                            }
                        });

                        ['change', 'input', 'keyup', 'undo', 'redo'].forEach(evt => {
                            editor.on(evt, () => markDirty());
                        });
                    }
                });
            };

            // Function to open image cropper
            const openImageCropper = (editorInstance, imgElement) => {
                const originalSrc = imgElement.src || imgElement.getAttribute('src');
                const originalAlt = imgElement.alt || imgElement.getAttribute('alt') || '';

                // Store editor instance and original image data for later use
                const editor = editorInstance;
                
                // Extract filename from URL
                const urlParts = originalSrc.split('/');
                const filenameWithExt = urlParts[urlParts.length - 1];
                const filenameMatch = filenameWithExt.match(/^(.+?)(\.(webp|jpg|jpeg|png|gif|svg))$/i);
                const baseFilename = filenameMatch ? filenameMatch[1] : filenameWithExt.replace(/\.[^.]+$/, '');
                const extension = filenameMatch ? filenameMatch[3] : 'webp';

                // Remove existing -size-w-h pattern if exists
                const cleanBaseFilename = baseFilename.replace(/-size-\d+-\d+$/, '');
                
                const originalImageData = {
                    src: originalSrc,
                    alt: originalAlt,
                    element: imgElement,
                    filenameWithExt: filenameWithExt,
                };

                const modal = document.createElement('div');
                modal.style.position = 'fixed';
                modal.style.top = '0';
                modal.style.left = '0';
                modal.style.right = '0';
                modal.style.bottom = '0';
                modal.style.background = 'rgba(0,0,0,0.8)';
                modal.style.display = 'flex';
                modal.style.alignItems = 'center';
                modal.style.justifyContent = 'center';
                modal.style.zIndex = '10000';
                modal.innerHTML = `
                    <div style="background:#fff;padding:20px;border-radius:12px;max-width:90vw;max-height:90vh;overflow:auto;width:800px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                            <h4 style="margin:0;">Cắt ảnh</h4>
                            <button type="button" data-close-crop style="border:none;background:none;font-size:24px;cursor:pointer;color:#666;">&times;</button>
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block;margin-bottom:8px;font-weight:500;">Chọn tỷ lệ:</label>
                            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                <button type="button" data-aspect-ratio="1" class="btn btn-sm btn-outline-primary">1:1 (Vuông)</button>
                                <button type="button" data-aspect-ratio="4/3" class="btn btn-sm btn-outline-primary">4:3</button>
                                <button type="button" data-aspect-ratio="16/9" class="btn btn-sm btn-outline-primary">16:9</button>
                                <button type="button" data-aspect-ratio="3/4" class="btn btn-sm btn-outline-primary">3:4 (Dọc)</button>
                                <button type="button" data-aspect-ratio="NaN" class="btn btn-sm btn-outline-primary">Tự do</button>
                            </div>
                        </div>
                        <div style="margin-bottom:15px;">
                            <img id="crop-image" src="${originalSrc}" style="max-width:100%;max-height:400px;display:block;">
                        </div>
                        <div style="display:flex;gap:10px;justify-content:flex-end;">
                            <button type="button" class="btn btn-secondary" data-close-crop>Hủy</button>
                            <button type="button" class="btn btn-primary" data-crop-apply>Cắt và Lưu</button>
                        </div>
                    </div>
                `;

                let cropper = null;
                let currentAspectRatio = NaN;

                const closeModal = () => {
                    if (cropper) {
                        cropper.destroy();
                    }
                    document.body.removeChild(modal);
                };

                const applyCrop = async () => {
                    if (!cropper) {
                        alert('Vui lòng chọn tỷ lệ cắt ảnh');
                        return;
                    }

                    const canvas = cropper.getCroppedCanvas({
                        width: cropper.getData().width,
                        height: cropper.getData().height,
                    });

                    if (!canvas) {
                        alert('Không thể cắt ảnh. Vui lòng thử lại.');
                        return;
                    }

                    // Get crop dimensions
                    const cropData = cropper.getData();
                    const width = Math.round(cropData.width);
                    const height = Math.round(cropData.height);

                    // Convert canvas to blob
                    canvas.toBlob(async (blob) => {
                        if (!blob) {
                            alert('Không thể tạo file ảnh. Vui lòng thử lại.');
                            return;
                        }

                        // Create new filename: cleanBaseFilename-size-w-h.extension
                        const newFilename = `${cleanBaseFilename}-size-${width}-${height}.${extension}`;

                        // Create FormData
                        const formData = new FormData();
                        formData.append('image', blob, newFilename);
                        formData.append('original_filename', cleanBaseFilename + '.' + extension);

                        try {
                            // Show loading
                            const applyBtn = modal.querySelector('[data-crop-apply]');
                            const originalText = applyBtn.textContent;
                            applyBtn.disabled = true;
                            applyBtn.textContent = 'Đang upload...';

                            // Upload to server
                            const response = await fetch('{{ route("admin.products.upload-cropped-image") }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                },
                                body: formData,
                            });

                            if (!response.ok) {
                                throw new Error('Upload failed');
                            }

                            const data = await response.json();

                            if (data.success && data.url) {
                                // Replace image in editor using TinyMCE API
                                if (editor && editor.dom) {
                                    // Method 1: Try to use the stored element directly (most reliable)
                                    let targetImg = originalImageData.element;
                                    
                                    // Method 2: If element is not in editor body, try to find it by src
                                    if (!targetImg || !editor.getBody().contains(targetImg)) {
                                        const editorBody = editor.getBody();
                                        const images = editorBody.querySelectorAll('img');
                                        
                                        // Find the image by comparing src
                                        const originalSrcNormalized = originalImageData.src.replace(/^https?:\/\/[^\/]+/, '').replace(/^\/+/, '');
                                        
                                        for (let i = 0; i < images.length; i++) {
                                            const img = images[i];
                                            const imgSrc = img.getAttribute('src') || '';
                                            const imgSrcNormalized = imgSrc.replace(/^https?:\/\/[^\/]+/, '').replace(/^\/+/, '');
                                            
                                            // Try multiple matching strategies
                                            if (
                                                imgSrcNormalized === originalSrcNormalized ||
                                                imgSrc === originalImageData.src ||
                                                imgSrc.includes(originalImageData.filenameWithExt) ||
                                                imgSrcNormalized.includes(originalImageData.filenameWithExt) ||
                                                originalSrcNormalized.includes(originalImageData.filenameWithExt)
                                            ) {
                                                targetImg = img;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // Method 3: Use current selection if available
                                    if (!targetImg) {
                                        const selectedNode = editor.selection.getNode();
                                        if (selectedNode && selectedNode.tagName === 'IMG') {
                                            targetImg = selectedNode;
                                        }
                                    }
                                    
                                    if (targetImg && editor.getBody().contains(targetImg)) {
                                        // Update image src using TinyMCE DOM API
                                        editor.dom.setAttrib(targetImg, 'src', data.url);
                                        if (originalImageData.alt) {
                                            editor.dom.setAttrib(targetImg, 'alt', originalImageData.alt);
                                        }
                                        
                                        // Select the updated image to ensure it's visible
                                        editor.selection.select(targetImg);
                                        
                                        // Trigger change event to mark editor as dirty
                                        editor.dispatch('change');
                                        editor.nodeChanged();
                                    } else {
                                        // Fallback: Update all images with matching src
                                        const editorBody = editor.getBody();
                                        const images = editorBody.querySelectorAll('img');
                                        const originalSrcNormalized = originalImageData.src.replace(/^https?:\/\/[^\/]+/, '').replace(/^\/+/, '');
                                        
                                        images.forEach(img => {
                                            const imgSrc = img.getAttribute('src') || '';
                                            const imgSrcNormalized = imgSrc.replace(/^https?:\/\/[^\/]+/, '').replace(/^\/+/, '');
                                            
                                            if (
                                                imgSrcNormalized === originalSrcNormalized ||
                                                imgSrc === originalImageData.src ||
                                                imgSrc.includes(originalImageData.filenameWithExt)
                                            ) {
                                                editor.dom.setAttrib(img, 'src', data.url);
                                                if (originalImageData.alt) {
                                                    editor.dom.setAttrib(img, 'alt', originalImageData.alt);
                                                }
                                            }
                                        });
                                        
                                        editor.dispatch('change');
                                        editor.nodeChanged();
                                    }
                                } else {
                                    // Fallback: update imgElement directly
                                    if (originalImageData.element) {
                                        originalImageData.element.setAttribute('src', data.url);
                                        if (originalImageData.alt) {
                                            originalImageData.element.setAttribute('alt', originalImageData.alt);
                                        }
                                    }
                                }
                                markDirty();
                                closeModal();
                            } else {
                                throw new Error(data.message || 'Upload failed');
                            }
                        } catch (error) {
                            console.error('Error uploading cropped image:', error);
                            alert('Không thể upload ảnh đã cắt: ' + error.message);
                            const applyBtn = modal.querySelector('[data-crop-apply]');
                            applyBtn.disabled = false;
                            applyBtn.textContent = 'Cắt và Lưu';
                        }
                    }, 'image/webp', 0.9);
                };

                // Event handlers
                modal.addEventListener('click', (e) => {
                    if (e.target.matches('[data-close-crop]') || e.target === modal) {
                        closeModal();
                    } else if (e.target.matches('[data-aspect-ratio]')) {
                        const aspectRatio = e.target.dataset.aspectRatio;
                        if (aspectRatio === 'NaN') {
                            currentAspectRatio = NaN;
                        } else {
                            currentAspectRatio = eval(aspectRatio); // 4/3, 16/9, etc.
                        }
                        if (cropper) {
                            cropper.setAspectRatio(currentAspectRatio);
                        }
                        // Update button states
                        modal.querySelectorAll('[data-aspect-ratio]').forEach(btn => {
                            btn.classList.remove('btn-primary');
                            btn.classList.add('btn-outline-primary');
                        });
                        e.target.classList.remove('btn-outline-primary');
                        e.target.classList.add('btn-primary');
                    } else if (e.target.matches('[data-crop-apply]')) {
                        applyCrop();
                    }
                });

                document.body.appendChild(modal);

                // Initialize Cropper.js after image loads
                const cropImage = modal.querySelector('#crop-image');
                cropImage.onload = () => {
                    cropper = new Cropper(cropImage, {
                        aspectRatio: NaN, // Free crop by default
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                    });
                };
            };

            const openImagePicker = (editor) => {
                if (!editor || !editor.hasFocus()) {
                    alert('Hãy click vào trình soạn thảo trước khi chèn ảnh.');
                    return;
                }

                const bookmark = editor.selection.getBookmark(2, true);

                // Sử dụng popup media mới
                if (typeof openMediaPicker === 'function') {
                    openMediaPicker({
                        mode: 'single',
                        scope: 'client',
                        onSelect: (file) => {
                            if (file && file.url) {
                                editor.selection.moveToBookmark(bookmark);
                                const alt = file.alt || file.title || file.filename || file.name || '';
                                editor.insertContent(`<img src="${file.url}" alt="${alt}">`);
                                markDirty();
                            }
                        }
                    });
                } else {
                    alert('Popup thư viện ảnh chưa được tải. Vui lòng F5 lại trang.');
                }
            };

            // Repeater handlers
            document.querySelectorAll('[data-add]').forEach(btn => {
                const targetSelector = btn.dataset.add;
                const templateSelector = btn.dataset.template;
                counters[targetSelector] = document.querySelectorAll(`${targetSelector} .repeater-item`).length;

                btn.addEventListener('click', () => {
                    const target = document.querySelector(targetSelector);
                    const template = document.querySelector(templateSelector);
                    if (!target || !template) return;

                    let html = template.innerHTML.replace(/__INDEX__/g, counters[targetSelector]++);
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = html.trim();
                    const newBlock = wrapper.firstElementChild;
                    target.appendChild(newBlock);
                    initTinyMCE();
                    markDirty();
                });
            });

            // Remove handlers
            document.addEventListener('click', (e) => {
                if (e.target.matches('[data-remove]')) {
                    e.target.closest('.repeater-item')?.remove();
                    markDirty();
                }

                // Open gallery picker for image selection
                if (e.target.matches('[data-open-gallery-picker]')) {
                    const btn = e.target;
                    const targetSelector = btn.dataset.target;
                    const previewSelector = btn.dataset.preview;
                    const altInputSelector = btn.dataset.altInput;
                    
                    if (!targetSelector || !previewSelector) return;
                    
                    const targetInput = document.querySelector(targetSelector);
                    const previewDiv = document.querySelector(previewSelector);
                    const repeaterItem = btn.closest('.repeater-item');
                    
                    if (!targetInput || !previewDiv || !repeaterItem) return;
                    
                    // Mở media picker
                    if (typeof openMediaPicker === 'function') {
                        openMediaPicker({
                            mode: 'single',
                            scope: 'client',
                            folder: 'clothes', // Chỉ lấy ảnh từ folder clothes
                            onSelect: (file) => {
                                if (file && file.url) {
                                    // Lưu relative_path (path tương đối từ folder clothes, ví dụ: thumbs/filename.jpg)
                                    // Nếu không có relative_path, fallback về filename
                                    const pathToSave = file.relative_path || file.filename || file.name || '';
                                    
                                    // Set giá trị vào hidden input existing_path
                                    targetInput.value = pathToSave;
                                    
                                    // Hiển thị preview
                                    previewDiv.innerHTML = `<img src="${file.url}" alt="${file.alt || ''}" style="max-width:100%;height:auto;">`;
                                    
                                    // Set alt nếu có input alt
                                    if (altInputSelector) {
                                        const altInput = repeaterItem.querySelector(altInputSelector);
                                        if (altInput && file.alt) {
                                            altInput.value = file.alt;
                                        }
                                    }
                                    
                                    // Đảm bảo có hidden input id (nếu chưa có thì tạo mới)
                                    let idInput = repeaterItem.querySelector('input[name*="[id]"]');
                                    if (!idInput) {
                                        const firstInput = repeaterItem.querySelector('input');
                                        if (firstInput) {
                                            idInput = document.createElement('input');
                                            idInput.type = 'hidden';
                                            idInput.name = targetInput.name.replace('[existing_path]', '[id]');
                                            idInput.value = '';
                                            firstInput.parentNode.insertBefore(idInput, firstInput);
                                        }
                                    }
                                    
                                    // Ẩn file input (không cần upload nữa)
                                    const fileInput = repeaterItem.querySelector('.image-file-input');
                                    if (fileInput) {
                                        fileInput.value = '';
                                    }
                                    
                                    markDirty();
                                }
                            }
                        });
                    } else {
                        alert('Popup thư viện ảnh chưa được tải. Vui lòng F5 lại trang.');
                    }
                }


                // Add step
                if (e.target.matches('[data-add-step]')) {
                    const container = e.target.closest('.repeater-item')?.querySelector('.steps-list');
                    if (!container) return;
                    const index = container.dataset.index;
                    const stepIndex = container.querySelectorAll('.step-item').length;
                    const html = `
                        <div class="step-item">
                            <input type="text" class="form-control" name="how_tos[${index}][steps][]" placeholder="Bước ${stepIndex + 1}">
                            <button type="button" data-remove-step>&times;</button>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                    markDirty();
                }

                // Remove step
                if (e.target.matches('[data-remove-step]')) {
                    e.target.closest('.step-item')?.remove();
                    markDirty();
                }

                // Add supply
                if (e.target.matches('[data-add-supply]')) {
                    const container = e.target.closest('.repeater-item')?.querySelector('.supplies-list');
                    if (!container) return;
                    const index = container.dataset.index;
                    const supplyIndex = container.querySelectorAll('.supply-item').length;
                    const html = `
                        <div class="supply-item">
                            <input type="text" class="form-control" name="how_tos[${index}][supplies][]" placeholder="Dụng cụ ${supplyIndex + 1}">
                            <button type="button" data-remove-supply>&times;</button>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                    markDirty();
                }

                // Remove supply
                if (e.target.matches('[data-remove-supply]')) {
                    e.target.closest('.supply-item')?.remove();
                    markDirty();
                }
            });

            // Image file preview
            document.addEventListener('change', (e) => {
                if (e.target.matches('.image-file-input')) {
                    const preview = e.target.closest('.repeater-item')?.querySelector('.image-preview');
                    if (!preview || !e.target.files?.length) return;
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        preview.innerHTML = `<img src="${ev.target.result}" alt="" style="max-width:100%;height:auto;">`;
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
            

            // TomSelect
            if (document.querySelector('#primary-category')) {
                new TomSelect('#primary-category', {create: false, allowEmptyOption: true});
            }
            if (document.querySelector('#included-categories')) {
                new TomSelect('#included-categories', {plugins: ['remove_button'], persist: false});
            }
            if (document.querySelector('#extra-categories')) {
                new TomSelect('#extra-categories', {plugins: ['remove_button'], persist: false});
            }
            if (document.querySelector('select[name="tag_ids[]"]')) {
                new TomSelect('select[name="tag_ids[]"]', {
                    placeholder: 'Chọn tags từ danh sách...',
                    plugins: ['remove_button'],
                    maxItems: null,
                    create: false,
                    sortField: {
                        field: 'text',
                        direction: 'asc'
                    }
                });
            }

            initTinyMCE();

            // ==== Media Picker (popup mới) ====
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-media-picker]');
                if (!btn) return;
                const targetSel = btn.dataset.target;
                const previewSel = btn.dataset.preview;
                const mode = btn.dataset.mode || 'single';
                const targetInput = document.querySelector(targetSel);
                const previewEl = document.querySelector(previewSel);
                if (!targetInput || !previewEl || typeof window.openMediaPicker !== 'function') {
                    return;
                }
                window.openMediaPicker({
                    mode,
                    scope: 'client',
                    onSelect: (fileOrFiles) => {
                        const files = Array.isArray(fileOrFiles) ? fileOrFiles : [fileOrFiles];
                        const first = files[0];
                        if (!first) return;
                        const filename = first.url ? first.url.split('/').pop() : first.filename;
                        targetInput.value = filename || '';
                        previewEl.innerHTML = `<img src="${first.url}" alt="${first.alt || ''}" style="max-width:100%;height:auto;">`;
                        markDirty();
                    }
                });
            });

            // Form dirty guard
            const form = document.querySelector('#product-form');
            if (form) {
                form.addEventListener('input', markDirty, true);
                form.addEventListener('change', markDirty, true);

                window.addEventListener('beforeunload', (event) => {
                    if (!isDirty) {
                        @if($isEdit)
                            if (navigator.sendBeacon) {
                                const formData = new FormData();
                                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
                                navigator.sendBeacon('{{ route("admin.products.release-lock", $product) }}', formData);
                            } else {
                                const xhr = new XMLHttpRequest();
                                xhr.open('POST', '{{ route("admin.products.release-lock", $product) }}', false);
                                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.content || '');
                                xhr.send();
                            }
                        @endif
                        return;
                    }
                    event.preventDefault();
                    event.returnValue = '';
                });

                form.addEventListener('submit', () => {
                    isDirty = false;
                });

                document.addEventListener('visibilitychange', () => {
                    if (document.hidden && !isDirty) {
                        @if($isEdit)
                            fetch('{{ route("admin.products.release-lock", $product) }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                    'Content-Type': 'application/json',
                                },
                            }).catch(() => {});
                        @endif
                    }
                });
            }
        });
    </script>
@endpush

@section('content')
    <form id="product-form" data-dirty-guard="true"
          action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:20px;">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">↩️ Quay lại danh sách</a>
            <button type="submit" class="btn btn-primary">💾 Lưu sản phẩm</button>
        </div>

        @if($isEdit && $product->locked_by === auth('web')->id())
            <div style="margin-bottom:15px;padding:12px 14px;border-radius:8px;background:#e0f2fe;color:#0f172a;">
                <strong>🔒 Đang chỉnh sửa:</strong>
                Bạn đang khóa sản phẩm này để chỉnh sửa. Hệ thống sẽ tự động mở khóa khi bạn lưu hoặc sau {{ config('app.editor_lock_minutes', 15) }} phút không hoạt động.
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
                    <div style="display:flex;align-items:center;gap:8px;margin-top:6px;">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}>
                        <span>Hiển thị tại mục "Sản phẩm nổi bật"</span>
                    </div>
                </div>
            </div>
            <div style="margin-top:15px;">
                <label>Mô tả ngắn</label>
                <textarea class="form-control tinymce-editor" name="short_description" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
            </div>
            <div style="margin-top:15px;">
                <label>Mô tả chi tiết</label>
                <textarea class="form-control tinymce-editor" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
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
            <div style="margin-top:15px;">
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
            <div style="margin-top:10px;">
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
                        <div style="margin-top:10px;">
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
                            <div class="d-flex gap-2 mt-2">
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
                        <div style="margin-top:10px;">
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
                        <div style="margin-top:10px;">
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
                        <div style="margin-top:10px;">
                            <label>Mô tả</label>
                            <textarea class="form-control" name="how_tos[{{ $index }}][description]" rows="2">{{ $howTo['description'] ?? '' }}</textarea>
                        </div>
                        <div style="margin-top:10px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
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
                        <div style="margin-top:10px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
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

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:20px;">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">↩️ Quay lại danh sách</a>
            <button type="submit" class="btn btn-primary">💾 Lưu sản phẩm</button>
        </div>
    </form>

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
            <div style="margin-top:10px;">
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
                        style="margin-top: 8px;">
                    📷 Chọn ảnh từ thư viện
                </button>
            </div>
            <div style="margin-top:10px;">
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
            <div style="margin-top:10px;">
                <label>Trả lời</label>
                <textarea class="form-control" name="faqs[__INDEX__][answer]" rows="2"></textarea>
            </div>
            <input type="hidden" name="faqs[__INDEX__][order]" value="__INDEX__">
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
            <div style="margin-top:10px;">
                <label>Mô tả</label>
                <textarea class="form-control" name="how_tos[__INDEX__][description]" rows="2"></textarea>
            </div>
            <div style="margin-top:10px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <label>Danh sách bước</label>
                    <button type="button" class="btn btn-secondary btn-sm" data-add-step>+ Thêm bước</button>
                </div>
                <div class="steps-list" data-index="__INDEX__">
                    <div class="step-item">
                        <input type="text" class="form-control" name="how_tos[__INDEX__][steps][]" placeholder="Bước 1">
                        <button type="button" data-remove-step>&times;</button>
                    </div>
                </div>
            </div>
            <div style="margin-top:10px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <label>Dụng cụ cần thiết</label>
                    <button type="button" class="btn btn-secondary btn-sm" data-add-supply>+ Thêm dụng cụ</button>
                </div>
                <div class="supplies-list" data-index="__INDEX__">
                    <div class="supply-item">
                        <input type="text" class="form-control" name="how_tos[__INDEX__][supplies][]" placeholder="Dụng cụ 1">
                        <button type="button" data-remove-supply>&times;</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection
