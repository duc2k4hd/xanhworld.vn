@php
    $aiChatEndpoint = route('client.ai.chat');
@endphp
<!-- Chat -->
<section>
    <div class="xanhworld_chat">
        <div class="xanhworld_back_to_top">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path
                    d="M270.7 9.7C268.2 3.8 262.4 0 256 0s-12.2 3.8-14.7 9.7L197.2 112.6c-3.4 8-5.2 16.5-5.2 25.2l0 77-144 84L48 280c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 56 0 32 0 24c0 13.3 10.7 24 24 24s24-10.7 24-24l0-8 144 0 0 32.7L133.5 468c-3.5 3-5.5 7.4-5.5 12l0 16c0 8.8 7.2 16 16 16l96 0 0-64c0-8.8 7.2-16 16-16s16 7.2 16 16l0 64 96 0c8.8 0 16-7.2 16-16l0-16c0-4.6-2-9-5.5-12L320 416.7l0-32.7 144 0 0 8c0 13.3 10.7 24 24 24s24-10.7 24-24l0-24 0-32 0-56c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 18.8-144-84 0-77c0-8.7-1.8-17.2-5.2-25.2L270.7 9.7z" />
            </svg>
        </div>
        <!-- Zalo -->
        <a href="https://zalo.me/{{ $settings->contact_zalo ?? '' }}" aria-label="Zalo" alt="Zalo" title="Zalo" target="_blank" class="xanhworld_chat_zalo" rel="noreferrer">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path
                    d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z" />
            </svg>
        </a>
        <!-- Gọi điện -->
        <a href="tel:{{ $settings->contact_phone ?? '' }}" class="xanhworld_chat_phone" aria-label="Phone" alt="Phone" title="Phone">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path
                    d="M256.6 8C116.5 8 8 110.3 8 248.6c0 72.3 29.7 134.8 78.1 177.9 8.4 7.5 6.6 11.9 8.1 58.2A19.9 19.9 0 0 0 122 502.3c52.9-23.3 53.6-25.1 62.6-22.7C337.9 521.8 504 423.7 504 248.6 504 110.3 396.6 8 256.6 8zm149.2 185.1l-73 115.6a37.4 37.4 0 0 1 -53.9 9.9l-58.1-43.5a15 15 0 0 0 -18 0l-78.4 59.4c-10.5 7.9-24.2-4.6-17.1-15.7l73-115.6a37.4 37.4 0 0 1 53.9-9.9l58.1 43.5a15 15 0 0 0 18 0l78.4-59.4c10.4-8 24.1 4.5 17.1 15.6z" />
            </svg>
        </a>
        <!-- Trợ lý AI -->
        <a href="#" title="Trợ lý AI dành riêng cho {{ $settings->site_name }}" class="xanhworld_chat_facebook" data-ai-chat-trigger aria-label="Trợ lý AI dành riêng cho {{ $settings->site_name }}" alt="Trợ lý AI dành riêng cho {{ $settings->site_name }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" aria-hidden="true">
                <path
                    d="M320 0c17.7 0 32 14.3 32 32l0 64 120 0c39.8 0 72 32.2 72 72l0 272c0 39.8-32.2 72-72 72l-304 0c-39.8 0-72-32.2-72-72l0-272c0-39.8 32.2-72 72-72l120 0 0-64c0-17.7 14.3-32 32-32zM208 384c-8.8 0-16 7.2-16 16s7.2 16 16 16l32 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-32 0zm96 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l32 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-32 0zm96 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l32 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-32 0zM264 256a40 40 0 1 0 -80 0 40 40 0 1 0 80 0zm152 40a40 40 0 1 0 0-80 40 40 0 1 0 0 80zM48 224l16 0 0 192-16 0c-26.5 0-48-21.5-48-48l0-96c0-26.5 21.5-48 48-48zm544 0c26.5 0 48 21.5 48 48l0 96c0 26.5-21.5 48-48 48l-16 0 0-192 16 0z" />
            </svg>
        </a>
        
        <script>
            document.querySelector('.xanhworld_chat_facebook').addEventListener('click', (e) => e.preventDefault());
        </script>
    </div>

    <div class="xanhworld_chat_popup" id="xanhworldChatPopup" data-endpoint="{{ $aiChatEndpoint }}">
        <div class="xanhworld_chat_panel" role="dialog" aria-modal="true" aria-label="Trợ lý THẾ GIỚI CÂY XANH XWORLD">
            <div class="xanhworld_chat_header">
                <div>
                    <p class="xanhworld_chat_title">Trợ lý cây xanh XWorld</p>
                    <p class="xanhworld_chat_desc">Hỏi mọi thứ về sản phẩm, bài viết, cách chăm cây</p>
                </div>
                <button type="button" class="xanhworld_chat_close" aria-label="Đóng trợ lý">&times;</button>
            </div>
            <div class="xanhworld_chat_messages" aria-live="polite">
                <div class="xanhworld_chat_message is-assistant">
                    Xin chào! Bạn đang cần tư vấn cây cảnh, decor hay muốn tìm hiểu bài viết nào? Mình có thể dùng dữ liệu sản phẩm & bài viết mới nhất để trả lời ngay.
                </div>
            </div>
            <form class="xanhworld_chat_form" autocomplete="off">
                <label class="sr-only" for="xanhworldChatInput">Nhập câu hỏi</label>
                <textarea id="xanhworldChatInput" name="question" rows="2" placeholder="Nhập câu hỏi (ví dụ: Cây nào hợp bàn làm việc nhiều sáng?)" minlength="5"></textarea>
                <div class="xanhworld_chat_form_actions">
                    <span class="xanhworld_chat_hint">Nhập tối thiểu 5 ký tự. Bạn có thể hỏi bằng tiếng Việt.</span>
                    <button type="submit" class="xanhworld_chat_send" disabled>Gửi</button>
                </div>
            </form>
        </div>
    </div>
</section>