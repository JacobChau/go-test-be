<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class UploadService
{
    public function uploadToS3(string $imageUrl): string
    {
        $fileName = $this->getFileName($imageUrl);

        Storage::disk('s3')->put($fileName, file_get_contents($imageUrl), 'public');

        return $fileName;
    }
}
