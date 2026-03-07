document.addEventListener("click", async (e) => {
    // Bỏ qua nếu click vào menu mobile
    if (e.target.closest(".xanhworld_header_main_mobile_bars") || 
        e.target.closest(".xanhworld_header_mobile_main_nav")) {
        return;
    }
});

const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute("content") : '';

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

function showCustomToast(
    message = "Thông báo!",
    type = "info",
    duration = 5000
) {
    const container = document.getElementById("custom-toast-container");
    if (!container) {
        console.warn('Toast container not found');
        return;
    }
    
    const toast = document.createElement("div");
    const icon = document.createElement("span");

    toast.className = `custom-toast ${type}`;
    icon.className = "custom-toast-icon";

    // Gán biểu tượng theo loại
    const icons = {
        success: "✅",
        error: "❌",
        warning: "⚠️",
        info: "💬",
    };
    icon.textContent = icons[type] || "🔔";

    toast.appendChild(icon);
    toast.appendChild(document.createTextNode(message));
    container.appendChild(toast);

    // Kích hoạt animation
    setTimeout(() => {
        if (toast && toast.classList) {
            toast.classList.add("show");
        }
    }, 100);

    toast.addEventListener("click", () => {
        if (toast && toast.classList) {
            toast.classList.remove("show");
        }
        setTimeout(() => {
            if (container && toast && container.contains(toast)) {
                container.removeChild(toast);
            }
        }, 300);
        return;
    });

    // Gỡ thông báo sau duration
    setTimeout(() => {
        if (toast && toast.classList) {
            toast.classList.remove("show");
        }
        setTimeout(() => {
            if (container && toast && container.contains(toast)) {
                container.removeChild(toast);
            }
        }, 300);
        return;
    }, duration);
}

async function showOverlayMain(ms) {
    const overlay = document.querySelector(".xanhworld_loading_overlay");
    if (!overlay) return;
    overlay.style.display = "flex";
    await sleep(ms);
    if (overlay) {
        overlay.style.display = "none";
    }
}

function parseVND(value) {
    if (typeof value !== "string") return 0;

    return parseInt(
        value.replace(/[^\d]/g, "") // Xoá mọi ký tự không phải số
    );
}

function formatCurrencyVND(amount) {
    if (isNaN(amount)) return 0;
    return Number(amount).toLocaleString("vi-VN");
}

function postAndRedirect(url, data = {}) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = url;

    // CSRF token nếu dùng Laravel web.php
    const csrf = document.querySelector('meta[name="csrf-token"]');
    if (csrf) {
        const token = document.createElement("input");
        token.type = "hidden";
        token.name = "_token";
        token.value = csrf.getAttribute("content") || csrfToken;
        form.appendChild(token);
    }

    // Đệ quy xử lý mảng/lồng object
    function appendFormData(key, value) {
        if (Array.isArray(value)) {
            value.forEach((v, i) => {
                for (const subKey in v) {
                    appendFormData(`${key}[${i}][${subKey}]`, v[subKey]);
                }
            });
        } else if (typeof value === "object") {
            for (const subKey in value) {
                appendFormData(`${key}[${subKey}]`, value[subKey]);
            }
        } else {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }
    }

    for (const key in data) {
        if (data.hasOwnProperty(key)) {
            appendFormData(key, data[key]);
        }
    }

    document.body.appendChild(form);
    form.submit();
}

setTimeout(() => {
    document
        .querySelectorAll(".xanhworld_header_main_nav_links_item_title")
        .forEach((item, index) => {
            const list = document.querySelectorAll(
                ".xanhworld_header_main_nav_links_item_list"
            )[index];

            if (!item || !list) return;

            const left = item.getBoundingClientRect().left;

            list.style.transform = `translateX(-${left - 10}px)`;
        });
}, 10); // ⏱ chạy sau 200ms

const mainMenu = document.querySelector(".xanhworld_header_main_nav");

if (mainMenu) {
    window.addEventListener("scroll", () => {
        if (window.scrollY > 240) {
            mainMenu.classList.add("xanhworld_header_main_nav_fixed");
        } else {
            mainMenu.classList.remove("xanhworld_header_main_nav_fixed");
        }
    });
}


// Custom Xanhworld-select
function initCustomSelect(selector) {
    document.querySelectorAll(selector).forEach(select => {

        const isMultiple = select.dataset.multiple === "true";
        const wrapper = document.createElement("div");
        wrapper.className = "xanhworld-select-wrapper";

        const display = document.createElement("div");
        display.className = "xanhworld-select-display";
        display.textContent = "Chọn...";

        const dropdown = document.createElement("div");
        dropdown.className = "xanhworld-select-options";

        // Ẩn select gốc
        select.style.display = "none";
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        wrapper.appendChild(display);
        wrapper.appendChild(dropdown);

        // Thêm option vào dropdown
        [...select.options].forEach(opt => {
            if (!opt.value) return;
            const item = document.createElement("div");
            item.className = "xanhworld-select-option";
            item.textContent = opt.textContent;
            item.dataset.value = opt.value;

            item.addEventListener("click", () => {
                if (isMultiple) {
                    opt.selected = !opt.selected;
                    item.classList.toggle("xanhworld-select-selected");
                } else {
                    [...dropdown.children].forEach(c => c.classList.remove("xanhworld-select-selected"));
                    item.classList.add("xanhworld-select-selected");

                    select.value = opt.value;
                    display.textContent = opt.textContent;
                    dropdown.style.display = "none";
                }

                if (isMultiple) {
                    const selected = [...select.selectedOptions].map(o => o.textContent);
                    display.textContent = selected.length ? selected.join(", ") : "Chọn...";
                }
            });

            dropdown.appendChild(item);
        });

        // Toggle dropdown
        display.addEventListener("click", () => {
            dropdown.style.display =
                dropdown.style.display === "block" ? "none" : "block";
        });

        // Click ngoài để tắt
        document.addEventListener("click", e => {
            if (!wrapper.contains(e.target)) {
                dropdown.style.display = "none";
            }
        });
    });
}

// Xử lý menu mobile - đảm bảo chạy sau khi DOM ready
function initMobileMenu() {
    const openMenuMobile = document.querySelector(
        ".xanhworld_header_main_mobile_bars"
    );
    const closeMenuMobile = document.querySelector(
        ".xanhworld_header_mobile_main_nav_close"
    );
    const menuMobile = document.querySelector(
        ".xanhworld_header_mobile_main_nav"
    );
    const overlay = document.querySelector(".xanhworld_header_mobile_overlay");

    // Kiểm tra phần tử tồn tại
    if (!openMenuMobile) {
        return;
    }
    if (!closeMenuMobile) {
        return;
    }
    if (!menuMobile) {
        return;
    }

    // open - sử dụng stopPropagation để tránh conflict
    openMenuMobile.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (menuMobile && menuMobile.classList) {
            menuMobile.classList.add("active");
        }
        if (overlay && overlay.classList) {
            overlay.classList.add("active");
        }
        // Ngăn scroll body khi menu mở
        if (document.body) {
            document.body.style.overflow = "hidden";
        }
    });

    // close
    closeMenuMobile.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (menuMobile && menuMobile.classList) {
            menuMobile.classList.remove("active");
        }
        if (overlay && overlay.classList) {
            overlay.classList.remove("active");
        }
        // Khôi phục scroll body
        if (document.body) {
            document.body.style.overflow = "";
        }
    });

    // Close khi click overlay
    if (overlay) {
        overlay.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (menuMobile && menuMobile.classList) {
                menuMobile.classList.remove("active");
            }
            if (overlay && overlay.classList) {
                overlay.classList.remove("active");
            }
            if (document.body) {
                document.body.style.overflow = "";
            }
        });
    }

    // Close khi click ra ngoài menu (nếu không có overlay)
    if (!overlay) {
        document.addEventListener("click", (e) => {
            if (menuMobile && menuMobile.classList && menuMobile.classList.contains("active")) {
                // Nếu click không phải vào menu hoặc button mở menu
                if (openMenuMobile && !menuMobile.contains(e.target) && !openMenuMobile.contains(e.target)) {
                    menuMobile.classList.remove("active");
                    if (document.body) {
                        document.body.style.overflow = "";
                    }
                }
            }
        });
    }
}

// Chạy khi DOM ready
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initMobileMenu);
} else {
    // DOM đã sẵn sàng
    initMobileMenu();
}

// submenu toggle
document
    .querySelectorAll(".xanhworld_header_mobile_main_nav_links_item_title")
    .forEach((title) => {
        if (!title) {
            return;
        }
        title.addEventListener("click", () => {
            const subMenu = title.nextElementSibling;
            const svg = title.querySelector("svg");

            if (!subMenu || !subMenu.classList) {
                return;
            }

            const isOpen = subMenu.classList.contains("show");

            if (isOpen) {
                subMenu.classList.remove("show");
                if (svg && svg.style) {
                    svg.style.transform = "rotate(0deg)";
                }
            } else {
                subMenu.classList.add("show");
                if (svg && svg.style) {
                    svg.style.transform = "rotate(180deg)";
                }
            }
        });
    });

