<?php

namespace App\Http\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

class PassageDetailResource extends JsonApiResource
{
    /**
     * @var string[]
     */
    protected array $attributes = [
        'title',
        'content',
    ];
}
