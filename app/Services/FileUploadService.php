<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    /**
     * رفع ملف جديد والتخزين في الـ Public Disk
     */
    public function upload(?UploadedFile $file, string $folder = 'categories'): ?string
    {
        if (!$file) {
            return null;
        }

        // تخزين الملف في مجلد storage/app/public/categories (أو الـ public disk المعرف)
        $path = $file->store($folder, 'public');

        return $path; // بيحفظ المسار زي: categories/xyz.png
    }

    /**
     * حذف الملف القديم عند التحديث أو الحذف النهائي
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}