const backToTopBtn = document.querySelector(".xanhworld_back_to_top");

if (backToTopBtn) {
    window.addEventListener("scroll", () => {
        if (window.scrollY > 300) {
            backToTopBtn.style.display = "flex";

            const orderSummary = document.querySelector(".xanhworld_order_summary");
            if (orderSummary) {
                orderSummary.classList.add("xanhworld_order_summary_fixed");
            }

        } else {
            backToTopBtn.style.display = "none";

            const orderSummary = document.querySelector(".xanhworld_order_summary");
            if (orderSummary) {
                orderSummary.classList.remove("xanhworld_order_summary_fixed");
            }
        }
    });

    backToTopBtn.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
}

function toggleFormOverlay(show = true) {
    const overlay = document.querySelector(
        ".xanhworld_main_loading_form_overlay"
    );
    if (!overlay) return;
    if (show) overlay.removeAttribute("hidden");
    else overlay.setAttribute("hidden", "");
}

document.addEventListener("DOMContentLoaded", function () {

    // Delay 200ms để tránh lỗi khi DOM chưa ổn định (giảm CLS)
    setTimeout(() => {

        const trigger = document.querySelector("[data-ai-chat-trigger]");
        const popup = document.getElementById("xanhworldChatPopup");
        if (!trigger || !popup) return;

        const form = popup.querySelector(".xanhworld_chat_form");
        const textarea = popup.querySelector("textarea");
        const sendButton = popup.querySelector(".xanhworld_chat_send");
        const messagesBox = popup.querySelector(".xanhworld_chat_messages");
        const closeButton = popup.querySelector(".xanhworld_chat_close");
        const endpoint = popup.dataset.endpoint;
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrf = csrfMeta ? csrfMeta.getAttribute("content") : "";
        const history = [];
        const STORAGE_KEY = "xanhworld-chat-messages";
        const MAX_MESSAGES = 10;
        const defaultGreeting =
            "Xin chào! Bạn đang cần tư vấn cây cảnh, decor hay muốn tìm hiểu bài viết nào? Mình có thể dùng dữ liệu sản phẩm & bài viết mới nhất để trả lời ngay.";
        let isProcessing = false;
        let persistedMessages = [];

        const escapeHtml = (value) =>
            value.replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

        const formatMessageContent = (text = "") => {
            if (!text.trim()) return "<p></p>";

            let html = escapeHtml(text);
            html = html.replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>");
            html = html.replace(/\*(.+?)\*/g, "<em>$1</em>");
            html = html.replace(/`(.+?)`/g, "<code>$1</code>");

            const paragraphs = html
                .split(/\n{2,}/)
                .map(p => `<p>${p.trim().replace(/\n/g, "<br>")}</p>`);

            return paragraphs.join("");
        };

        const trimHistory = () => {
            while (history.length > 10) history.shift();
        };

        const normalizeReferences = (refs) => {
            if (!Array.isArray(refs)) return [];
            return refs
                .map((item) => {
                    if (!item) return null;
                    const url = item.url || item.link;
                    if (!url) return null;
                    const label = item.title || item.name || item.label || "Xem thêm";
                    return { label, url };
                })
                .filter(Boolean);
        };

        const loadMessagesFromStorage = () => {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                if (!raw) return (persistedMessages = []);

                const parsed = JSON.parse(raw);
                if (!Array.isArray(parsed)) return (persistedMessages = []);

                persistedMessages = parsed.slice(-MAX_MESSAGES).map(entry => ({
                    role: entry.role || "assistant",
                    content: entry.content || "",
                    references: normalizeReferences(entry.references),
                }));
            } catch {
                persistedMessages = [];
            }
        };

        const saveMessagesToStorage = () => {
            try {
                localStorage.setItem(
                    STORAGE_KEY,
                    JSON.stringify(persistedMessages.slice(-MAX_MESSAGES))
                );
            } catch {}
        };

        const renderMessage = (entry) => {
            const message = document.createElement("div");
            message.className = `xanhworld_chat_message is-${entry.role}`;
            message.innerHTML = formatMessageContent(entry.content);

            if (entry.references?.length) {
                const refs = document.createElement("div");
                refs.className = "xanhworld_chat_sources";

                entry.references.forEach((r) => {
                    const a = document.createElement("a");
                    a.href = r.url;
                    a.target = "_blank";
                    a.textContent = r.label;
                    a.className = "xanhworld_chat_source_link";
                    refs.appendChild(a);
                });

                message.appendChild(refs);
            }

            messagesBox.appendChild(message);
            messagesBox.scrollTop = messagesBox.scrollHeight;
        };

        const renderStoredMessages = () => {
            messagesBox.innerHTML = "";
            if (!persistedMessages.length) {
                renderMessage({
                    role: "assistant",
                    content: defaultGreeting,
                    references: [],
                });
                return;
            }
            persistedMessages.forEach(renderMessage);
        };

        const syncHistoryFromMessages = () => {
            history.length = 0;
            persistedMessages.forEach(entry => {
                if (entry.content) history.push({ role: entry.role, content: entry.content });
            });
            trimHistory();
        };

        const addMessage = (role, text, references = null, opt = {}) => {
            const entry = {
                role,
                content: text,
                references: normalizeReferences(references),
            };

            renderMessage(entry);

            if (opt.persist === false) return entry;

            persistedMessages.push(entry);
            if (persistedMessages.length > MAX_MESSAGES) {
                persistedMessages = persistedMessages.slice(-MAX_MESSAGES);
            }
            saveMessagesToStorage();
            syncHistoryFromMessages();

            return entry;
        };

        const togglePopup = () => {
            const isOpening = !popup.classList.contains("is-open");
            popup.classList.toggle("is-open");
            
            // Toggle overflow hidden trên body/html để tránh cuộn không đúng
            if (isOpening) {
                document.body.style.overflow = "hidden";
                document.documentElement.style.overflow = "hidden";
                setTimeout(() => textarea.focus(), 150);
            } else {
                document.body.style.overflow = "";
                document.documentElement.style.overflow = "";
            }
        };

        const appendError = (msg) => {
            const div = document.createElement("div");
            div.className = "xanhworld_chat_message is-assistant is-error";
            div.innerHTML = formatMessageContent(msg);
            messagesBox.appendChild(div);
            messagesBox.scrollTop = messagesBox.scrollHeight;
        };

        const appendTyping = () => {
            const div = document.createElement("div");
            div.className = "xanhworld_chat_message is-assistant";
            div.innerHTML =
                '<div class="xanhworld_chat_typing"><span></span><span></span><span></span></div>';
            messagesBox.appendChild(div);
            messagesBox.scrollTop = messagesBox.scrollHeight;
            return div;
        };

        const setLoading = (b) => {
            isProcessing = b;
            updateSendState();
        };

        const updateSendState = () => {
            sendButton.disabled = isProcessing || textarea.value.trim().length < 5;
        };

        // INIT CHAT UI
        loadMessagesFromStorage();
        renderStoredMessages();
        syncHistoryFromMessages();

        trigger.addEventListener("click", (e) => {
            e.preventDefault();
            togglePopup();
        });

        closeButton.addEventListener("click", () => {
            popup.classList.remove("is-open");
            // Khôi phục overflow khi đóng modal
            document.body.style.overflow = "";
            document.documentElement.style.overflow = "";
        });

        // Xử lý tab switching
        const tabs = popup.querySelectorAll(".xanhworld_chat_tab");
        const tabContents = popup.querySelectorAll(".xanhworld_chat_tab_content");
        
        tabs.forEach(tab => {
            tab.addEventListener("click", () => {
                const targetTab = tab.dataset.tab;
                
                // Remove active class từ tất cả tabs và contents
                tabs.forEach(t => t.classList.remove("active"));
                tabContents.forEach(content => content.classList.remove("active"));
                
                // Add active class cho tab và content được chọn
                tab.classList.add("active");
                const targetContent = popup.querySelector(`[data-tab-content="${targetTab}"]`);
                if (targetContent) {
                    targetContent.classList.add("active");
                }
            });
        });

        textarea.addEventListener("input", updateSendState);

        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            if (isProcessing) return;

            const content = textarea.value.trim();
            if (content.length < 5) return;

            addMessage("user", content);
            textarea.value = "";
            updateSendState();

            const typing = appendTyping();
            setLoading(true);

            try {
                const resp = await fetch(endpoint, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrf,
                    },
                    body: JSON.stringify({
                        question: content,
                        history,
                    }),
                });

                const data = await resp.json();
                typing.remove();

                if (!resp.ok || !data.success) {
                    throw new Error(data.message || "Không gửi được câu hỏi. Hãy thử lại sau.");
                }

                const refs = [
                    ...(data.references?.products || []),
                    ...(data.references?.posts || []),
                ];

                addMessage("assistant", data.answer, refs);
            } catch (err) {
                typing.remove();
                appendError(err.message || "Trợ lý đang bận, thử lại giúp mình nhé.");
            } finally {
                setLoading(false);
            }
        });

        document.addEventListener("keyup", (e) => {
            if (e.key === "Escape") popup.classList.remove("is-open");
        });

    }, 200); // END DELAY 200ms
});

