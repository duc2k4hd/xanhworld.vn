<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $postsQuery = Post::query()
            ->published()
            ->with('category');

        $activeCategory = null;
        if ($categorySlug = $request->query('category')) {
            $activeCategory = Category::where('slug', $categorySlug)->first();

            if (! $activeCategory) {
                abort(404);
            }

            $postsQuery->where('category_id', $activeCategory->id);
        }

        $activeTags = collect();
        $shouldNoindex = false;

        // Xử lý tags mới: tags=slug1,slug2
        if ($request->has('tags')) {
            $shouldNoindex = true;
            $tagsInput = $request->input('tags');

            // Nếu là array, chuyển thành string
            if (is_array($tagsInput)) {
                $tagsInput = implode(',', $tagsInput);
            }

            // Tách các tags
            $tagSlugs = array_filter(array_map('trim', explode(',', (string) $tagsInput)));

            if (! empty($tagSlugs)) {
                $activeTags = Tag::query()
                    ->whereIn('slug', $tagSlugs)
                    ->where('entity_type', Post::class)
                    ->where('is_active', true)
                    ->get();

                if ($activeTags->isNotEmpty()) {
                    $tagIds = $activeTags->pluck('id')->toArray();
                    $postsQuery->where(function ($query) use ($tagIds) {
                        foreach ($tagIds as $tagId) {
                            $query->orWhereJsonContains('tag_ids', (int) $tagId)
                                ->orWhereJsonContains('tag_ids', (string) $tagId);
                        }
                    });
                }
            }
        }

        // Xử lý tag cũ (backward compatibility): tag=slug
        if ($request->has('tag') && ! $request->has('tags')) {
            $shouldNoindex = true;
            $tagSlug = trim((string) $request->query('tag'));

            if ($tagSlug !== '') {
                $activeTag = Tag::query()
                    ->where('slug', $tagSlug)
                    ->where('entity_type', Post::class)
                    ->first();

                if ($activeTag) {
                    $activeTags = collect([$activeTag]);
                    $postsQuery->where(function ($query) use ($activeTag) {
                        $query->whereJsonContains('tag_ids', (int) $activeTag->id)
                            ->orWhereJsonContains('tag_ids', (string) $activeTag->id);
                    });
                }
            }
        }

        if ($searchTerm = trim((string) $request->query('q'))) {
            $postsQuery->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('excerpt', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        $posts = $postsQuery
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();
        Post::preloadImages($posts->getCollection());

        $featuredPosts = Post::query()
            ->published()
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take(3)
            ->get();
        Post::preloadImages($featuredPosts);

        $sidebarCategories = Category::query()
            ->withCount([
                'posts as posts_count' => function ($query) {
                    $query->published();
                },
            ])
            ->having('posts_count', '>', 0)
            ->orderByDesc('posts_count')
            ->take(6)
            ->get();

        $sidebarTags = Tag::query()
            ->where('entity_type', Post::class)
            ->where('is_active', true)
            ->orderByDesc('usage_count')
            ->take(12)
            ->get();

        $recentPosts = Post::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
        Post::preloadImages($recentPosts);

        $popularPosts = Post::query()
            ->published()
            ->orderByDesc('views')
            ->orderByDesc('published_at')
            ->take(5)
            ->get();
        Post::preloadImages($popularPosts);

        $schemaData = $this->buildIndexSchemas($posts->getCollection());
        $meta = $this->resolveIndexMeta($activeCategory, $activeTags->first(), $searchTerm ?? null);

        return view('clients.pages.blog.index', [
            'posts' => $posts,
            'featuredPosts' => $featuredPosts,
            'sidebarCategories' => $sidebarCategories,
            'sidebarTags' => $sidebarTags,
            'recentPosts' => $recentPosts,
            'popularPosts' => $popularPosts,
            'schemaData' => $schemaData,
            'activeCategory' => $activeCategory,
            'activeTag' => $activeTags->first(),
            'activeTags' => $activeTags,
            'searchTerm' => $searchTerm ?? null,
            'pageTitle' => $meta['title'],
            'pageDescription' => $meta['description'],
            'pageKeywords' => $meta['keywords'],
            'heroHeading' => $meta['heading'],
            'heroSubheading' => $meta['subheading'],
            'heroContextLabel' => $meta['contextLabel'],
            'shouldNoindex' => $shouldNoindex,
        ]);
    }

    public function show(Post $post)
    {
        if ($post->status !== 'published' || ($post->published_at && $post->published_at->isFuture())) {
            abort(404);
        }

        $post->loadMissing(['category', 'author', 'creator']);
        Post::preloadImages([$post]);
        $post->increment('views');

        $contentData = $this->buildContentAnchors($post->content);
        $tags = $this->resolveTags($post);
        $meta = $this->resolvePostMeta($post);

        $relatedPosts = Cache::remember('blog_related_posts_'.$post->id, now()->addDays(30), function () use ($post) {
            $query = Post::query()
                ->published()
                ->where('id', '!=', $post->id)
                ->when($post->category_id, function ($q) use ($post) {
                    $q->where('category_id', $post->category_id);
                })
                ->orderByDesc('published_at')
                ->orderByDesc('created_at')
                ->take(4);

            $posts = $query->get();
            Post::preloadImages($posts);

            return $posts;
        });

        $internalLinks = Cache::remember('blog_internal_links_'.$post->id, now()->addDays(30), function () use ($post) {
            $links = Post::query()
                ->published()
                ->where('id', '!=', $post->id)
                ->inRandomOrder()
                ->take(3)
                ->get();
            Post::preloadImages($links);

            return $links;
        });

        // Load comments và rating stats - chỉ load 10 đầu tiên
        $comments = Comment::where('commentable_type', 'post')
            ->where('commentable_id', $post->id)
            ->whereNull('parent_id')
            ->approved()
            ->with(['account'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Load admin replies separately để đảm bảo relationship hoạt động đúng
        $commentIds = $comments->pluck('id');
        $adminReplies = Comment::whereIn('parent_id', $commentIds)
            ->whereNotNull('account_id')
            ->whereHas('account', function ($q) {
                $q->where('role', 'admin');
            })
            ->with('account')
            ->get()
            ->keyBy('parent_id');

        // Attach admin replies to comments
        $comments->each(function ($comment) use ($adminReplies) {
            if ($adminReplies->has($comment->id)) {
                $comment->setRelation('adminReply', $adminReplies->get($comment->id));
            }
        });

        // Get total count for "load more" functionality
        $totalComments = Comment::where('commentable_type', 'post')
            ->where('commentable_id', $post->id)
            ->whereNull('parent_id')
            ->approved()
            ->count();

        $commentService = app(\App\Services\CommentService::class);
        $ratingStats = $commentService->calculateRatingStats('post', $post->id);

        $schemaData = $this->buildShowSchemas($post, $tags);

        return view('clients.pages.blog.show', [
            'post' => $post,
            'schemaData' => $schemaData,
            'tags' => $tags,
            'toc' => $contentData['toc'],
            'contentWithAnchors' => $contentData['content'],
            'internalLinks' => $internalLinks,
            'relatedPosts' => $relatedPosts,
            'comments' => $comments,
            'ratingStats' => $ratingStats,
            'totalComments' => $totalComments,
            'pageTitle' => $meta['title'],
            'pageDescription' => $meta['description'],
            'pageKeywords' => $meta['keywords'],
            'canonicalUrl' => $meta['canonical'],
            'coverAsset' => $meta['cover'],
        ]);
    }

    protected function buildIndexSchemas(Collection $posts): array
    {
        $indexUrl = route('client.blog.index');
        $siteName = config('app.name');

        $breadcrumb = $this->buildBreadcrumbSchema([
            [
                'name' => 'Trang chủ',
                'url' => route('client.home.index'),
            ],
            [
                'name' => 'Tin tức',
                'url' => $indexUrl,
            ],
        ]);

        $latestPosts = $posts->take(6)->values();
        $itemListElements = [];

        foreach ($latestPosts as $index => $post) {
            $coverPath = $post->coverImagePath();
            $coverUrl = $coverPath ? asset($coverPath) : asset('clients/assets/img/posts/no-image.webp');

            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => [
                    '@type' => 'BlogPosting',
                    '@id' => route('client.blog.show', $post),
                    'url' => route('client.blog.show', $post),
                    'headline' => $post->title,
                    'description' => $post->excerpt_text,
                    'datePublished' => optional($post->published_at)->toIso8601String(),
                    'image' => $coverUrl,
                    'author' => [
                        '@type' => 'Organization',
                        'name' => $post->author->name ?? $siteName,
                    ],
                ],
            ];
        }

        $blogSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Blog',
            'name' => 'Tin tức & cảm hứng cây xanh - '.$siteName,
            'description' => 'Nơi cập nhật xu hướng trồng cây, trang trí không gian và kiến thức chăm cây do '.$siteName.' biên soạn.',
            'url' => $indexUrl,
            'inLanguage' => 'vi-VN',
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => config('app.url') ?? url('/'),
            ],
        ];

        $collectionSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Tin tức cây xanh - '.$siteName,
            'description' => 'Danh sách bài viết mới nhất về chăm sóc cây, decor không gian và câu chuyện thương hiệu '.$siteName.'.',
            'url' => $indexUrl,
            'inLanguage' => 'vi-VN',
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListOrder' => 'https://schema.org/ItemListOrderDescending',
                'numberOfItems' => $latestPosts->count(),
                'itemListElement' => $itemListElements,
            ],
        ];

        return [
            $breadcrumb,
            $blogSchema,
            $collectionSchema,
        ];
    }

    protected function resolveIndexMeta(?Category $category, ?Tag $tag, ?string $searchTerm): array
    {
        $siteName = config('app.name');
        $title = 'Tin tức & Blog cây xanh | '.$siteName;
        $description = 'Khám phá mẹo chăm sóc, gợi ý trang trí và câu chuyện thương hiệu '.$siteName.'.';
        $heading = 'Chuyên mục tin tức từ '.$siteName;
        $subheading = 'Tổng hợp kiến thức chăm cây, decor xanh và xu hướng sống bền vững.';
        $contextLabel = null;

        if ($category) {
            $title = $category->name.' | Tin tức '.$siteName;
            $description = 'Tin tức và cảm hứng xoay quanh '.$category->name.' – cập nhật bởi '.$siteName.'.';
            $heading = 'Chuyên mục: '.$category->name;
            $subheading = 'Những bài viết liên quan đến '.$category->name.' được cập nhật thường xuyên.';
            $contextLabel = 'Danh mục: '.$category->name;
        } elseif ($tag) {
            $title = 'Chủ đề #'.$tag->name.' | '.$siteName;
            $description = 'Tổng hợp bài viết nổi bật với hashtag #'.$tag->name.' tại '.$siteName.'.';
            $heading = 'Chủ đề #'.$tag->name;
            $subheading = 'Các bài viết xoay quanh chủ đề #'.$tag->name.' mà bạn quan tâm.';
            $contextLabel = '#'.$tag->name;
        } elseif ($searchTerm) {
            $title = 'Kết quả "'.$searchTerm.'" | '.$siteName;
            $description = 'Các bài viết liên quan tới "'.$searchTerm.'" do '.$siteName.' biên soạn.';
            $heading = 'Kết quả cho "'.$searchTerm.'"';
            $subheading = 'Những bài viết phù hợp nhất với từ khóa bạn đang tìm.';
            $contextLabel = 'Từ khóa: '.$searchTerm;
        }

        $keywords = implode(', ', array_filter([
            'tin tức cây xanh',
            'kinh nghiệm chăm cây',
            $category?->name,
            $tag?->name,
            $searchTerm,
            $siteName,
        ]));

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'heading' => $heading,
            'subheading' => $subheading,
            'contextLabel' => $contextLabel,
        ];
    }

    protected function resolvePostMeta(Post $post): array
    {
        $siteName = config('app.name');
        $title = $post->meta_title ?? ($post->title.' | '.$siteName);
        $description = $post->meta_description ?? $post->excerpt_text;
        $keywords = $post->meta_keywords ?? $post->tags()->pluck('name')->implode(', ');
        $settings = View::shared('settings');
        $siteUrl = rtrim($settings->site_url ?? config('app.url') ?? url('/'), '/');
        if ($post->meta_canonical) {
            $canonical = $siteUrl.'/'.ltrim($post->meta_canonical, '/');
        } else {
            $canonical = $siteUrl.'/tin-tuc/'.$post->slug;
        }
        $coverPath = $post->coverImagePath();
        $cover = $coverPath ? asset($coverPath) : asset('clients/assets/img/posts/no-image.webp');

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $canonical,
            'cover' => $cover,
        ];
    }

    protected function buildBreadcrumbSchema(array $items): array
    {
        $elements = [];
        foreach ($items as $index => $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    protected function buildShowSchemas(Post $post, Collection $tags): array
    {
        $coverPath = $post->coverImagePath();
        $coverUrl = $coverPath ? asset($coverPath) : asset('clients/assets/img/posts/default.webp');
        $schema = [
            $this->buildBreadcrumbSchema([
                [
                    'name' => 'Trang chủ',
                    'url' => route('client.home.index'),
                ],
                [
                    'name' => 'Tin tức',
                    'url' => route('client.blog.index'),
                ],
                [
                    'name' => $post->title,
                    'url' => route('client.blog.show', $post),
                ],
            ]),
            [
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $post->title,
                'description' => $post->meta_description ?? $post->excerpt_text,
                'mainEntityOfPage' => route('client.blog.show', $post),
                'datePublished' => optional($post->published_at)->toIso8601String(),
                'dateModified' => optional($post->updated_at)->toIso8601String(),
                'image' => $coverUrl,
                'author' => [
                    '@type' => 'Person',
                    'name' => $post->author?->name ?? $post->creator?->name ?? 'Editorial Team',
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => config('app.name'),
                    'url' => config('app.url') ?? url('/'),
                ],
                'articleSection' => $post->category?->name,
                'keywords' => $tags->pluck('name')->implode(', '),
            ],
        ];

        if ($tags->isNotEmpty()) {
            $schema[] = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'Từ khóa bài viết',
                'itemListElement' => $tags->values()->map(function ($tag, $index) {
                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $tag->name,
                        'url' => route('client.blog.index', ['tag' => $tag->slug]),
                    ];
                }),
            ];
        }

        return $schema;
    }

    protected function buildContentAnchors(?string $content): array
    {
        $content = $content ?? '';

        if (trim($content) === '') {
            return [
                'content' => '',
                'toc' => collect(),
            ];
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $tocItems = [];
        $usedIds = [];

        foreach (['h2', 'h3'] as $tag) {
            $nodes = $dom->getElementsByTagName($tag);

            foreach ($nodes as $node) {
                $text = trim($node->textContent ?? '');

                if ($text === '') {
                    continue;
                }

                $baseId = Str::slug(Str::limit($text, 80, ''));
                if ($baseId === '') {
                    $baseId = 'section-'.(count($tocItems) + 1);
                }

                $id = $baseId;
                $suffix = 1;
                while (in_array($id, $usedIds, true)) {
                    $id = $baseId.'-'.$suffix;
                    $suffix++;
                }

                $usedIds[] = $id;
                $node->setAttribute('id', $id);

                $tocItems[] = [
                    'id' => $id,
                    'label' => $text,
                    'tag' => $tag,
                ];
            }
        }

        return [
            'content' => $dom->saveHTML() ?: $content,
            'toc' => collect($tocItems),
        ];
    }

    protected function resolveTags(Post $post): Collection
    {
        $tagIds = collect($post->tag_ids ?? [])
            ->filter()
            ->unique()
            ->values();

        if ($tagIds->isEmpty()) {
            return collect();
        }

        return Tag::query()
            ->whereIn('id', $tagIds)
            ->where('entity_type', Post::class)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
