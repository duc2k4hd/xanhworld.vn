@extends('clients.layouts.master')

@php
    $resolvedTitle = $pageTitle ?? ('Tin tức & Blog cây xanh | ' . config('app.name'));
    $resolvedDescription = $pageDescription ?? 'Chia sẻ kinh nghiệm chăm sóc cây, gợi ý decor không gian xanh và câu chuyện thương hiệu.';
    $resolvedKeywords = $pageKeywords ?? 'tin tức, blog cây xanh, chăm sóc cây, decor xanh';
    $canonicalUrl = route('client.blog.index');
    $pageImage = asset('clients/assets/img/business/' . ($settings->site_logo ?? 'logo.png'));
@endphp

@section('title', $resolvedTitle)

@push('js_page')
    <script defer src="{{ asset('clients/assets/js/main.js') }}"></script>
@endpush

@section('head')
    <meta name="description" content="{{ $resolvedDescription }}">
    <meta name="keywords" content="{{ $resolvedKeywords }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $resolvedTitle }}">
    <meta property="og:description" content="{{ $resolvedDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $settings->site_name ?? config('app.name') }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $resolvedTitle }}">
    <meta name="twitter:description" content="{{ $resolvedDescription }}">
    <meta name="twitter:image" content="{{ $pageImage }}">
    @if(!empty($shouldNoindex))
        <meta name="robots" content="noindex, follow">
    @endif
