<?php

namespace App\Services;

class UploadService
{
    public function upload($file, $folder = 'others')
    {
        $path = public_path("uploads/{$folder}");

        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }

        $filename = uniqid() . '.' . $file->getClientOriginalExtension();

        file_put_contents(
            $path . '/' . $filename,
            file_get_contents($file->getRealPath())
        );

        return "{$folder}/{$filename}";
    }
}
