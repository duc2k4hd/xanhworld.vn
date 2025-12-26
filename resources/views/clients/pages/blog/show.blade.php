@extends('clients.layouts.master')

@section(
    'title',
    (
        $pageTitle
        ?? $post->meta_title
        ?? $post->title
    ) . ' | ' . ($settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD')
)

@push('css_page')
    <link rel="stylesheet" href="{{ asset('clients/assets/css/blog.css') }}">
    <link
        rel="preload"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        as="style"
        onload="this.onload=null;this.rel='stylesheet'">

    <noscript>
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </noscript>
@endpush

@section('head')
    @php
        // Lấy ảnh LCP (ảnh đầu tiên trong gallery hoặc cover image)
        $lcpImage = null;
        $galleryImages = $post->images;
        if ($galleryImages->isNotEmpty()) {
            $firstImage = $galleryImages->first();
            $lcpImage = asset('clients/assets/img/posts/'.$firstImage->url);
        } else {
            $lcpImage = $coverAsset ?? asset('clients/assets/img/posts/no-image.webp');
        }
    @endphp
    
    {{-- Preload LCP Image - Tối ưu LCP --}}
    <link rel="preload" as="image" href="{{ $lcpImage }}" fetchpriority="high">
    
    {{-- Preconnect to image domain for faster loading --}}
    @php
        $parsedUrl = parse_url($lcpImage);
        if ($parsedUrl && isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
            $imageDomain = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        } else {
            $imageDomain = url('/');
        }
    @endphp
    <link rel="preconnect" href="{{ $imageDomain }}" crossorigin>
    
    {{-- SEO Meta Tags --}}
    <meta name="description" content="{{ $pageDescription ?? ($post->meta_description ?? $post->excerpt) }}">
    <meta name="keywords" content="{{ $pageKeywords ?? $post->meta_keywords }}">
    <link rel="canonical" href="{{ $canonicalUrl ?? route('client.blog.show', $post) }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $pageTitle ?? ($post->meta_title ?? $post->title) }}">
    <meta property="og:description" content="{{ $pageDescription ?? ($post->meta_description ?? $post->excerpt) }}">
    <meta property="og:url" content="{{ $canonicalUrl ?? route('client.blog.show', $post) }}">
    <meta property="og:image" content="{{ $coverAsset ?? asset('clients/assets/img/posts/no-image.webp') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle ?? ($post->meta_title ?? $post->title) }}">
    <meta name="twitter:description" content="{{ $pageDescription ?? ($post->meta_description ?? $post->excerpt) }}">
    <meta name="twitter:image" content="{{ $coverAsset ?? asset('clients/assets/img/posts/no-image.webp') }}">
@endsection

@push('js_page')
    <script defer src="{{ asset('clients/assets/js/main.js') }}"></script>
@endpush

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
    @php
        $shareUrl = urlencode(route('client.blog.show', $post));
        $shareText = urlencode($post->title . ' - ' . config('app.name'));
    @endphp

    <div class="xanhworld-article-wrapper">
        <!-- Header Top -->
        @if($tags->isNotEmpty())
        <div class="xanhworld-article-header-top">
            <div class="xanhworld-article-header-top-label">
                <strong>Từ khóa:</strong>
                        </div>
            @foreach($tags as $tag)
                <a href="{{ route('client.blog.index', ['tags' => $tag->slug]) }}" class="xanhworld-article-tag"># {{ $tag->name }}</a>
            @endforeach
                    </div>
        @endif

        <!-- Breadcrumb -->
        <div class="xanhworld-article-breadcrumb">
            <a href="{{ route('client.home.index') }}">🏠 Trang chủ</a>
            <span>»</span>
            <a href="{{ route('client.blog.index') }}">Tin tức</a>
            @if($post->category)
                <span>»</span>
                <a href="{{ route('client.blog.index', ['category' => $post->category->slug]) }}">{{ $post->category->name }}</a>
            @endif
            <span>»</span>
            <span>{{ str()->limit($post->title, 60) }}</span>
                    </div>

        <div class="xanhworld-article-content-wrapper">
            <div class="xanhworld-article-content-inner">
                <!-- Hero Section -->
                <div class="xanhworld-article-hero">
                    <div class="xanhworld-article-hero-image">
                        @php
                            // Images đã được preload trong controller, không cần query lại
                            $galleryImages = $post->images;
                        @endphp
                        <div class="xanhworld-article-carousel" id="postImageCarousel">
                            <div class="xanhworld-article-carousel-inner">
                                @if($galleryImages->isNotEmpty())
                                    @foreach($galleryImages as $index => $image)
                                        @php
                                            $imgPath = 'clients/assets/img/posts/'.$image->url;
                                            $imgUrl = asset($imgPath);
                                            $isLcp = $index === 0;
                                        @endphp
                                        <div class="xanhworld-article-carousel-item {{ $isLcp ? 'active' : '' }}">
                                            <img width="100%" height="100%"
                                                 src="{{ $imgUrl }}"
                                                 alt="{{ $image->alt ?? $post->title }}"
                                                 @if($isLcp)
                                                 loading="eager"
                                                 fetchpriority="high"
                                                 @else
                                                 loading="lazy"
                                                 fetchpriority="auto"
                                                 @endif>
                                        </div>
                                    @endforeach
                                @else
                                    @php
                                        $fallbackImage = $coverAsset ?? asset('clients/assets/img/posts/no-image.webp');
                                    @endphp
                                    <div class="xanhworld-article-carousel-item active">
                                        <img width="100%" height="100%"
                                             src="{{ $fallbackImage }}"
                                             alt="{{ $post->title }}"
                                             loading="eager"
                                             fetchpriority="high">
                                    </div>
                                @endif
                            </div>
                            @if($galleryImages->count() > 1)
                                <button class="xanhworld-article-carousel-prev" type="button" aria-label="Previous">
                                    <span aria-hidden="true">‹</span>
                                </button>
                                <button class="xanhworld-article-carousel-next" type="button" aria-label="Next">
                                    <span aria-hidden="true">›</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="xanhworld-article-container">
                    <div class="xanhworld-article-content">
                        <div class="xanhworld-article-hero-content">
                            @if($post->category)
                                <a href="/{{ $post->category->slug }}"><div class="xanhworld-article-hero-tag">{{ $post->category->name }}</div></a>
                            @endif
                            <h1>{{ $post->title }}</h1>
                        </div>

                        @php
                            $colorAvatar = [
                                // Blue
                                ['background' => '0D8ABC', 'color' => 'FFFFFF'],

                                // Red
                                ['background' => 'D32F2F', 'color' => 'FFFFFF'],

                                // Grey light
                                ['background' => 'E0E0E0', 'color' => '000000'],

                                // Green
                                ['background' => '388E3C', 'color' => 'FFFFFF'],

                                // Orange
                                ['background' => 'F57C00', 'color' => 'FFFFFF'],

                                // Purple
                                ['background' => '7B1FA2', 'color' => 'FFFFFF'],

                                // Teal
                                ['background' => '00796B', 'color' => 'FFFFFF'],

                                // Yellow (chữ đen)
                                ['background' => 'FFEB3B', 'color' => '000000'],
                            ];

                            // Random 1 item
                            $avatarColor = $colorAvatar[array_rand($colorAvatar)];
    @endphp
                        <!-- Author Info -->
                        <div class="xanhworld-article-author-info">
                            <div class="xanhworld-article-author-avatar">
                                <img width="100%" height="100%" src="https://ui-avatars.com/api/?name={{ $post?->creator?->name ?? 'Đức Nobi 💖' }}&background={{ $avatarColor['background'] }}&color={{ $avatarColor['color'] }}&size=48&rounded=true" alt="">
        </div>
                            <div class="xanhworld-article-author-details">
                                <h3>{{ $post?->creator?->name ?? 'Đội ngũ biên tập' }}<span class="xanhworld-article-verified-badge"></span></h3>
                                <p>📅 {{ optional($post->published_at)->format('d/m/Y') ?? $post->updated_at->format('d/m/Y') }}</p>
                </div>
            </div>

                        @mobile
                            <!-- TOC Mobile -->
                @if($toc->isNotEmpty())
                                <div class="xanhworld-article-toc" id="toc-mobile">
                                    <div class="xanhworld-article-toc-title">
                                        <p>📑 Mục lục</p>
                                    </div>
                                    <ul class="xanhworld-article-toc-list">
                            @foreach($toc as $item)
                                            <li class="{{ $item['tag'] === 'h3' ? 'xanhworld-article-toc-item-h3' : '' }}">
                                    <a href="#{{ $item['id'] }}">{{ $item['label'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                        @endmobile

                        <!-- Article Content -->
                        <article class="xanhworld-article-article-content rich-content">
                    {!! $contentWithAnchors !!}
                        </article>

                        <!-- Comments Section -->
                        <div class="xanhworld-article-comments">
                            @include('clients.partials.comments', [
                                'type' => 'post',
                                'objectId' => $post->id,
                                'comments' => $comments ?? null,
                                'ratingStats' => $ratingStats ?? null,
                                'totalComments' => $totalComments ?? 0
                            ])
                        </div>

                        <!-- Tags Section -->
                        @if($tags->isNotEmpty())
                        <div class="xanhworld-article-tags-footer">
                            <div class="xanhworld-article-tags-footer-label">
                                <strong>Thẻ:</strong>
                            </div>
                            <div class="xanhworld-article-tags-footer-list">
                                @php
                                    $allTagSlugs = $tags->pluck('slug')->implode(',');
                                @endphp
                                @foreach($tags as $tag)
                                    <a href="{{ route('client.blog.index', ['tags' => $allTagSlugs]) }}" class="xanhworld-article-tag-footer">#{{ $tag->name }}</a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                </div>
                    </div>
                </div>

            <div class="xanhworld-article-sidebar">
                <div class="xanhworld-article-sidebar-content">
                    @desktop
                        <!-- TOC -->
                @if($toc->isNotEmpty())
                            <div class="xanhworld-article-toc" id="toc-desktop">
                                <div class="xanhworld-article-toc-title">
                                    <p>📑 Mục lục</p>
                                </div>
                                <ul class="xanhworld-article-toc-list">
                            @foreach($toc as $item)
                                        <li class="{{ $item['tag'] === 'h3' ? 'xanhworld-article-toc-item-h3' : '' }}">
                                    <a href="#{{ $item['id'] }}">{{ $item['label'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                    @enddesktop
                @if($relatedPosts->isNotEmpty())
                    <div class="xanhworld-article-sidebar-content-item">
                        <div class="xanhworld-article-sidebar-content-item-title">
                            <h3>Bài viết liên quan</h3>
                        </div>
                                <div class="xanhworld-article-sidebar-posts">
                            @foreach($relatedPosts as $related)
                                @php
                                    // Images đã được preload trong controller, không cần query lại
                                    $relatedPath = $related->coverImagePath();
                                    $relatedUrl = asset($relatedPath ?? 'clients/assets/img/posts/no-image.webp');
                                @endphp
                                <a href="{{ route('client.blog.show', $related) }}" class="xanhworld-article-sidebar-post">
                                    <img src="{{ $relatedUrl }}" alt="{{ $related->title }}" loading="lazy">
                                    <div class="xanhworld-article-sidebar-post-info">
                                        <h4>{{ str()->limit($related->title, 60) }}</h4>
                                        <span>{{ optional($related->published_at)->format('d/m/Y') }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                </div>
        </div>
        </div>
    </div>
@endsection

@section('foot')
    <script>
        // Carousel functionality
        document.addEventListener('DOMContentLoaded', () => {
            const carousel = document.getElementById('postImageCarousel');
            if (!carousel) return;

            const items = carousel.querySelectorAll('.xanhworld-article-carousel-item');
            const prevBtn = carousel.querySelector('.xanhworld-article-carousel-prev');
            const nextBtn = carousel.querySelector('.xanhworld-article-carousel-next');
            
            if (items.length <= 1) return;

            let currentIndex = 0;

            function showSlide(index) {
                items.forEach((item, i) => {
                    item.classList.remove('active');
                    if (i === index) {
                        item.classList.add('active');
                    }
                });
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % items.length;
                showSlide(currentIndex);
            }

            function prevSlide() {
                currentIndex = (currentIndex - 1 + items.length) % items.length;
                showSlide(currentIndex);
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', prevSlide);
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', nextSlide);
            }

            // Auto play (optional)
            // let autoPlayInterval = setInterval(nextSlide, 5000);
            // carousel.addEventListener('mouseenter', () => clearInterval(autoPlayInterval));
            // carousel.addEventListener('mouseleave', () => {
            //     autoPlayInterval = setInterval(nextSlide, 5000);
            // });
        });

        // Auto Highlight TOC on scroll - Tối ưu hiệu năng
        document.addEventListener('DOMContentLoaded', () => {
            // Support both desktop and mobile TOC
            const tocDesktop = document.getElementById('toc-desktop');
            const tocMobile = document.getElementById('toc-mobile');
            const tocContainer = tocDesktop || tocMobile;
            
            if (!tocContainer) return;

            // Cache các heading elements để tránh query lại nhiều lần
            const contentSections = document.querySelectorAll('.xanhworld-article-article-content h2[id], .xanhworld-article-article-content h3[id]');
            if (contentSections.length === 0) return;

            // Cache các TOC links để tăng tốc độ
            const tocLinks = new Map();
            tocContainer.querySelectorAll('a').forEach(link => {
                const href = link.getAttribute('href');
                if (href) {
                    const id = href.substring(1);
                    tocLinks.set(id, link);
                }
            });

            // Tối ưu IntersectionObserver với debounce
            let activeId = null;
            const observer = new IntersectionObserver((entries) => {
                // Tìm entry đầu tiên đang visible (từ trên xuống)
                let currentEntry = null;
                for (const entry of entries) {
                    if (entry.isIntersecting && entry.intersectionRatio > 0) {
                        if (!currentEntry || entry.boundingClientRect.top < currentEntry.boundingClientRect.top) {
                            currentEntry = entry;
                        }
                    }
                }

                // Nếu không có entry nào visible, tìm entry gần nhất phía trên
                if (!currentEntry) {
                    const viewportTop = window.scrollY + 100; // Offset cho header
                    for (const entry of entries) {
                        const rect = entry.boundingClientRect;
                        const elementTop = rect.top + window.scrollY;
                        if (elementTop <= viewportTop) {
                            if (!currentEntry || elementTop > (currentEntry.boundingClientRect.top + window.scrollY)) {
                                currentEntry = entry;
                            }
                        }
                    }
                }

                if (currentEntry) {
                    const id = currentEntry.target.getAttribute('id');
                    if (id && id !== activeId) {
                        activeId = id;
                        
                        // Remove active từ tất cả links
                        tocContainer.querySelectorAll('a').forEach(link => {
                            link.classList.remove('active');
                        });
                        
                        // Add active cho link hiện tại
                        const tocLink = tocLinks.get(id);
                        if (tocLink) {
                            tocLink.classList.add('active');
                            
                            // Scroll TOC container để hiển thị active link (nếu cần)
                            if (tocContainer.scrollHeight > tocContainer.clientHeight) {
                                const linkTop = tocLink.offsetTop;
                                const linkHeight = tocLink.offsetHeight;
                                const containerHeight = tocContainer.clientHeight;
                                const scrollTop = tocContainer.scrollTop;
                                
                                if (linkTop < scrollTop) {
                                    tocContainer.scrollTop = linkTop - 20;
                                } else if (linkTop + linkHeight > scrollTop + containerHeight) {
                                    tocContainer.scrollTop = linkTop - containerHeight + linkHeight + 20;
                                }
                            }
                        }
                    }
                }
            }, {
                rootMargin: '-100px 0px -60% 0px', // Tối ưu vùng trigger
                threshold: [0, 0.1, 0.5, 1] // Nhiều threshold để chính xác hơn
            });

            // Observe tất cả sections một lần
            contentSections.forEach((section) => {
                observer.observe(section);
            });
            
            // Lazy load images - chỉ chạy một lần
            const images = document.querySelectorAll('.xanhworld-article-article-content img:not([loading])');
            images.forEach(img => {
                img.setAttribute('loading', 'lazy');
            });

            // Smooth scroll for TOC links - dùng event delegation
            tocContainer.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link || !link.hash) return;
                
                e.preventDefault();
                const targetId = link.hash.substring(1);
                const target = document.getElementById(targetId);
                if (target) {
                    const offset = 100; // Offset cho fixed header
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                    
                    // Update active sau khi scroll (delay nhỏ)
                    setTimeout(() => {
                        const id = target.getAttribute('id');
                        if (id) {
                            tocContainer.querySelectorAll('a').forEach(l => l.classList.remove('active'));
                            const tocLink = tocLinks.get(id);
                            if (tocLink) {
                                tocLink.classList.add('active');
                            }
                        }
                    }, 500);
                }
            });
        });
    </script>
@endsection