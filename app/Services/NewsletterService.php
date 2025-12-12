<?php

namespace App\Services;

use App\Models\Newsletter;
use App\Helpers\EmailHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NewsletterService
{
    public function sendVerifyEmail(Newsletter $subscription): void
    {
        if (! $subscription->email || ! $subscription->verify_token) {
            return;
        }

        try {
            // Lấy from từ cấu hình email (ưu tiên EmailAccount, fallback .env)
            $fromAddress = EmailHelper::getFromEmail();
            $fromName = EmailHelper::getFromName() ?: config('app.name');

            $verifyUrl = route('newsletter.verify', ['token' => $subscription->verify_token]);

            Mail::send('emails.newsletters.verify', [
                'subscription' => $subscription,
                'verifyUrl' => $verifyUrl,
            ], function ($message) use ($subscription, $fromAddress, $fromName): void {
                $message->from($fromAddress, $fromName)
                    ->to($subscription->email)
                    ->subject('Xác nhận đăng ký nhận bản tin - ' . config('app.name'));
            });
        } catch (\Throwable $e) {
            Log::error('Failed to send newsletter verify email', [
                'email' => $subscription->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Gửi email marketing hàng loạt.
     *
     * @return array{total:int,success:array<int,int>,failed:array<int,int>}
     */
    public function sendMarketingEmail(
        array $subscriptionIds,
        string $subject,
        string $template,
        array $emailData,
        int $emailAccountId
    ): array {
        $success = [];
        $failed = [];

        // Chia nhỏ ID theo lô để tránh load quá nhiều bản ghi cùng lúc (ví dụ 1tr email)
        $chunks = array_chunk($subscriptionIds, 1000);

        foreach ($chunks as $idChunk) {
            $subscriptions = Newsletter::whereIn('id', $idChunk)
                ->subscribed()
                ->get();

            foreach ($subscriptions as $subscription) {
                try {
                    // Với newsletter marketing, luôn ưu tiên lấy từ env (MAIL_USERNAME / MAIL_FROM_ADDRESS)
                    $fromAddress = trim((string) (
                        env('MAIL_USERNAME')
                        ?: env('MAIL_FROM_ADDRESS')
                        ?: EmailHelper::getFromEmail($emailAccountId)
                    ));

                    if ($fromAddress === '') {
                        $fromAddress = 'xanhworldvietnam@gmail.com';
                    }

                    $fromName = env('MAIL_FROM_NAME')
                        ?: EmailHelper::getFromName($emailAccountId)
                        ?: config('app.name');

                    // Với số lượng lớn nên cân nhắc dùng queue driver (db/redis) thay vì gửi sync.
                    Mail::send('emails.newsletters.marketing', array_merge($emailData, [
                        'subscription' => $subscription,
                    ]), function ($message) use ($subscription, $subject, $fromAddress, $fromName): void {
                        $message->from($fromAddress, $fromName)
                            ->to($subscription->email)
                            ->subject($subject);
                    });

                    $success[] = $subscription->id;
                } catch (\Throwable $e) {
                    $failed[] = $subscription->id;

                    Log::error('Failed to send marketing newsletter', [
                        'email' => $subscription->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return [
            'total' => count($subscriptionIds),
            'success' => $success,
            'failed' => $failed,
        ];
    }
}


