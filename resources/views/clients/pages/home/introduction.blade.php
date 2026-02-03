@extends('clients.layouts.master')

@section('title', 'Giới thiệu ' .($settings->site_name ?? $settings->site_name ?? 'Thế giới cây xanh Xworld'). ' | Cây cảnh & giải pháp cây xanh')

@section('head')
    <meta name="robots" content="index, follow" />
    <meta name="description"
        content="{{ $settings->site_name ?? $settings->site_name ?? 'Thế giới cây xanh Xworld' }} dẫn đầu giải pháp cây phong thủy, cây để bàn, cây trang trí nội thất và cảnh quan cho doanh nghiệp Việt." />
    <meta property="og:title" content="Giới thiệu {{ $settings->site_name ?? $settings->site_name ?? 'Thế giới cây xanh Xworld' }} - Giải pháp cây xanh trọn gói" />
    <meta property="og:description"
        content="Khám phá hệ sinh thái {{ $settings->site_name ?? $settings->site_name ?? 'Thế giới cây xanh Xworld' }}: tư vấn, cung cấp, bảo dưỡng và thiết kế cảnh quan cây xanh chuyên nghiệp." />
    <meta property="og:image"
        content="{{ asset('clients/assets/img/business/' . ($settings->site_banner ?? $settings->site_logo ?? 'no-image.webp')) }}" />
    <meta property="og:url" content="{{ route('client.introduction.index') }}" />
    <link rel="canonical" href="{{ route('client.introduction.index') }}">
@endsection

@push('js_page')
    <script defer src="{{ asset('clients/assets/js/main.js') }}"></script>
@endpush

