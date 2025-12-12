@php($assignTargets = $uploadTargets ?? [])
<div class="modal fade" id="mediaAssignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gán ảnh vào đối tượng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="mediaAssignForm">
                    @csrf
                    <input type="hidden" name="media_id">
                    <input type="hidden" name="source">
                    <div class="mb-3">
                        <label class="form-label">Gán cho</label>
                        <select name="target_type" class="form-select" required>
                            @foreach($assignTargets as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ID đối tượng</label>
                        <input type="number" name="target_id" class="form-control" placeholder="Nhập ID cần gán" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Huỷ</button>
                <button type="button" class="btn btn-primary" id="mediaAssignSubmitBtn">Gán ảnh</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal chọn ảnh kiểu WordPress --}}
<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thư viện ảnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="media-picker d-flex">
                    <div class="media-picker-main flex-grow-1">
                        <div class="media-picker-toolbar d-flex align-items-center gap-2 flex-wrap p-3 border-bottom">
                            <button class="btn btn-primary btn-sm" id="mediaPickerUploadBtn">📤 Upload</button>
                            <button class="btn btn-outline-secondary btn-sm" id="mediaPickerRefreshBtn">🔄 Reload</button>
                            <input type="text" class="form-control form-control-sm" style="max-width: 320px;" id="mediaPickerSearch" placeholder="Tìm kiếm theo tên/alt/title...">
                            <div class="ms-auto d-flex align-items-center gap-2">
                                <span class="small text-muted" id="mediaPickerCount"></span>
                            </div>
                        </div>
                        <div class="media-picker-grid p-3" id="mediaPickerGrid">
                            <div class="text-center text-muted py-5">Đang tải...</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center px-3 pb-3">
                            <button class="btn btn-outline-secondary btn-sm" id="mediaPickerPrevPage">← Trang trước</button>
                            <button class="btn btn-outline-secondary btn-sm" id="mediaPickerNextPage">Trang tiếp →</button>
                        </div>
                    </div>
                    <div class="media-picker-preview border-start" style="width: 360px; min-width: 320px; max-width: 420px;">
                        <div class="p-3" id="mediaPickerPreviewEmpty">
                            <p class="text-muted mb-0">Chọn một ảnh để xem chi tiết</p>
                        </div>
                        <div class="p-3 d-none" id="mediaPickerPreview">
                            <div class="mb-3 text-center">
                                <img id="mediaPickerPreviewImg" src="" alt="" class="img-fluid rounded" style="max-height: 280px; object-fit: contain;">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small mb-1">Tên file</label>
                                <div class="form-control form-control-sm" id="mediaPickerFilename" readonly></div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small mb-1">Title</label>
                                <input type="text" class="form-control form-control-sm" id="mediaPickerTitle">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small mb-1">Alt</label>
                                <input type="text" class="form-control form-control-sm" id="mediaPickerAlt">
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm flex-grow-1" id="mediaPickerUpdateMeta">Lưu alt/title</button>
                                <button class="btn btn-outline-danger btn-sm" id="mediaPickerDelete">Xoá</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div class="text-muted small" id="mediaPickerSelectionInfo">Chưa chọn ảnh</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" id="mediaPickerUseBtn">Dùng ảnh này</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const modalEl = document.getElementById('mediaPickerModal');
            if (!modalEl) return;

            const modal = new bootstrap.Modal(modalEl);
            const gridEl = document.getElementById('mediaPickerGrid');
            const searchEl = document.getElementById('mediaPickerSearch');
            const countEl = document.getElementById('mediaPickerCount');
            const prevBtn = document.getElementById('mediaPickerPrevPage');
            const nextBtn = document.getElementById('mediaPickerNextPage');
            const useBtn = document.getElementById('mediaPickerUseBtn');
            const refreshBtn = document.getElementById('mediaPickerRefreshBtn');
            const uploadBtn = document.getElementById('mediaPickerUploadBtn');
            const previewEmpty = document.getElementById('mediaPickerPreviewEmpty');
            const previewWrap = document.getElementById('mediaPickerPreview');
            const previewImg = document.getElementById('mediaPickerPreviewImg');
            const filenameEl = document.getElementById('mediaPickerFilename');
            const titleEl = document.getElementById('mediaPickerTitle');
            const altEl = document.getElementById('mediaPickerAlt');
            const updateMetaBtn = document.getElementById('mediaPickerUpdateMeta');
            const deleteBtn = document.getElementById('mediaPickerDelete');
            const selectionInfo = document.getElementById('mediaPickerSelectionInfo');

            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.multiple = true;
            fileInput.accept = 'image/*';

            const state = {
                page: 1,
                perPage: 40,
                search: '',
                total: 0,
                files: [],
                selected: new Set(),
                current: null,
                mode: 'single',
                onSelect: null,
                scope: 'client',
            };

            window.openMediaPicker = function (options = {}) {
                state.mode = options.mode === 'multiple' ? 'multiple' : 'single';
                state.onSelect = typeof options.onSelect === 'function' ? options.onSelect : null;
                state.scope = options.scope || 'client';
                state.selected.clear();
                state.current = null;
                selectionInfo.textContent = 'Chưa chọn ảnh';
                previewEmpty.classList.remove('d-none');
                previewWrap.classList.add('d-none');
                state.page = 1;
                state.search = '';
                searchEl.value = '';
                loadPage();
                modal.show();
            };

            function buildParams() {
                const params = new URLSearchParams();
                params.set('scope', state.scope);
                params.set('page', state.page);
                params.set('limit', state.perPage);
                if (state.search) params.set('search', state.search);
                return params.toString();
            }

            function loadPage() {
                gridEl.innerHTML = '<div class="text-center text-muted py-5">Đang tải...</div>';
                
                // Nếu scope='client', dùng API products.media-images (đệ quy toàn bộ img)
                // Nếu scope='admin', dùng API media.list (theo folder)
                let apiUrl;
                if (state.scope === 'client') {
                    const params = new URLSearchParams();
                    params.set('offset', (state.page - 1) * state.perPage);
                    params.set('limit', state.perPage);
                    if (state.search) params.set('search', state.search);
                    apiUrl = `{{ route('admin.products.media-images') }}?${params.toString()}`;
                } else {
                    apiUrl = `{{ route('admin.media.list') }}?${buildParams()}`;
                }
                
                fetch(apiUrl)
                    .then(r => r.json())
                    .then(data => {
                        // Xử lý format khác nhau giữa 2 API
                        let files = [];
                        let total = 0;
                        
                        if (state.scope === 'client') {
                            // API products.media-images trả về { data: [...], total: ..., offset: ..., limit: ..., has_more: ... }
                            files = data.data || [];
                            total = data.total || files.length;
                            // Map format để giống với media.list
                            files = files.map(f => ({
                                filename: f.name || f.filename || '',
                                url: f.url || '',
                                path: f.path || f.url || '',
                                title: f.title || null,
                                alt: f.alt || null,
                                size: f.size || 0,
                                mime_type: f.mime_type || 'image/jpeg',
                            }));
                        } else {
                            // API media.list trả về { files: [...], pagination: { total: ... } }
                            files = data.files || [];
                            total = data.pagination?.total ?? files.length;
                        }
                        
                        state.total = total;
                        state.files = files;
                        renderGrid();
                        updatePager();
                    })
                    .catch((err) => {
                        console.error('Load media error:', err);
                        gridEl.innerHTML = '<div class="text-center text-danger py-4">Không thể tải dữ liệu</div>';
                    });
            }

            function renderGrid() {
                if (!state.files.length) {
                    gridEl.innerHTML = '<div class="text-center text-muted py-5">Không có ảnh</div>';
                    countEl.textContent = '0 ảnh';
                    return;
                }
                const frag = document.createDocumentFragment();
                state.files.forEach(file => {
                    const card = document.createElement('div');
                    card.className = 'media-picker-card';
                    card.dataset.path = file.path || file.url || file.filename;
                    const url = file.url || '';
                    const sizeText = file.size ? formatSize(file.size) : (file.mime_type || '');
                    card.innerHTML = `
                        <div class="media-picker-thumb">
                            <img src="${url}" alt="${file.filename || ''}" onerror="this.style.display='none'">
                        </div>
                        <div class="media-picker-meta">
                            <div class="media-picker-name" title="${file.filename || ''}">${file.filename || file.name || ''}</div>
                            <div class="media-picker-size">${sizeText}</div>
                        </div>
                    `;
                    if (state.selected.has(card.dataset.path)) {
                        card.classList.add('selected');
                    }
                    card.addEventListener('click', () => {
                        toggleSelect(card.dataset.path, file);
                    });
                    frag.appendChild(card);
                });
                gridEl.innerHTML = '';
                gridEl.appendChild(frag);
                countEl.textContent = `${state.total} ảnh`;
            }

            function toggleSelect(path, file) {
                if (state.mode === 'single') {
                    state.selected.clear();
                    state.selected.add(path);
                } else {
                    if (state.selected.has(path)) {
                        state.selected.delete(path);
                    } else {
                        state.selected.add(path);
                    }
                }
                state.current = file;
                renderGrid();
                updatePreview(file);
                selectionInfo.textContent = `${state.selected.size} ảnh đã chọn`;
            }

            function updatePreview(file) {
                if (!file) {
                    previewEmpty.classList.remove('d-none');
                    previewWrap.classList.add('d-none');
                    return;
                }
                previewEmpty.classList.add('d-none');
                previewWrap.classList.remove('d-none');
                const url = file.url || '';
                previewImg.src = url;
                previewImg.alt = file.alt || file.title || file.filename || '';
                filenameEl.textContent = file.filename || file.name || '';
                titleEl.value = file.title || '';
                altEl.value = file.alt || '';
            }

            function updatePager() {
                const totalPages = Math.max(1, Math.ceil(state.total / state.perPage));
                prevBtn.disabled = state.page <= 1;
                nextBtn.disabled = state.page >= totalPages;
            }

            function formatSize(bytes) {
                if (!bytes) return '';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return `${(bytes / Math.pow(k, i)).toFixed(1)} ${sizes[i]}`;
            }

            prevBtn.addEventListener('click', () => {
                if (state.page > 1) {
                    state.page -= 1;
                    loadPage();
                }
            });
            nextBtn.addEventListener('click', () => {
                const totalPages = Math.max(1, Math.ceil(state.total / state.perPage));
                if (state.page < totalPages) {
                    state.page += 1;
                    loadPage();
                }
            });
            refreshBtn.addEventListener('click', () => {
                loadPage();
            });
            let searchTimer;
            searchEl.addEventListener('input', (e) => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    state.search = e.target.value.trim();
                    state.page = 1;
                    loadPage();
                }, 250);
            });

            uploadBtn.addEventListener('click', () => {
                fileInput.click();
            });
            fileInput.addEventListener('change', () => {
                if (!fileInput.files?.length) return;
                const formData = new FormData();
                formData.append('scope', state.scope);
                formData.append('folder', '');
                Array.from(fileInput.files).forEach(f => formData.append('files[]', f));
                gridEl.innerHTML = '<div class="text-center text-muted py-5">Đang upload...</div>';
                fetch(`{{ route('admin.media.upload') }}`, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                    body: formData
                }).then(r => r.json())
                    .then(() => {
                        fileInput.value = '';
                        loadPage();
                    })
                    .catch(() => {
                        fileInput.value = '';
                        gridEl.innerHTML = '<div class="text-center text-danger py-4">Upload thất bại</div>';
                    });
            });

            useBtn.addEventListener('click', () => {
                if (!state.selected.size || !state.onSelect) return;
                const selectedFiles = state.files.filter(f => state.selected.has(f.path || f.url || f.filename));
                const payload = selectedFiles.map(f => ({
                    url: f.url,
                    filename: f.filename || f.name,
                    alt: f.alt || '',
                    title: f.title || '',
                    path: f.path || '',
                }));
                state.onSelect(state.mode === 'single' ? payload[0] : payload);
                modal.hide();
            });

            updateMetaBtn.addEventListener('click', () => {
                if (!state.current) return;
                const body = {
                    path: state.current.path || state.current.url || '',
                    alt: altEl.value || '',
                    title: titleEl.value || '',
                    scope: state.scope
                };
                const btnText = updateMetaBtn.textContent;
                updateMetaBtn.disabled = true;
                updateMetaBtn.textContent = 'Đang lưu...';
                fetch(`{{ route('admin.media.update-meta') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(body)
                }).then(r => r.json()).then(data => {
                    if (data.success && data.data) {
                        // Cập nhật state.current với data mới từ database
                        if (state.current) {
                            state.current.alt = data.data.alt || '';
                            state.current.title = data.data.title || '';
                        }
                        // Cập nhật preview với data mới
                        updatePreview(state.current);
                        // Reload grid để hiển thị alt/title mới
                        loadPage();
                    }
                }).catch(err => {
                    console.error('Update meta error:', err);
                    alert('Không thể lưu alt/title. Vui lòng thử lại.');
                }).finally(() => {
                    updateMetaBtn.disabled = false;
                    updateMetaBtn.textContent = btnText;
                });
            });

            deleteBtn.addEventListener('click', () => {
                if (!state.current) return;
                if (!confirm('Bạn có chắc muốn xoá ảnh này?')) return;
                fetch(`{{ route('admin.media.delete') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        path: state.current.path || state.current.url || '',
                        scope: state.scope
                    })
                }).then(r => r.json()).then(() => {
                    state.selected.delete(state.current.path || state.current.url || '');
                    state.current = null;
                    selectionInfo.textContent = `${state.selected.size} ảnh đã chọn`;
                    loadPage();
                });
            });
        })();
    </script>
@endpush