<style>
    /* Tổng thể trang */
    .blog-page {
        width: 100%;
    }

    /* Hero */
    .blog-hero {
        background: linear-gradient(135deg, #f0fdf4, #ecfeff);
        border-radius: 16px;
        padding: clamp(20px, 3vw, 32px);
        margin-bottom: clamp(16px, 2vw, 24px);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .blog-hero h1 {
        font-size: clamp(22px, 3vw, 30px);
        line-height: 1.4;
    }

    .hero-list {
        margin: 16px 0 0;
        padding-left: 20px;
        color: #4b5563;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: 13px;
        font-weight: 600;
    }

    /* Featured */
    .blog-featured .card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        transition: 0.2s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .blog-featured .card img {
        height: 140px;
        width: 100%;
        object-fit: cover;
    }

    .blog-featured .card-body {
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex-grow: 1;
    }

    .blog-featured .card:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        transform: translateY(-3px);
    }

    /* Bài viết */
    .blog-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        transition: 0.2s;
        background: #fff;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .blog-card:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.07);
        transform: translateY(-3px);
    }

    .blog-card img {
        height: 150px;
        object-fit: cover;
        width: 100%;
    }

    @media (min-width: 768px) {
        .blog-card img {
            height: 130px;
        }
    }

    .blog-card .card-body {
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex-grow: 1;
    }

    .blog-card .card-body h3 {
        font-size: 16px;
        line-height: 1.4;
        margin-bottom: 4px;
    }

    .blog-card .meta-row {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
    }

    /* Sidebar */
    .blog-sidebar .card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 18px;
    }

    .blog-sidebar h5 {
        font-size: 16px;
        font-weight: 700;
    }

    /* Tag */
    .blog-tag {
        display: inline-block;
        background: #f3f4f6;
        color: #111827;
        padding: 3px 10px;
        margin: 4px 6px 0 0;
        border-radius: 999px;
        font-size: 12px;
    }

    /* List trong sidebar */
    .blog-sidebar ul li {
        padding: 6px 0;
        border-bottom: 1px solid #f1f1f1;
    }

    .blog-sidebar ul li:last-child {
        border-bottom: none;
    }

    .tiny {
        font-size: 11px;
    }

    /* Breadcrumb */
    .blog-breadcrumb {
        margin-bottom: 16px;
    }
    .breadcrumb-list {
        display: flex;
        align-items: center;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
        flex-wrap: wrap;
    }
    .breadcrumb-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .breadcrumb-item a {
        color: var(--text-muted, #6b7280);
        text-decoration: none;
        font-size: 13px;
        transition: color 0.2s;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .breadcrumb-item a:hover {
        color: var(--text-primary, #111827);
    }
    .breadcrumb-item a i {
        font-size: 12px;
    }
    .breadcrumb-item.active span {
        color: var(--text-primary, #111827);
        font-size: 13px;
        font-weight: 500;
    }
    .breadcrumb-separator {
        color: var(--text-muted, #9ca3af);
        font-size: 10px;
        display: flex;
        align-items: center;
    }
    * {
        list-style: none !important;
        text-decoration: none !important;
    }
</style>

@endsection

@section('schema')
    @if(isset($schemaData) && is_array($schemaData))
        @foreach($schemaData as $schema)
            <script type="application/ld+json">
                {!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
            </script>
        @endforeach
    @endif
@endsection

@section('content')
<section class="container py-4 blog-page">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="blog-breadcrumb mb-3">
        <ol class="breadcrumb-list">
            <li class="breadcrumb-item">
                <a href="{{ route('client.home.index') }}">
                    <i class="fas fa-home"></i>
                    <span>Trang chủ</span>
                </a>
            </li>
            <li class="breadcrumb-separator">
                <i class="fas fa-chevron-right"></i>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <span>Tin tức</span>
            </li>
        </ol>
    </nav>

    <!-- HERO -->
    <div class="blog-hero">
        <div class="row align-items-center">
            <div class="col-lg-8">
                @if($heroContextLabel)
                    <span class="hero-badge">{{ $heroContextLabel }}</span>
                @endif
                <h1 class="h3 fw-bold mb-2 mt-2">{{ $heroHeading ?? 'Góc tin tức cây xanh' }}</h1>
                <p class="text-muted small mb-2">
                    {{ $heroSubheading ?? ('Tổng hợp kiến thức chăm cây và cảm hứng decor từ ' . ($settings->site_name ?? config('app.name'))) }}
                </p>
                <ul class="hero-list small">
                    <li>Đội ngũ biên tập cập nhật mỗi ngày với trải nghiệm thực tế.</li>
                    <li>Bám sát xu hướng sống xanh, tối ưu không gian nhà phố & văn phòng.</li>
                    <li>Hướng dẫn chi tiết cho người mới bắt đầu lẫn người chơi cây lâu năm.</li>
                </ul>
            </div>

            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="d-flex justify-content-lg-end gap-3">
                    <div>
                        <div class="fw-bold">{{ number_format($featuredPosts->count()) }}</div>
                        <span class="text-muted tiny">Góc cảm hứng</span>
                    </div>
                    <div>
                        <div class="fw-bold">{{ number_format($posts->total()) }}</div>
                        <span class="text-muted tiny">Chia sẻ hữu ích</span>
                    </div>
                    <div>
                        <div class="fw-bold">{{ number_format($sidebarCategories->count()) }}</div>
                        <span class="text-muted tiny">Chủ đề xanh</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FEATURED -->
    @if($featuredPosts->isNotEmpty())
        <section class="blog-featured mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 fw-bold mb-0">Bài viết nổi bật</h2>
                <span class="text-muted tiny">Được chọn bởi biên tập viên</span>
            </div>
            <div class="row g-3">
                @foreach($featuredPosts as $featured)
                    <div class="col-md-4">
                        <article class="card h-100">
                            @php
                                $featuredCover = $featured->coverImagePath();
                                $featuredCoverUrl = asset($featuredCover ?? 'clients/assets/img/clothes/default.webp');
                            @endphp
                            <img src="{{ $featuredCoverUrl }}" alt="{{ $featured->title }}" loading="lazy">

                            <div class="card-body">
                                <span class="badge bg-light text-dark rounded-pill mb-2 small">
                                    {{ $featured->category?->name ?? 'Tin tức' }}
                                </span>

                                <h3 class="h6 fw-bold mb-2">
                                    <a href="{{ route('client.blog.show', $featured) }}"
                                        class="text-dark text-decoration-none">
                                        {{ $featured->title }}
                                    </a>
                                </h3>

                                <p class="text-muted tiny">{{ $featured->excerpt_text }}</p>

                                <div class="d-flex justify-content-between tiny text-muted">
                                    <span>{{ optional($featured->published_at)->format('d/m/Y') }}</span>
                                    <span>{{ number_format($featured->views) }} xem</span>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="row g-4">
        <!-- MAIN POSTS -->
        <div class="col-lg-8">
            <div class="row g-3">
                @forelse($posts as $post)
                    <div class="col-md-6">
                        <article class="blog-card h-100">
                            @php
                                $coverPath = $post->coverImagePath();
                                $coverUrl = asset($coverPath ?? 'clients/assets/img/clothes/default.webp');
                            @endphp
                            <img src="{{ $coverUrl }}" alt="{{ $post->title }}" loading="lazy">

                            <div class="card-body">
                                <div class="d-flex gap-2 tiny text-muted mb-1">
                                    <span>{{ $post->category?->name ?? 'Tin tức' }}</span> •
                                    <span>{{ optional($post->published_at)->format('d/m/Y') }}</span>
                                </div>

                                <h3 class="h6 fw-bold mb-2">
                                    <a href="{{ route('client.blog.show', $post) }}"
                                        class="text-dark text-decoration-none">
                                        {{ $post->title }}
                                    </a>
                                </h3>

                                <p class="text-muted tiny mb-2">{{ $post->excerpt_text }}</p>

                                <div class="d-flex justify-content-between align-items-center tiny text-muted">
                                    <span>{{ number_format($post->views) }} xem</span>
                                    <a href="{{ route('client.blog.show', $post) }}" class="btn btn-sm btn-outline-dark">
                                        Đọc tiếp
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-light border text-center">
                            Đang cập nhật bài viết mới, bạn quay lại sau nhé!
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-3">
                {{ $posts->links('pagination::bootstrap-5') }}
            </div>
        </div>

        <!-- SIDEBAR -->
        <aside class="col-lg-4 blog-sidebar">

            <!-- Categories -->
            <div class="card mb-3">
                <h5 class="fw-bold mb-2">Chủ đề tiêu biểu</h5>
                <ul class="list-unstyled mb-0">
                    @foreach($sidebarCategories as $category)
                        <li class="d-flex justify-content-between small">
                            <a href="{{ route('client.blog.index', ['category' => $category->slug]) }}" class="text-dark text-decoration-none">
                                {{ $category->name }}
                            </a>
                            <span class="text-muted">{{ $category->posts_count }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Tags -->
            <div class="card mb-3">
                <h5 class="fw-bold mb-2">Từ khóa bạn quan tâm</h5>
                @foreach($sidebarTags as $tag)
                    <a href="{{ route('client.blog.index', ['tag' => $tag->slug]) }}" class="blog-tag">#{{ $tag->name }}</a>
                @endforeach
            </div>

            <!-- Recent Posts -->
            <div class="card mb-3">
                <h5 class="fw-bold mb-2">Bài viết mới nhất</h5>
                <ul class="list-unstyled mb-0">
                    @foreach($recentPosts as $recent)
                        <li class="mb-2">
                            <a href="{{ route('client.blog.show', $recent) }}"
                                class="text-dark small text-decoration-none">
                                {{ $recent->title }}
                            </a>
                            <div class="text-muted tiny">{{ optional($recent->published_at)->format('d/m') }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Popular -->
            <div class="card">
                <h5 class="fw-bold mb-2">Được xem nhiều</h5>
                <ul class="list-unstyled mb-0">
                    @foreach($popularPosts as $popular)
                        <li class="mb-2">
                            <a href="{{ route('client.blog.show', $popular) }}"
                                class="text-dark small text-decoration-none">
                                {{ $popular->title }}
                            </a>
                            <div class="text-muted tiny">{{ number_format($popular->views) }} views</div>
                        </li>
                    @endforeach
                </ul>
            </div>

        </aside>
    </div>

</section>
@endsection