@section('content')
    <section class="xworld-hero">
        <div class="xworld-hero__content">
            <p class="eyebrow">{{ $settings->site_name ?? $settings->site_name ?? 'Thế giới cây xanh Xworld' }} Ecosystem</p>
            <h1>{{ ($settings->site_name ?? 'Thế giới cây xanh Xworld') . ' - Cây cảnh & giải pháp cây xanh' }}</h1>
            <p>
                Từ cây phong thủy văn phòng, tiểu cảnh sân vườn đến dịch vụ chăm sóc định kỳ, {{ $settings->site_name ?? $settings->site_name ?? 'Thế giới cây xanh Xworld' }} đồng hành cùng
                doanh nghiệp và gia đình kiến tạo môi trường sống giàu năng lượng.
            </p>
            <p style="font-size:16px; line-height:1.8; color: white; margin-bottom:16px; margin-top:16px;">
                Trong quá trình tìm hiểu thông tin, nhiều khách hàng của 
                <strong style="color:#1b7f5a;">Thế giới cây xanh Xworld</strong> thường đặt câu hỏi:
                <em>“Vì sao khi tìm kiếm XWORLD trên Google lại xuất hiện một trò chơi, nhưng không thấy website của công ty?”</em>
            </p>
            
            <p style="font-size:16px; line-height:1.8; color: white; margin-bottom:16px;">
                Chúng tôi xin làm rõ: <strong>XWORLD</strong> là thương hiệu hoạt động trong lĩnh vực 
                <strong>cây xanh và cảnh quan</strong>, chuyên cung cấp các sản phẩm và dịch vụ như 
                cây xanh nội – ngoại thất, chăm sóc và bảo dưỡng cây, thiết kế – thi công cảnh quan,
                cùng nhiều giải pháp xanh dành cho doanh nghiệp và hộ gia đình.
            </p>
            
            <p style="font-size:16px; line-height:1.8; color: white; margin-bottom:16px;">
                Website chính thức của <strong>Thế giới cây xanh Xworld</strong> hiện đang hoạt động tại:
                <a href="{{ $settings->site_url ?? '#' }}" 
                   target="_blank" 
                   rel="noopener"
                   style="color:#1b7f5a; font-weight:600; text-decoration:none;">
                    {{ $settings->site_name ?? 'xanhworld.vn' }}
                </a>
            </p>
            
            <p style="font-size:16px; line-height:1.8; color: white;">
                Tên gọi <strong>XWORLD</strong> được viết tắt từ 
                <strong>“Thế Giới Cây Xanh”</strong>, gắn liền với tên miền 
                <strong>xanhworld.vn</strong>. 
                Việc trùng tên với một tựa game trên thị trường chỉ là sự trùng hợp ngẫu nhiên, 
                không liên quan đến lĩnh vực hoạt động hay định hướng thương hiệu của XWORLD.
            </p>            
            <div class="hero-actions">
                <a class="btn primary" href="{{ route('client.shop.index') }}">Khám phá sản phẩm</a>
                <a class="btn ghost" href="{{ route('client.contact.index') }}">Đặt lịch tư vấn</a>
            </div>
            <ul class="hero-stats">
                <li>
                    <strong>650+</strong>
                    <span>Dự án cây xanh đã triển khai</span>
                </li>
                <li>
                    <strong>24h</strong>
                    <span>Phản hồi & khảo sát toàn quốc</span>
                </li>
                <li>
                    <strong>18</strong>
                    <span>Nhà vườn & kho cây liên kết</span>
                </li>
            </ul>
        </div>
        <div class="xworld-hero__media">
            <img src="{{ asset('clients/assets/img/business/' . ($settings->site_banner ?? $settings->site_logo ?? 'no-image.webp')) }}"
                alt="Không gian trưng bày {{ $settings->site_name ?? $settings->site_name ?? 'Thế giới cây xanh Xworld' }}" loading="lazy" />
            <div class="media-badge">
                <p>GREEN CONCIERGE</p>
                <h4>Thiết kế - Thi công - Bảo dưỡng</h4>
                <span>Đội ngũ kỹ sư cây xanh đạt chứng chỉ SFA</span>
            </div>
        </div>
    </section>

    <section class="xworld-panels">
        <article class="panel highlight">
            <p class="eyebrow">Sứ mệnh</p>
            <h3>Tái tạo bầu không khí xanh cho đô thị Việt</h3>
            <p>Đưa cây xanh trở thành yếu tố cốt lõi trong kiến trúc, nâng cao trải nghiệm sống và làm việc.</p>
        </article>
        <article class="panel">
            <p class="eyebrow">Tầm nhìn</p>
            <h3>Trở thành đối tác cây xanh chiến lược</h3>
            <p>Kết nối chuỗi cung ứng cây cảnh chất lượng, dịch vụ chuẩn quốc tế và giải pháp số hóa quản lý cây.</p>
        </article>
        <article class="panel">
            <p class="eyebrow">Giá trị cốt lõi</p>
            <ul>
                <li>Đúng cam kết, minh bạch tiến độ.</li>
                <li>Thiết kế thẩm mỹ  chuẩn phong thủy.</li>
                <li>Phát triển bền vững, bảo vệ môi trường.</li>
            </ul>
        </article>
    </section>

    <section class="xworld-journey">
        <div class="journey-content">
            <h2>Hành trình {{ $settings->site_name ?? $settings->site_name ?? 'Thế giới cây xanh Xworld' }}</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <span class="year">2019</span>
                    <p>Thành lập vườn ươm đầu tiên tại {{ $settings->city }}, cung cấp cây trang trí cho 200 hộ gia đình.</p>
                </div>
                <div class="timeline-item">
                    <span class="year">2021</span>
                    <p>Ra mắt dịch vụ Green Concierge: thuê cây văn phòng & bảo dưỡng định kỳ.</p>
                </div>
                <div class="timeline-item">
                    <span class="year">2023</span>
                    <p>Ký kết với 30 doanh nghiệp lớn trong lĩnh vực F&B, khách sạn, co-working.</p>
                </div>
                <div class="timeline-item">
                    <span class="year">2025</span>
                    <p>Ứng dụng IoT giám sát dinh dưỡng và độ ẩm cho hệ thống cây nội thất cao cấp.</p>
                </div>
            </div>
        </div>
        <div class="journey-media">
            <img src="{{ asset('clients/assets/img/other/xworld-garden-journey.jpg') }}" alt="Hành trình phát triển XWorld Garden"
                loading="lazy">
            <div class="media-caption">
                <strong>Green Lab</strong>
                <span>Nghiên cứu giống cây bản địa & giải pháp trồng theo môi trường ánh sáng thực tế.</span>
            </div>
        </div>
    </section>

    <section class="xworld-grid">
        <article>
            <h3>Thiết kế cảnh quan</h3>
            <p>Render 3D chi tiết, lựa chọn cây theo bố cục kiến trúc và phong thủy hướng sinh khí.</p>
        </article>
        <article>
            <h3>Cây phong thủy & để bàn</h3>
            <p>Bonsai, sen đá, monstera, trầu bà nam mỹ, được kiểm định sâu bệnh và tư vấn theo mệnh.</p>
        </article>
        <article>
            <h3>Thuê cây sự kiện</h3>
            <p>Gói decor theo chủ đề, bao gồm vận chuyển, set up, nhặt lá và tháo dỡ đúng tiến độ.</p>
        </article>
        <article>
            <h3>Chăm sóc định kỳ</h3>
            <p>Lịch bảo dưỡng linh hoạt, nhật ký số hóa, cảnh báo thay chậu  thay đất qua ứng dụng.</p>
        </article>
    </section>

    <section class="xworld-network">
        <div class="network-card">
            <p class="eyebrow">Hệ sinh thái XWorld</p>
            <h2>Showroom & trung tâm trải nghiệm</h2>
            <ul>
                <li>Trung tâm Green Studio tại TP.HCM, Hà Nội, Đà Nẵng.</li>
                <li>Phòng tư vấn phong thủy  đo ánh sáng  phối chậu.</li>
                <li>Khu trải nghiệm cây để bàn, sản phẩm chăm cây hữu cơ.</li>
            </ul>
        </div>
        <div class="network-card gradient">
            <h3>Kết nối đa kênh</h3>
            <div class="channel">
                <span>Website</span>
                <a href="{{ $settings->site_url ?? '#' }}" target="_blank">{{ $settings->site_name ?? 'xworld.vn' }}</a>
            </div>
            <div class="channel">
                <span>Marketplace & Social</span>
                <p>Shopee Mall  TikTok Shop  Facebook Live Garden</p>
            </div>
            <div class="channel">
                <span>Giải pháp doanh nghiệp</span>
                <p>Combo decor lễ hội, quà tặng cây phong thủy, chăm sóc cây văn phòng.</p>
            </div>
        </div>
    </section>

    <section class="xworld-impact">
        <div class="impact-card">
            <p class="eyebrow">Bền vững</p>
            <h3>Trách nhiệm với cộng đồng xanh</h3>
            <ul>
                <li>Tái sử dụng giá thể & chậu gốm, hạn chế nhựa một lần.</li>
                <li>Hợp tác nông trại địa phương, đảm bảo nguồn gốc truy xuất.</li>
                <li>Chương trình Một cây cho trường học  trồng 5.000 cây/năm.</li>
            </ul>
        </div>
        <div class="impact-card list">
            <h3>Dịch vụ nổi bật</h3>
            <div class="impact-item">
                <strong>Green Workspace</strong>
                <span>Thiết kế góc xanh  lọc khí cho văn phòng mở.</span>
            </div>
            <div class="impact-item">
                <strong>Home Forest</strong>
                <span>Biến ban công, sân thượng thành khu vườn mini.</span>
            </div>
            <div class="impact-item">
                <strong>Holiday Plant Styling</strong>
                <span>Trang trí cây theo concept lễ hội, sự kiện doanh nghiệp.</span>
            </div>
        </div>
    </section>

    <section class="xworld-map">
        <div class="map-info">
            <p class="eyebrow">Flagship Green Studio</p>
            <h2>{{ $settings->contact_address ?? '595/1 Thiên Lôi, Hải Phòng' }}</h2>
            <p>
                Đặt lịch trước để được chuẩn bị phòng tư vấn riêng tư, dịch vụ đo ánh sáng và phối chậu theo phong cách nội thất.
            </p>
            <div class="contact">
                <span>Hotline</span>
                <a href="tel:{{ $settings->contact_phone ?? '' }}">{{ $settings->contact_phone ?? '0909 988 889' }}</a>
            </div>
            <div class="author">
                <span>Author</span>
                <a href="{{ $settings->facebook_link ?? '#' }}" target="_blank">Nguyễn Minh Đức</a>
            </div>
            <div class="contact">
                <span>Email</span>
                <a href="mailto:{{ $settings->contact_email ?? '' }}">{{ $settings->contact_email ?? 'xanhworldvietnam@gmail.com' }}</a>
            </div>
        </div>
        <div class="map-frame">
            <iframe width="100%" height="100%"
                src="{{ $settings->source_map ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3974.074551510752!2d106.45132018840634!3d20.838643175148697!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x313589331558271b%3A0x3a5a70f9ba3d5718!2zTmjDoCBWxrDhu51uIFRo4bqvbmcgVGjhuq9t!5e1!3m2!1svi!2s!4v1766629712920!5m2!1svi!2s' }}"
                loading="lazy" allowfullscreen></iframe>
        </div>
    </section>

    <section class="xworld-cta">
        <div class="cta-card">
            <div>
                <p class="eyebrow">Kết nối cùng {{ $settings->site_name ?? $settings->site_name ?? 'Thế giới cây xanh Xworld' }}</p>
                <h3>Đăng ký nhận bản tin Green Insight & mời tham dự workshop chăm cây</h3>
            </div>
            <a class="btn secondary" href="{{ route('client.contact.index') }}">Đăng ký ngay</a>
        </div>
    </section>

    

    <div class="xworld-products">
        @include('clients.templates.product_new')
    </div>

    <style>
        :root {
            --intro-dark: #05170f;
            --intro-green: #0f5132;
            --intro-border: rgba(0, 78, 56, 0.12);
            --intro-light: #f2fff8;
        }

        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.28em;
            font-size: 11px;
            color: #1fe3a8;
            margin-bottom: 12px;
        }

        .xworld-hero {
            width: 92%;
            margin: 40px auto;
            padding: 48px;
            border-radius: 36px;
            color: #f6fffb;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
            gap: 32px;
            background: radial-gradient(circle at top right, rgba(18, 255, 197, 0.35), transparent 55%),
                linear-gradient(135deg, #062a18, #0d2a1c 60%, #07110a);
        }

        .xworld-hero__content h1 {
            font-size: clamp(36px, 4vw, 58px);
            line-height: 1.15;
            margin-bottom: 16px;
        }

        .xworld-hero__content p {
            color: rgba(255, 255, 255, 0.8);
        }

        .hero-actions {
            margin: 26px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }

        .btn {
            border-radius: 999px;
            padding: 14px 26px;
            font-weight: 600;
            border: 1px solid transparent;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.25s ease;
        }

        .btn.primary {
            background: linear-gradient(90deg, #1fe3a8, #0bbf82);
            color: #052418;
        }

        .btn.ghost {
            border-color: rgba(255, 255, 255, 0.35);
            color: #f6fffb;
        }

        .btn.secondary {
            background: #052414;
            color: #dfffea;
            border-color: rgba(255, 255, 255, 0.1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
        }

        .hero-stats {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 14px;
        }

        .hero-stats li {
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 18px;
            padding: 16px;
            backdrop-filter: blur(6px);
        }

        .hero-stats strong {
            display: block;
            font-size: 28px;
        }

        .xworld-hero__media {
            position: relative;
        }

        .xworld-hero__media img {
            width: 100%;
            height: 100%;
            max-height: 420px;
            border-radius: 32px;
            object-fit: cover;
        }

        .media-badge {
            position: absolute;
            bottom: 20px;
            right: 20px;
            padding: 18px;
            background: rgba(5, 12, 10, 0.75);
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            max-width: 80%;
        }

        .xworld-panels {
            width: 92%;
            margin: 35px auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .panel {
            padding: 28px;
            border-radius: 26px;
            border: 1px solid var(--intro-border);
            background: #fff;
            box-shadow: 0 20px 40px rgba(5, 12, 10, 0.08);
        }

        .panel.highlight {
            background: linear-gradient(130deg, #041c12, #093124);
            color: #e9fff6;
            border: none;
        }

        .panel ul {
            padding-left: 18px;
            margin: 0;
            color: #4b5d57;
            line-height: 1.6;
        }

        .panel.highlight ul {
            color: #c7ffeb;
        }

        .xworld-journey {
            width: 92%;
            margin: 40px auto;
            border-radius: 34px;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
            background: #fff;
            box-shadow: 0 30px 60px rgba(5, 17, 15, 0.15);
        }

        .journey-content {
            padding: 42px;
        }

        .timeline {
            margin-top: 26px;
            border-left: 2px solid rgba(5, 17, 15, 0.1);
            padding-left: 28px;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .timeline-item {
            position: relative;
        }

        .timeline-item::before {
            content: "";
            position: absolute;
            left: -34px;
            top: 5px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1fe3a8, #00a87a);
            box-shadow: 0 0 0 6px rgba(31, 227, 168, 0.2);
        }

        .journey-media {
            position: relative;
        }

        .journey-media img {
            width: 100%;
            height: 100%;
            min-height: 320px;
            object-fit: cover;
        }

        .media-caption {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(3, 11, 9, 0.8);
            color: #eafff6;
            border-radius: 16px;
            padding: 18px;
            max-width: 70%;
        }

        .xworld-grid,
        .xworld-network,
        .xworld-impact,
        .xworld-map,
        .xworld-products,
        .xworld-cta {
            width: 92%;
            margin: 40px auto;
        }

        .xworld-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .xworld-grid article {
            border-radius: 24px;
            padding: 24px;
            border: 1px dashed rgba(31, 227, 168, 0.5);
            background: rgba(31, 227, 168, 0.05);
        }

        .xworld-network {
            display: grid;
            grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
            gap: 22px;
        }

        .network-card {
            border-radius: 30px;
            padding: 32px;
            background: #fff;
            border: 1px solid var(--intro-border);
            box-shadow: 0 25px 55px rgba(5, 12, 10, 0.1);
        }

        .network-card.gradient {
            background: linear-gradient(135deg, #041910, #082a1d);
            color: #dcfff1;
            border: none;
        }

        .network-card ul {
            margin-top: 16px;
            padding-left: 20px;
            line-height: 1.7;
        }

        .channel {
            margin-top: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .channel:last-child {
            border-bottom: 0;
        }

        .channel span {
            font-size: 13px;
            opacity: 0.7;
        }

        .channel a {
            color: inherit;
            text-decoration: none;
            font-weight: 600;
        }

        .xworld-impact {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 20px;
        }

        .impact-card {
            border-radius: 30px;
            padding: 30px;
            background: #fff;
            border: 1px solid var(--intro-border);
        }

        .impact-card.list {
            background: rgba(0, 80, 56, 0.05);
        }

        .impact-item {
            border-bottom: 1px dashed rgba(5, 12, 10, 0.1);
            padding: 12px 0;
        }

        .impact-item:last-child {
            border-bottom: 0;
        }

        .xworld-map {
            display: grid;
            grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
            gap: 24px;
        }

        .map-info {
            background: #02150d;
            color: #d8ffef;
            border-radius: 28px;
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .map-info a {
            color: #85ffd9;
            text-decoration: none;
            font-weight: 600;
        }

        .map-frame {
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(5, 12, 10, 0.2);
        }

        .xworld-cta .cta-card {
            border-radius: 32px;
            padding: 34px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            background: linear-gradient(120deg, #051a11, #0e3726);
            color: #e7fff6;
        }

        .xworld-products {
            margin-bottom: 60px;
        }

        @media (max-width: 1100px) {
            .xworld-hero,
            .xworld-journey,
            .xworld-network,
            .xworld-impact,
            .xworld-map {
                grid-template-columns: 1fr;
            }

            .media-badge {
                position: relative;
                inset: auto;
                margin-top: 16px;
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .xworld-hero,
            .xworld-panels,
            .xworld-grid,
            .xworld-network,
            .xworld-impact,
            .xworld-map,
            .xworld-products,
            .xworld-cta {
                width: 94%;
            }

            .xworld-hero {
                padding: 32px 24px;
            }

            .hero-actions,
            .xworld-cta .cta-card {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endsection
