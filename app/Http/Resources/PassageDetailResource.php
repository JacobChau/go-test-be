<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
