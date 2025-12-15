<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function index(Request $request, ?string $slug = null)
    {
        $settings = View::shared('settings') ?? Setting::first();
        $keyword = $this->sanitizeKeyword($request->input('keyword', ''));
        $filters = $this->resolveFilters($request);
        $categoryContext = $this->resolveCategoryContext($slug ?? $request->input('category'));

        if ($categoryContext['slug'] && ! $categoryContext['category']) {
            return view('clients.pages.errors.404');
        }

        $baseQuery = $this->baseProductQuery();

        if (! empty($categoryContext['ids'])) {
            $baseQuery->inCategory($categoryContext['ids']);
        }

        if ($keyword !== '') {
            $this->applyKeywordFilter($baseQuery, $keyword);
        }

        $filteredQuery = $this->applyFilters(clone $baseQuery, $filters);

        $productsForView = clone $filteredQuery;
        $productsMain = $this->buildProductListing(clone $filteredQuery, $filters);
        $newProducts = $this->resolveNewProducts(clone $filteredQuery);

        $seoMeta = $this->prepareSeoMeta($settings, $categoryContext['category'], $keyword);

        return view('clients.pages.shop.index', [
            'products' => $productsForView,
            'productsMain' => $productsMain,
            'newProducts' => $newProducts,
            'keyword' => $keyword,
            'selectedCategory' => $categoryContext['category'],
            'selectedCategorySlug' => $categoryContext['slug'],
            'category' => $categoryContext['category'],
            'perPage' => $filters['perPage'],
            'minPriceRange' => $filters['minPriceRange'],
            'maxPriceRange' => $filters['maxPriceRange'],
            'minRating' => $filters['minRating'],
            'tags' => $filters['tags'],
            'sort' => $filters['sort'],
            'pageTitle' => $seoMeta['title'],
            'pageDescription' => $seoMeta['description'],
            'pageKeywords' => $seoMeta['keywords'],
            'canonicalUrl' => $seoMeta['canonical'],
            'pageImage' => $seoMeta['image'],
        ]);
    }

    public function search(Request $request)
    {
        $keyword = $this->sanitizeKeyword($request->input('keyword', ''));

        if ($keyword === '') {
            return response()->json([]);
        }

        $products = Product::query()
            ->select(['id', 'name', 'slug', 'price', 'sale_price'])
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('sku', 'like', "%{$keyword}%");
            })
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function searchKeyword(Request $request)
    {
        $settings = View::shared('settings') ?? Setting::first();
        $keyword = $this->sanitizeKeyword($request->input('keyword', ''));
        $filters = $this->resolveFilters($request);
        $categoryContext = $this->resolveCategoryContext($request->input('category'));

        if ($keyword !== '' && ($skuProduct = $this->findProductBySku($keyword))) {
            return redirect()
                ->route('client.product.detail', $skuProduct->slug)
                ->with('success', 'Chuyển đến trang sản phẩm theo SKU: '.$skuProduct->sku);
        }

        $baseQuery = $this->baseProductQuery();

        if (! empty($categoryContext['ids'])) {
            $baseQuery->inCategory($categoryContext['ids']);
        }

        if ($keyword !== '') {
            $this->applyKeywordFilter($baseQuery, $keyword);
            $baseQuery->orderByRaw(
                'CASE WHEN name LIKE ? THEN 1 WHEN name LIKE ? THEN 2 ELSE 3 END',
                ["%{$keyword}%", "{$keyword}%"]
            )->orderBy('name');
        }

        $filteredQuery = $this->applyFilters(clone $baseQuery, $filters);

        $productsForView = clone $filteredQuery;
        $productsMain = $this->buildProductListing(clone $filteredQuery, $filters);
        $newProducts = $this->resolveNewProducts(clone $filteredQuery);

        $defaultSiteName = $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD';
        $seoMeta = [
            'title' => "Kết quả tìm kiếm cho '{$keyword}' - {$defaultSiteName}",
            'description' => "Tìm thấy các sản phẩm liên quan đến '{$keyword}' tại {$defaultSiteName}.",
            'keywords' => $keyword.', shop '.$defaultSiteName.', cây cảnh',
            'canonical' => ($settings->site_url ?? url('/')).'/shop/search?keyword='.urlencode($keyword),
            'image' => asset('clients/assets/img/business/'.($settings->site_banner ?? $settings->site_logo)),
        ];

        return view('clients.pages.shop.index', [
            'products' => $productsForView,
            'productsMain' => $productsMain,
            'newProducts' => $newProducts,
            'keyword' => $keyword,
            'selectedCategory' => $categoryContext['category'],
            'selectedCategorySlug' => $categoryContext['slug'],
            'category' => $categoryContext['category'],
            'perPage' => $filters['perPage'],
            'minPriceRange' => $filters['minPriceRange'],
            'maxPriceRange' => $filters['maxPriceRange'],
            'minRating' => $filters['minRating'],
            'tags' => $filters['tags'],
            'sort' => $filters['sort'],
            'pageTitle' => $seoMeta['title'],
            'pageDescription' => $seoMeta['description'],
            'pageKeywords' => $seoMeta['keywords'],
            'canonicalUrl' => $seoMeta['canonical'],
            'pageImage' => $seoMeta['image'],
        ]);
    }

    protected function baseProductQuery(): Builder
    {
        return Product::query()
            ->select([
                'id',
                'name',
                'slug',
                'sku',
                'price',
                'sale_price',
                'stock_quantity',
                'primary_category_id',
                'image_ids',
                'is_featured',
            ])
            ->active()
            ->withApprovedCommentsMeta()
            ->with('variants');
    }

    protected function resolveFilters(Request $request): array
    {
        $perPageOptions = [12, 24, 30, 48, 60];
        $perPage = (int) $request->input('perPage', 30);
        if (! in_array($perPage, $perPageOptions, true)) {
            $perPage = 30;
        }

        $minPriceRange = $request->filled('minPriceRange') ? max(0, (int) $request->input('minPriceRange')) : null;
        $maxPriceRange = $request->filled('maxPriceRange') ? max(0, (int) $request->input('maxPriceRange')) : null;

        if (! is_null($minPriceRange) && ! is_null($maxPriceRange) && $minPriceRange > $maxPriceRange) {
            [$minPriceRange, $maxPriceRange] = [$maxPriceRange, $minPriceRange];
        }

        $minRating = $request->filled('minRating') ? max(1, min(5, (int) $request->input('minRating'))) : null;

        $tags = $request->input('tags', []);
        if (! is_array($tags)) {
            $tags = explode(',', (string) $tags);
        }
        $tags = array_values(array_filter(array_map('intval', $tags), fn ($id) => $id > 0));

        $allowedSort = ['default', 'newest', 'price-asc', 'price-desc', 'name-asc', 'name-desc'];
        $sort = $request->input('sort', 'default');
        if (! in_array($sort, $allowedSort, true)) {
            $sort = 'default';
        }

        return [
            'perPage' => $perPage,
            'minPriceRange' => $minPriceRange,
            'maxPriceRange' => $maxPriceRange,
            'minRating' => $minRating,
            'tags' => $tags,
            'sort' => $sort,
        ];
    }

    protected function resolveCategoryContext(?string $slug): array
    {
        $slug = $this->sanitizeSlug($slug);
        if (! $slug) {
            return ['category' => null, 'ids' => [], 'slug' => null];
        }

        $category = Category::query()
            ->with('parent')
            ->active()
            ->where('slug', $slug)
            ->first();

        if (! $category) {
            return ['category' => null, 'ids' => [], 'slug' => $slug];
        }

        $ids = $this->collectDescendantIds($category);

        return ['category' => $category, 'ids' => $ids, 'slug' => $slug];
    }

    protected function collectDescendantIds(Category $category): array
    {
        $ids = [$category->id];
        $currentLevel = [$category->id];

        while (! empty($currentLevel)) {
            $children = Category::query()
                ->whereIn('parent_id', $currentLevel)
                ->pluck('id')
                ->all();

            if (empty($children)) {
                break;
            }

            $ids = array_merge($ids, $children);
            $currentLevel = $children;
        }

        return array_values(array_unique($ids));
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $priceExpression = $this->priceExpression();

        if (! is_null($filters['minPriceRange']) && ! is_null($filters['maxPriceRange'])) {
            $query->whereBetween(DB::raw($priceExpression), [$filters['minPriceRange'], $filters['maxPriceRange']]);
        } elseif (! is_null($filters['minPriceRange'])) {
            $query->where(DB::raw($priceExpression), '>=', $filters['minPriceRange']);
        } elseif (! is_null($filters['maxPriceRange'])) {
            $query->where(DB::raw($priceExpression), '<=', $filters['maxPriceRange']);
        }

        if (! is_null($filters['minRating'])) {
            $query->whereHas('comments', function ($q) use ($filters) {
                $q->where('is_approved', true)->where('rating', '>=', $filters['minRating']);
            });
        }

        if (! empty($filters['tags'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['tags'] as $tagId) {
                    $q->orWhereJsonContains('tag_ids', $tagId);
                }
            });
        }

        return $query;
    }

    protected function applyKeywordFilter(Builder $query, string $keyword): void
    {
        $words = array_filter(explode(' ', $keyword));

        $query->where(function ($q) use ($keyword, $words) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('slug', 'like', "%{$keyword}%")
                ->orWhere('sku', 'like', "%{$keyword}%");

            foreach ($words as $word) {
                $q->orWhere('name', 'like', "%{$word}%")
                    ->orWhere('slug', 'like', "%{$word}%")
                    ->orWhere('sku', 'like', "%{$word}%");
            }
        });
    }

    protected function buildProductListing(Builder $query, array $filters)
    {
        $this->applySorting($query, $filters['sort']);

        $paginator = $query
            ->paginate($filters['perPage'])
            ->withQueryString();

        // Preload images để tránh N+1 queries
        Product::preloadImages($paginator->items());

        return $paginator;
    }

    protected function applySorting(Builder $query, string $sort): void
    {
        $priceExpression = $this->priceExpression();

        switch ($sort) {
            case 'price-asc':
                $query->orderByRaw("{$priceExpression} ASC")->orderBy('created_at', 'desc');
                break;
            case 'price-desc':
                $query->orderByRaw("{$priceExpression} DESC")->orderBy('created_at', 'desc');
                break;
            case 'name-asc':
                $query->orderBy('name')->orderBy('created_at', 'desc');
                break;
            case 'name-desc':
                $query->orderBy('name', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'newest':
            case 'default':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }

    protected function resolveNewProducts(Builder $query)
    {
        $products = $query
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // Preload images để tránh N+1 queries
        Product::preloadImages($products);

        return $products;
    }

    protected function prepareSeoMeta(object $settings, ?Category $category, string $keyword): array
    {
        $defaultSiteName = $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD';

        if ($category) {
            return [
                'title' => $category->meta_title
                    ? $category->meta_title.' – '.$defaultSiteName
                    : $category->name.' - '.$defaultSiteName,
                'description' => $category->meta_description
                    ?? strip_tags($category->description ?: 'Khám phá các sản phẩm '.$category->name.' chất lượng tại '.$defaultSiteName.'.'),
                'keywords' => $category->meta_keywords
                    ?? $category->name.', cây xanh, chậu cảnh, '.$defaultSiteName,
                'canonical' => $category->meta_canonical
                    ?? ($settings->site_url ? $settings->site_url.'/'.$category->slug : url()->current()),
                'image' => $category->image
                    ? asset('storage/categories/'.$category->image)
                    : asset('clients/assets/img/business/'.($settings->site_banner ?? $settings->site_logo)),
            ];
        }

        if ($keyword !== '') {
            return [
                'title' => 'Kết quả cho '.$keyword.' - '.$defaultSiteName,
                'description' => 'Tìm kiếm sản phẩm liên quan tới '.$keyword.' tại '.$defaultSiteName.'.',
                'keywords' => $keyword.', shop '.$defaultSiteName.', cây cảnh',
                'canonical' => url()->current(),
                'image' => asset('clients/assets/img/business/'.($settings->site_banner ?? $settings->site_logo)),
            ];
        }

        return [
            'title' => 'Shop '.$defaultSiteName.' - Thế giới cây xanh',
            'description' => 'Khám phá bộ sưu tập cây xanh, chậu cảnh và phụ kiện tại '.$defaultSiteName.'.',
            'keywords' => 'shop '.$defaultSiteName.', cây xanh, chậu cảnh, phụ kiện garden',
            'canonical' => $settings->site_url ? $settings->site_url.'/shop' : url()->current(),
            'image' => asset('clients/assets/img/business/'.($settings->site_banner ?? $settings->site_logo)),
        ];
    }

    protected function findProductBySku(string $keyword): ?Product
    {
        return Product::active()
            ->whereRaw('LOWER(sku) = ?', [strtolower($keyword)])
            ->first();
    }

    protected function sanitizeKeyword(string $keyword): string
    {
        $keyword = strip_tags($keyword);
        $keyword = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $keyword);
        $keyword = preg_replace('/\s+/u', ' ', $keyword);
        $keyword = mb_substr($keyword, 0, 100);

        return trim($keyword);
    }

    protected function sanitizeSlug(?string $slug): ?string
    {
        if (! $slug) {
            return null;
        }

        $slug = Str::slug(strip_tags($slug));

        return $slug === '' ? null : $slug;
    }

    protected function priceExpression(): string
    {
        return 'COALESCE(NULLIF(sale_price, 0), price)';
    }
}
