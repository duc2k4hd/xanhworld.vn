@extends('clients.layouts.master')

@section('title', 'Chính sách bảo hành - ' . ($settings->site_name ?? $settings->subname ?? 'XWorld'))

@section('head')

    <meta name="description"

          content="Chính sách bảo hành {{ $settings->site_name ?? 'XWorld' }} - phạm vi áp dụng, điều kiện bảo hành và quy trình xử lý chi tiết cho cây cảnh.">

    <link rel="canonical" href="{{ url()->current() }}">

@endsection

@push('js_page')
    <script defer src="{{ asset('clients/assets/js/main.js') }}"></script>
@endpush

@push('styles')

    @include('clients.pages.policy.partials.styles')

@endpush

@section('content')

    <div class="policy-page">

        <section class="policy-hero">

            <div class="policy-tags">

                <span class="policy-tag">Warranty</span>

                <span class="policy-tag">After-sale care</span>

            </div>

            <h1>Chính sách bảo hành</h1>

            <p>

                Cảm ơn bạn đã tin tưởng lựa chọn <strong>{{ $settings->site_name ?? $settings->subname ?? 'XWorld' }}</strong>.

                Chính sách này áp dụng cho tất cả đơn hàng mua tại showroom, website và các kênh chính thức của XWorld.

            </p>

        </section>

        <section class="policy-section">

            <h2>1. Phạm vi áp dụng</h2>

            <ul class="policy-list">

                <li>Cây cảnh bị héo úa, chết do lỗi vận chuyển hoặc đóng gói không đúng cách.</li>

                <li>Cây bị sâu bệnh phát hiện trong vòng 7 ngày đầu sau khi nhận hàng.</li>

                <li>Chậu cây bị vỡ, nứt do lỗi đóng gói hoặc vận chuyển.</li>

                <li>Sai giống loại cây so với đơn đặt hàng.</li>

                <li>Cây không khỏe mạnh, yếu ớt do lỗi từ nhà cung cấp.</li>

            </ul>

        </section>

        <section class="policy-section">

            <h2>2. Thời hạn bảo hành</h2>

            <ul class="policy-list">

                <li><strong>7 ngày</strong> kể từ ngày mua trực tiếp tại showroom.</li>

                <li><strong>7 ngày</strong> kể từ ngày nhận hàng online.</li>

            </ul>

            <div class="policy-note">Vui lòng giữ hóa đơn hoặc mã đơn hàng để được hỗ trợ nhanh chóng.</div>

        </section>

        <section class="policy-section">

            <h2>3. Điều kiện bảo hành</h2>

            <ul class="policy-list">

                <li>Cây còn nguyên vẹn, chưa thay chậu hoặc tách chiết.</li>

                <li>Chưa qua xử lý hóa chất hoặc phân bón không đúng cách gây hư hại.</li>

                <li>Không bị héo úa do thiếu nước hoặc chăm sóc sai cách.</li>

                <li>Không bị gãy cành, hỏng lá do tác động bên ngoài.</li>

                <li>Có hóa đơn mua hàng hoặc mã đơn hợp lệ.</li>

            </ul>

        </section>

        <section class="policy-section">

            <h2>4. Trường hợp không áp dụng</h2>

            <ul class="policy-list">

                <li>Cây héo úa do khách hàng không tưới nước đúng cách hoặc để thiếu ánh sáng.</li>

                <li>Cây chết do đặt sai vị trí (quá nắng, quá tối, gần nguồn nhiệt).</li>

                <li>Tự ý thay chậu, tách chiết hoặc cắt tỉa không đúng cách.</li>

                <li>Cây bị sâu bệnh do môi trường sống của khách hàng.</li>

                <li>Mất hóa đơn hoặc không xác minh được lịch sử mua.</li>

                <li>Cây giảm giá trên 30%, cây mini, sen đá, phụ kiện.</li>

            </ul>

        </section>

        <section class="policy-section">

            <h2>5. Quy trình tiếp nhận</h2>

            <div class="policy-timeline">

                <div class="policy-timeline-item"><strong>Bước 1:</strong> Liên hệ hotline/inbox/email mô tả tình trạng cây.</div>

                <div class="policy-timeline-item"><strong>Bước 2:</strong> Xác minh đơn hàng và hướng dẫn gửi hình ảnh hoặc mang cây đến showroom.</div>

                <div class="policy-timeline-item"><strong>Bước 3:</strong> Nhân viên kiểm tra tình trạng trong 1–2 ngày.</div>

                <div class="policy-timeline-item"><strong>Bước 4:</strong> Đổi cây mới tương đương hoặc hoàn tiền nếu hết hàng.</div>

            </div>

        </section>

        <section class="policy-section">

            <h2>6. Chi phí & thời gian</h2>

            <ul class="policy-list">

                <li>Miễn phí 100% với lỗi từ XWorld hoặc vận chuyển.</li>

                <li>Khách chịu phí vận chuyển khi lỗi do chăm sóc sai cách hoặc quá thời hạn.</li>

                <li>Thời gian xử lý: tối thiểu 1 ngày, tối đa 3 ngày làm việc.</li>

            </ul>

        </section>

        <section class="policy-contact">

            <h3>Liên hệ hỗ trợ</h3>

            <p>📞 Hotline: <a href="tel:{{ $settings->contact_phone ?? '' }}">{{ $settings->contact_phone ?? '' }}</a></p>

            <p>✉ Email: <a href="mailto:{{ $settings->contact_email ?? '' }}">{{ $settings->contact_email ?? '' }}</a></p>

            <p>🌐 Website: <a href="{{ $settings->site_url ?? '#' }}">{{ $settings->site_name ?? 'XWorld' }}</a></p>

        </section>

        <p class="policy-updated">Chính sách bảo hành có hiệu lực từ ngày 01/11/2025 và sẽ được cập nhật để nâng cao quyền lợi khách hàng.</p>

    </div>

@endsection
