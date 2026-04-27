<?php

namespace App\Services;

class UploadService
{
    protected array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    public function upload($file, $folder = 'others'): string
    {
        // 1. Validate extension
        $ext = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($ext, $this->allowedExtensions)) {
            throw new \Exception("File type [{$ext}] is not allowed.");
        }

        // 2. Tạo thư mục nếu chưa có
        $path = public_path("uploads/{$folder}");
        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }

        // 3. Lưu file
        $filename = uniqid() . '.' . $ext;

        file_put_contents(
            $path . '/' . $filename,
            file_get_contents($file->getRealPath())
        );

        return "uploads/{$folder}/{$filename}";
    }
}
