<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        // --- SETTINGS ---
        if (Schema::hasTable('settings')) {
            $settings = Cache::rememberForever('settings', function () {
                return Setting::active()
                    ->get() // ❗ quan trọng
                    ->mapWithKeys(fn ($s) => [$s->key => $s->getParsedValue()])
                    ->toArray();
            });
            View::share('settings', (object) $settings);
        }

        // --- CATEGORIES ---
        if (Schema::hasTable('categories')) {
            $categories = Category::query()->active()
                ->whereNull('parent_id')
                ->with('children.children')   // tự sort theo quan hệ
                ->get();
            View::share('categories', $categories);
        }

        // --- ACCOUNT + CART (Global composer) ---
        View::composer('*', function ($view) {
            // Không cache để đảm bảo luôn lấy dữ liệu mới nhất, đặc biệt sau khi đăng xuất
            $accountId = auth('web')->id();
            $sessionId = session()->getId();

                try {
                    $account = auth('web')->user() ?? null;

                // Lấy cart: nếu đã đăng nhập thì lấy theo account_id, nếu chưa thì lấy theo session_id
                    $cartQuery = Cart::query()->active()->with(['items' => function ($q) {
                        $q->where(function ($q2) {
                            $q2->whereNull('status')->orWhere('status', 'active');
                        });
                    }]);

                if ($accountId) {
                    // Đã đăng nhập: chỉ lấy cart của account này
                    $cartQuery->where('account_id', $accountId);
                    } else {
                    // Chưa đăng nhập: chỉ lấy cart theo session và không có account_id
                    // QUAN TRỌNG: Phải đảm bảo account_id là NULL để không lấy cart của user khác
                        $cartQuery->whereNull('account_id')->where('session_id', $sessionId);
                    }

                    $cart = $cartQuery->orderByDesc('id')->first();

                // Đếm số lượng cart items
                    $cartItemSumQuery = CartItem::query()->active()
                        ->where(function ($q) {
                            $q->whereNull('status')->orWhere('status', 'active');
                        })
                    ->whereHas('cart', function ($q) use ($accountId, $sessionId) {
                        if ($accountId) {
                            $q->where('account_id', $accountId);
                            } else {
                            // QUAN TRỌNG: Phải đảm bảo account_id là NULL
                                $q->whereNull('account_id')->where('session_id', $sessionId);
                            }
                        });

                    $cartCount = (int) ($cartItemSumQuery->sum('quantity') ?? 0);
                    $cartLink = $cartCount > 0 ? route('client.cart.index') : null;

                // Lấy favorites
                $favorites = Favorite::ofOwner($accountId, $sessionId)->pluck('product_id');
                    $favCount = $favorites->count();
                    $favIds = $favorites->toArray();
                $favLink = $favCount > 0 ? route('client.wishlist.index') : null;

                    $sharedPayload = [
                        'account' => $account,
                        'cart' => $cart,
                        'cartCount' => $cartCount,
                        'cartLink' => $cartLink,
                        'cartQuantity' => $cartCount,
                        'cartQty' => $cartCount,
                        'cart_items_count' => $cartCount,
                        'cartUrl' => $cartLink,
                        'wishlistCount' => $favCount,
                        'wishlistLink' => $favLink,
                        'favoriteProductIds' => $favIds,
                    ];
                } catch (Throwable $e) {
                    Log::debug('Trình soạn thảo ViewServiceProvider đã bỏ qua', [
                    'error' => $e->getMessage(),
                    ]);

                    $sharedPayload = [
                        'account' => null,
                        'cart' => null,
                        'cartCount' => 0,
                        'cartLink' => null,
                        'cartQuantity' => 0,
                        'cartQty' => 0,
                        'cart_items_count' => 0,
                        'cartUrl' => null,
                        'wishlistCount' => 0,
                        'wishlistLink' => null,
                        'favoriteProductIds' => [],
                    ];
            }

            foreach ($sharedPayload as $key => $value) {
                $view->with($key, $value);
            }

        });
    }
}
