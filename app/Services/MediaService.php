<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MediaType;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaService extends BaseService
{
    public function __construct(Media $subject)
    {
        $this->model = $subject;
    }

    /**
     * Process and save images from given content.
     */
    public function processAndSaveImages(string $content, int $mediableId, string $mediableType): void
    {
        if (preg_match_all('/<img.*?src="([^"]+)"/', $content, $matches)) {
            foreach ($matches[1] as $imageUrl) {
                $this->create([
                    'url' => $imageUrl,
                    'type' => MediaType::Image,
                    'mediable_id' => $mediableId,
                    'mediable_type' => $mediableType,
                ]);
            }
        }
    }

    /**
     * Process, synchronize images with S3 bucket, and save new images from given content.
     */
    public function syncContentImages(string $content, int $mediableId, string $mediableType): void
    {
        $oldImages = $this->model
            ->where('mediable_id', $mediableId)
            ->where('mediable_type', $mediableType)
            ->get();

        $oldImageUrls = $oldImages->pluck('url')->toArray();

        $currentImageUrls = [];
        if (preg_match_all('/<img.*?src="([^"]+)"/', $content, $matches)) {
            $currentImageUrls = $matches[1];

            // Add new images to the database
            foreach ($currentImageUrls as $imageUrl) {
                if (! in_array($imageUrl, $oldImageUrls)) {
                    $this->create([
                        'url' => $imageUrl,
                        'type' => MediaType::Image,
                        'mediable_id' => $mediableId,
                        'mediable_type' => $mediableType,
                    ]);
                }
            }
        }

        $imagesToDelete = array_diff($oldImageUrls, $currentImageUrls);
        if (! empty($imagesToDelete)) {
            $this->model
                ->where('mediable_id', $mediableId)
                ->where('mediable_type', $mediableType)
                ->whereIn('url', $imagesToDelete)
                ->delete();

            $this->syncImagesS3($oldImages, $currentImageUrls);
        }

    }

    protected function syncImagesS3($oldImages, array $currentImageUrls): void
    {
        $imagesToDelete = [];

        foreach ($oldImages as $image) {
            if (! in_array($image->url, $currentImageUrls)) {
                $imageName = basename($image->url);
                $imagesToDelete[] = $imageName;
            }
        }

        if (! empty($imagesToDelete)) {
            Storage::disk('s3')->delete($imagesToDelete);
        }
    }

    protected function removeImagesS3(array $imageUrls): void
    {
        $imagesToDelete = [];

        foreach ($imageUrls as $imageUrl) {
            $imageName = basename($imageUrl);
            $imagesToDelete[] = $imageName;
        }

        if (! empty($imagesToDelete)) {
            Storage::disk('s3')->delete($imagesToDelete);
        }
    }

    public function deleteMedia(string $mediableType, int $mediableId): void
    {
        $media = $this->model
            ->where('mediable_id', $mediableId)
            ->where('mediable_type', $mediableType)
            ->get();

        $imageUrls = $media->pluck('url')->toArray();
        foreach ($media as $medium) {
            $this->delete($medium->id);
        }

        $this->removeImagesS3($imageUrls);
    }
}
