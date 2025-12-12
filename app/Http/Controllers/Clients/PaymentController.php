<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Hiển thị trang thanh toán thành công.
     */
    public function return(Request $request)
    {
        Log::info('PayOS Return Callback', $request->all());

        $orderCode = $request->query('orderCode');
        $status = $request->query('status');
        $message = $request->query('message');

        $payment = Payment::with(['order.items.product', 'order.shippingAddress'])
            ->where('transaction_code', $orderCode)
            ->where('method', 'payos')
            ->first();

        if (! $payment || ! $payment->order) {
            return view('clients.pages.payment.cancel', [
                'orderCode' => $orderCode,
                'message' => 'Không tìm thấy thông tin đơn hàng. Vui lòng liên hệ XWorld Garden để được hỗ trợ.',
            ]);
        }

        $order = $payment->order;
        $order->loadMissing(['billingAddress', 'payments']);

        if ($status !== 'PAID') {
            return view('clients.pages.payment.cancel', [
                'orderCode' => $order->code,
                'message' => $message ?? 'Thanh toán chưa hoàn tất. Vui lòng thử lại hoặc chọn phương thức khác.',
                'order' => $order,
            ]);
        }

        // ============================
        // Đồng bộ trạng thái khi return (fallback khi webhook chưa cấu hình)
        // ============================
        try {
            // Cập nhật payment nếu chưa success
            if ($payment->status !== 'success') {
                $raw = $payment->raw_response ?? [];
                if (! is_array($raw)) {
                    $raw = [];
                }

                $payment->update([
                    'status' => 'success',
                    'paid_at' => now(),
                    'raw_response' => array_merge($raw, [
                        'return_callback' => $request->all(),
                        'return_synced_at' => now()->toISOString(),
                    ]),
                ]);
            }

            // Cập nhật đơn hàng nếu chưa đánh dấu đã thanh toán
            if ($order->payment_status !== 'paid') {
                // Khi thanh toán thành công, luôn set status = 'processing'
                // Đảm bảo logic đồng nhất với PayOSService->handlePaymentSuccess()
                // (PayOSService luôn set status = 'processing' khi thanh toán thành công)
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                ]);
            }

            $order->refresh();
        } catch (\Throwable $e) {
            Log::error('Failed to sync order/payment status on PayOS return', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'message' => $e->getMessage(),
            ]);
        }

        return view('clients.pages.payment.success', [
            'order' => $order,
            'payment' => $payment,
            'message' => $message ?? 'Thanh toán thành công! Đơn hàng của bạn đang được xử lý.',
        ]);
    }

    /**
     * Trang hủy thanh toán.
     */
    public function cancel(Request $request)
    {
        Log::info('PayOS Cancel Callback', $request->all());

        $orderCode = $request->query('orderCode');
        $message = $request->query('message');

        $payment = Payment::with('order')
            ->where('transaction_code', $orderCode)
            ->where('method', 'payos')
            ->first();

        return view('clients.pages.payment.cancel', [
            'orderCode' => optional($payment?->order)->code ?? $orderCode,
            'message' => $message ?? 'Bạn đã hủy thanh toán. Đơn hàng tạm thời chưa được xử lý.',
        ]);
    }
}
