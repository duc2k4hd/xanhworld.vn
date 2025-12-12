<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;

class ImageController extends Controller
{
    public function resize(Request $request)
    {
        $url = $request->query('url');
        $width = (int) $request->query('width', 300);
        $height = (int) $request->query('height', 300);
    
        if(!$url || $width > 1900 || $height > 1900) {
            return view('clients.pages.errors.404');
        }
    
        // ❗ GIỮ NGUYÊN CODE GỐC CỦA BẠN
        $path = parse_url($url, PHP_URL_PATH);
        if (!file_exists($path)) {
            return view('clients.pages.errors.404');
        }
    
        // ======================
        //  THÊM CACHE TỐI ƯU
        // ======================
    
        // Tạo thư mục cache
        $cacheDir = public_path("clients/assets/img/clothes/resize/{$width}x{$height}");
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
    
        // Tạo tên file cache = tên ảnh gốc nhưng convert sang .webp
        $filename = pathinfo($path, PATHINFO_FILENAME) . ".webp";
        $cachePath = $cacheDir . '/' . $filename;
    
        // 🔥 Nếu file cache đã tồn tại → trả về luôn (cực nhanh)
        if (file_exists($cachePath)) {
            return response()->file($cachePath, [
                'Content-Type' => 'image/webp',
                'Cache-Control' => 'public, max-age=31536000'
            ]);
        }
    
        // ======================
        //  CHẠY RESIZE (CHỈ 1 LẦN)
        // ======================
    
        $img = \Intervention\Image\Facades\Image::make($path)
            ->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();  // Giữ tỷ lệ gốc
                $constraint->upsize();       // Không phóng to quá mức
            })
            ->encode('webp', 80);
    
        // Lưu cache
        file_put_contents($cachePath, $img);
    
        return response($img)->header('Content-Type', 'image/webp');
    }
}