function openImageSearchModal() {
    document.getElementById('imageSearchModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeImageSearchModal() {
    document.getElementById('imageSearchModal').style.display = 'none';
    document.body.style.overflow = '';
    resetImageSearch();
}

function resetImageSearch() {
    document.getElementById('imageInput').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('uploadArea').querySelector('.xanhworld_image_search_upload_content').style.display = 'block';
    document.getElementById('searchButton').disabled = true;
    document.getElementById('loadingState').style.display = 'none';
}

// Image search modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const removeImage = document.getElementById('removeImage');
    const searchButton = document.getElementById('searchButton');
    const form = document.getElementById('imageSearchForm');
    const loadingState = document.getElementById('loadingState');

    if (!uploadArea || !imageInput || !form) return;

    // Click to select file
    uploadArea.addEventListener('click', function(e) {
        if (e.target !== removeImage && !e.target.closest('#removeImage')) {
            imageInput.click();
        }
    });

    // File selection
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                alert('File quá lớn. Vui lòng chọn file nhỏ hơn 5MB.');
                return;
            }
            displayPreview(file);
        }
    });

    // Drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            if (file.size > 5 * 1024 * 1024) {
                alert('File quá lớn. Vui lòng chọn file nhỏ hơn 5MB.');
                return;
            }
            imageInput.files = e.dataTransfer.files;
            displayPreview(file);
        }
    });

    // Display preview
    function displayPreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            imagePreview.style.display = 'block';
            uploadArea.querySelector('.xanhworld_image_search_upload_content').style.display = 'none';
            searchButton.disabled = false;
            searchButton.style.opacity = '1';
        };
        reader.readAsDataURL(file);
    }

    // Remove image
    removeImage.addEventListener('click', function(e) {
        e.stopPropagation();
        resetImageSearch();
    });

    // Form submit
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!imageInput.files[0]) {
            alert('Vui lòng chọn ảnh để tìm kiếm');
            return;
        }

        const formData = new FormData(form);
        
        // Show loading
        loadingState.style.display = 'block';
        searchButton.disabled = true;
        searchButton.style.opacity = '0.5';

        try {
            const response = await fetch('{{ route("client.image-search.search") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Redirect to shop page with image search results (dù products rỗng vẫn redirect)
                const keywords = data.keywords || [];
                const keywordParam = keywords.length > 0 ? keywords[0] : '';
                window.location.href = '{{ route("client.shop.index") }}?keyword=' + encodeURIComponent(keywordParam) + '&image_search=1';
            } else {
                alert(data.message || 'Không tìm thấy sản phẩm nào phù hợp với hình ảnh. Vui lòng thử với ảnh khác.');
                loadingState.style.display = 'none';
                searchButton.disabled = false;
                searchButton.style.opacity = '1';
            }
        } catch (error) {
            console.error('Search error:', error);
            alert('Có lỗi xảy ra. Vui lòng thử lại sau.');
            loadingState.style.display = 'none';
            searchButton.disabled = false;
            searchButton.style.opacity = '1';
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('imageSearchModal').style.display === 'flex') {
            closeImageSearchModal();
        }
    });
});


// Tabs mô tả
const tabButtons = document.querySelectorAll(
    ".xanhworld_single_desc_button button"
);
const tabContents = document.querySelectorAll(
    ".xanhworld_single_desc_tabs > div"
);

tabButtons.forEach((btn, i) => {
    btn.addEventListener("click", () => {
        tabButtons.forEach((b) =>
            b.classList.remove("xanhworld_single_desc_button_active")
        );
        tabContents.forEach((tab) =>
            tab.classList.remove("xanhworld_single_desc_tabs_active")
        );
        btn.classList.add("xanhworld_single_desc_button_active");
        tabContents[i].classList.add("xanhworld_single_desc_tabs_active");
    });
});
if (tabButtons[0]) {
    tabButtons[0]?.click();
}

function tabReview() {
    // Activate review tab (third tab, index 2)
    if (tabButtons[2]) {
        tabButtons[2].click();
    }
    
    // Scroll to reviews section
    const reviewsSection = document.getElementById('reviews');
    if (reviewsSection) {
        // Wait a bit for tab transition, then scroll
        setTimeout(() => {
            reviewsSection.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }, 100);
    }
}

function tabSizeGuide() {
    if (tabButtons[1]) {
        tabButtons[1]?.click();
    }
}

// Click ảnh con => ảnh chính
const mainIMG = document.querySelector(
    ".xanhworld_single_info_images_main_image"
);
const galleryImages = document.querySelectorAll(
    ".xanhworld_single_info_images_gallery_image"
);
galleryImages.forEach((img) => {
    img.addEventListener("click", () => {
        const newSrc = img.dataset.src || img.src;
        if (newSrc) {
            mainIMG.removeAttribute("srcset");
            mainIMG.removeAttribute("sizes");
            mainIMG.setAttribute("src", newSrc);
            galleryImages.forEach((i) =>
                i.classList.remove(
                    "xanhworld_single_info_images_gallery_image_active"
                )
            );
            img.classList.add(
                "xanhworld_single_info_images_gallery_image_active"
            );
        }
    });
});

document
    .querySelectorAll(".xanhworld_single_info_voucher_code_item")
    ?.forEach((item) => {
        item.addEventListener("click", () => {
            navigator.clipboard
                .writeText(item.textContent.trim())
                .then(() =>
                    showCustomToast(
                        "Mã voucher đã được sao chép vào clipboard!",
                        "info"
                    )
                )
                .catch((error) => {
                    console.error("Error:", error);
                    showCustomToast(
                        "Có lỗi xảy ra khi sao chép mã voucher.",
                        "error"
                    );
                });
        });
    });

const qtyDisplay = document.querySelector(
    ".xanhworld_single_info_specifications_actions_value"
) || document.querySelector(".qty-input");
const qtyWrapper = document.querySelector(
    ".xanhworld_single_info_specifications_actions_qty"
);
const qtyInputField = document.querySelector("input[name='quantity']") || document.getElementById("quantity_input") || document.getElementById("form_quantity_input");
const qtyMax = parseInt(
    qtyWrapper?.dataset.maxStock || qtyInputField?.dataset.maxStock || document.querySelector(".qty-input")?.dataset.maxStock || 99,
    10
);

function safeToast(message, type = "info") {
    if (typeof showCustomToast === "function") {
        showCustomToast(message, type);
    }
}

function syncQuantity(val) {
    if (qtyDisplay) {
        if (qtyDisplay.tagName === 'INPUT') {
            qtyDisplay.value = val;
        } else {
            qtyDisplay.textContent = val;
        }
    }
    if (qtyInputField) {
        qtyInputField.value = val;
    }
    // Also update form quantity input for new theme
    const formQtyInput = document.getElementById('form_quantity_input');
    if (formQtyInput) formQtyInput.value = val;
}

function currentQty() {
    return (
        parseInt(qtyDisplay?.textContent || qtyInputField?.value || "1", 10) ||
        1
    );
}

