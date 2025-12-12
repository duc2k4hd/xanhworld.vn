<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Newsletter;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function index()
    {
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
                ->take(18)
                ->get() ?? collect();
            Product::preloadImages($products);

            return $products;
        });
        Product::preloadImages($productsFeatured);

        $productRandom = Cache::remember('products_random_home', now()->addDays(30), function () {
            $products = Product::active()
                ->withApprovedCommentsMeta()
                ->when(
                    $category = Category::where('slug', 'cay-phong-thuy')->first(),
                    function ($query) use ($category) {
                        $query->inCategory([$category->id]);
                    }
                )->inRandomOrder()->limit(20)->get();

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
                        $productQuery->where('is_active', true)->withApprovedCommentsMeta();
                    },
                    'items.product.primaryCategory',
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
                }
            } else {
                // Flash sale không còn đang chạy (đã tắt, đã kết thúc, hoặc chưa bắt đầu)
                $flashSale = null;
            }
        }

        return view('clients.pages.home.index', compact('banners_home_parent', 'banners_home_children', 'vouchers', 'productsFeatured', 'productRandom', 'flashSale'));
    }

    public function newsletter(Request $request)
    {
        $email = $request->input('xanhworld_main_newsletter_email');

        $validator = Validator::make(
            ['email' => $email],
            [
                'email' => ['required', 'email', 'max:80'],
            ],
            [
                'email.required' => 'Vui lòng nhập địa chỉ email.',
                'email.email' => 'Địa chỉ email không hợp lệ.',
                'email.max' => 'Địa chỉ email không được vượt quá 80 ký tự.',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $email = $validated['email'];

        // Rate limit nhẹ theo email để tránh spam
        $rateKey = 'newsletter_email_'.sha1($email);
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return redirect()->back()
                ->with('error', "Bạn thao tác quá nhanh, vui lòng thử lại sau {$seconds} giây.")
                ->withInput();
        }
        RateLimiter::hit($rateKey, 3600);

        try {
            $newsletter = Newsletter::where('email', $email)->first();

            if (! $newsletter) {
                $newsletter = Newsletter::create([
                    'email' => $email,
                    'ip' => $request->ip(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => Newsletter::STATUS_PENDING,
                    'source' => 'homepage_form',
                    'verify_token' => bin2hex(random_bytes(32)),
                    'is_verified' => false,
                ]);
            } else {
                // Nếu đã hủy hoặc pending, cho phép đăng ký lại
                if ($newsletter->status === Newsletter::STATUS_SUBSCRIBED) {
                    return redirect()->back()
                        ->with('success', 'Email này đã đăng ký nhận bản tin rồi. Cảm ơn bạn!')
                        ->withInput();
                }

                $newsletter->fill([
                    'status' => Newsletter::STATUS_PENDING,
                    'verify_token' => bin2hex(random_bytes(32)),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])->save();
            }

            // TODO: gọi NewsletterService::sendVerifyEmail($newsletter) khi service sẵn sàng

            return redirect()->back()
                ->with('success', 'Cảm ơn bạn đã đăng ký. Vui lòng kiểm tra email để xác nhận đăng ký nhận bản tin!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Đã có lỗi xảy ra. Vui lòng thử lại sau.')
                ->withInput();
        }
    }
}
