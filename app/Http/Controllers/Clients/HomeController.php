<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function index()
    {
        // --- Helper: Chuẩn bị Variants Data for Blade ---
        $prepareVariants = function(Collection $products) {
            foreach ($products as $product) {
                $vData = [];
                if ($product->relationLoaded('variants')) {
                    foreach ($product->variants as $v) {
                        $attrs = is_array($v->attributes) ? $v->attributes : (is_string($v->attributes) ? json_decode($v->attributes, true) : []);
                        $details = [];
                        if (!empty($attrs['size'])) $details[] = $attrs['size'];
                        if (!empty($attrs['has_pot']) && $attrs['has_pot']) $details[] = 'Có chậu';
                        if (!empty($attrs['combo_type'])) $details[] = $attrs['combo_type'];
                        if (!empty($attrs['notes'])) $details[] = $attrs['notes'];
                        $vData[] = [
                            'id' => $v->id,
                            'name' => $v->name,
                            'price' => $v->price,
                            'sale_price' => $v->sale_price,
                            'display_price' => $v->display_price,
                            'stock_quantity' => $v->stock_quantity,
                            'is_active' => $v->is_active,
                            'details' => $details,
                            'is_on_sale' => $v->isOnSale(),
                            'discount_percent' => $v->discount_percent,
                        ];
                    }
                }
                $product->variants_json_data = $vData;
            }
        };

        $banners_home_parent = Cache::remember('banners_home_parent', now()->addDays(30), function () {
            return Banner::active()->where('position', 'homepage_banner_parent')->get();
        });
        $banners_home_children = Cache::remember('banners_home_children', now()->addDays(30), function () {
            return Banner::active()->where('position', 'homepage_banner_children')->get();
        });

        $vouchers = Cache::remember('vouchers_home', now()->addDays(30), function () {
            return Voucher::active()->limit(3)->get();
        });

        $productsFeatured = Cache::remember('products_featured_home', now()->addDays(30), function () {
            $products = Product::active()
                ->featured()
                ->withApprovedCommentsMeta()
                ->with(['variants', 'primaryCategory'])
                ->take(18)
                ->get() ?? collect();
            Product::preloadImages($products);

            return $products;
        });
        Product::preloadImages($productsFeatured);

        $productRandom = Cache::remember('products_random_home', now()->addDays(30), function () {
            $products = Product::active()
                ->withApprovedCommentsMeta()
                ->with(['variants', 'primaryCategory'])
                ->when(
                    $category = Category::where('slug', 'cay-phong-thuy')->first(),
                    function ($query) use ($category) {
                        $query->inCategory([$category->id]);
                    }
                )->latest()->limit(20)->get() ?? collect();

            if ($products->count() > 9) {
                $products = $products->random(min(9, $products->count()));
            }

            Product::preloadImages($products);

            return $products;
        });
        Product::preloadImages($productRandom);

        $flashSale = Cache::remember('flash_sale_data', 3600, function () {
            return FlashSale::where('is_active', true)
                ->where('status', 'active')
                ->where('start_time', '<=', now())  // Đã bắt đầu
                ->where('end_time', '>=', now())    // Chưa kết thúc
                ->whereHas('items', function ($query) {
                    $query->where('is_active', true)
                        ->whereRaw('stock > sold')
                        ->whereHas('product', function ($productQuery) {
                            $productQuery->where('is_active', true)
                                ->where('stock_quantity', '>', 0);
                        });
                })
                ->orderBy('start_time', 'desc')
                ->with([
                    'items' => function ($query) {
                        $query->where('is_active', true)
                            ->whereRaw('stock > sold')
                            ->whereHas('product', function ($productQuery) {
                                $productQuery->where('is_active', true)
                                    ->where('stock_quantity', '>', 0);
                            })
                            ->orderBy('sort_order')
                            ->orderBy('id');
                    },
                    'items.product' => function ($productQuery) {
                        $productQuery->where('is_active', true)
                            ->withApprovedCommentsMeta()
                            ->with(['variants', 'primaryCategory']);
                    },
                ])
                ->first()
                ?->makeHidden([
                    'start_time', 'end_time', 'created_at', 'updated_at',
                ]);
        });

        if ($flashSale) {
            Product::preloadImages(
                $flashSale->items->pluck('product')->filter()
            );
        }

        // 2. Lấy thời gian realtime và kiểm tra lại điều kiện
        if ($flashSale) {
            $flashSaleTime = FlashSale::where('id', $flashSale->id)
                ->select('id', 'start_time', 'end_time', 'is_active', 'status')
                ->first();

            // 3. Kiểm tra lại điều kiện: phải đang chạy (không phải scheduled)
            if ($flashSaleTime
                && $flashSaleTime->is_active
                && $flashSaleTime->status === 'active'
                && $flashSaleTime->start_time <= now()
                && $flashSaleTime->end_time >= now()) {

                $flashSale->start_time = $flashSaleTime->start_time;
                $flashSale->end_time = $flashSaleTime->end_time;

                // Lọc lại items nếu cần (đảm bảo chỉ lấy items active và còn hàng)
                $filteredItems = $flashSale->items->filter(function ($item) {
                    return $item->is_active
                        && ($item->stock > $item->sold)
                        && $item->product
                        && $item->product->is_active
                        && ($item->product->stock_quantity > 0);
                });

                // Nếu không có items active thì không hiển thị flash sale
                if ($filteredItems->isEmpty()) {
                    $flashSale = null;
                } else {
                    $flashSale->setRelation('items', $filteredItems);
                
                    // Chuẩn bị variants data cho Flash Sale
                    $prepareVariants($flashSale->items->pluck('product')->filter());
                }
            } else {
                // Flash sale không còn đang chạy (đã tắt, đã kết thúc, hoặc chưa bắt đầu)
                $flashSale = null;
            }
        }

        // --- NEW: Tối ưu hóa Category Counts (Bulk calculation) ---
        // Thay vì để Blade chạy count() hàng chục lần, ta tính 1 lần ở đây
        $categoryCounts = Cache::remember('homepage_category_counts', 1800, function() {
            $productCategories = Product::active()
                ->select('primary_category_id', 'category_ids')
                ->get();
            
            $counts = [];
            foreach ($productCategories as $p) {
                // Primary
                if ($p->primary_category_id) {
                    $counts[$p->primary_category_id] = ($counts[$p->primary_category_id] ?? 0) + 1;
                }
                // Extra
                $extra = is_array($p->category_ids) ? $p->category_ids : json_decode($p->category_ids, true);
                if (is_array($extra)) {
                    foreach ($extra as $cid) {
                        $counts[$cid] = ($counts[$cid] ?? 0) + 1;
                    }
                }
            }
            return $counts;
        });

        $prepareVariants($productsFeatured);
        $prepareVariants($productRandom);

        return view('clients.pages.home.index', compact(
            'banners_home_parent', 
            'banners_home_children', 
            'vouchers', 
            'productsFeatured', 
            'productRandom', 
            'flashSale', 
            'categoryCounts'
        ));
    }
}
