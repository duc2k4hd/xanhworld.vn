<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Show an order detail page for the authenticated client.
     */
    public function show(Request $request, string $code): View|RedirectResponse
    {
        $accountId = auth('web')->id();

        if (! $accountId) {
            return redirect()->route('client.auth.login')
                ->with('error', 'Vui lòng đăng nhập để xem đơn hàng.');
        }

        $order = Order::query()
            ->with([
                'items.product',
                'shippingAddress',
                'billingAddress',
                'payments',
            ])
            ->where('code', $code)
            ->where('account_id', $accountId)
            ->firstOrFail();

        Product::preloadImages(
            $order->items->pluck('product')->filter()
        );

        $statusFlow = [
            ['key' => 'pending', 'label' => 'Chờ xác nhận', 'description' => 'Đơn hàng đã được ghi nhận tại XWorld.'],
            ['key' => 'confirmed', 'label' => 'Đang xử lý', 'description' => 'Đội ngũ đang chuẩn bị hàng và đóng gói.'],
            ['key' => 'shipping', 'label' => 'Đang vận chuyển', 'description' => 'Đã bàn giao cho đơn vị vận chuyển.'],
            ['key' => 'completed', 'label' => 'Hoàn tất', 'description' => 'Đơn hàng đã được giao thành công.'],
        ];

        $statusAliases = [
            'pending' => 'pending',
            'awaiting_payment' => 'pending',
            'processing' => 'confirmed',
            'confirmed' => 'confirmed',
            'packed' => 'confirmed',
            'shipping' => 'shipping',
            'shipped' => 'shipping',
            'delivered' => 'completed',
            'completed' => 'completed',
        ];

        $normalizedStatus = $statusAliases[$order->status] ?? $order->status ?? 'pending';
        $currentStatusIndex = collect($statusFlow)->pluck('key')->search($normalizedStatus);

        if ($currentStatusIndex === false) {
            $currentStatusIndex = $normalizedStatus === 'cancelled' ? -1 : 0;
        }

        return view('clients.pages.order.detail', [
            'order' => $order,
            'statusFlow' => $statusFlow,
            'currentStatusIndex' => $currentStatusIndex,
            'normalizedStatus' => $normalizedStatus,
        ]);
    }
}
