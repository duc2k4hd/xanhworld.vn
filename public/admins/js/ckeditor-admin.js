(function () {
    if (!window.CKEDITOR) {
        console.error('[CKEditor] CKEDITOR UMD not loaded');
        return;
    }

    const {
        ClassicEditor,
        Autosave,
        Essentials,
        Paragraph,
        ImageBlock,
        ImageToolbar,
        CloudServices,
        ImageUpload,
        ImageInsertViaUrl,
        AutoImage,
        ImageTextAlternative,
        ImageCaption,
        ImageStyle,
        ImageInline,
        List,
        TodoList,
        Mention,
        ImageUtils,
        ImageEditing,
        Heading,
        Link,
        AutoLink,
        BlockQuote,
        HorizontalLine,
        CodeBlock,
        Indent,
        IndentBlock,
        Alignment,
        Style,
        GeneralHtmlSupport,
        Fullscreen,
        // Emoji,
        Autoformat,
        TextTransformation,
        MediaEmbed,
        Bold,
        Italic,
        Underline,
        Strikethrough,
        Code,
        Subscript,
        Superscript,
        FontBackgroundColor,
        FontColor,
        FontFamily,
        FontSize,
        Highlight,
        Table,
        TableToolbar,
        PlainTableOutput,
        TableCaption,
        HtmlComment,
        SourceEditing,
        ShowBlocks,
        BalloonToolbar,
        BlockToolbar,
    } = window.CKEDITOR;

    const PluginBase = window.CKEDITOR.core && window.CKEDITOR.core.Plugin;
    const ButtonViewBase =
        window.CKEDITOR.ui &&
        window.CKEDITOR.ui.button &&
        window.CKEDITOR.ui.button.ButtonView;

    // License key lấy từ ckeditor5-builder-47.4.0/main.js
    const LICENSE_KEY =
        'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3Nzk0OTQzOTksImp0aSI6IjZhZmExMjEzLWJlMTEtNDg4ZC1iMzczLTY3Mjc2ZTNhZDU1ZCIsImxpY2Vuc2VkSG9zdHMiOlsiMTI3LjAuMC4xIiwibG9jYWxob3N0IiwiMTkyLjE2OC4qLioiLCIxMC4qLiouKiIsIjE3Mi4qLiouKiIsIioudGVzdCIsIioubG9jYWxob3N0IiwiKi5sb2NhbCJdLCJ1c2FnZUVuZHBvaW50IjoiaHR0cHM6Ly9wcm94eS1ldmVudC5ja2VkaXRvci5jb20iLCJkaXN0cmlidXRpb25DaGFubmVsIjpbImNsb3VkIiwiZHJ1cGFsIl0sImxpY2Vuc2VUeXBlIjoiZGV2ZWxvcG1lbnQiLCJmZWF0dXJlcyI6WyJEUlVQIiwiRTJQIiwiRTJXIl0sInZjIjoiYTFjMmVmODAifQ.iYS2Jnjyu0JewNgmJABblnPrQWkWNc1WhMAGPm7vpxuWA2nCf-Dk0trrPtzVtXYM7it7NPJ-HH6ZjvgL5zVCVA';

    window.CKEDITOR_INSTANCES = window.CKEDITOR_INSTANCES || {};

    const editorConfigBase = {
        toolbar: {
            items: [
                'undo',
                'redo',
                '|',
                'sourceEditing',
                'showBlocks',
                'fullscreen',
                '|',
                'heading',
                // 'style', // dùng nếu cần style theo class
                '|',
                'fontSize',
                'fontFamily',
                // 'fontColor',
                // 'fontBackgroundColor',
                '|',
                'bold',
                'italic',
                'underline',
                'strikethrough',
                'subscript',
                'superscript',
                'code',
                '|',
                // 'emoji',
                'horizontalLine',
                'link',
                'mediaEmbed',
                'insertTable',
                'blockQuote',
                'codeBlock',
                '|',
                'alignment',
                '|',
                'bulletedList',
                'numberedList',
                'todoList',
                'outdent',
                'indent',
            ],
            shouldNotGroupWhenFull: true,
        },
        plugins: [
            Alignment,
            Autoformat,
            AutoImage,
            AutoLink,
            Autosave,
            BalloonToolbar,
            BlockQuote,
            BlockToolbar,
            Bold,
            CloudServices,
            Code,
            CodeBlock,
            // Emoji,
            Essentials,
            FontBackgroundColor,
            FontColor,
            FontFamily,
            FontSize,
            Fullscreen,
            GeneralHtmlSupport,
            Heading,
            Highlight,
            HorizontalLine,
            HtmlComment,
            ImageBlock,
            ImageCaption,
            ImageEditing,
            ImageInline,
            ImageInsertViaUrl,
            ImageStyle,
            ImageTextAlternative,
            ImageToolbar,
            ImageUpload,
            ImageUtils,
            Indent,
            IndentBlock,
            Italic,
            Link,
            List,
            MediaEmbed,
            Mention,
            Paragraph,
            PlainTableOutput,
            ShowBlocks,
            SourceEditing,
            Strikethrough,
            Style,
            Subscript,
            Superscript,
            Table,
            TableCaption,
            TableToolbar,
            TextTransformation,
            TodoList,
            Underline,
        ],
        balloonToolbar: ['bold', 'italic', '|', 'link', '|', 'bulletedList', 'numberedList'],
        blockToolbar: [
            'fontSize',
            'fontColor',
            'fontBackgroundColor',
            '|',
            'bold',
            'italic',
            '|',
            'link',
            'insertTable',
            '|',
            'bulletedList',
            'numberedList',
            'outdent',
            'indent',
        ],
        fontFamily: {
            supportAllValues: true,
        },
        fontSize: {
            options: [10, 12, 14, 'default', 18, 20, 22],
            supportAllValues: true,
        },
        htmlSupport: {
            allow: [
                {
                    name: /^.*$/,
                    styles: true,
                    attributes: true,
                    classes: true,
                },
            ],
        },
        image: {
            toolbar: ['toggleImageCaption', 'imageTextAlternative', '|', 'imageStyle:inline', 'imageStyle:wrapText', 'imageStyle:breakText'],
        },
        language: 'vi',
        placeholder: 'Nhập nội dung...',
        link: {
            addTargetToExternalLinks: true,
            defaultProtocol: 'https://',
            decorators: {
                toggleDownloadable: {
                    mode: 'manual',
                    label: 'Downloadable',
                    attributes: {
                        download: 'file',
                    },
                },
            },
        },
        mention: {
            feeds: [
                {
                    marker: '@',
                    feed: [],
                },
            ],
        },
        table: {
            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
        },
        licenseKey: LICENSE_KEY,
        heading: {
            // Đảm bảo có đủ H1–H6 giống cấu hình builder
            options: [
                {
                    model: 'paragraph',
                    title: 'Paragraph',
                    class: 'ck-heading_paragraph',
                },
                {
                    model: 'heading1',
                    view: 'h1',
                    title: 'Heading 1',
                    class: 'ck-heading_heading1',
                },
                {
                    model: 'heading2',
                    view: 'h2',
                    title: 'Heading 2',
                    class: 'ck-heading_heading2',
                },
                {
                    model: 'heading3',
                    view: 'h3',
                    title: 'Heading 3',
                    class: 'ck-heading_heading3',
                },
                {
                    model: 'heading4',
                    view: 'h4',
                    title: 'Heading 4',
                    class: 'ck-heading_heading4',
                },
                {
                    model: 'heading5',
                    view: 'h5',
                    title: 'Heading 5',
                    class: 'ck-heading_heading5',
                },
                {
                    model: 'heading6',
                    view: 'h6',
                    title: 'Heading 6',
                    class: 'ck-heading_heading6',
                },
            ],
        },
    };

    function sanitizePastedHtml(html) {
        if (!html || typeof html !== 'string') return html;
        let out = html.replace(/\sstyle=(["'])(?:(?=(\\?))\2.)*?\1/gi, '');
        out = out.replace(/<span\b[^>]*>\s*<\/span>/gi, '');
        out = out.replace(/<span>\s*([\s\S]*?)\s*<\/span>/gi, '$1');
        return out;
    }

    // Kích hoạt plugin ImageResize (nếu build UMD có)
    const ImageResizePlugin = window.CKEDITOR.ImageResize;
    if (ImageResizePlugin) {
        editorConfigBase.plugins.push(ImageResizePlugin);
        editorConfigBase.image = editorConfigBase.image || {};
        editorConfigBase.image.resizeUnit = 'px';
    }

    function getEditorMediaFolder(editor, textarea) {
        const el = textarea || editor._sourceTextarea || null;
        const attr =
            (el && el.getAttribute && el.getAttribute('data-media-folder')) || '';
        if (attr) {
            return attr;
        }
        if (el && el.name && el.name.indexOf('description') !== -1) {
            return 'clothes';
        }
        return 'posts';
    }

    // Plugin custom: nút "Thư viện ảnh" trên toolbar (nếu core.Plugin & ButtonView có sẵn)
    let MediaPickerToolbarPlugin = null;
    if (PluginBase && ButtonViewBase) {
        MediaPickerToolbarPlugin = class MediaPickerToolbarPlugin extends PluginBase {
            init() {
                const editor = this.editor;
                editor.ui.componentFactory.add('mediaPicker', (locale) => {
                    const view = new ButtonViewBase(locale);
                    view.set({
                        label: '🖼 Thư viện ảnh',
                        withText: true,
                        tooltip: 'Chèn ảnh từ thư viện',
                    });
                    view.on('execute', () => {
                        if (typeof window.openMediaPicker !== 'function') {
                            alert('Không tải được popup thư viện ảnh. Vui lòng F5.');
                            return;
                        }

                        const mediaFolder = getEditorMediaFolder(
                            editor,
                            editor._sourceTextarea || null,
                        );

                        window.openMediaPicker({
                            mode: 'single',
                            scope: 'client',
                            folder: mediaFolder,
                            onSelect: (file) => {
                                if (!file || !file.url) {
                                    return;
                                }
                                const alt =
                                    file.alt || file.title || file.filename || file.name || '';

                                if (editor.model.schema.isRegistered('imageBlock')) {
                                    editor.model.change((writer) => {
                                        const imageElement = writer.createElement('imageBlock', {
                                            src: file.url,
                                            alt,
                                        });
                                        editor.model.insertContent(
                                            imageElement,
                                            editor.model.document.selection,
                                        );
                                    });
                                } else {
                                    const html = `<img src="${file.url}" alt="${alt}">`;
                                    const viewFragment = editor.data.processor.toView(html);
                                    const modelFragment = editor.data.toModel(viewFragment);
                                    editor.model.insertContent(
                                        modelFragment,
                                        editor.model.document.selection,
                                    );
                                }
                            },
                        });
                    });
                    return view;
                });
            }
        };

        editorConfigBase.plugins.push(MediaPickerToolbarPlugin);
        editorConfigBase.toolbar.items.push('mediaPicker');
    }

    function attachMediaPickerButton(editor, textarea) {
        if (typeof window.openMediaPicker !== 'function') {
            return;
        }

        const editorElement = editor.ui.view && editor.ui.view.element;
        if (!editorElement || !editorElement.parentNode) {
            return;
        }

        const wrapper = editorElement.parentNode;
        const existing = wrapper.querySelector('.ck-media-picker-btn');
        if (existing) {
            return;
        }

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-secondary mt-2 ck-media-picker-btn';
        btn.textContent = '📚 Chọn ảnh từ thư viện';

        btn.addEventListener('click', () => {
            if (typeof window.openMediaPicker !== 'function') {
                alert('Không tải được popup thư viện ảnh. Vui lòng F5.');
                return;
            }

            const mediaFolder = getEditorMediaFolder(editor, textarea);

            window.openMediaPicker({
                mode: 'single',
                scope: 'client',
                folder: mediaFolder,
                onSelect: (file) => {
                    if (!file || !file.url) {
                        return;
                    }
                    const alt = file.alt || file.title || file.filename || file.name || '';

                    // Chèn ảnh vào editor (ưu tiên imageBlock nếu có)
                    if (editor.model.schema.isRegistered('imageBlock')) {
                        editor.model.change((writer) => {
                            const imageElement = writer.createElement('imageBlock', {
                                src: file.url,
                                alt,
                            });
                            editor.model.insertContent(imageElement, editor.model.document.selection);
                        });
                    } else {
                        const html = `<img src="${file.url}" alt="${alt}">`;
                        const viewFragment = editor.data.processor.toView(html);
                        const modelFragment = editor.data.toModel(viewFragment);
                        editor.model.insertContent(modelFragment, editor.model.document.selection);
                    }
                },
            });
        });

        wrapper.appendChild(btn);
    }

    function addMediaPickerToolbarButton(editor) {
        if (typeof window.openMediaPicker !== 'function') {
            return false;
        }

        const uiNs = window.CKEDITOR.ui;
        const ButtonView = uiNs && uiNs.button && uiNs.button.ButtonView;
        if (!ButtonView) {
            return false;
        }

        const locale = editor.locale;
        const toolbarView = editor.ui.view && editor.ui.view.toolbar;
        if (!toolbarView || !toolbarView.items) {
            return false;
        }

        const mediaButton = new ButtonView(locale);
        mediaButton.set({
            label: '🖼 Thư viện ảnh',
            withText: true,
            tooltip: 'Chèn ảnh từ thư viện',
        });

        mediaButton.on('execute', () => {
            if (typeof window.openMediaPicker !== 'function') {
                alert('Không tải được popup thư viện ảnh. Vui lòng F5.');
                return;
            }

            window.openMediaPicker({
                mode: 'single',
                scope: 'client',
                onSelect: (file) => {
                    if (!file || !file.url) {
                        return;
                    }
                    const alt = file.alt || file.title || file.filename || file.name || '';

                    if (editor.model.schema.isRegistered('imageBlock')) {
                        editor.model.change((writer) => {
                            const imageElement = writer.createElement('imageBlock', {
                                src: file.url,
                                alt,
                            });
                            editor.model.insertContent(imageElement, editor.model.document.selection);
                        });
                    } else {
                        const html = `<img src="${file.url}" alt="${alt}">`;
                        const viewFragment = editor.data.processor.toView(html);
                        const modelFragment = editor.data.toModel(viewFragment);
                        editor.model.insertContent(modelFragment, editor.model.document.selection);
                    }
                },
            });
        });

        toolbarView.items.add(mediaButton);
        return true;
    }

    async function initEditorFor(textarea, key) {
        if (!textarea) return;
        if (window.CKEDITOR_INSTANCES[key]) return;

        // Tránh khởi tạo trùng trên cùng một textarea (khi script bị load 2 lần)
        if (textarea.dataset.ckeditorInitialized === '1' || textarea.dataset.ckeditorInitialized === 'pending') {
            return;
        }
        // Nếu đã có wrapper CKEditor ngay sau textarea thì cũng bỏ qua
        if (
            textarea.nextElementSibling &&
            textarea.nextElementSibling.classList &&
            textarea.nextElementSibling.classList.contains('ck-editor')
        ) {
            textarea.dataset.ckeditorInitialized = '1';
            return;
        }

        textarea.dataset.ckeditorInitialized = 'pending';

        const config = { ...editorConfigBase };

        try {
            const editor = await ClassicEditor.create(textarea, config);
            textarea.dataset.ckeditorInitialized = '1';
            editor._sourceTextarea = textarea;

            if (editor.plugins.has('ClipboardPipeline')) {
                const clipboard = editor.plugins.get('ClipboardPipeline');
                clipboard.on('inputTransformation', (evt, data) => {
                    if (!data.content) return;
                    const html = editor.data.processor.toData(data.content);
                    const cleaned = sanitizePastedHtml(html);
                    data.content = editor.data.processor.toView(cleaned);
                });
            }

            // Nếu không đăng ký được button toolbar (thiếu core.Plugin/ButtonView) thì fallback nút dưới editor
            if (!MediaPickerToolbarPlugin) {
                attachMediaPickerButton(editor, textarea);
            }

            const form = textarea.closest('form');
            if (form) {
                form.addEventListener(
                    'submit',
                    () => {
                        textarea.value = editor.getData();
                    },
                    { capture: true },
                );
            }

            window.CKEDITOR_INSTANCES[key] = editor;
        } catch (e) {
            console.error('[CKEditor] init error for', key, e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const postEditor = document.getElementById('post-content-editor');
        if (postEditor) {
            initEditorFor(postEditor, 'post-content-editor');
        }

        document.querySelectorAll('.tinymce-editor').forEach((el, idx) => {
            const key = el.name || `tinymce-editor-${idx}`;
            initEditorFor(el, key);
        });

        const newsletter = document.querySelector('textarea[name="content"]');
        if (newsletter) {
            initEditorFor(newsletter, 'newsletter-content');
        }

        const contactReply = document.getElementById('contact-reply-editor');
        if (contactReply) {
            initEditorFor(contactReply, 'contact-reply-editor');
        }
    });
})();

