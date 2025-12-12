@extends('clients.layouts.master')

@section('title', 'Chính sách bán hàng - ' . ($settings->site_name ?? $settings->subname ?? 'XWorld'))

@section('head')

    <meta name="description"

          content="Chính sách bán hàng {{ $settings->site_name ?? 'XWorld' }} - cam kết chất lượng cây cảnh, giao hàng, ưu đãi và chăm sóc khách hàng chuyên nghiệp.">

    <link rel="canonical" href="{{ url()->current() }}">

@endsection

@push('styles')

    @include('clients.pages.policy.partials.styles')

@endpush

@section('content')

    <div class="policy-page">

        <section class="policy-hero">

            <div class="policy-tags">

                <span class="policy-tag">Sales Policy</span>

                <span class="policy-tag">Premium service</span>

            </div>

            <h1>Chính sách bán hàng</h1>

            <p>

                <strong>XWorld</strong> cam kết mang đến trải nghiệm mua sắm đẳng cấp: cây cảnh chất lượng cao,

                thông tin minh bạch, dịch vụ tư vấn tận tâm và bảo chứng hậu mãi rõ ràng trên mọi kênh bán hàng.

            </p>

            <div class="policy-meta">

                <div class="policy-meta-card">

                    <span>Cam kết chất lượng</span>

                    <strong>100% cây khỏe mạnh</strong>

                </div>

                    <div class="policy-meta-card">

                    <span>Miễn phí giao hàng</span>

                    <strong>Từ 500.000đ</strong>

                </div>

                <div class="policy-meta-card">

                    <span>CSKH</span>

                    <strong>24/7</strong>

                </div>

            </div>

        </section>

        <section class="policy-section">

            <h2>Cam kết chất lượng cây xanh</h2>

            <ul class="policy-list">

                <li>Cây cảnh được chọn lọc kỹ lưỡng, đảm bảo khỏe mạnh, không sâu bệnh.</li>

                <li>Hình ảnh hiển thị khớp 95–100% với cây thực tế (kích thước, màu sắc lá, dáng cây).</li>

                <li>Cây được kiểm định chất lượng, có nguồn gốc rõ ràng, không bán cây bệnh, cây yếu.</li>

                <li>Tư vấn chọn cây phù hợp với không gian sống và điều kiện ánh sáng của khách hàng.</li>

            </ul>

        </section>

        <section class="policy-section">

            <h2>Giao hàng & chăm sóc đơn</h2>

            <p><strong>XWorld</strong> giao hàng nhanh – an toàn trên toàn quốc.</p>

            <ul class="policy-list">

                <li><strong>Hà Nội – TP.HCM:</strong> 1 – 2 ngày.</li>

                <li><strong>Các tỉnh khác:</strong> 2 – 5 ngày.</li>

                <li>Cho phép kiểm hàng trước thanh toán (tùy khu vực hỗ trợ COD).</li>

                <li>Đóng gói chống sốc, chống ẩm kỹ lưỡng để đảm bảo cây không bị hư hại.</li>

                <li>Miễn phí giao hàng cho đơn từ <strong>500.000đ</strong>.</li>

            </ul>

            <div class="policy-note">

                Phí ship dao động 20.000 – 50.000đ tùy tỉnh và kích thước cây, hiển thị rõ ràng ở bước Checkout.

            </div>

        </section>

        <section class="policy-section">

            <h2>Ưu đãi & quyền lợi khách hàng</h2>

            <div class="policy-grid">

                <div class="policy-card">

                    <strong>Voucher khách hàng thân thiết</strong>

                    <p>Tặng mã giảm giá cho đơn kế tiếp và chương trình tích điểm đổi quà.</p>

                </div>

                <div class="policy-card">

                    <strong>Sinh nhật & khách hàng VIP</strong>

                    <p>Ưu đãi đặc biệt theo hạng thành viên và dịp sinh nhật, tặng kèm phân bón hoặc chậu cây.</p>

                </div>

                <div class="policy-card">

                    <strong>Sự kiện mùa lễ & phong thủy</strong>

                    <p>Voucher riêng cho Tết, lễ hội, và các dịp đặc biệt về phong thủy.</p>

                </div>

            </div>

        </section>

        <section class="policy-section">

            <h2>Tư vấn & hỗ trợ</h2>

            <ul class="policy-list">

                <li>Tư vấn chọn cây phù hợp với không gian và ánh sáng.</li>

                <li>Hỗ trợ xem cây trực tiếp tại showroom, đổi cây tương đương nếu không hài lòng.</li>

                <li>Giải đáp về cách chăm sóc, tưới nước, bón phân, vị trí đặt cây.</li>

                <li>Xử lý khiếu nại nhanh chóng, chuyên nghiệp.</li>

            </ul>

        </section>

        <section class="policy-section">

            <h2>Chính sách đổi trả</h2>

            <ul class="policy-list">

                <li>Đổi hàng trong vòng <strong>15 ngày</strong>.</li>

                <li>Cây còn nguyên vẹn, chưa thay chậu, không héo úa, không hư hỏng.</li>

                <li>Đổi cây cùng hoặc cao hơn giá trị (cùng loại hoặc loại tương đương).</li>

                <li>Không hoàn tiền trừ trường hợp lỗi kỹ thuật.</li>

                <li>Không áp dụng cho cây giảm giá trên 30%, cây mini, sen đá, phụ kiện.</li>

            </ul>

            <p style="margin-top: 12px; font-weight: 600;">Đổi do lỗi nhà cung cấp:</p>

            <ul class="policy-list">

                <li>Cây héo chết do vận chuyển, chậu vỡ, sai giống loại, sâu bệnh phát hiện sớm.</li>

                <li>Đổi mới 100% trong 15 ngày và miễn phí vận chuyển.</li>

            </ul>

        </section>

        <section class="policy-contact">

            <h3>Liên hệ hỗ trợ</h3>

            <p>📞 Hotline: <a href="tel:{{ $settings->contact_phone ?? '' }}">{{ $settings->contact_phone ?? '' }}</a></p>

            <p>✉ Email: <a href="mailto:{{ $settings->contact_email ?? '' }}">{{ $settings->contact_email ?? '' }}</a></p>

            <p>🌐 Website: <a href="{{ $settings->site_url ?? '#' }}">{{ $settings->site_name ?? 'XWorld' }}</a></p>

            <p>🛒 Fanpage: <a href="{{ $settings->facebook_link ?? '#' }}" target="_blank">Facebook XWorld</a></p>

        </section>

        <p class="policy-updated">

            Cảm ơn bạn đã đồng hành cùng XWorld. Chính sách bán hàng hiệu lực từ 01/11/2025 và sẽ tiếp tục được cập nhật

            để nâng cao chất lượng dịch vụ.

        </p>

    </div>

@endsection