// Variant selection handler
function selectVariant(variantId, price, salePrice, stock) {
    // Update active state
    const clickedButton = event?.target?.closest('.xanhworld_single_info_specifications_variant_item') || 
                          document.querySelector(`[data-variant-id="${variantId}"]`);
    
    document.querySelectorAll('.xanhworld_single_info_specifications_variant_item').forEach(btn => {
        btn.classList.remove('active');
    });
    
    if (clickedButton) {
        clickedButton.classList.add('active');
    }
    
    // Update hidden input
    const variantInput = document.getElementById('selected_variant_id');
    const formVariantInput = document.getElementById('form_variant_id');
    if (variantInput) variantInput.value = variantId;
    if (formVariantInput) formVariantInput.value = variantId;
    
    // Update price display
    const priceElement = document.querySelector('.xanhworld_single_info_specifications_new_price');
    const oldPriceElement = document.querySelector('.xanhworld_single_info_specifications_old_price');
    
    // Update price display
    if (priceElement) {
        const displayPrice = salePrice && salePrice > 0 && salePrice < price ? salePrice : price;
        priceElement.textContent = formatCurrencyVND(displayPrice) + '₫';
    }
    
    // Update old price (strikethrough)
    if (oldPriceElement) {
        if (salePrice && salePrice > 0 && salePrice < price) {
            oldPriceElement.textContent = formatCurrencyVND(price) + '₫';
            oldPriceElement.style.display = 'inline';
        } else {
            oldPriceElement.style.display = 'none';
        }
    }
    
    // Update max stock for quantity
    const quantityBox = document.getElementById('quantity_box');
    if (quantityBox) {
        if (stock !== null && stock !== undefined) {
            const maxStock = Math.max(1, stock);
            quantityBox.setAttribute('data-max-stock', maxStock);
            const currentQty = parseInt(document.querySelector('.xanhworld_single_info_specifications_actions_value')?.textContent || '1');
            if (currentQty > maxStock) {
                const qtyValueEl = document.querySelector('.xanhworld_single_info_specifications_actions_value');
                const qtyInputEl = document.getElementById('quantity_input');
                if (qtyValueEl) qtyValueEl.textContent = maxStock;
                if (qtyInputEl) qtyInputEl.value = maxStock;
            }
        } else {
            quantityBox.setAttribute('data-max-stock', '9999');
        }
    }
    
    // Check if out of stock
    const addToCartBtn = document.querySelector('.xanhworld_single_info_specifications_actions_cart');
    const buyNowBtn = document.querySelector('.xanhworld_single_info_specifications_actions_buy');
    
    if (stock !== null && stock <= 0) {
        if (addToCartBtn) {
            addToCartBtn.classList.add('disabled');
            addToCartBtn.disabled = true;
        }
        if (buyNowBtn) {
            buyNowBtn.classList.add('disabled');
            buyNowBtn.style.pointerEvents = 'none';
        }
    } else {
        if (addToCartBtn) {
            addToCartBtn.classList.remove('disabled');
            addToCartBtn.disabled = false;
        }
        if (buyNowBtn) {
            buyNowBtn.classList.remove('disabled');
            buyNowBtn.style.pointerEvents = 'auto';
        }
    }
}

function increaseQty() {
    const qty = currentQty();
    if (qty >= qtyMax) {
        safeToast(`Số lượng tối đa trong kho là ${qtyMax}`, "warning");
        return;
    }
    syncQuantity(qty + 1);
}

function decreaseQty() {
    const qty = currentQty();
    if (qty <= 1) {
        safeToast("Số lượng tối thiểu là 1", "warning");
        return;
    }
    syncQuantity(qty - 1);
}

function countDownFlashSale(endTimestamp) {
    const daysEl = document.querySelector(
        ".xanhworld_single_info_specifications_box_days"
    );
    const hoursEl = document.querySelector(
        ".xanhworld_single_info_specifications_box_house"
    );
    const minutesEl = document.querySelector(
        ".xanhworld_single_info_specifications_box_minute"
    );
    const secondsEl = document.querySelector(
        ".xanhworld_single_info_specifications_box_second"
    );
    if (!daysEl || !hoursEl || !minutesEl || !secondsEl) return;

    const endTime = new Date(endTimestamp); // timestamp ms

    function updateCountdown() {
        const now = new Date();
        const distance = endTime.getTime() - now.getTime();

        if (distance <= 0) {
            // Hết hạn → reload 1 lần
            location.reload();
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor(
            (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
        );
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        updateBox(daysEl, days);
        updateBox(hoursEl, hours);
        updateBox(minutesEl, minutes);
        updateBox(secondsEl, seconds);
    }

    function updateBox(el, newValue) {
        const oldValue = el.textContent;
        const formatted = newValue.toString().padStart(2, "0");

        if (oldValue !== formatted) {
            el.textContent = formatted;
            el.classList.remove("animate");
            void el.offsetWidth; // trigger reflow
            el.classList.add("animate");
        }
    }

    // ✅ chạy ngay khi load
    updateCountdown();

    // Sau đó lặp lại mỗi giây
    setInterval(updateCountdown, 1000);
}

if (typeof endTime !== "undefined") {
    // Truyền timestamp ms
    countDownFlashSale(endTime);
}

function showPopupVoucher() {
    const popup = document.querySelector(
        ".xanhworld_main_show_popup_voucher_overlay"
    );
    const closeBtn = document.querySelector(
        ".xanhworld_main_show_popup_voucher_close"
    );
    const codeEl = document.querySelectorAll(
        ".xanhworld_main_show_popup_voucher_code"
    );

    // // Hiện popup sau 10 giây
    // setTimeout(() => {

    // }, 10000);
    popup.style.display = "flex";

    // Đóng popup
    closeBtn.addEventListener("click", () => {
        popup.style.display = "none";
    });

    // Click ra ngoài để đóng
    popup.addEventListener("click", (e) => {
        if (e.target === popup) {
            popup.style.display = "none";
        }
    });

    // Copy voucher code khi click
    codeEl.forEach((el) => {
        el.addEventListener("click", () => {
            if (el.dataset.copied === "true") return; // nếu voucher này đã copy rồi thì bỏ qua

            const originalText = el.textContent.trim();

            navigator.clipboard
                .writeText(originalText)
                .then(() => {
                    showCustomToast("Mã voucher đã được sao chép!", "info");
                    el.textContent = "Đã sao chép!";
                    el.dataset.copied = "true"; // đánh dấu riêng cho voucher này

                    // Reset lại sau 2 giây
                    setTimeout(() => {
                        el.textContent = originalText;
                        el.dataset.copied = "false";
                    }, 5000);
                })
                .catch((err) => {
                    console.error("Copy thất bại: ", err);
                });
        });
    });
}

setTimeout(() => {
    showPopupVoucher();
}, 20000);

document.addEventListener("DOMContentLoaded", () => {
    // === BASE ELEMENTS ===
    const xanhworldOverlay = document.querySelector(
        ".xanhworld_single_info_images_main_overlay"
    );
    const xanhworldOverlayImagesWrapper = document.querySelector(
        ".xanhworld_single_info_images_main_overlay_images"
    );
    const xanhworldOverlayImageItems = document.querySelectorAll(
        ".xanhworld_single_info_images_main_overlay_image"
    );

    // === STATE ===
    let xanhworldCurrentIndex = 0;
    let xanhworldTouchStartX = 0;

    // === BUTTONS ===
    const xanhworldBtnPrev = document.createElement("div");
    xanhworldBtnPrev.className = "xanhworld_nav_btn xanhworld_prev";
    xanhworldBtnPrev.textContent = "‹";

    const xanhworldBtnNext = document.createElement("div");
    xanhworldBtnNext.className = "xanhworld_nav_btn xanhworld_next";
    xanhworldBtnNext.textContent = "›";

    const xanhworldBtnClose = document.createElement("div");
    xanhworldBtnClose.className = "xanhworld_close_btn";
    xanhworldBtnClose.textContent = "✕";

    // Check if overlay exists before appending
    if (xanhworldOverlay) {
        xanhworldOverlay.appendChild(xanhworldBtnPrev);
        xanhworldOverlay.appendChild(xanhworldBtnNext);
        xanhworldOverlay.appendChild(xanhworldBtnClose);
    }

    // === LOCK/UNLOCK BODY SCROLL ===
    let scrollPosition = 0;
    
    function lockBodyScroll() {
        // Lưu vị trí scroll hiện tại
        scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
        
        // Lock scroll
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.top = `-${scrollPosition}px`;
        document.body.style.width = '100%';
    }

    function unlockBodyScroll() {
        // Unlock scroll
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.width = '';
        
        // Restore scroll position
        window.scrollTo(0, scrollPosition);
    }

    // === CLICK THUMBNAIL TO OPEN OVERLAY (TÍCH HỢP VÀO CODE CŨ) ===
    if (xanhworldOverlay) {
        document
            .querySelectorAll(".xanhworld_single_info_images_main img")
            .forEach((thumb, index) => {
                thumb.addEventListener("click", () => {
                    xanhworldCurrentIndex = index;
                    xanhworldOverlay.style.display = "flex";
                    lockBodyScroll(); // Lock scroll khi mở overlay

                    setTimeout(() => {
                        xanhworldOverlay.classList.add("xanhworld_show");
                    }, 10);

                    xanhworldUpdatePosition();
                });
            });
    }
    
    // === OPEN OVERLAY ===
    if (xanhworldOverlay) {
        document
            .querySelectorAll(
                ".xanhworld_single_info_images_main_overlay_image"
            )
            .forEach((xanhworldImg, i) => {
                xanhworldImg.addEventListener("click", () => {
                    xanhworldCurrentIndex = i;
                    xanhworldOverlay.style.display = "flex";
                    lockBodyScroll(); // Lock scroll khi mở overlay
                    setTimeout(
                        () => xanhworldOverlay.classList.add("xanhworld_show"),
                        10
                    );
                    xanhworldUpdatePosition();
                });
            });
    }

    // === UPDATE POSITION ===
    function xanhworldUpdatePosition() {
        if (xanhworldOverlayImagesWrapper) {
            xanhworldOverlayImagesWrapper.style.transform = `translateX(-${
                xanhworldCurrentIndex * 100
            }vw)`;
        }
    }

    // === NEXT / PREV ===
    if (xanhworldBtnNext) {
        xanhworldBtnNext.addEventListener("click", () => {
            xanhworldCurrentIndex =
                (xanhworldCurrentIndex + 1) % xanhworldOverlayImageItems.length;
            xanhworldUpdatePosition();
        });
    }

    if (xanhworldBtnPrev) {
        xanhworldBtnPrev.addEventListener("click", () => {
            xanhworldCurrentIndex =
                (xanhworldCurrentIndex - 1 + xanhworldOverlayImageItems.length) %
                xanhworldOverlayImageItems.length;
            xanhworldUpdatePosition();
        });
    }

    // === CLOSE ===
    function xanhworldCloseOverlay() {
        if (xanhworldOverlay) {
            xanhworldOverlay.classList.remove("xanhworld_show");
            unlockBodyScroll(); // Unlock scroll khi đóng overlay
            setTimeout(() => (xanhworldOverlay.style.display = "none"), 200);
        }
    }

    if (xanhworldBtnClose) {
        xanhworldBtnClose.addEventListener("click", xanhworldCloseOverlay);
    }

    if (xanhworldOverlay) {
        xanhworldOverlay.addEventListener("click", (e) => {
            if (e.target === xanhworldOverlay) xanhworldCloseOverlay();
        });
    }

    // === ESC TO CLOSE ===
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && xanhworldOverlay) xanhworldCloseOverlay();
    });

    // === MOBILE SWIPE ===
    if (xanhworldOverlayImagesWrapper) {
        xanhworldOverlayImagesWrapper.addEventListener("touchstart", (e) => {
            xanhworldTouchStartX = e.touches[0].clientX;
        });

        xanhworldOverlayImagesWrapper.addEventListener("touchend", (e) => {
            let touchEndX = e.changedTouches[0].clientX;
            let touchDiff = xanhworldTouchStartX - touchEndX;

            if (touchDiff > 50) {
                xanhworldCurrentIndex =
                    (xanhworldCurrentIndex + 1) % xanhworldOverlayImageItems.length;
                xanhworldUpdatePosition();
            }
            if (touchDiff < -50) {
                xanhworldCurrentIndex =
                    (xanhworldCurrentIndex -
                        1 +
                        xanhworldOverlayImageItems.length) %
                    xanhworldOverlayImageItems.length;
                xanhworldUpdatePosition();
            }
        });
    }

    // === DOUBLE TAP TO ZOOM ===
    let xanhworldLastTap = 0;

    if (xanhworldOverlay) {
        xanhworldOverlay.addEventListener("touchend", () => {
            const now = Date.now();
            if (now - xanhworldLastTap < 250) {
                xanhworldOverlay.classList.toggle("xanhworld_zoom_active");
            }
            xanhworldLastTap = now;
        });
    }

    initAccessoryDragScroll();
    initAccessoryQuickAdd();
});

