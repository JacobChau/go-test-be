<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Passage extends Model
{
    use HasCreatedBy;

    protected $fillable = ['title', 'content'];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
