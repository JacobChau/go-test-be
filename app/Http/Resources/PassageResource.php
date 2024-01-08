<?php

namespace App\Http\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

class PassageResource extends JsonApiResource
{
    /**
     * @var string[]
     */
    protected array $attributes = [
        'title',
    ];
}
