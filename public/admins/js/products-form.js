document.addEventListener('DOMContentLoaded', () => {
    const counters = {};
    let isDirty = false;
    const markDirty = () => { isDirty = true; };
    
    // TinyMCE replaced by CKEditor 5 (initialized in ckeditor-admin.js)

    /* =========================================
       MEDIA PICKER & IMAGE HANDLING
       ========================================= */

    // Open gallery picker for image selection (Repeater)
    document.addEventListener('click', (e) => {
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
            
            // Open media picker
            if (typeof openMediaPicker === 'function') {
                openMediaPicker({
                    mode: 'single',
                    scope: 'client',
                    folder: 'clothes', // Only from 'clothes' folder
                    onSelect: (file) => {
                        if (file && file.url) {
                            const pathToSave = file.relative_path || file.filename || file.name || '';
                            
                            // Set value to hidden input
                            targetInput.value = pathToSave;
                            
                            // Show preview
                            previewDiv.innerHTML = `<img src="${file.url}" alt="${file.alt || ''}" style="max-width:100%;height:auto;">`;
                            
                            // Set alt if exists
                            if (altInputSelector) {
                                const altInput = repeaterItem.querySelector(altInputSelector);
                                if (altInput && file.alt) {
                                    altInput.value = file.alt;
                                }
                            }
                            
                            // Ensure ID input exists
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
                            
                            // Hide file input
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
    });

    // Image file preview (File Input)
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

    // General Media Picker (New Popup)
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
                
                // FORCE use of relative path. 
                // If relative_path is missing, try path.
                // If both missing (rare), try to construct from URL if it contains 'img/'
                let valueToSave = first.relative_path || first.path;
                
                if (!valueToSave && first.url) {
                    // Try to extract from URL: .../img/clothes/abc.jpg -> clothes/abc.jpg
                    const marker = '/img/';
                    const idx = first.url.indexOf(marker);
                    if (idx !== -1) {
                        valueToSave = first.url.substring(idx + marker.length);
                    } else {
                         // Fallback to filename if all else fails
                         valueToSave = first.filename;
                    }
                }
                
                targetInput.value = valueToSave || '';
                previewEl.innerHTML = `<img src="${first.url}" alt="${first.alt || ''}" style="max-width:100%;height:auto;">`;
                markDirty();
            }
        });
    });

    /* =========================================
       FORM DIRTY GUARD
       ========================================= */
    const form = document.querySelector('#product-form');
    if (form) {
        form.addEventListener('input', markDirty, true);
        form.addEventListener('change', markDirty, true);

        // Retrieve release lock URL from meta or data attribute if needed
        // For simplicity, we assume the backend handles lock release on page unload via beacon if possible
        // But since we can't easily pass the route() into a static JS file without a global variable,
        // we will look for a data attribute on the form.
        const releaseLockUrl = form.dataset.releaseLockUrl;

        window.addEventListener('beforeunload', (event) => {
            if (!isDirty) {
                 if (releaseLockUrl && navigator.sendBeacon) {
                    const formData = new FormData();
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    formData.append('_token', csrfToken);
                    navigator.sendBeacon(releaseLockUrl, formData);
                }
                return;
            }
            event.preventDefault();
            event.returnValue = '';
        });

        form.addEventListener('submit', () => {
            isDirty = false;
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden && !isDirty && releaseLockUrl) {
                 fetch(releaseLockUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Content-Type': 'application/json',
                    },
                }).catch(() => {});
            }
        });
    }

    /* =========================================
       TOM SELECT INIT
       ========================================= */
    if (typeof TomSelect !== 'undefined') {
        const selectSettings = {
            plugins: ['remove_button'],
            create: false,
            maxItems: null,
        };
        
        ['#primary-category', '#included-categories', '#extra-categories', '#tagSelect'].forEach(sel => {
            const el = document.querySelector(sel);
            if (el) {
                new TomSelect(el, selectSettings);
            }
        });
    } else {
        console.warn('TomSelect library not loaded.');
    }

    /* =========================================
       REPEATER LOGIC
       ========================================= */

    // Generic Add
    document.querySelectorAll('[data-add]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-add');
            const templateId = btn.getAttribute('data-template');
            const container = document.querySelector(targetId);
            const template = document.querySelector(templateId);
            
            if (container && template) {
                const index = container.querySelectorAll('.repeater-item').length;
                const html = template.innerHTML.replace(/__INDEX__/g, index);
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                container.appendChild(wrapper.firstElementChild);
                markDirty();
            }
        });
    });

    // Generic Remove
    document.addEventListener('click', (e) => {
        if (e.target.matches('[data-remove]')) {
            const selector = e.target.getAttribute('data-item');
            const item = e.target.closest(selector);
            if (item && confirm('Bạn có chắc chắn muốn xóa?')) {
                item.remove();
                markDirty();
            }
        }
    });

    // Custom Description Repeater
    const descContainer = document.querySelector('#description-sections-list');
    const descAddBtn = document.querySelector('#add-description-section');
    const descTemplate = document.querySelector('#description-section-template');

    if (descAddBtn && descTemplate && descContainer) {
        let descCounter = descContainer.querySelectorAll('.repeater-item').length;

        descAddBtn.addEventListener('click', () => {
            let html = descTemplate.innerHTML
                .replace(/__INDEX__/g, descCounter)
                .replace(/__INDEX_PLUS_1__/g, descCounter + 1);
            
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const newBlock = wrapper.firstElementChild;
            descContainer.appendChild(newBlock);

            // Init CKEditor
            const textarea = newBlock.querySelector('.tinymce-editor');
            if (textarea && window.initCkEditorFor) {
                    const key = `description-section-${descCounter}-${Date.now()}`;
                    window.initCkEditorFor(textarea, key);
            }
            
            descCounter++;
            markDirty();
        });
    }

    // Nested Steps/Supplies Logic
    document.addEventListener('click', (e) => {
        if (e.target.matches('[data-add-step]')) {
            const list = e.target.parentElement.nextElementSibling;
            const index = list.dataset.index;
            const div = document.createElement('div');
            div.className = 'step-item';
            div.innerHTML = `<input type="text" class="form-control" name="how_tos[${index}][steps][]" placeholder="Bước mới"><button type="button" class="btn-link" data-remove-step>&times;</button>`;
            list.appendChild(div);
        }
        if (e.target.matches('[data-add-supply]')) {
            const list = e.target.parentElement.nextElementSibling;
            const index = list.dataset.index;
            const div = document.createElement('div');
            div.className = 'supply-item';
            div.innerHTML = `<input type="text" class="form-control" name="how_tos[${index}][supplies][]" placeholder="Dụng cụ mới"><button type="button" class="btn-link" data-remove-supply>&times;</button>`;
            list.appendChild(div);
        }
        if (e.target.matches('[data-remove-step]') || e.target.matches('[data-remove-supply]')) {
            e.target.parentElement.remove();
        }
    });
 
    
    // Debug form submission
    // Debug form submission
    if (form) {
        form.addEventListener('submit', function() {
            console.log('Form submitting...');
            const descInputs = document.querySelectorAll('input[name^="description["]');
            descInputs.forEach(input => {
                console.log(input.name, input.value);
            });
        });
    }

    // Initialize formatting
    if (window.formatCurrencyInputs) {
        formatCurrencyInputs();
    }
});
