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
    let menuScrollTimer = null;

    window.addEventListener("scroll", () => {
        clearTimeout(menuScrollTimer);

        menuScrollTimer = setTimeout(() => {
            if (window.scrollY > 240) {
                mainMenu.classList.add("xanhworld_header_main_nav_fixed");
            } else {
                mainMenu.classList.remove("xanhworld_header_main_nav_fixed");
            }
        }, 200); // ⏱ chạy sau khi dừng cuộn 200ms
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

    let scrollTimer = null;

    window.addEventListener("scroll", () => {
        clearTimeout(scrollTimer);

        scrollTimer = setTimeout(() => {
            if (window.scrollY > 300) {
                backToTopBtn.style.display = "flex";

                const orderSummary = document.querySelector(".xanhworld_order_summary");
                if (orderSummary) {
                    orderSummary.classList.add("shop_haiphonglife_order_summary_fixed");
                }

            } else {
                backToTopBtn.style.display = "none";

                const orderSummary = document.querySelector(".xanhworld_order_summary");
                if (orderSummary) {
                    orderSummary.classList.remove("shop_haiphonglife_order_summary_fixed");
                }
            }
        }, 200); // ⏱ delay 200ms sau khi người dùng dừng cuộn
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
            popup.classList.toggle("is-open");
            if (popup.classList.contains("is-open")) {
                setTimeout(() => textarea.focus(), 150);
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

        closeButton.addEventListener("click", () => popup.classList.remove("is-open"));

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
    if (tabButtons[2]) {
        tabButtons[2]?.click();
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
);
const qtyWrapper = document.querySelector(
    ".xanhworld_single_info_specifications_actions_qty"
);
const qtyInputField = document.querySelector("input[name='quantity']");
const qtyMax = parseInt(
    qtyWrapper?.dataset.maxStock || qtyInputField?.dataset.maxStock || 99,
    10
);

function safeToast(message, type = "info") {
    if (typeof showCustomToast === "function") {
        showCustomToast(message, type);
    }
}

function syncQuantity(val) {
    if (qtyDisplay) {
        qtyDisplay.textContent = val;
    }
    if (qtyInputField) {
        qtyInputField.value = val;
    }
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

    xanhworldOverlay.appendChild(xanhworldBtnPrev);
    xanhworldOverlay.appendChild(xanhworldBtnNext);
    xanhworldOverlay.appendChild(xanhworldBtnClose);

    // === CLICK THUMBNAIL TO OPEN OVERLAY (TÍCH HỢP VÀO CODE CŨ) ===
    document
        .querySelectorAll(".xanhworld_single_info_images_main img")
        .forEach((thumb, index) => {
            thumb.addEventListener("click", () => {
                xanhworldCurrentIndex = index;
                xanhworldOverlay.style.display = "flex";

                setTimeout(() => {
                    xanhworldOverlay.classList.add("xanhworld_show");
                }, 10);

                xanhworldUpdatePosition();
            });
        });
    // === OPEN OVERLAY ===
    document
        .querySelectorAll(
            ".xanhworld_single_info_images_main_overlay_image"
        )
        .forEach((xanhworldImg, i) => {
            xanhworldImg.addEventListener("click", () => {
                xanhworldCurrentIndex = i;
                xanhworldOverlay.style.display = "flex";
                setTimeout(
                    () => xanhworldOverlay.classList.add("xanhworld_show"),
                    10
                );
                xanhworldUpdatePosition();
            });
        });

    // === UPDATE POSITION ===
    function xanhworldUpdatePosition() {
        xanhworldOverlayImagesWrapper.style.transform = `translateX(-${
            xanhworldCurrentIndex * 100
        }vw)`;
    }

    // === NEXT / PREV ===
    xanhworldBtnNext.addEventListener("click", () => {
        xanhworldCurrentIndex =
            (xanhworldCurrentIndex + 1) % xanhworldOverlayImageItems.length;
        xanhworldUpdatePosition();
    });

    xanhworldBtnPrev.addEventListener("click", () => {
        xanhworldCurrentIndex =
            (xanhworldCurrentIndex - 1 + xanhworldOverlayImageItems.length) %
            xanhworldOverlayImageItems.length;
        xanhworldUpdatePosition();
    });

    // === CLOSE ===
    xanhworldBtnClose.addEventListener("click", xanhworldCloseOverlay);

    xanhworldOverlay.addEventListener("click", (e) => {
        if (e.target === xanhworldOverlay) xanhworldCloseOverlay();
    });

    function xanhworldCloseOverlay() {
        xanhworldOverlay.classList.remove("xanhworld_show");
        setTimeout(() => (xanhworldOverlay.style.display = "none"), 200);
    }

    // === ESC TO CLOSE ===
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") xanhworldCloseOverlay();
    });

    // === MOBILE SWIPE ===
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

    // === DOUBLE TAP TO ZOOM ===
    let xanhworldLastTap = 0;

    xanhworldOverlay.addEventListener("touchend", () => {
        const now = Date.now();
        if (now - xanhworldLastTap < 250) {
            xanhworldOverlay.classList.toggle("xanhworld_zoom_active");
        }
        xanhworldLastTap = now;
    });

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
}

function handleAccessoryAdd(button, csrf) {
    const productId = button.dataset.accessoryAdd;
    if (!productId) {
        return;
    }

    const originalText = button.textContent;
    button.disabled = true;
    button.dataset.loadingText = originalText;
    button.textContent = "Đang thêm...";

    fetch("/api/v1/cart/accessories", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": csrf,
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1,
        }),
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