function addWishlist(productId) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "/san-pham/yeu-thich";

    const inputProduct = document.createElement("input");
    inputProduct.type = "hidden";
    inputProduct.name = "product_id";
    inputProduct.value = productId;

    const inputToken = document.createElement("input");
    inputToken.type = "hidden";
    inputToken.name = "_token";
    inputToken.value = csrfToken;

    form.appendChild(inputProduct);
    form.appendChild(inputToken);

    document.body.appendChild(form);
    form.submit();
}

function removeWishlist(productId) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "/san-pham/yeu-thich";

    const token = document.createElement("input");
    token.type = "hidden";
    token.name = "_token";
    token.value = csrfToken;

    const method = document.createElement("input");
    method.type = "hidden";
    method.name = "_method";
    method.value = "DELETE";

    const id = document.createElement("input");
    id.type = "hidden";
    id.name = "product_id";
    id.value = productId;

    form.appendChild(token);
    form.appendChild(method);
    form.appendChild(id);

    document.body.appendChild(form);
    form.submit();
}

function initAccessoryDragScroll() {
    const scrollers = document.querySelectorAll("[data-accessory-scroll]");
    if (!scrollers.length) {
        return;
    }

    scrollers.forEach((scroller) => {
        let isDown = false;
        let startX = 0;
        let scrollLeft = 0;

        scroller.addEventListener("mousedown", (e) => {
            isDown = true;
            scroller.classList.add("is-dragging");
            startX = e.pageX - scroller.offsetLeft;
            scrollLeft = scroller.scrollLeft;
        });

        ["mouseleave", "mouseup"].forEach((evt) => {
            scroller.addEventListener(evt, () => {
                isDown = false;
                scroller.classList.remove("is-dragging");
            });
        });

        scroller.addEventListener("mousemove", (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - scroller.offsetLeft;
            const walk = (x - startX) * 1.2;
            scroller.scrollLeft = scrollLeft - walk;
        });
    });
}

function initAccessoryQuickAdd() {
    const buttons = document.querySelectorAll("[data-accessory-add]");
    if (!buttons.length) {
        return;
    }

    const csrf = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    if (!csrf) {
        console.warn("CSRF token not found for accessory quick add.");
        return;
    }

    buttons.forEach((btn) => {
        btn.addEventListener("click", () => handleAccessoryAdd(btn, csrf));
    });

    // Xử lý modal variant cho accessories
    const modal = document.getElementById('accessory-variant-modal');
    if (modal) {
        // Đảm bảo modal ẩn mặc định khi khởi tạo
        if (modal.classList.contains('active')) {
            modal.classList.remove('active');
        }
        // Đảm bảo body không bị lock scroll
        if (document.body.style.overflow === 'hidden') {
            document.body.style.overflow = '';
        }

        const modalOverlay = modal.querySelector('.xanhworld_variant_modal_overlay');
        const modalClose = modal.querySelector('.xanhworld_variant_modal_close');
        const modalCancel = document.getElementById('accessory-modal-cancel-btn');
        const quantityInput = document.getElementById('accessory-modal-quantity');
        const quantityDecrease = modal.querySelector('[data-action="decrease"]');
        const quantityIncrease = modal.querySelector('[data-action="increase"]');
        const addToCartBtn = document.getElementById('accessory-modal-add-to-cart-btn');

        // Đóng modal - function global để có thể gọi từ nơi khác
        window.closeAccessoryModal = function() {
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
                // Reset form
                const qtyInput = document.getElementById('accessory-modal-quantity');
                if (qtyInput) {
                    qtyInput.value = 1;
                }
                // Reset variant selection
                const variantItems = modal.querySelectorAll('#accessory-modal-variants-list .xanhworld_variant_modal_variant_item');
                variantItems.forEach(item => item.classList.remove('active'));
                if (variantItems.length > 0 && !variantItems[0].disabled) {
                    variantItems[0].classList.add('active');
                }
            }
        };

        const closeModal = window.closeAccessoryModal;

        if (modalOverlay) {
            modalOverlay.addEventListener('click', closeModal);
        }
        if (modalClose) {
            modalClose.addEventListener('click', closeModal);
        }
        if (modalCancel) {
            modalCancel.addEventListener('click', closeModal);
        }

        // Đóng modal khi bấm ESC (chỉ khi modal đang mở)
        function handleEscapeKey(e) {
            if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
                e.preventDefault();
                e.stopPropagation();
                closeModal();
            }
        }
        document.addEventListener('keydown', handleEscapeKey);

        // Tăng/giảm số lượng
        if (quantityDecrease) {
            quantityDecrease.addEventListener('click', function() {
                const input = quantityInput;
                const currentValue = parseInt(input.value) || 1;
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                }
            });
        }

        if (quantityIncrease) {
            quantityIncrease.addEventListener('click', function() {
                const input = quantityInput;
                const currentValue = parseInt(input.value) || 1;
                const maxStock = parseInt(input.max) || 999;
                if (currentValue < maxStock) {
                    input.value = currentValue + 1;
                }
            });
        }

        // Validate số lượng khi nhập
        if (quantityInput) {
            quantityInput.addEventListener('change', function() {
                const value = parseInt(this.value) || 1;
                const maxStock = parseInt(this.max) || 999;
                const minValue = parseInt(this.min) || 1;
                
                if (value < minValue) {
                    this.value = minValue;
                } else if (value > maxStock) {
                    this.value = maxStock;
                }
            });
        }

        // Submit form thêm vào giỏ
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                const productId = modal.dataset.currentProductId;
                const csrfToken = modal.dataset.currentCsrf;
                const quantity = parseInt(quantityInput.value) || 1;
                
                if (!productId || !csrfToken) {
                    showCustomToast('Lỗi: Không tìm thấy thông tin sản phẩm', 'error');
                    return;
                }

                // Validate số lượng
                if (quantity < 1) {
                    showCustomToast('Số lượng phải lớn hơn 0', 'error');
                    quantityInput.focus();
                    return;
                }

                // Lấy variant ID nếu có
                const selectedVariant = modal.querySelector('#accessory-modal-variants-list .xanhworld_variant_modal_variant_item.active');
                const variantId = selectedVariant ? selectedVariant.dataset.variantId : null;

                // Validate variant nếu có variants
                const hasVariants = document.getElementById('accessory-modal-variants-section').style.display !== 'none';
                if (hasVariants && !variantId) {
                    showCustomToast('Vui lòng chọn biến thể sản phẩm', 'error');
                    return;
                }

                // Validate stock
                if (selectedVariant) {
                    const stock = selectedVariant.dataset.variantStock;
                    if (stock !== 'null' && stock !== null && parseInt(stock) <= 0) {
                        showCustomToast('Sản phẩm này đã hết hàng', 'error');
                        return;
                    }
                    if (stock !== 'null' && stock !== null && quantity > parseInt(stock)) {
                        showCustomToast(`Số lượng vượt quá tồn kho (còn ${stock} sản phẩm)`, 'error');
                        quantityInput.value = stock;
                        quantityInput.focus();
                        return;
                    }
                }

                // Disable button
                this.disabled = true;
                const originalText = this.innerHTML;
                this.innerHTML = '<span>Đang thêm...</span>';

                // Gửi request
                const requestBody = {
                    product_id: productId,
                    quantity: quantity,
                };

                if (variantId) {
                    requestBody.product_variant_id = variantId;
                }

                fetch("/api/v1/cart/accessories", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify(requestBody),
                })
                    .then(async (response) => {
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(data.message || 'Có lỗi xảy ra khi thêm vào giỏ hàng');
                        }
                        return data;
                    })
                    .then((data) => {
                        // Đóng modal trước
                        if (typeof window.closeAccessoryModal === 'function') {
                            window.closeAccessoryModal();
                        }
                        // Hiển thị thông báo thành công
                        if (data.message) {
                            showCustomToast(data.message, 'success');
                        } else {
                            showCustomToast('Đã thêm vào giỏ hàng thành công!', 'success');
                        }
                        // Reload trang để cập nhật giỏ hàng
                        window.location.reload();
                    })
                    .catch((error) => {
                        console.error('Error adding to cart:', error);
                        showCustomToast(error.message || 'Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
                        this.disabled = false;
                        this.innerHTML = originalText;
                    });
            });
        }
    }
}

