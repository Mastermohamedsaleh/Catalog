<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function uploadAndOptimizeImage(?UploadedFile $file, string $folder = 'products/images'): ?string
    {
        if (!$file) {
            return null;
        }

        $filename = Str::uuid() . '.webp';
        $path = $folder . '/' . $filename;

        $sourcePath = $file->getRealPath();
        list($width, $height, $type) = getimagesize($sourcePath);

        // إنشاء صورة من المصدر حسب نوعها
        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                imagepalettetotruecolor($sourceImage);
                imagealphablending($sourceImage, true);
                imagesavealpha($sourceImage, true);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return $file->store($folder, 'public');
        }

        // تصغير الأبعاد لو العرض أكبر من 1200 بيكسل مع الحفاظ على الأبعاد
        $maxAllowedWidth = 1200;
        if ($width > $maxAllowedWidth) {
            $newWidth = $maxAllowedWidth;
            $newHeight = floor($height * ($maxAllowedWidth / $width));
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        $virtualImage = imagecreatetruecolor($newWidth, $newHeight);
        
        imagealphablending($virtualImage, false);
        imagesavealpha($virtualImage, true);
        
        imagecopyresampled($virtualImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // حفظ الصورة في الـ Temp وتحويلها لـ WebP بجودة 80%
        $tempPath = sys_get_temp_dir() . '/' . $filename;
        imagewebp($virtualImage, $tempPath, 80);

        // رفعها لتخزين لارافيل
        Storage::disk('public')->put($path, file_get_contents($tempPath));

        // تنظيف الذاكرة
        imagedestroy($sourceImage);
        imagedestroy($virtualImage);
        @unlink($tempPath);

        return $path;
    }

    public function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}