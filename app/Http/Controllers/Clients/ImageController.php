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
            abort(404);
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (!file_exists(public_path($path))) {
            abort(404);
        }

        $cacheDir = public_path("clients/assets/img/clothes/resize/{$width}x{$height}");
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $filename = pathinfo($path, PATHINFO_FILENAME) . '.webp';
        $cachePath = "{$cacheDir}/{$filename}";

        // ✅ CACHE HIT
        if (file_exists($cachePath)) {
            return response()->file($cachePath, [
                'Content-Type' => 'image/webp',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        // 🔒 ATOMIC LOCK (IMAGE + SIZE)
        $lockKey = 'resize:lock:' . md5($cachePath);
        $lock = Cache::lock($lockKey, 10); // 10s

        try {
            if ($lock->get()) {

                // double check sau khi lock
                if (!file_exists($cachePath)) {
                    Image::make(public_path($path))
                        ->resize($width, $height, function ($c) {
                            $c->aspectRatio();
                            $c->upsize();
                        })
                        ->encode('webp', 80)
                        ->save($cachePath);
                }

                $lock->release();
            } else {
                // đợi lock (tránh resize trùng)
                sleep(1);
            }
        } finally {
            optional($lock)->release();
        }

        if (!file_exists($cachePath)) {
            abort(500);
        }

        return response()->file($cachePath, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

}