function handleAccessoryAdd(button, csrf) {
    const productId = button.dataset.accessoryAdd;
    if (!productId) {
        return;
    }

    const hasVariants = button.dataset.accessoryHasVariants === '1';
    const variantsData = button.dataset.accessoryVariants ? JSON.parse(button.dataset.accessoryVariants) : [];

    // Nếu có variants, hiển thị modal
    if (hasVariants && variantsData.length > 0) {
        openAccessoryVariantModal(button, csrf, productId, variantsData);
        return;
    }

    // Nếu không có variants, thêm trực tiếp
    addAccessoryToCartDirect(productId, 1, button, csrf);
}

function openAccessoryVariantModal(button, csrf, productId, variantsData) {
    const modal = document.getElementById('accessory-variant-modal');
    if (!modal) {
        console.error('[Accessory Modal] Modal not found');
        // Nếu không có modal, thêm trực tiếp với variant đầu tiên
        if (variantsData.length > 0) {
            const firstVariant = variantsData[0];
            addAccessoryToCartDirect(productId, 1, button, csrf, firstVariant.id);
        } else {
            addAccessoryToCartDirect(productId, 1, button, csrf);
        }
        return;
    }

    const productName = button.dataset.accessoryName || '';
    const productImage = button.dataset.accessoryImage || '';
    const productPrice = parseFloat(button.dataset.accessoryPrice) || 0;
    const productSalePrice = button.dataset.accessorySalePrice ? parseFloat(button.dataset.accessorySalePrice) : null;

    // Hiển thị thông tin sản phẩm
    document.getElementById('accessory-modal-product-image').src = productImage;
    document.getElementById('accessory-modal-product-image').alt = productName;
    document.getElementById('accessory-modal-product-name').textContent = productName;
    
    // Hiển thị giá
    let priceHtml = '';
    if (productSalePrice && productSalePrice > 0 && productSalePrice < productPrice) {
        priceHtml = `<span style="color: #e6525e; font-weight: bold; font-size: 18px;">${formatCurrencyVND(productSalePrice)}₫</span> <span style="text-decoration: line-through; color: #999; font-size: 14px;">${formatCurrencyVND(productPrice)}₫</span>`;
    } else {
        priceHtml = `<span style="color: #e6525e; font-weight: bold; font-size: 18px;">${formatCurrencyVND(productPrice)}₫</span>`;
    }
    document.getElementById('accessory-modal-product-price').innerHTML = priceHtml;

    // Hiển thị variants
    const variantsSection = document.getElementById('accessory-modal-variants-section');
    const variantsList = document.getElementById('accessory-modal-variants-list');
    variantsList.innerHTML = '';

    if (variantsData.length > 0) {
        variantsSection.style.display = 'block';
        variantsData.forEach((variant, index) => {
            const variantBtn = document.createElement('button');
            variantBtn.type = 'button';
            variantBtn.className = 'xanhworld_variant_modal_variant_item' + (index === 0 ? ' active' : '');
            variantBtn.dataset.variantId = variant.id;
            variantBtn.dataset.variantPrice = variant.display_price || variant.price;
            variantBtn.dataset.variantStock = variant.stock_quantity ?? 'null';
            
            const attrs = variant.attributes || {};
            const details = [];
            if (attrs.size) details.push(attrs.size);
            if (attrs.has_pot === true || attrs.has_pot === '1' || attrs.has_pot === 1) details.push('Có chậu');
            if (attrs.combo_type) details.push(attrs.combo_type);
            if (attrs.notes) details.push(attrs.notes);
            const detailsText = details.length > 0 ? ' (' + details.join(', ') + ')' : '';

            let variantHtml = `<span class="variant-name">${variant.name}${detailsText}</span>`;
            variantHtml += `<span class="variant-price">${formatCurrencyVND(variant.display_price || variant.price)}₫</span>`;
            
            if (variant.sale_price && variant.sale_price > 0 && variant.sale_price < variant.price) {
                const discount = Math.round(((variant.price - variant.sale_price) / variant.price) * 100);
                variantHtml += `<span class="variant-discount">-${discount}%</span>`;
            }
            
            if (variant.stock_quantity !== null && variant.stock_quantity <= 0) {
                variantHtml += `<span class="variant-out-of-stock">Hết hàng</span>`;
                variantBtn.disabled = true;
                variantBtn.classList.add('disabled');
            }

            variantBtn.innerHTML = variantHtml;
            variantBtn.addEventListener('click', function() {
                if (this.disabled) return;
                document.querySelectorAll('#accessory-modal-variants-list .xanhworld_variant_modal_variant_item').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                updateAccessoryModalPrice(this.dataset.variantPrice);
                updateAccessoryModalStock(this.dataset.variantStock);
            });
            variantsList.appendChild(variantBtn);
        });

        // Chọn variant đầu tiên mặc định
        const firstVariant = variantsList.querySelector('.xanhworld_variant_modal_variant_item');
        if (firstVariant && !firstVariant.disabled) {
            updateAccessoryModalPrice(firstVariant.dataset.variantPrice);
            updateAccessoryModalStock(firstVariant.dataset.variantStock);
        }
    } else {
        variantsSection.style.display = 'none';
    }

    // Reset quantity
    document.getElementById('accessory-modal-quantity').value = 1;

    // Lưu thông tin để submit
    modal.dataset.currentProductId = productId;
    modal.dataset.currentCsrf = csrf;

    // Hiển thị modal
    // Đảm bảo body không bị lock từ trước
    document.body.style.overflow = '';
    // Thêm class active để hiển thị modal
    modal.classList.add('active');
    // Lock scroll sau khi modal hiển thị
    setTimeout(() => {
        document.body.style.overflow = 'hidden';
    }, 10);
}

function updateAccessoryModalPrice(price) {
    const priceEl = document.getElementById('accessory-modal-product-price');
    if (priceEl) {
        priceEl.innerHTML = `<span style="color: #e6525e; font-weight: bold; font-size: 18px;">${formatCurrencyVND(price)}₫</span>`;
    }
}

