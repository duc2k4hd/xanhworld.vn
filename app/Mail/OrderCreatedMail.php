<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {
    }

    public function envelope(): Envelope
    {
        $siteName = \App\Models\Setting::where('key', 'site_name')->value('value') ?? config('app.name');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name', $siteName);

        if (empty($fromAddress) || ! filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            $appUrl = config('app.url', 'http://localhost');
            $host = parse_url($appUrl, PHP_URL_HOST);
            $fromAddress = env('MAIL_FROM_ADDRESS', 'noreply@'.($host ?: 'localhost'));
        }

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            to: new Address($this->order->receiver_email ?? $this->order->account?->email, $this->order->receiver_name ?? $this->order->account?->name),
            subject: "Đơn hàng #{$this->order->code} đã được tạo - {$siteName}",
        );
    }

    public function content(): Content
    {
        $siteName = \App\Models\Setting::where('key', 'site_name')->value('value') ?? config('app.name');
        $siteUrl = config('app.url');

        return new Content(
            view: 'emails.orders.created',
            with: [
                'order' => $this->order,
                'siteName' => $siteName,
                'siteUrl' => $siteUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

