<?php

namespace Tests\Unit\UploadService;

use App\Services\UploadService;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    private UploadService $uploadService;

    public function setUp(): void
    {
        parent::setUp();
        $this->app->register(FilesystemServiceProvider::class);
        $this->uploadService = $this->app->make(UploadService::class);
        Storage::fake('s3');
    }

    public function testUploadToS3()
    {
        // Arrange
        $imageUrl = 'https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png';
        $expectedFileName = 'googlelogo_color_272x92dp.png';

        // Act
        $result = $this->uploadService->uploadToS3($imageUrl);

        // Assert
        Storage::disk('s3')->assertExists($expectedFileName);
        $this->assertEquals($expectedFileName, $result);
    }

    //    public function uploadToS3(string $imageUrl): string
    //    {
    //        $fileName = $this->getFileName($imageUrl);
    //
    //        Storage::disk('s3')->put($fileName, file_get_contents($imageUrl), 'public');
    //
    //        return $fileName;
    //    }
}