function updateAccessoryModalStock(stock) {
    const quantityInput = document.getElementById('accessory-modal-quantity');
    if (quantityInput && stock !== 'null' && stock !== null) {
        const maxStock = parseInt(stock) || 999;
        quantityInput.max = maxStock;
        if (parseInt(quantityInput.value) > maxStock) {
            quantityInput.value = maxStock;
        }
    }
}

// formatCurrencyVND đã được định nghĩa ở trên, không cần định nghĩa lại

function addAccessoryToCartDirect(productId, quantity, button, csrf, variantId = null) {
    const originalText = button.textContent;
    button.disabled = true;
    button.dataset.loadingText = originalText;
    button.textContent = "Đang thêm...";

    const requestBody = {
        product_id: productId,
        quantity: quantity,
    };
    
    if (variantId) {
        requestBody.product_variant_id = variantId;
    }

    fetch("/api/v1/cart/accessories", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": csrf,
        },
        body: JSON.stringify(requestBody),
    })
        .then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message =
                    data?.message ||
                    Object.values(data?.errors ?? {})?.[0]?.[0] ||
                    "Không thể thêm sản phẩm vào giỏ.";
                throw new Error(message);
            }

            updateCartCountBadge(data.cart_total_items ?? null);

            safeToast(
                data.message || "Đã thêm sản phẩm đi kèm vào giỏ hàng.",
                "success"
            );
        })
        .catch((error) => {
            safeToast(error.message || "Không thể thêm sản phẩm.", "error");
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = button.dataset.loadingText || originalText;
            delete button.dataset.loadingText;
        });
}

function updateCartCountBadge(count) {
    if (typeof count !== "number") {
        return;
    }

    document
        .querySelectorAll(".xanhworld_header_main_icon_cart_count")
        .forEach((badge) => {
            badge.textContent = count;
            if (count <= 0) {
                badge.classList.add("d-none");
            } else {
                badge.classList.remove("d-none");
            }
        });
}

[
    '.xanhworld_header_main_search_select',
].forEach(selector => {

    document.querySelectorAll(selector)?.forEach(el => {
        if (typeof SlimSelect === "function") {
            new SlimSelect({ select: el });
        } else {
            console.warn("SlimSelect is not available; skipping select enhancement.");
        }
    });

});

