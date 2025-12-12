<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $this->getCart($request, withItems: true);

        if ($cart) {
            $cart->items->loadMissing('product.currentFlashSaleItem.flashSale');
            $cart->items->each->syncPrice();
            $cart->setRelation('cartItems', $cart->items);

            Product::preloadImages(
                $cart->items->pluck('product')->filter()
            );
        }

        $productNew = Cache::remember('new_products', 3600, function () {
            $products = Product::active()
                ->orderBy('created_at', 'desc')
                ->inRandomOrder()
                ->limit(9)
                ->get() ?? collect();

            Product::preloadImages($products);

            return $products;
        });

        Product::preloadImages($productNew);

        return view('clients.pages.cart.index', [
            'account' => auth('web')->user(),
            'cart' => $cart,
            'cartItems' => $cart?->items ?? collect(),
            'cartTotal' => $cart?->items?->sum(fn ($item) => $item->subtotal) ?? 0,
            'productNew' => $productNew,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity = $data['quantity'] ?? 1;

        $product = Product::active()
            ->with('currentFlashSaleItem.flashSale')
            ->findOrFail($data['product_id']);

        if ($product->stock_quantity !== null && $product->stock_quantity < 1) {
            throw ValidationException::withMessages([
                'product_id' => 'Sản phẩm đã hết hàng.',
            ]);
        }
        $result = $this->addProductToCart($request, $product, $quantity);

        if ($result['added_quantity'] <= 0) {
            $remaining = null;
            if (! is_null($product->stock_quantity)) {
                $remaining = max(0, (int) $product->stock_quantity - (int) $result['current_quantity']);
            }

            $message = 'Bạn đã thêm tối đa số lượng sản phẩm này vào giỏ hàng.';
            if (! is_null($remaining)) {
                $message = $remaining > 0
                    ? "Chỉ còn lại tối đa {$remaining} sản phẩm có thể thêm vào giỏ."
                    : 'Không thể thêm thêm vì đã hết tồn kho cho sản phẩm này.';
            }

            return redirect()
                ->back()
                ->with('warning', $message);
        }

        return redirect()
            ->back()
            ->with('success', 'Đã thêm sản phẩm vào giỏ hàng.');
    }

    public function addAccessory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity = $data['quantity'] ?? 1;

        $product = Product::active()
            ->with('currentFlashSaleItem.flashSale')
            ->findOrFail($data['product_id']);

        if ($product->stock_quantity !== null && $product->stock_quantity < 1) {
            throw ValidationException::withMessages([
                'product_id' => 'Sản phẩm đã hết hàng.',
            ]);
        }

        $result = $this->addProductToCart($request, $product, $quantity);

        if ($result['added_quantity'] <= 0) {
            $remaining = null;
            if (! is_null($product->stock_quantity)) {
                $remaining = max(0, (int) $product->stock_quantity - (int) $result['current_quantity']);
            }

            $message = 'Bạn đã thêm tối đa số lượng sản phẩm này vào giỏ hàng.';
            if (! is_null($remaining)) {
                $message = $remaining > 0
                    ? "Chỉ còn lại tối đa {$remaining} sản phẩm có thể thêm vào giỏ."
                    : 'Không thể thêm thêm vì đã hết tồn kho cho sản phẩm này.';
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'cart_total_items' => $result['cart_total_items'],
                'cart_total_amount' => $result['cart_total_amount'],
            ], 422);
        }

        Product::preloadImages(collect([$product]));

        $cartItem = $result['cart_item'];

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm sản phẩm đi kèm vào giỏ hàng.',
            'cart_total_items' => $result['cart_total_items'],
            'cart_total_amount' => $result['cart_total_amount'],
            'cart_item' => [
                'id' => $cartItem?->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $result['current_quantity'],
                'price' => $product->resolveCartPrice(),
                'thumbnail' => asset('clients/assets/img/clothes/'.($product->primaryImage->url ?? 'no-image.webp')),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $cart = $this->getCart($request, withItems: true);

        if (! $cart) {
            return redirect()->route('client.cart.index')
                ->with('warning', 'Giỏ hàng của bạn hiện đang trống.');
        }

        $quantities = $request->input('items', []);

        if (! is_array($quantities) || empty($quantities)) {
            return redirect()->route('client.cart.index')
                ->with('info', 'Không có thay đổi nào được gửi lên.');
        }

        DB::transaction(function () use ($cart, $quantities) {
            $cart->items->loadMissing('product.currentFlashSaleItem.flashSale');

            foreach ($cart->items as $item) {
                if (! array_key_exists($item->id, $quantities)) {
                    continue;
                }

                $requestedQuantity = (int) $quantities[$item->id];
                $requestedQuantity = max($requestedQuantity, 0);

                $product = $item->product;

                if (! $product) {
                    $item->delete();

                    continue;
                }

                $unitPrice = $product->resolveCartPrice();
                $maxPerUser = $product->flashSaleLimitPerUser();
                $availableStock = $product->stock_quantity;

                if ($maxPerUser && $requestedQuantity > $maxPerUser) {
                    $requestedQuantity = $maxPerUser;
                }

                if (! is_null($availableStock) && $requestedQuantity > $availableStock) {
                    $requestedQuantity = $availableStock;
                }

                if ($requestedQuantity <= 0) {
                    $item->delete();

                    continue;
                }

                $item->update([
                    'quantity' => $requestedQuantity,
                    'price' => $unitPrice,
                ]);
            }
        });

        return redirect()
            ->route('client.cart.index')
            ->with('success', 'Đã cập nhật giỏ hàng.');
    }

    public function removeItem(Request $request, string $cartItem)
    {
        if (! ctype_digit($cartItem)) {
            return redirect()->route('client.cart.index')
                ->with('warning', 'Yêu cầu không hợp lệ.');
        }

        $cartItemId = (int) $cartItem;

        $cart = $this->getCart($request, withItems: true);

        if (! $cart) {
            return redirect()->route('client.cart.index')
                ->with('info', 'Giỏ hàng của bạn đang trống.');
        }

        $item = $cart->items->firstWhere('id', $cartItemId);

        if (! $item) {
            return redirect()->route('client.cart.index')
                ->with('warning', 'Không tìm thấy sản phẩm trong giỏ hàng.');
        }

        $item->delete();

        return redirect()->route('client.cart.index')
            ->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    public function removeAll(Request $request)
    {
        $cart = $this->getCart($request, withItems: true);

        if (! $cart) {
            return redirect()->route('client.cart.index')
                ->with('info', 'Giỏ hàng của bạn đang trống.');
        }

        $cart->items()->delete();
        $cart->update([
            'quantity' => 0,
        ]);

        return redirect()->route('client.cart.index')
            ->with('success', 'Đã xóa toàn bộ sản phẩm trong giỏ hàng.');
    }

    protected function getCart(Request $request, bool $withItems = false, bool $createIfMissing = false, ?int $seedProductId = null): ?Cart
    {
        $accountId = auth('web')->id();
        $sessionId = $request->session()->getId();

        $query = Cart::query();

        if ($accountId) {
            $query->where('account_id', $accountId);
        } else {
            $query->whereNull('account_id')->where('session_id', $sessionId);
        }

        if ($withItems) {
            $query->with([
                'items.product.currentFlashSaleItem.flashSale',
            ]);
        }

        $cart = $query->latest('id')->first();

        if (! $cart && $createIfMissing) {
            $cart = Cart::create([
                'account_id' => $accountId,
                'session_id' => $accountId ? null : $sessionId,
                'product_id' => $seedProductId ?? 0,
                'quantity' => 0,
            ]);
        } elseif ($cart && $seedProductId && ! $cart->product_id) {
            $cart->update(['product_id' => $seedProductId]);
        }

        return $cart?->loadMissing('items');
    }

    protected function addProductToCart(Request $request, Product $product, int $quantity = 1): array
    {
        $cart = $this->getCart($request, createIfMissing: true, seedProductId: $product->id);

        $result = [
            'added_quantity' => 0,
            'current_quantity' => 0,
            'cart_item' => null,
        ];

        DB::transaction(function () use ($cart, $product, $quantity, &$result) {
            $cartItem = $cart->items()->where('product_id', $product->id)->lockForUpdate()->first();
            $unitPrice = $product->resolveCartPrice();
            $maxPerUser = $product->flashSaleLimitPerUser();

            $isFlashSale = $product->isInFlashSale();
            $flashSaleItem = $isFlashSale ? $product->currentFlashSaleItem()->first() : null;

            $originalQuantity = $cartItem ? $cartItem->quantity : 0;
            $newQuantity = $originalQuantity + $quantity;

            if ($maxPerUser && $newQuantity > $maxPerUser) {
                $newQuantity = $maxPerUser;
            }

            if ($product->stock_quantity !== null && $newQuantity > $product->stock_quantity) {
                $newQuantity = $product->stock_quantity;
            }

            if ($cartItem) {
                $cartItem->update([
                    'quantity' => $newQuantity,
                    'price' => $unitPrice,
                    'is_flash_sale' => $isFlashSale,
                    'flash_sale_item_id' => $flashSaleItem?->id,
                ]);
            } else {
                $cartItem = $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $newQuantity,
                    'price' => $unitPrice,
                    'is_flash_sale' => $isFlashSale,
                    'flash_sale_item_id' => $flashSaleItem?->id,
                    'uuid' => (string) Str::uuid(),
                ]);
            }

            $result['added_quantity'] = max($newQuantity - $originalQuantity, 0);
            $result['current_quantity'] = $newQuantity;
            $result['cart_item'] = $cartItem;
        });

        $cart->loadMissing('items.product');

        $cartItem = $cart->items->firstWhere('product_id', $product->id);

        return [
            'cart' => $cart,
            'product' => $product,
            'cart_item' => $cartItem,
            'cart_total_items' => $cart->items->sum(fn ($item) => $item->quantity),
            'cart_total_amount' => $cart->items->sum(fn ($item) => $item->subtotal),
            'added_quantity' => $result['added_quantity'],
            'current_quantity' => $result['current_quantity'],
        ];
    }
}
