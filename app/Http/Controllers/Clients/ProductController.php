<?php

namespace App\Http\Controllers\Clients;

use App\Helpers\CategoryHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Voucher;
use App\Services\ProductViewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function __construct(
        private ProductViewService $productViewService
    ) {}

    public function detail($slug)
    {
        $quantityProductDetail = Product::where('slug', $slug)
            ->active()
            ->value('stock_quantity') ?? 0;
        $product = Cache::rememberForever('product_detail_'.$slug, function () use ($slug) {
            $product = Product::where('slug', $slug)
                ->active()
                ->with('variants')
                ->first();

            if ($product) {
                Product::preloadImages([$product]);
            }

            return $product;
        });

        if ($product) {
            Product::preloadImages([$product]);
            // Load variants nếu chưa có
            if (! $product->relationLoaded('variants')) {
                $product->load('variants');
            }
        }

        if (! $product) {
            return view('clients.pages.errors.404');
        }

        // Record product view
        $this->productViewService->recordView($product);

        $vouchers = Cache::remember('vouchers_for_product_'.$product->id, 3600, function () {
            return Voucher::active()
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get();
        });

        $productNew = Cache::remember('new_products', 3600, function () use ($product) {
            $products = Product::active()
                ->where('id', '!=', $product->id)
                ->orderBy('created_at', 'desc')
                ->inRandomOrder()
                ->limit(9)
                ->withApprovedCommentsMeta()
                ->get() ?? collect();

            Product::preloadImages($products);

            return $products;
        });

        Product::preloadImages($productNew);

        // Sản phẩm liên quan: lấy 5 sản phẩm trước và 5 sản phẩm sau, cache vĩnh viễn
        $productRelated = Cache::rememberForever('related_products_'.$product->id, function () use ($product) {
            // Hàm getRelatedProducts đã tự preload images
            return Product::getRelatedProducts($product, 10);
        });

        // Sản phẩm đi kèm theo danh mục category_included_ids (nếu có)
        $includedProducts = collect();
        $includedCategoryIds = collect($product->category_included_ids ?? [])
            ->filter(fn ($id) => ! empty($id))
            ->unique()
            ->values();

        if ($includedCategoryIds->isNotEmpty()) {
            $cacheKey = 'included_products_'.$product->id.'_'.md5($includedCategoryIds->join('-'));
            $cachedSets = Cache::remember(
                $cacheKey,
                now()->addHours(6),
                function () use ($product, $includedCategoryIds) {
                    $sets = [];

                    foreach ($includedCategoryIds as $categoryId) {
                        $category = Category::select('id', 'name', 'slug')->find($categoryId);
                        if (! $category) {
                            continue;
                        }

                        $descendantIds = CategoryHelper::getDescendants($categoryId);

                        $products = Product::query()
                            ->active()
                            ->where('id', '!=', $product->id)
                            ->where(function ($q) use ($descendantIds) {
                                $q->whereIn('primary_category_id', $descendantIds)
                                    ->orWhere(function ($sub) use ($descendantIds) {
                                        foreach ($descendantIds as $id) {
                                            $sub->orWhereJsonContains('category_ids', (int) $id)
                                                ->orWhereJsonContains('category_ids', (string) $id);
                                        }
                                    });
                            })
                            ->with('variants')
                            ->inRandomOrder()
                            ->limit(10)
                            ->get();

                        if ($products->isEmpty()) {
                            continue;
                        }

                        Product::preloadImages($products);

                        $sets[] = [
                            'category' => $category,
                            'products' => $products,
                        ];
                    }

                    return $sets;
                }
            );

            $includedProducts = collect($cachedSets);
        }

        // Load comments và rating stats - chỉ load 10 đầu tiên
        $comments = Comment::where('commentable_type', 'product')
            ->where('commentable_id', $product->id)
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
        $totalComments = Comment::where('commentable_type', 'product')
            ->where('commentable_id', $product->id)
            ->whereNull('parent_id')
            ->approved()
            ->count();

        $commentService = app(\App\Services\CommentService::class);
        $ratingStats = $commentService->calculateRatingStats('product', $product->id);

        // 5 đánh giá mới nhất cho schema Product
        $latestReviews = Comment::where('commentable_type', 'product')
            ->where('commentable_id', $product->id)
            ->whereNull('parent_id')
            ->approved()
            ->whereNotNull('rating')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('clients.pages.single.index',
            compact('product', 'vouchers', 'productNew', 'productRelated', 'includedProducts', 'quantityProductDetail', 'comments', 'ratingStats', 'latestReviews', 'totalComments')
        );
    }

    public function wishlist(Request $request)
    {
        $productID = $request->input('product_id');
        $query = Favorite::where('product_id', $productID);

        if (auth('web')->check()) {
            // user đăng nhập
            $query->where('account_id', auth('web')->id());
        } else {
            // user khách dùng session
            $query->where('session_id', session()->getId());
        }

        $wishlist = $query->first();

        if ($wishlist) {
            return redirect()->back()->with('error', 'Sản phẩm đã có trong danh sách yêu thích.');
        }
        $product = Product::where('id', $productID)->active()->first();

        if (! $product) {
            return redirect()->back()->with('error', 'Sản phẩm không tồn tại.');
        }

        try {
            $accountID = auth('web')->user()->id ?? null;
            if ($accountID) {
                Favorite::firstOrCreate([
                    'account_id' => $accountID,
                    'product_id' => $productID,
                    'session_id' => null,
                ]);
            } else {
                Favorite::firstOrCreate([
                    'account_id' => null,
                    'product_id' => $productID,
                    'session_id' => session()->getId(),
                ]);
            }

            return redirect()->back()->with('success', 'Thêm vào danh sách yêu thích thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi thêm vào danh sách yêu thích.');
        }
    }

    public function wishlistRemove(Request $request)
    {
        $productID = $request->input('product_id');

        // Nếu user đăng nhập
        $accountID = auth('web')->user()->id ?? null;

        // Query chung
        $query = Favorite::where('product_id', $productID);

        if ($accountID) {
            $query->where('account_id', $accountID);
        } else {
            $query->where('session_id', session()->getId());
        }

        // Lấy bản ghi
        $favorite = $query->first();

        if (! $favorite) {
            $request->merge(['product_id' => $productID]);

            return $this->wishlist($request);
        }

        try {
            $favorite->delete();

            return redirect()->back()->with('success', 'Đã xóa khỏi danh sách yêu thích.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Không thể xóa sản phẩm này.');
        }
    }

    /**
     * Nhận yêu cầu gọi tư vấn từ trang chi tiết sản phẩm.
     */
    public function phoneRequest(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'phone' => ['required', 'regex:/^[0-9]{10,11}$/'],
        ], [
            'product_id.required' => 'Thiếu mã sản phẩm.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không hợp lệ (10-11 chữ số).',
        ]);

        return redirect()->back()->with('success', 'Đã nhận số điện thoại, chúng tôi sẽ liên hệ sớm.');
    }
}