// ===== NEW THEME FUNCTIONALITY =====
document.addEventListener('DOMContentLoaded', function() {
    
    // ===== THUMBNAIL GALLERY SCROLL FUNCTIONALITY =====
    const thumbnailGallery = document.querySelector('.thumbnail-gallery');
    const scrollUpBtn = document.querySelector('.scroll-up');
    const scrollDownBtn = document.querySelector('.scroll-down');
    
    if (thumbnailGallery) {
        const scrollAmount = 88;
        
        function updateScrollButtons() {
            const atTop = thumbnailGallery.scrollTop === 0;
            const atBottom = Math.ceil(thumbnailGallery.scrollTop + thumbnailGallery.clientHeight) >= thumbnailGallery.scrollHeight;
            if (scrollUpBtn) scrollUpBtn.disabled = atTop;
            if (scrollDownBtn) scrollDownBtn.disabled = atBottom;
        }
        
        if (scrollUpBtn) {
            scrollUpBtn.addEventListener('click', function() {
                thumbnailGallery.scrollBy({ top: -scrollAmount, behavior: 'smooth' });
            });
        }
        
        if (scrollDownBtn) {
            scrollDownBtn.addEventListener('click', function() {
                thumbnailGallery.scrollBy({ top: scrollAmount, behavior: 'smooth' });
            });
        }
        
        thumbnailGallery.addEventListener('scroll', updateScrollButtons);
        updateScrollButtons();
        window.addEventListener('resize', updateScrollButtons);
    }
    
    // ===== IMAGE GALLERY FUNCTIONALITY =====
    const mainImage = document.getElementById('mainImage');
    const thumbnails = document.querySelectorAll('.thumbnail-item');
    
    // Set initial data attribute
    if (mainImage && !mainImage.getAttribute('data-current-image')) {
        mainImage.setAttribute('data-current-image', mainImage.src);
    }
    
    if (mainImage && thumbnails.length > 0) {
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Remove active from all thumbnails
                thumbnails.forEach(t => t.classList.remove('active'));
                
                // Add active to clicked thumbnail
                this.classList.add('active');
                
                // Update main image
                const newImageSrc = this.getAttribute('data-image');
                if (newImageSrc && mainImage) {
                    // Update src and srcset
                    mainImage.src = newImageSrc;
                    mainImage.srcset = newImageSrc + ' 500w';
                    mainImage.setAttribute('data-current-image', newImageSrc);
                }
            });
        });
        
        // Click on main image to open lightbox
        mainImage.addEventListener('click', function() {
            openImageLightbox();
        });
        
        // Also make main image container clickable
        const mainImageContainer = document.querySelector('.main-image-container');
        if (mainImageContainer) {
            mainImageContainer.style.cursor = 'pointer';
            mainImageContainer.addEventListener('click', function(e) {
                if (e.target === this || e.target === mainImage) {
                    openImageLightbox();
                }
            });
        }
    }
    
    // ===== IMAGE LIGHTBOX =====
    let currentLightboxIndex = 0;
    let lightboxImages = [];
    let touchStartX = 0;
    let touchEndX = 0;
    let isDragging = false;
    let startX = 0;
    let scrollLeft = 0;
    
    function initLightboxImages() {
        const thumbnails = document.querySelectorAll('.thumbnail-item');
        lightboxImages = Array.from(thumbnails).map(thumb => ({
            src: thumb.getAttribute('data-image'),
            alt: thumb.querySelector('img')?.alt || ''
        }));
        
        // Find current image index
        const currentSrc = mainImage?.getAttribute('data-current-image') || mainImage?.src;
        currentLightboxIndex = lightboxImages.findIndex(img => img.src === currentSrc);
        if (currentLightboxIndex === -1) currentLightboxIndex = 0;
    }
    
    function openImageLightbox() {
        initLightboxImages();
        if (lightboxImages.length === 0) return;
        
        // Create lightbox if not exists
        let lightbox = document.getElementById('product-lightbox');
        if (!lightbox) {
            lightbox = createLightbox();
            document.body.appendChild(lightbox);
        }
        
        updateLightboxImage();
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function createLightbox() {
        const lightbox = document.createElement('div');
        lightbox.id = 'product-lightbox';
        lightbox.className = 'product-lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-overlay"></div>
            <button class="lightbox-close" aria-label="Đóng">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="24" height="24">
                    <path fill="currentColor" d="M324.5 411.1c6.2 6.2 16.4 6.2 22.6 0s6.2-16.4 0-22.6L214.6 256 347.1 123.5c6.2-6.2 6.2-16.4 0-22.6s-16.4-6.2-22.6 0L192 233.4 59.5 100.9c-6.2-6.2-16.4-6.2-22.6 0s-6.2 16.4 0 22.6L169.4 256 36.9 388.5c-6.2 6.2-6.2 16.4 0 22.6s16.4 6.2 22.6 0L192 278.6 324.5 411.1z"/>
                </svg>
            </button>
            <button class="lightbox-prev" aria-label="Ảnh trước">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" width="24" height="24">
                    <path fill="currentColor" d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                </svg>
            </button>
            <button class="lightbox-next" aria-label="Ảnh sau">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" width="24" height="24">
                    <path fill="currentColor" d="M278.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-160 160c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L210.7 256 73.4 118.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l160 160z"/>
                </svg>
            </button>
            <div class="lightbox-content">
                <div class="lightbox-image-wrapper">
                    <img class="lightbox-image" src="" alt="" loading="lazy">
                    <div class="lightbox-loader"></div>
                </div>
                <div class="lightbox-info">
                    <div class="lightbox-counter">
                        <span class="lightbox-current">1</span> / <span class="lightbox-total">1</span>
                    </div>
                    <div class="lightbox-actions">
                        <button class="lightbox-download" aria-label="Tải ảnh">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20">
                                <path fill="currentColor" d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V274.7l-73.4-73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l128 128c12.5 12.5 32.8 12.5 45.3 0l128-128c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L288 274.7V32zM64 352c-35.3 0-64 28.7-64 64v32c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V416c0-35.3-28.7-64-64-64H346.5l-45.3 45.3c-25 25-65.5 25-90.5 0L165.5 352H64zm368 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/>
                            </svg>
                            Tải ảnh
                        </button>
                        <button class="lightbox-zoom-in" aria-label="Phóng to">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20">
                                <path fill="currentColor" d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288zm64-208c0-8.8-7.2-16-16-16s-16 7.2-16 16v48H192c-8.8 0-16 7.2-16 16s7.2 16 16 16h48v48c0 8.8 7.2 16 16 16s16-7.2 16-16V272h48c8.8 0 16-7.2 16-16s-7.2-16-16-16H288V144z"/>
                            </svg>
                        </button>
                        <button class="lightbox-zoom-out" aria-label="Thu nhỏ" style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20">
                                <path fill="currentColor" d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM192 176c0-8.8 7.2-16 16-16H288c8.8 0 16 7.2 16 16s-7.2 16-16 16H208c-8.8 0-16-7.2-16-16z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Event listeners
        lightbox.querySelector('.lightbox-overlay').addEventListener('click', closeLightbox);
        lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
        lightbox.querySelector('.lightbox-prev').addEventListener('click', () => navigateLightbox(-1));
        lightbox.querySelector('.lightbox-next').addEventListener('click', () => navigateLightbox(1));
        lightbox.querySelector('.lightbox-download').addEventListener('click', downloadImage);
        lightbox.querySelector('.lightbox-zoom-in').addEventListener('click', zoomIn);
        lightbox.querySelector('.lightbox-zoom-out').addEventListener('click', zoomOut);
        
        // Keyboard navigation
        document.addEventListener('keydown', handleLightboxKeyboard);
        
        // Touch/swipe support
        const imageWrapper = lightbox.querySelector('.lightbox-image-wrapper');
        imageWrapper.addEventListener('touchstart', handleTouchStart, { passive: true });
        imageWrapper.addEventListener('touchend', handleTouchEnd, { passive: true });
        imageWrapper.addEventListener('mousedown', handleMouseDown);
        
        return lightbox;
    }
    
    function updateLightboxImage() {
        const lightbox = document.getElementById('product-lightbox');
        if (!lightbox || lightboxImages.length === 0) return;
        
        const image = lightbox.querySelector('.lightbox-image');
        const loader = lightbox.querySelector('.lightbox-loader');
        const current = lightbox.querySelector('.lightbox-current');
        const total = lightbox.querySelector('.lightbox-total');
        const prevBtn = lightbox.querySelector('.lightbox-prev');
        const nextBtn = lightbox.querySelector('.lightbox-next');
        
        if (currentLightboxIndex < 0) currentLightboxIndex = 0;
        if (currentLightboxIndex >= lightboxImages.length) currentLightboxIndex = lightboxImages.length - 1;
        
        const currentImage = lightboxImages[currentLightboxIndex];
        
        // Show loader
        loader.style.display = 'block';
        image.style.opacity = '0';
        
        // Load image
        const img = new Image();
        img.onload = function() {
            image.src = currentImage.src;
            image.alt = currentImage.alt;
            image.style.opacity = '1';
            loader.style.display = 'none';
            // Reset zoom
            image.style.transform = 'scale(1)';
            lightbox.querySelector('.lightbox-zoom-out').style.display = 'none';
            lightbox.querySelector('.lightbox-zoom-in').style.display = 'block';
        };
        img.onerror = function() {
            loader.style.display = 'none';
            const noImagePath = document.querySelector('#mainImage')?.getAttribute('data-default-src')?.replace(/\/[^\/]+$/, '/no-image.webp') || '/clients/assets/img/clothes/no-image.webp';
            image.src = noImagePath;
        };
        img.src = currentImage.src;
        
        // Update counter
        if (current) current.textContent = currentLightboxIndex + 1;
        if (total) total.textContent = lightboxImages.length;
        
        // Update buttons
        if (prevBtn) prevBtn.style.display = currentLightboxIndex === 0 ? 'none' : 'flex';
        if (nextBtn) nextBtn.style.display = currentLightboxIndex === lightboxImages.length - 1 ? 'none' : 'flex';
    }
    
    function navigateLightbox(direction) {
        currentLightboxIndex += direction;
        if (currentLightboxIndex < 0) currentLightboxIndex = lightboxImages.length - 1;
        if (currentLightboxIndex >= lightboxImages.length) currentLightboxIndex = 0;
        updateLightboxImage();
    }
    
    function closeLightbox() {
        const lightbox = document.getElementById('product-lightbox');
        if (lightbox) {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
    
    function downloadImage() {
        const lightbox = document.getElementById('product-lightbox');
        if (!lightbox) return;
        
        const image = lightbox.querySelector('.lightbox-image');
        const imageSrc = image.src;
        const link = document.createElement('a');
        link.href = imageSrc;
        link.download = imageSrc.split('/').pop() || 'image.webp';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    let isZoomed = false;
    function zoomIn() {
        const lightbox = document.getElementById('product-lightbox');
        if (!lightbox) return;
        
        const image = lightbox.querySelector('.lightbox-image');
        image.style.transform = 'scale(2)';
        image.style.cursor = 'zoom-out';
        lightbox.querySelector('.lightbox-zoom-out').style.display = 'block';
        lightbox.querySelector('.lightbox-zoom-in').style.display = 'none';
        isZoomed = true;
    }
    
    function zoomOut() {
        const lightbox = document.getElementById('product-lightbox');
        if (!lightbox) return;
        
        const image = lightbox.querySelector('.lightbox-image');
        image.style.transform = 'scale(1)';
        image.style.cursor = 'zoom-in';
        lightbox.querySelector('.lightbox-zoom-out').style.display = 'none';
        lightbox.querySelector('.lightbox-zoom-in').style.display = 'block';
        isZoomed = false;
    }
    
    function handleLightboxKeyboard(e) {
        const lightbox = document.getElementById('product-lightbox');
        if (!lightbox || !lightbox.classList.contains('active')) return;
        
        switch(e.key) {
            case 'Escape':
                closeLightbox();
                break;
            case 'ArrowLeft':
                navigateLightbox(-1);
                break;
            case 'ArrowRight':
                navigateLightbox(1);
                break;
        }
    }
    
    function handleTouchStart(e) {
        touchStartX = e.touches[0].clientX;
    }
    
    function handleTouchEnd(e) {
        if (!isZoomed) {
            touchEndX = e.changedTouches[0].clientX;
            const diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    navigateLightbox(1); // Swipe left - next
                } else {
                    navigateLightbox(-1); // Swipe right - prev
                }
            }
        }
    }
    
    function handleMouseDown(e) {
        if (isZoomed) return;
        isDragging = true;
        startX = e.pageX - e.currentTarget.offsetLeft;
        scrollLeft = e.currentTarget.scrollLeft;
        e.preventDefault();
    }
    
    document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
        const lightbox = document.getElementById('product-lightbox');
        if (!lightbox) return;
        const imageWrapper = lightbox.querySelector('.lightbox-image-wrapper');
        if (!imageWrapper) return;
        const x = e.pageX - imageWrapper.offsetLeft;
        const walk = (x - startX) * 2;
        // Handle drag for navigation
    });
    
    document.addEventListener('mouseup', () => {
        isDragging = false;
    });
    
    // ===== TABS FUNCTIONALITY =====
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Ngăn scroll tự động
            const targetTab = this.getAttribute('data-tab');
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            this.classList.add('active');
            const targetPane = document.getElementById(targetTab);
            if (targetPane) targetPane.classList.add('active');
        });
    });
    
    // ===== QUANTITY CONTROLS =====
    const qtyInput = document.querySelector('.qty-input');
    const formQtyInput = document.getElementById('form_quantity_input');
    
    if (qtyInput) {
        const maxStock = parseInt(qtyInput.getAttribute('data-max-stock')) || 999;
        
        // Input validation only - buttons use onclick in HTML
        qtyInput.addEventListener('input', function() {
            let val = parseInt(this.value);
            if (isNaN(val) || val < 1) this.value = 1;
            else if (val > maxStock) this.value = maxStock;
            if (formQtyInput) formQtyInput.value = this.value;
        });
        
        qtyInput.addEventListener('blur', function() {
            if (!this.value || parseInt(this.value) < 1) this.value = 1;
            if (formQtyInput) formQtyInput.value = this.value;
        });
    }
    
    // ===== SMOOTH SCROLL FOR TABS - Đã tắt để tránh scroll tự động khi click tab =====
    // const tabsSection = document.querySelector('.product-tabs');
    // if (tabsSection && window.innerWidth < 768) {
    //     tabButtons.forEach(button => {
    //         button.addEventListener('click', function() {
    //             tabsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    //         });
    //     });
    // }
    
    // ===== STICKY PRODUCT INFO =====
    const productInfo = document.querySelector('.product-info');
    if (window.innerWidth > 992 && productInfo) {
        window.addEventListener('scroll', function() {
            const container = document.querySelector('.xanhworld_single_info.container');
            if (container && window.pageYOffset > container.offsetTop) {
                productInfo.style.top = '20px';
            }
        });
    }
    
    // ===== RESPONSIVE HANDLING =====
    function handleResize() {
        if (productInfo) {
            productInfo.style.position = window.innerWidth < 992 ? 'static' : 'sticky';
        }
    }
    window.addEventListener('resize', handleResize);
    handleResize();
});