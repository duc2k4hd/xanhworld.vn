<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Tính lại đơn giá cho từng item và cập nhật tổng tiền / số lượng (accessor).
     */
    public function recalculateTotals(Cart $cart): Cart
    {
        $cart->loadMissing('items.product.currentFlashSaleItem.flashSale');

        DB::transaction(function () use ($cart) {
            foreach ($cart->items as $item) {
                if (! $item->product) {
                    continue;
                }

                $item->product->loadMissing('currentFlashSaleItem.flashSale');

                $item->update([
                    'price' => $item->product->resolveCartPrice(),
                ]);
            }
        });

        return $cart->fresh('items.product');
    }

    /**
     * Kiểm tra giỏ hàng trước khi tạo đơn.
     *
     * @return array<string> Danh sách lỗi (rỗng nếu hợp lệ)
     */
    public function validateCart(Cart $cart, bool $skipPriceCheck = false): array
    {
        $errors = [];

        $cart->loadMissing('items.product.currentFlashSaleItem.flashSale');

        if ($cart->items->isEmpty()) {
            $errors[] = 'Giỏ hàng không có sản phẩm nào.';

            return $errors;
        }

        foreach ($cart->items as $item) {
            /** @var Product|null $product */
            $product = $item->product;

            if (! $product) {
                $errors[] = 'Một sản phẩm trong giỏ không còn tồn tại.';

                continue;
            }

            if (! $product->is_active) {
                $errors[] = sprintf('Sản phẩm "%s" đã ngừng kinh doanh.', $product->name);

                continue;
            }

            $requestedQty = (int) ($item->quantity ?? 0);

            if ($requestedQty <= 0) {
                $errors[] = sprintf('Số lượng của sản phẩm "%s" không hợp lệ.', $product->name);

                continue;
            }

            if (! is_null($product->stock_quantity) && $requestedQty > $product->stock_quantity) {
                $errors[] = sprintf(
                    'Sản phẩm "%s" chỉ còn %d sản phẩm trong kho.',
                    $product->name,
                    (int) $product->stock_quantity
                );
            }

            if (! $skipPriceCheck) {
                $resolvedPrice = $product->resolveCartPrice();
                if ((float) $item->price !== (float) $resolvedPrice) {
                    $errors[] = sprintf(
                        'Giá của sản phẩm "%s" đã thay đổi, vui lòng tải lại giỏ hàng.',
                        $product->name
                    );
                }
            }
        }

        return array_values(array_unique($errors));
    }
}


