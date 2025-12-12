<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\ImageManagerStatic as Image;

class ImageController extends Controller
{
    public function resize(Request $request)
    {
        $url = $request->query('url');
        $width = (int) $request->query('width', 300);
        $height = (int) $request->query('height', 300);

        if (!$url || $width > 1900 || $height > 1900) {
            return view('clients.pages.errors.404');
        }

        // ❗ GIỮ NGUYÊN LOGIC GỐC
        $path = parse_url($url, PHP_URL_PATH);
        if (!file_exists($path)) {
            return view('clients.pages.errors.404');
        }

        // ======================
        //  CACHE PATH
        // ======================
        $cacheDir = public_path("clients/assets/img/clothes/resize/{$width}x{$height}");
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $filename = pathinfo($path, PATHINFO_FILENAME) . ".webp";
        $cachePath = $cacheDir . '/' . $filename;

        // ✅ CACHE HIT → TRẢ LUÔN (KHÔNG BỊ LIMIT)
        if (file_exists($cachePath)) {
            return response()->file($cachePath, [
                'Content-Type'  => 'image/webp',
                'Cache-Control' => 'public, max-age=31536000'
            ]);
        }

        // ======================
        //  LẤY IP (HỖ TRỢ CLOUDFLARE)
        // ======================
        $ip = $request->header('CF-Connecting-IP') ?? $request->ip();

        // ======================
        // 🔥 LAYER 3: CHECK BLOCK 24H
        // ======================
        $blockedKey = "resize:blocked:{$ip}";
        if (Cache::has($blockedKey)) {
            return response('Your IP is temporarily blocked', 429);
        }

        // ======================
        // 🔒 LAYER 1: IP LIMIT / PHÚT
        // ======================
        $ipMinuteKey = "resize:write:minute:{$ip}";
        $ipMinuteLimit = 10;

        $minuteCount = Cache::add($ipMinuteKey, 1, 60)
            ? 1
            : Cache::increment($ipMinuteKey);

        if ($minuteCount > $ipMinuteLimit) {
            return response('Too many resize requests (minute limit)', 429);
        }

        // ======================
        // 🔒 LAYER 2: IMAGE + SIZE LIMIT
        // ======================
        $imageKey = "resize:write:image:" . md5($cachePath);
        $imageLimit = 2;

        $imageCount = Cache::add($imageKey, 1, 300)
            ? 1
            : Cache::increment($imageKey);

        if ($imageCount > $imageLimit) {
            return response('Too many resize requests (image limit)', 429);
        }

        // ======================
        // 🔥 LAYER 3.1: DAILY CREATE LIMIT
        // ======================
        $dailyKey = "resize:create:daily:{$ip}";
        $dailyLimit = 10;

        $dailyCount = Cache::add($dailyKey, 1, now()->endOfDay())
            ? 1
            : Cache::increment($dailyKey);

        if ($dailyCount > $dailyLimit) {
            Cache::put($blockedKey, true, now()->addDay());
            return response(
                'Daily resize limit exceeded. IP blocked for 24 hours.',
                429
            );
        }

        // ======================
        //  RESIZE (LOGIC GỐC)
        // ======================
        $img = Image::make($path)
            ->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 80);

        // ======================
        // 🔥 GHI CACHE (AN TOÀN)
        // ======================
        file_put_contents($cachePath, $img);

        return response($img, 200, [
            'Content-Type'  => 'image/webp',
            'Cache-Control' => 'public, max-age=31536000'
        ]);
    }
}
