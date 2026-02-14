<div class="repeater-item description-item" data-index="{{ $index }}">
    <div class="repeater-header">
        <strong>Section #<span class="section-index">{{ is_numeric($index) ? $index + 1 : '__INDEX_PLUS_1__' }}</span></strong>
        <div class="actions">
            <button type="button" class="btn-link text-danger" data-remove data-item=".description-item">Xóa section</button>
        </div>
    </div>
    
    <div class="grid-2">
        <div>
            <label>Key <span class="text-danger">*</span></label>
            @php
                $keyOptions = [
                    'intro' => 'Giới thiệu',
                    'feature' => 'Đặc điểm',
                    'use' => 'Công dụng',
                    'meaning' => 'Ý nghĩa',
                    'care' => 'Chăm sóc',
                ];
                $currentKey = $section['key'] ?? '';
            @endphp
            <select class="form-control" name="description[sections][{{ $index }}][key]" required>
                <option value="">-- Chọn loại section --</option>
                @foreach($keyOptions as $k => $label)
                    <option value="{{ $k }}" {{ $currentKey == $k ? 'selected' : '' }}>{{ $label }} ({{ $k }})</option>
                @endforeach
                <!-- Fallback for other keys if data exists -->
                @if($currentKey && !array_key_exists($currentKey, $keyOptions))
                    <option value="{{ $currentKey }}" selected>{{ $currentKey }} (Legacy/Other)</option>
                @endif
            </select>
        </div>
        <div>
            <label>Tiêu đề</label>
            <input type="text" class="form-control" name="description[sections][{{ $index }}][title]" value="{{ $section['title'] ?? '' }}" placeholder="Tiêu đề hiển thị (nếu có)">
        </div>
    </div>

    <div class="mt-3">
        <label>Media (Ảnh/Video) - Tùy chọn</label>
        @php
            $mediaType = data_get($section, 'media.type', 'image');
            $mediaUrl = $section['media']['url'] ?? null;
            // Generate full URL for preview
            // If mediaUrl already has a path structure (contains /), use it directly relative to img root
            // Otherwise assume it's in clothes folder (backward compatibility)
            $fullMediaUrl = '';
            if ($mediaUrl) {
                if (str_contains($mediaUrl, '/')) {
                     $fullMediaUrl = asset('clients/assets/img/'.$mediaUrl);
                } else {
                     $fullMediaUrl = asset('clients/assets/img/clothes/'.$mediaUrl);
                }
            }
        @endphp
        <div class="media-preview-container border p-2 rounded" style="background:#f9fafb;">
            <div class="d-flex align-items-center gap-2 mb-2">
                 <select name="description[sections][{{ $index }}][media][type]" class="form-control media-type-select" style="width:150px;">
                    <option value="">-- Không dùng --</option>
                    <option value="image" {{ $mediaType == 'image' ? 'selected' : '' }}>Hình ảnh</option>
                    <option value="video" {{ $mediaType == 'video' ? 'selected' : '' }}>Video</option>
                 </select>
                 <button type="button" class="btn btn-sm btn-outline-primary"
                        data-media-picker
                        data-target="#description-media-url-{{ $index }}"
                        data-preview="#description-media-preview-{{ $index }}"
                        data-mode="single">
                    Chọn từ thư viện
                 </button>
            </div>
            
            <input type="hidden" id="description-media-url-{{ $index }}" name="description[sections][{{ $index }}][media][url]" value="{{ $mediaUrl }}">
            
            <div id="description-media-preview-{{ $index }}" class="mt-2" style="max-width: 200px;">
                @if($mediaType == 'image' && $mediaUrl)
                    <img src="{{ $fullMediaUrl }}" alt="" class="img-fluid rounded">
                @elseif($mediaType == 'video' && $mediaUrl)
                    <video src="{{ $fullMediaUrl }}" controls class="img-fluid rounded"></video>
                @endif
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="mt-3">
        <label>Nội dung chi tiết</label>
        <textarea class="form-control tinymce-editor section-content-editor" 
                  name="description[sections][{{ $index }}][content]" 
                  id="section-content-{{ $index }}"
                  data-media-folder="clothes"
                  rows="4">{{ $section['content'] ?? '' }}</textarea>
    </div>
</div>
