<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MediaType;
use App\Models\Media;

class MediaService extends BaseService
{
    public function __construct(Media $subject)
    {
        $this->model = $subject;
    }

    /**
     * Process and save images from given content.
     *
     * @param string $content
     * @param int $mediableId
     * @param string $mediableType
     */
    public function processImages(string $content, int $mediableId, string $mediableType): void
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
}
